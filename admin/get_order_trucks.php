<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Superadmin', 'Staff'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID specified.']);
    exit;
}

try {
    // 1. Fetch order details
    $stmt = $pdo->prepare("
        SELECT o.*, 
               u.username AS checker_username,
               CONCAT(c.first_name, ' ', c.last_name) AS checker_full_name
        FROM orders o
        LEFT JOIN users u ON u.id = o.checker_id
        LEFT JOIN checkers c ON c.id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // 2. Fetch gravel type labels
    $_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $gravelTypeLabels = [];
    foreach ($_gravel_rows as $_g) {
        $gravelTypeLabels[$_g['type_key']] = $_g['label'];
    }
    $gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];

    // 3. Query dispatches linked to this order
    $orderDispatches = [];
    try {
        $dispStmt = $pdo->prepare("
            SELECT 
                d.id AS dispatch_id,
                d.ticket_number,
                d.truck_id,
                t.truck_code,
                t.plate_number,
                d.driver_id,
                CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
                d.cubic_meters,
                d.status,
                COALESCE(d.transit_end_time, d.created_at) AS delivery_time,
                d.created_at AS dispatch_time
            FROM dispatches d
            LEFT JOIN trucks t ON t.id = d.truck_id
            LEFT JOIN drivers dr ON dr.id = d.driver_id
            WHERE (d.order_id = ? OR (d.order_id IS NULL AND d.destination = ? AND d.client_name = ? AND d.created_at >= ?))
              AND d.status NOT IN ('Cancelled')
            ORDER BY delivery_time ASC, d.id ASC
        ");
        $dispStmt->execute([
            $order_id, 
            $order['destination'], 
            $order['client_name'], 
            date('Y-m-d 00:00:00', strtotime($order['created_at']))
        ]);
        $orderDispatches = $dispStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}

    // 4. Query order_scans for this order
    $orderScans = [];
    try {
        $scanStmt = $pdo->prepare("
            SELECT 
                os.id AS scan_id,
                os.truck_id,
                t.truck_code,
                t.plate_number,
                os.checker_id,
                COALESCE(NULLIF(TRIM(CONCAT(c.first_name, ' ', c.last_name)), ''), u.username) AS checker_name,
                os.scanned_at,
                os.dispatch_id,
                os.cubic_meters
            FROM order_scans os
            LEFT JOIN trucks t ON t.id = os.truck_id
            LEFT JOIN users u ON u.id = os.checker_id
            LEFT JOIN checkers c ON c.id = u.id
            WHERE os.order_id = ?
            ORDER BY os.scanned_at ASC, os.id ASC
        ");
        $scanStmt->execute([$order_id]);
        $orderScans = $scanStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}

    // 5. Unify delivery logs (match dispatches & scans)
    $deliveryLogs = [];
    $usedScanIds = [];

    $orderCheckerName = trim($order['checker_full_name'] ?? '') ?: ($order['checker_username'] ?? 'Authorized Staff');

    foreach ($orderDispatches as $disp) {
        $matchedScan = null;
        // Priority 1: Match by dispatch_id
        foreach ($orderScans as $sc) {
            if (!in_array($sc['scan_id'], $usedScanIds)) {
                if (!empty($sc['dispatch_id']) && $sc['dispatch_id'] == $disp['dispatch_id']) {
                    $matchedScan = $sc;
                    break;
                }
            }
        }
        // Priority 2: Match by truck_id
        if (!$matchedScan) {
            foreach ($orderScans as $sc) {
                if (!in_array($sc['scan_id'], $usedScanIds)) {
                    if ($sc['truck_id'] == $disp['truck_id']) {
                        $matchedScan = $sc;
                        break;
                    }
                }
            }
        }

        if ($matchedScan) {
            $usedScanIds[] = $matchedScan['scan_id'];
        }

        $cMeters = floatval($disp['cubic_meters'] ?? 0);
        if ($cMeters <= 0 && $matchedScan && floatval($matchedScan['cubic_meters'] ?? 0) > 0) {
            $cMeters = floatval($matchedScan['cubic_meters']);
        }
        if ($cMeters <= 0) $cMeters = 10.00;

        $deliveryLogs[] = [
            'dispatch_id'   => $disp['dispatch_id'],
            'scan_id'       => $matchedScan ? $matchedScan['scan_id'] : null,
            'ticket_number' => $disp['ticket_number'] ?: ('TKT-' . $disp['dispatch_id']),
            'truck_id'      => $disp['truck_id'],
            'truck_code'    => $disp['truck_code'] ?: ('Truck #' . $disp['truck_id']),
            'plate_number'  => $disp['plate_number'] ?: '',
            'driver_name'   => trim($disp['driver_name'] ?? '') ?: 'Assigned Driver',
            'checker_name'  => $matchedScan['checker_name'] ?? $orderCheckerName,
            'cubic_meters'  => $cMeters,
            'timestamp'     => $matchedScan['scanned_at'] ?? $disp['delivery_time'],
            'status'        => $disp['status']
        ];
    }

    // Remaining scans without matching dispatch
    foreach ($orderScans as $sc) {
        if (!in_array($sc['scan_id'], $usedScanIds)) {
            $cMeters = floatval($sc['cubic_meters'] ?? 0);
            if ($cMeters <= 0) $cMeters = 10.00;

            $deliveryLogs[] = [
                'dispatch_id'   => $sc['dispatch_id'],
                'scan_id'       => $sc['scan_id'],
                'ticket_number' => !empty($sc['dispatch_id']) ? ('TKT-' . $sc['dispatch_id']) : 'RFID-SCAN',
                'truck_id'      => $sc['truck_id'],
                'truck_code'    => $sc['truck_code'] ?: ('Truck #' . $sc['truck_id']),
                'plate_number'  => $sc['plate_number'] ?: '',
                'driver_name'   => 'Assigned Driver',
                'checker_name'  => $sc['checker_name'] ?: $orderCheckerName,
                'cubic_meters'  => $cMeters,
                'timestamp'     => $sc['scanned_at'],
                'status'        => 'Delivered'
            ];
        }
    }

    // Sort deliveries newest first for display
    usort($deliveryLogs, function($a, $b) {
        return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
    });

    // 6. Aggregate by truck
    $truckMap = [];
    $totalContributedVol = 0.0;

    foreach ($deliveryLogs as $log) {
        $key = $log['truck_id'] ?: $log['truck_code'];
        if (!isset($truckMap[$key])) {
            $truckMap[$key] = [
                'truck_id'           => $log['truck_id'],
                'truck_code'         => $log['truck_code'],
                'plate_number'       => $log['plate_number'],
                'trips_count'        => 0,
                'total_cubic_meters' => 0.0,
                'drivers'            => [],
                'latest_timestamp'   => $log['timestamp'],
                'latest_status'      => $log['status'],
            ];
        }
        $truckMap[$key]['trips_count']++;
        $truckMap[$key]['total_cubic_meters'] += floatval($log['cubic_meters']);
        $totalContributedVol += floatval($log['cubic_meters']);

        if (!empty($log['driver_name']) && !in_array($log['driver_name'], $truckMap[$key]['drivers'])) {
            $truckMap[$key]['drivers'][] = $log['driver_name'];
        }
        if (strtotime($log['timestamp']) > strtotime($truckMap[$key]['latest_timestamp'])) {
            $truckMap[$key]['latest_timestamp'] = $log['timestamp'];
            $truckMap[$key]['latest_status'] = $log['status'];
        }
    }

    $trucksList = array_values($truckMap);
    usort($trucksList, function($a, $b) {
        if ($b['total_cubic_meters'] !== $a['total_cubic_meters']) {
            return $b['total_cubic_meters'] <=> $a['total_cubic_meters'];
        }
        return $b['trips_count'] <=> $a['trips_count'];
    });

    $reqCm = floatval($order['cubic_meters_required'] ?? 0) > 0 ? floatval($order['cubic_meters_required']) : floatval($order['trucks_required']);
    $doneCm = floatval($order['cubic_meters_fulfilled'] ?? 0) > 0 ? floatval($order['cubic_meters_fulfilled']) : floatval($order['trucks_fulfilled']);
    if ($doneCm <= 0 && $totalContributedVol > 0) {
        $doneCm = $totalContributedVol;
    }
    $remCm = max(0.0, $reqCm - $doneCm);
    $pct = $reqCm > 0 ? round(($doneCm / $reqCm) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'order'   => [
            'id'                     => intval($order['id']),
            'order_number'           => $order['order_number'],
            'client_name'            => $order['client_name'],
            'contact_number'         => $order['contact_number'],
            'destination'            => $order['destination'],
            'landmark'               => $order['landmark'],
            'gravel_type'            => $order['gravel_type'],
            'gravel_label'           => $gravelLabel,
            'cubic_meters_required'  => $reqCm,
            'cubic_meters_fulfilled' => $doneCm,
            'remaining_cubic_meters' => $remCm,
            'percent_fulfilled'      => $pct,
            'status'                 => $order['status'],
            'created_at'             => $order['created_at'],
            'checker_name'           => $orderCheckerName,
            'notes'                  => $order['notes'] ?? ''
        ],
        'summary' => [
            'total_trucks'       => count($trucksList),
            'total_trips'        => count($deliveryLogs),
            'total_cubic_meters' => round($totalContributedVol, 2),
            'percent_fulfilled'  => $pct
        ],
        'trucks'      => $trucksList,
        'deliveries'  => $deliveryLogs
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving contributing trucks: ' . $e->getMessage()
    ]);
}

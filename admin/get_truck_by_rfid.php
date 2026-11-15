<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Superadmin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']);

    $stmt = $pdo->prepare("SELECT id, truck_code, status, rfid_tag FROM trucks WHERE rfid_tag = ? LIMIT 1");
    $stmt->execute([$rfid]);
    $truck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($truck) {
        $driverStmt = $pdo->prepare("
            SELECT id, CONCAT(first_name, ' ', last_name) AS name, status 
            FROM drivers 
            WHERE truck_id = ? AND status != 'Resigned'
            ORDER BY id ASC
        ");
        $driverStmt->execute([$truck['id']]);
        $drivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);

        $driverCount = count($drivers);
        $primaryDriver = $driverCount > 0 ? $drivers[0] : null;

        $todayDispStmt = $pdo->prepare("SELECT COUNT(*) FROM dispatches WHERE truck_id = ? AND dispatch_date = CURDATE()");
        $todayDispStmt->execute([$truck['id']]);
        $todayDispatches = (int)$todayDispStmt->fetchColumn();

        // Calculate queue info for idle waiting drivers today
        $queueStmt = $pdo->query("
            SELECT 
                d.id AS driver_id,
                d.truck_id,
                t.truck_code,
                t.rfid_tag,
                CONCAT(d.first_name, ' ', d.last_name) AS driver_name,
                d.queue_entry_time AS effective_queue_time
            FROM drivers d
            JOIN users u ON u.id = d.id
            LEFT JOIN trucks t ON t.id = d.truck_id
            WHERE d.status NOT IN ('Resigned', 'Suspended', 'On Leave')
              AND d.queue_entry_time IS NOT NULL
              AND DATE(d.queue_entry_time) = CURDATE()
              AND NOT EXISTS (
                  SELECT 1 FROM dispatches d2
                  WHERE d2.driver_id = d.id
                    AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
              )
            ORDER BY d.queue_entry_time ASC, d.id ASC
        ");
        $allQueue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

        $queuePosition = null;
        $priorityTruck = !empty($allQueue) ? $allQueue[0] : null;
        foreach ($allQueue as $idx => $qRow) {
            if (intval($qRow['truck_id']) === intval($truck['id'])) {
                $queuePosition = $idx + 1;
                break;
            }
        }

        $isPriority = ($queuePosition === 1 || empty($allQueue));

        echo json_encode([
            'success'              => true,
            'truck_id'             => $truck['id'],
            'truck_code'           => $truck['truck_code'],
            'status'               => $truck['status'],
            'driver_count'         => $driverCount,
            'drivers'              => $drivers,
            'driver_id'            => $primaryDriver ? $primaryDriver['id'] : null,
            'driver_name'          => $primaryDriver ? $primaryDriver['name'] : 'No Driver Assigned',
            'today_dispatches'     => $todayDispatches,
            'queue_position'       => $queuePosition,
            'queue_total'          => count($allQueue),
            'priority_truck_code'  => $priorityTruck ? $priorityTruck['truck_code'] : null,
            'priority_driver_name' => $priorityTruck ? $priorityTruck['driver_name'] : null,
            'is_priority'          => $isPriority
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No truck found for this RFID tag']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No RFID provided']);
}

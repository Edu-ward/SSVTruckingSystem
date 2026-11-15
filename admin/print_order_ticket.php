<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Superadmin'])) {
    die("Unauthorized Access");
}

if (!isset($_GET['id'])) die("Order ID not specified.");

$order_id = intval($_GET['id']);
$stmt = $pdo->prepare("
    SELECT o.*, u.username AS checker_name, CONCAT(c.first_name, ' ', c.last_name) AS checker_full_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.checker_id
    LEFT JOIN checkers c ON c.id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) die("Order not found.");

try {
    $chkCol = $pdo->query("SHOW COLUMNS FROM `order_scans` LIKE 'dispatch_id'")->fetch();
    if (!$chkCol) {
        $pdo->exec("ALTER TABLE `order_scans` ADD COLUMN `dispatch_id` INT DEFAULT NULL");
    }
    $chkCol2 = $pdo->query("SHOW COLUMNS FROM `order_scans` LIKE 'cubic_meters'")->fetch();
    if (!$chkCol2) {
        $pdo->exec("ALTER TABLE `order_scans` ADD COLUMN `cubic_meters` DECIMAL(10,2) DEFAULT '0.00'");
    }
} catch (Throwable $e) {
}

$_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$gravelTypeLabels = [];
foreach ($_gravel_rows as $_g) {
    $gravelTypeLabels[$_g['type_key']] = $_g['label'];
}
$gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];

$orderDispatches = [];
try {
    $dispStmt = $pdo->prepare("
        SELECT 
            d.id AS dispatch_id,
            d.ticket_number,
            d.truck_id,
            t.truck_code,
            d.driver_id,
            CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
            d.cubic_meters,
            d.status,
            COALESCE(d.transit_end_time, d.created_at) AS delivery_time
        FROM dispatches d
        LEFT JOIN trucks t ON t.id = d.truck_id
        LEFT JOIN drivers dr ON dr.id = d.driver_id
        WHERE (d.order_id = ? OR (d.order_id IS NULL AND d.destination = ? AND d.client_name = ? AND d.created_at >= ?))
          AND d.status IN ('Delivered', 'In Transit')
        ORDER BY delivery_time ASC, d.id ASC
    ");
    $dispStmt->execute([$order_id, $order['destination'], $order['client_name'], date('Y-m-d 00:00:00', strtotime($order['created_at']))]);
    $orderDispatches = $dispStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$orderScans = [];
try {
    $scanStmt = $pdo->prepare("
        SELECT 
            os.id AS scan_id,
            os.truck_id,
            t.truck_code,
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
} catch (Throwable $e) {
}

$deliveryLogs = [];
$usedScanIds = [];

foreach ($orderDispatches as $disp) {
    $matchedScan = null;
    foreach ($orderScans as $sc) {
        if (!in_array($sc['scan_id'], $usedScanIds)) {
            if (!empty($sc['dispatch_id']) && $sc['dispatch_id'] == $disp['dispatch_id']) {
                $matchedScan = $sc;
                break;
            }
        }
    }
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
        'ticket_number' => $disp['ticket_number'] ?: ('TKT-' . $disp['dispatch_id']),
        'truck_code'    => $disp['truck_code'] ?: ('Truck #' . $disp['truck_id']),
        'cubic_meters'  => $cMeters,
        'driver_name'   => trim($disp['driver_name'] ?? '') ?: 'Assigned Driver',
        'checker_name'  => $matchedScan['checker_name'] ?? (trim($order['checker_full_name'] ?? '') ?: ($order['checker_name'] ?? 'Authorized Staff')),
        'timestamp'     => $matchedScan['scanned_at'] ?? $disp['delivery_time'],
        'status'        => $disp['status']
    ];
}

foreach ($orderScans as $sc) {
    if (!in_array($sc['scan_id'], $usedScanIds)) {
        $deliveryLogs[] = [
            'ticket_number' => !empty($sc['dispatch_id']) ? ('TKT-' . $sc['dispatch_id']) : 'RFID-SCAN',
            'truck_code'    => $sc['truck_code'] ?: ('Truck #' . $sc['truck_id']),
            'cubic_meters'  => floatval($sc['cubic_meters'] ?? 0) > 0 ? floatval($sc['cubic_meters']) : 10.00,
            'driver_name'   => 'Assigned Driver',
            'checker_name'  => $sc['checker_name'] ?? (trim($order['checker_full_name'] ?? '') ?: ($order['checker_name'] ?? 'Authorized Staff')),
            'timestamp'     => $sc['scanned_at'],
            'status'        => 'Delivered'
        ];
    }
}

$reqCm = floatval($order['cubic_meters_required'] ?? 0) > 0 ? floatval($order['cubic_meters_required']) : floatval($order['trucks_required'] ?? 1);
$doneCm = floatval($order['cubic_meters_fulfilled'] ?? 0);
$sumLoggedCm = 0;
foreach ($deliveryLogs as $dl) {
    if ($dl['status'] === 'Delivered') {
        $sumLoggedCm += $dl['cubic_meters'];
    }
}

if ($sumLoggedCm > $doneCm) {
    $doneCm = $sumLoggedCm;
    try {
        $pdo->prepare("UPDATE orders SET cubic_meters_fulfilled = ?, trucks_fulfilled = ? WHERE id = ?")
            ->execute([$doneCm, count($deliveryLogs), $order_id]);
    } catch (Throwable $e) {
    }
} elseif ($doneCm <= 0 && $sumLoggedCm > 0) {
    $doneCm = $sumLoggedCm;
}

$remainingCm = max(0, $reqCm - $doneCm);
$avgVolume = count($deliveryLogs) > 0 ? ($doneCm / count($deliveryLogs)) : 15.0;
if ($avgVolume <= 0) $avgVolume = 15.0;
$pendingTripsCount = $remainingCm > 0 ? max(1, (int)ceil($remainingCm / $avgVolume)) : 0;
$pctFulfilled = $reqCm > 0 ? min(100, round(($doneCm / $reqCm) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Ticket — <?= htmlspecialchars($order['order_number']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            background: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }

        .ticket-container {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-gray-900">

    <div class="no-print bg-gray-900 text-white p-4 flex justify-between items-center fixed top-0 w-full z-10 shadow-md">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-print text-blue-400"></i>
            <span class="font-semibold">Order Ticket — <?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="space-x-2">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">Close Tab</button>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-sm font-semibold transition shadow"><i class="fa-solid fa-print mr-2"></i>Print Now</button>
        </div>
    </div>

    <div class="ticket-container mt-20">

        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <div class="flex items-center mb-1">
                    <img src="../assets/ssvLogo.png" alt="SSV Logo" class="h-16 w-auto mr-4">
                    <h1 class="text-4xl font-bold tracking-tight">SSV Trucking</h1>
                </div>
                <p class="text-sm text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-sm text-gray-600">Gravel Delivery Order Ticket</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-widest mb-1">ORDER TICKET</h2>
                <p class="text-sm text-gray-500 font-mono tracking-widest mt-2"><?= htmlspecialchars($order['order_number']) ?></p>
                <p class="text-xs text-gray-400 mt-1">Issued: <?= date('F d, Y', strtotime($order['created_at'])) ?></p>
                <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?= $pctFulfilled >= 100 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300' ?>">
                    <?= $pctFulfilled >= 100 ? 'Fully Fulfilled' : 'In Progress (' . $pctFulfilled . '%)' ?>
                </span>
            </div>
        </div>


        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-50 p-4 border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Client Details</p>
                <p class="font-semibold text-xl text-gray-800"><?= htmlspecialchars($order['client_name']) ?></p>
                <?php if (!empty($order['contact_number'])): ?>
                    <p class="text-sm text-gray-600 mt-1"><span class="font-semibold text-gray-800">Contact:</span> <?= htmlspecialchars($order['contact_number']) ?></p>
                <?php endif; ?>
                <div class="mt-3 pt-3 border-t border-gray-200 space-y-1">
                    <p class="text-sm text-gray-600"><span class="font-semibold text-gray-800">Destination:</span> <?= htmlspecialchars($order['destination']) ?></p>
                    <?php if (!empty($order['landmark'])): ?>
                        <p class="text-sm text-amber-800"><span class="font-semibold">Landmark:</span> <?= htmlspecialchars($order['landmark']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-gray-50 p-4 border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Order Details</p>
                <table class="w-full text-sm">
                    <tr>
                        <td class="text-gray-600 py-1.5 border-b border-gray-200">Gravel Type:</td>
                        <td class="text-right font-semibold"><?= htmlspecialchars($gravelLabel) ?></td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 py-1.5 border-b border-gray-200">Cubic Meters Required:</td>
                        <td class="text-right font-bold text-lg"><?= number_format($reqCm, 2) ?> cu.m</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 py-1.5">Cubic Meters Fulfilled:</td>
                        <td class="text-right font-bold text-lg <?= $pctFulfilled >= 100 ? 'text-emerald-700' : 'text-blue-700' ?>">
                            <?= number_format($doneCm, 2) ?> cu.m
                            <span class="text-xs font-normal text-gray-500 block">(<?= $pctFulfilled ?>% completed)</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <?php if (!empty($order['notes'])): ?>
            <div class="border border-gray-300 rounded p-4 mb-8 bg-yellow-50">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wide mb-1">Notes / Special Instructions</p>
                <p class="text-sm text-gray-800"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
        <?php endif; ?>


        <div class="border border-gray-300 rounded-lg overflow-hidden mb-10">
            <div class="bg-gray-100 px-4 py-2.5 border-b border-gray-300 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800">Truck Delivery Log</h3>
                    <p class="text-[11px] text-gray-500">Itemized log of every truck delivery trip and verified gravel volume</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-gray-700"><?= number_format($doneCm, 2) ?> / <?= number_format($reqCm, 2) ?> cu.m fulfilled</span>
                    <span class="text-[11px] text-gray-500 block"><?= count($deliveryLogs) ?> delivered trip<?= count($deliveryLogs) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-[11px] text-gray-600 uppercase border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2.5 text-center w-8">#</th>
                        <th class="px-3 py-2.5 text-left">Truck</th>
                        <th class="px-3 py-2.5 text-left">Waybill / Ticket</th>
                        <th class="px-3 py-2.5 text-right">Volume</th>
                        <th class="px-3 py-2.5 text-left">Driver</th>
                        <th class="px-3 py-2.5 text-left">Checker</th>
                        <th class="px-3 py-2.5 text-left">Date & Time</th>
                        <th class="px-3 py-2.5 text-center w-20">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($deliveryLogs)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">No delivery trips recorded yet.</td>
                        </tr>
                        <?php else: foreach ($deliveryLogs as $i => $log): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-3 py-2.5 text-center font-bold text-gray-400"><?= $i + 1 ?></td>
                                <td class="px-3 py-2.5 font-bold text-gray-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck text-indigo-500 text-[10px]"></i>
                                    <span><?= htmlspecialchars($log['truck_code']) ?></span>
                                </td>
                                <td class="px-3 py-2.5 font-mono text-gray-700"><?= htmlspecialchars($log['ticket_number']) ?></td>
                                <td class="px-3 py-2.5 text-right font-black text-gray-900 text-sm">
                                    <?= number_format($log['cubic_meters'], 2) ?> <span class="text-[10px] font-normal text-gray-500">cu.m</span>
                                </td>
                                <td class="px-3 py-2.5 text-gray-700"><?= htmlspecialchars($log['driver_name']) ?></td>
                                <td class="px-3 py-2.5 text-gray-600"><?= htmlspecialchars($log['checker_name']) ?></td>
                                <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap">
                                    <?= !empty($log['timestamp']) ? date('M d, Y H:i', strtotime($log['timestamp'])) : '—' ?>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $log['status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= htmlspecialchars($log['status']) ?>
                                    </span>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>

                    <?php if ($remainingCm > 0): ?>
                        <?php
                        $remAccum = $remainingCm;
                        for ($p = 0; $p < $pendingTripsCount; $p++):
                            $thisTripVol = min($avgVolume, $remAccum);
                            $remAccum = max(0, $remAccum - $thisTripVol);
                        ?>
                            <tr class="border-t border-dashed border-gray-200 text-gray-400">
                                <td class="px-3 py-2.5 text-center font-semibold text-gray-300"><?= count($deliveryLogs) + $p + 1 ?></td>
                                <td class="px-3 py-2.5">
                                    <div class="border-b border-dotted border-gray-300 h-3.5 w-20"></div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="border-b border-dotted border-gray-300 h-3.5 w-24"></div>
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold text-gray-400">
                                    ~<?= number_format($thisTripVol, 2) ?> <span class="text-[10px]">cu.m</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="border-b border-dotted border-gray-300 h-3.5 w-24"></div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="border-b border-dotted border-gray-300 h-3.5 w-20"></div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="border-b border-dotted border-gray-300 h-3.5 w-24"></div>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-300 font-bold text-gray-800 text-xs">
                    <tr>
                        <td colspan="3" class="px-4 py-2.5 text-left uppercase tracking-wider text-gray-600">
                            Total Fulfilled / Dispatched
                        </td>
                        <td class="px-3 py-2.5 text-right text-emerald-700 font-black text-sm">
                            <?= number_format($doneCm, 2) ?> <span class="text-[10px] font-normal">cu.m</span>
                        </td>
                        <td colspan="4" class="px-3 py-2.5 text-right text-gray-500">
                            <span><?= count($deliveryLogs) ?> completed trip<?= count($deliveryLogs) !== 1 ? 's' : '' ?></span>
                            <?php if ($remainingCm > 0): ?>
                                &nbsp;·&nbsp; <span class="text-amber-700 font-bold"><?= number_format($remainingCm, 2) ?> cu.m remaining</span>
                            <?php else: ?>
                                &nbsp;·&nbsp; <span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check text-emerald-500"></i> 100% Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>


        <div class="grid grid-cols-3 gap-10 mt-12 pt-8 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-10 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Admin Authorized</p>
                <p class="text-xs text-gray-400 mt-0.5">SSV Logistics Office</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-10 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Field Checker</p>
                <p class="text-xs text-gray-500 mt-1"><?= !empty($order['checker_full_name']) ? htmlspecialchars($order['checker_full_name']) : (!empty($order['checker_name']) ? htmlspecialchars($order['checker_name']) : '________________') ?></p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-10 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Client Received</p>
                <p class="text-xs text-gray-500 mt-1">Date: ____/____/________</p>
            </div>
        </div>

        <div class="text-center mt-8 opacity-40">
            <p class="text-xs font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-4">INTERNAL USE ONLY • DOCUMENT GENERATED ON <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    </script>
</body>

</html>
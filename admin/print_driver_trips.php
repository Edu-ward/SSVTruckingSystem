<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

if (!isset($_GET['driver_id'])) {
    die("Driver ID not specified.");
}

$driver_id = intval($_GET['driver_id']);

// Fetch Driver Info
$stmtDriver = $pdo->prepare("
    SELECT d.*, CONCAT(d.first_name, ' ', d.last_name) AS full_name, t.truck_code, t.rfid_tag
    FROM drivers d
    LEFT JOIN trucks t ON d.truck_id = t.id
    WHERE d.id = ?
");
$stmtDriver->execute([$driver_id]);
$driver = $stmtDriver->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    die("Driver not found.");
}

$allowedPeriods = ['today', 'weekly', 'monthly', 'all', 'custom'];
$period = in_array($_GET['period'] ?? 'monthly', $allowedPeriods) ? $_GET['period'] : 'monthly';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$status_filter = $_GET['status'] ?? 'delivered';

$where = "WHERE dt.driver_id = ?";
$params = [$driver_id];
$periodLabel = "This Month (" . date('F Y') . ")";

if ($period === 'today') {
    $where .= " AND DATE(COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at)) = CURDATE()";
    $periodLabel = "Today (" . date('M d, Y') . ")";
} elseif ($period === 'weekly') {
    $where .= " AND YEARWEEK(COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at), 1) = YEARWEEK(CURDATE(), 1)";
    $periodLabel = "This Week (" . date('M d', strtotime('monday this week')) . " – " . date('M d, Y', strtotime('sunday this week')) . ")";
} elseif ($period === 'monthly') {
    $where .= " AND MONTH(COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at)) = MONTH(CURDATE()) AND YEAR(COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at)) = YEAR(CURDATE())";
    $periodLabel = "Month of " . date('F Y');
} elseif ($period === 'custom' && $start_date && $end_date) {
    $where .= " AND DATE(COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at)) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $periodLabel = date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date));
} elseif ($period === 'all') {
    $periodLabel = "All Time History";
}

if ($status_filter === 'delivered') {
    $where .= " AND dt.status = 'Delivered'";
}

$sql = "
    SELECT 
        dt.*,
        COALESCE(NULLIF(dt.distance_km, 0), dest.distance_km, 0.00) AS distance_km,
        COALESCE(NULLIF(dt.pay_amount, 0), d.pay_amount, IF(LOWER(dest.name) LIKE '%san leonardo%', 300.00, IF(dest.distance_km > 0, ROUND(300.00 + GREATEST(0, dest.distance_km - IF(LOWER(dest.name) LIKE '%peñaranda%' OR LOWER(dest.name) LIKE '%penaranda%', 6, 12)) * 10, 2), IF(dest.driver_rate > 0, dest.driver_rate, 300.00))), 0.00) AS pay_amount,
        COALESCE(d.ticket_number, CONCAT('TRIP-', dt.id)) AS ticket_no,
        COALESCE(d.cubic_meters, 0.00) AS cubic_meters,
        t.truck_code AS dispatch_truck,
        o.order_number,
        COALESCE(NULLIF(d.client_name, ''), o.client_name) AS client_name
    FROM driver_trips dt
    LEFT JOIN destinations dest ON dest.name = dt.destination
    LEFT JOIN dispatches d ON d.driver_id = dt.driver_id AND d.destination = dt.destination AND DATE(COALESCE(d.dispatch_date, d.created_at)) = DATE(COALESCE(dt.trip_date, dt.created_at))
    LEFT JOIN trucks t ON d.truck_id = t.id
    LEFT JOIN orders o ON dt.order_id = o.id
    $where
    ORDER BY COALESCE(dt.transit_start_time, dt.trip_date, dt.created_at) DESC, dt.id DESC
";

$stmtTrips = $pdo->prepare($sql);
$stmtTrips->execute($params);
$trips = $stmtTrips->fetchAll(PDO::FETCH_ASSOC);

// Metrics calculation
$totalTrips = count($trips);
$deliveredCount = 0;
$totalCubicMeters = 0;
$totalTripEarnings = 0;

foreach ($trips as $t) {
    if ($t['status'] === 'Delivered') {
        $deliveredCount++;
        $totalCubicMeters += floatval($t['cubic_meters'] > 0 ? $t['cubic_meters'] : 10.0);
        $totalTripEarnings += floatval($t['pay_amount'] ?? 0);
    }
}

$reportNo = 'DRV-REP-' . date('Ymd') . '-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Trip Logs — <?= htmlspecialchars($driver['full_name']); ?> (<?= htmlspecialchars($periodLabel); ?>)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .ticket-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .ticket-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 36px 44px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
    </style>
</head>

<body class="text-slate-800 antialiased py-6">

    <!-- Floating Action Toolbar (Hidden during print) -->
    <div class="no-print fixed top-5 right-5 z-50 flex items-center gap-3 bg-white/95 backdrop-blur shadow-xl border border-slate-200 px-4 py-2.5 rounded-2xl">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-2 shadow transition active:scale-95">
            <i class="fa-solid fa-print"></i> Print Document
        </button>
        <button onclick="window.close()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3.5 py-2 rounded-xl transition">
            <i class="fa-solid fa-xmark mr-1"></i> Close
        </button>
    </div>

    <div class="ticket-container">
        <!-- Official Document Header -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-5 mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center text-2xl font-black shadow-md">
                    <i class="fa-solid fa-truck-moving"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">SSV TRUCKING SERVICES</h1>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-0.5">Aggregate & Gravel Hauling Operations</p>
                    <p class="text-[11px] text-slate-400">Brgy. Burgos San Leonardo, Nueva Ecija, Philippines</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block bg-slate-100 text-slate-800 text-[11px] font-extrabold uppercase px-3 py-1 rounded-md border border-slate-300">
                    Official Driver Trip Log
                </span>
                <div class="text-xs font-mono text-slate-500 mt-2">Ref #: <strong class="text-slate-800"><?= htmlspecialchars($reportNo); ?></strong></div>
                <div class="text-[11px] text-slate-400">Generated: <?= date('M d, Y h:i A'); ?></div>
            </div>
        </div>

        <!-- Document Sub-Header / Driver Info Grid -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 font-semibold block uppercase text-[10px]">Assigned Driver</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block truncate"><?= htmlspecialchars($driver['full_name']); ?></span>
                <span class="text-[11px] text-slate-500">ID #<?= htmlspecialchars($driver['id']); ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase text-[10px]">Driver's License (CDL)</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block font-mono"><?= htmlspecialchars($driver['cdl_number'] ?: 'N/A'); ?></span>
                <span class="text-[11px] text-slate-500">Contact: <?= htmlspecialchars($driver['phone'] ?: 'N/A'); ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase text-[10px]">Primary Truck</span>
                <span class="font-bold text-blue-600 text-sm mt-0.5 block"><?= htmlspecialchars($driver['truck_code'] ?: 'Unassigned'); ?></span>
                <span class="text-[11px] text-slate-500">RFID: <?= htmlspecialchars($driver['rfid_tag'] ?: 'N/A'); ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase text-[10px]">Coverage Period</span>
                <span class="font-bold text-slate-900 text-xs mt-0.5 block leading-tight text-blue-700"><?= htmlspecialchars($periodLabel); ?></span>
                <span class="text-[10px] text-slate-500 capitalize">Filter: <?= htmlspecialchars($status_filter); ?></span>
            </div>
        </div>

        <!-- Summary KPI Strip -->
        <div class="grid grid-cols-4 gap-3 mb-6 text-center">
            <div class="border border-slate-200 rounded-xl p-3 bg-white">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Dispatches</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5"><?= number_format($totalTrips); ?></div>
            </div>
            <div class="border border-emerald-200 rounded-xl p-3 bg-emerald-50/50">
                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Delivered Trips</div>
                <div class="text-2xl font-black text-emerald-700 mt-0.5"><?= number_format($deliveredCount); ?></div>
            </div>
            <div class="border border-indigo-200 rounded-xl p-3 bg-indigo-50/50">
                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Est. Volume Delivered</div>
                <div class="text-2xl font-black text-indigo-700 mt-0.5"><?= number_format($totalCubicMeters, 1); ?> <span class="text-xs font-bold">cu.m</span></div>
            </div>
            <div class="border border-amber-200 rounded-xl p-3 bg-amber-50/50">
                <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Gross Trip Pay</div>
                <div class="text-2xl font-black text-amber-700 mt-0.5">₱<?= number_format($totalTripEarnings, 2); ?></div>
            </div>
        </div>

        <!-- Itemized Trip Log Table -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Itemized Trip Dispatches</h3>
                <span class="text-[11px] text-slate-400"><?= count($trips); ?> record(s) listed</span>
            </div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white font-semibold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3 rounded-l-lg">#</th>
                        <th class="py-2.5 px-3">Dispatch Date & Time</th>
                        <th class="py-2.5 px-3">Arrival Date & Time</th>
                        <th class="py-2.5 px-3">Destination</th>
                        <th class="py-2.5 px-3 text-center">Distance</th>
                        <th class="py-2.5 px-3 text-center">Trip Pay</th>
                        <th class="py-2.5 px-3 text-center">Truck</th>
                        <th class="py-2.5 px-3 text-center">Duration</th>
                        <th class="py-2.5 px-3 text-right rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (count($trips) > 0): ?>
                        <?php $idx = 1;
                        foreach ($trips as $t): ?>
                            <?php
                            $duration = 'N/A';
                            if (!empty($t['transit_start_time']) && !empty($t['transit_end_time'])) {
                                $start = new DateTime($t['transit_start_time']);
                                $end = new DateTime($t['transit_end_time']);
                                $diff = $start->diff($end);
                                $duration = '';
                                if ($diff->h > 0) $duration .= $diff->h . 'h ';
                                $duration .= $diff->i . 'm';
                            }
                            $dispTimeStr = !empty($t['transit_start_time']) ? date('M d, Y h:i A', strtotime($t['transit_start_time'])) : (!empty($t['created_at']) ? date('M d, Y h:i A', strtotime($t['created_at'])) : date('M d, Y', strtotime($t['trip_date'])));
                            $arrTimeStr = !empty($t['transit_end_time']) ? date('M d, Y h:i A', strtotime($t['transit_end_time'])) : ($t['status'] === 'Delivered' ? 'Delivered' : '—');
                            ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2.5 px-3 font-mono text-slate-400"><?= $idx++; ?></td>
                                <td class="py-2.5 px-3 font-medium text-slate-900"><?= $dispTimeStr; ?></td>
                                <td class="py-2.5 px-3 text-slate-600"><?= $arrTimeStr; ?></td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    <?= htmlspecialchars($t['destination']); ?>
                                </td>
                                <td class="py-2.5 px-3 text-center font-semibold text-blue-700">
                                    <?= number_format($t['distance_km'] ?? 0, 1); ?> km
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-emerald-700">
                                    ₱<?= number_format($t['pay_amount'] ?? 0, 2); ?>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-blue-600">
                                    <?= htmlspecialchars($t['dispatch_truck'] ?: ($driver['truck_code'] ?: '—')); ?>
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-slate-500"><?= $duration; ?></td>
                                <td class="py-2.5 px-3 text-right">
                                    <?php if ($t['status'] === 'Delivered'): ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Delivered
                                        </span>
                                    <?php elseif ($t['status'] === 'Cancelled'): ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($t['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 italic">
                                No dispatch trips recorded for this driver within the selected coverage period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Verification & Signatures Section -->
        <div class="border-t-2 border-dashed border-slate-300 pt-6 mt-12 grid grid-cols-3 gap-8 text-center text-xs">
            <div>
                <div class="border-b border-slate-900 pb-1 mb-1 font-bold text-slate-900">
                    <br>
                </div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Prepared by (Admin)</div>
            </div>
            <div>
                <div class="border-b border-slate-900 pb-1 mb-1 font-bold text-slate-900">
                    <?= htmlspecialchars($driver['full_name']); ?>
                </div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Driver's Signature</div>
            </div>
            <div>
                <div class="border-b border-slate-900 pb-1 mb-1 font-bold text-slate-900 h-5"></div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Verified by Operations</div>
            </div>
        </div>

        <div class="text-center text-[10px] text-slate-400 mt-10 pt-4 border-t border-slate-100">
            This document is an official dispatch record generated by SSV Trucking Management System.
        </div>
    </div>

</body>

</html>
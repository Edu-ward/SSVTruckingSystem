<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access. Admin only.");
}

$settlement_id = intval($_GET['settlement_id'] ?? 0);
$driver_id     = intval($_GET['driver_id'] ?? 0);

$settledTrips = [];
$pendingTrips = [];

if ($settlement_id > 0) {
    $stmt = $pdo->prepare("
        SELECT s.*, d.first_name, d.last_name, d.cdl_number 
        FROM driver_payroll_settlements s
        JOIN drivers d ON d.id = s.driver_id
        WHERE s.id = ?
    ");
    $stmt->execute([$settlement_id]);
    $settlement = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$settlement) {
        die("Settlement record not found.");
    }
    $driver_id         = intval($settlement['driver_id']);
    $driver = [
        'first_name' => $settlement['first_name'],
        'last_name'  => $settlement['last_name'],
        'cdl_number' => $settlement['cdl_number']
    ];
    $ticket_number     = $settlement['settlement_ticket'];
    $gross_amount      = floatval($settlement['gross_amount']);
    $previous_balance  = floatval($settlement['previous_balance'] ?? 0);
    $ca_deduction      = floatval($settlement['cash_advance_deduction']);
    $net_pay           = floatval($settlement['net_pay']);
    $amount_claimed    = floatval($settlement['amount_claimed'] ?? $net_pay);
    if ($amount_claimed <= 0 && $net_pay > 0) $amount_claimed = $net_pay;
    $remaining_balance = floatval($settlement['remaining_balance'] ?? max(0, $net_pay - $amount_claimed));
    $trips_count       = intval($settlement['trips_count']);
    $settled_date      = date('F d, Y - h:i A', strtotime($settlement['settled_at']));

    // Fetch settled trips
    $stStmt = $pdo->prepare("
        SELECT 
            d.id, d.ticket_number, d.destination, d.dispatch_date, d.created_at, d.transit_end_time,
            COALESCE(NULLIF(d.pay_amount, 0), IF(dest.distance_km > 0, ROUND(dest.distance_km * 10, 2), dest.driver_rate), 0.00) AS pay_amount,
            COALESCE(dest.distance_km, ROUND(d.pay_amount / 10, 1), 0.00) AS distance_km
        FROM dispatches d
        LEFT JOIN destinations dest ON dest.name = d.destination
        WHERE d.payroll_id = ?
        ORDER BY d.id ASC
    ");
    $stStmt->execute([$settlement_id]);
    $settledTrips = $stStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($settledTrips)) {
        $dtStmt = $pdo->prepare("
            SELECT 
                dt.id, '' AS ticket_number, dt.destination, dt.trip_date AS dispatch_date, dt.created_at, dt.transit_end_time,
                COALESCE(NULLIF(dt.pay_amount, 0), IF(dest.distance_km > 0, ROUND(dest.distance_km * 10, 2), dest.driver_rate), 0.00) AS pay_amount,
                COALESCE(NULLIF(dt.distance_km, 0), dest.distance_km, 0.00) AS distance_km
            FROM driver_trips dt
            LEFT JOIN destinations dest ON dest.name = dt.destination
            WHERE dt.payroll_id = ?
            ORDER BY dt.id ASC
        ");
        $dtStmt->execute([$settlement_id]);
        $settledTrips = $dtStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($driver_id > 0) {
    $stmt = $pdo->prepare("SELECT first_name, last_name, cdl_number FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) {
        die("Driver not found.");
    }

    $lastSettleStmt = $pdo->prepare("SELECT * FROM driver_payroll_settlements WHERE driver_id = ? ORDER BY id DESC LIMIT 1");
    $lastSettleStmt->execute([$driver_id]);
    $lastSettle = $lastSettleStmt->fetch(PDO::FETCH_ASSOC);

    if ($lastSettle) {
        $ticket_number     = $lastSettle['settlement_ticket'];
        $gross_amount      = floatval($lastSettle['gross_amount']);
        $previous_balance  = floatval($lastSettle['previous_balance'] ?? 0);
        $ca_deduction      = floatval($lastSettle['cash_advance_deduction']);
        $net_pay           = floatval($lastSettle['net_pay']);
        $amount_claimed    = floatval($lastSettle['amount_claimed'] ?? $net_pay);
        if ($amount_claimed <= 0 && $net_pay > 0) $amount_claimed = $net_pay;
        $remaining_balance = floatval($lastSettle['remaining_balance'] ?? max(0, $net_pay - $amount_claimed));
        $trips_count       = intval($lastSettle['trips_count']);
        $settled_date      = date('F d, Y - h:i A', strtotime($lastSettle['settled_at']));
    } else {
        $earnStmt = $pdo->prepare("SELECT COALESCE(SUM(pay_amount), 0) FROM dispatches WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)");
        $earnStmt->execute([$driver_id]);
        $gross_amount = floatval($earnStmt->fetchColumn());

        $curBalStmt = $pdo->prepare("SELECT remaining_balance FROM driver_payroll WHERE driver_id = ?");
        $curBalStmt->execute([$driver_id]);
        $previous_balance = floatval($curBalStmt->fetchColumn() ?: 0);

        $caStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM cash_advances WHERE driver_id = ? AND status = 'Approved' AND (is_settled = 0 OR is_settled IS NULL)");
        $caStmt->execute([$driver_id]);
        $ca_deduction = floatval($caStmt->fetchColumn());

        $net_pay           = max(0, $gross_amount + $previous_balance - $ca_deduction);
        $amount_claimed    = $net_pay;
        $remaining_balance = 0.00;
        $trips_count       = 0;
        $ticket_number     = 'PAY-' . date('Y') . '-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));
        $settled_date      = date('F d, Y - h:i A');
    }
} else {
    die("No settlement or driver specified.");
}

// Fetch pending / in-transit trips for the driver
$ptStmt = $pdo->prepare("
    SELECT 
        d.id, d.ticket_number, d.destination, d.status, d.created_at,
        COALESCE(NULLIF(d.pay_amount, 0), IF(dest.distance_km > 0, ROUND(dest.distance_km * 10, 2), dest.driver_rate), 0.00) AS pay_amount,
        COALESCE(dest.distance_km, ROUND(d.pay_amount / 10, 1), 0.00) AS distance_km
    FROM dispatches d
    LEFT JOIN destinations dest ON dest.name = d.destination
    WHERE d.driver_id = ? AND d.status IN ('Pending', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
    ORDER BY d.id ASC
");
$ptStmt->execute([$driver_id]);
$pendingTrips = $ptStmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM driver_payroll WHERE driver_id = ?");
$stmt2->execute([$driver_id]);
$payroll = $stmt2->fetch(PDO::FETCH_ASSOC) ?: ['total_amount' => 0, 'amount_claimed' => 0, 'remaining_balance' => 0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Payroll Ticket - <?= htmlspecialchars($ticket_number); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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

        .waybill-container {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 35px 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-gray-900">

    <div class="no-print bg-gray-900 text-white p-4 flex justify-between items-center fixed top-0 w-full z-10 shadow-md">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-print text-emerald-400"></i>
            <span class="font-semibold">Print Preview - Driver Payroll Settlement Ticket</span>
        </div>
        <div class="space-x-2">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">Close Tab</button>
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 rounded text-sm font-semibold transition shadow"><i class="fa-solid fa-print mr-2"></i> Print Now</button>
        </div>
    </div>

    <div class="waybill-container mt-16 relative">
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-5 mb-5">
            <div>
                <div class="flex items-center mb-1">
                    <img src="../assets/ssvLogo.png" alt="SSV Logo" class="h-14 w-auto mr-3">
                    <h1 class="text-3xl font-extrabold tracking-tight">SSV Trucking</h1>
                </div>
                <p class="text-xs text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-xs text-gray-600 font-medium">Driver Payroll Claim & Disbursement Voucher</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-extrabold text-gray-800 uppercase tracking-widest mb-1">PAYROLL DISBURSEMENT</h2>
                <p class="text-xs text-gray-500 font-mono tracking-widest mt-1">
                    <?= htmlspecialchars($ticket_number); ?>
                </p>
                <?php if ($remaining_balance > 0): ?>
                    <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded text-[11px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                        PARTIALLY CLAIMED
                    </span>
                <?php else: ?>
                    <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        FULLY DISBURSED
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payout Overview Box -->
        <div class="bg-emerald-50 border border-emerald-200 p-5 rounded-xl mb-5">
            <div class="flex justify-between items-center mb-3 border-b border-emerald-200/80 pb-3">
                <div>
                    <h3 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Amount Disbursed Now</h3>
                    <p class="text-xs text-emerald-600 font-medium">Payment authorized for payout to driver</p>
                </div>
                <span class="text-3xl font-black text-emerald-700">
                    ₱<?= number_format($amount_claimed, 2); ?>
                </span>
            </div>
            <div class="grid grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-gray-500 block uppercase font-semibold mb-0.5">Gross Earnings:</span>
                    <strong class="text-gray-800 text-sm">
                        ₱<?= number_format($gross_amount, 2); ?>
                    </strong>
                    <?php if ($trips_count > 0): ?>
                        <span class="text-[10px] text-gray-400 block">(<?= $trips_count; ?> trips)</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-gray-500 block uppercase font-semibold mb-0.5">Prior Carried Balance:</span>
                    <strong class="text-indigo-600 text-sm">
                        +₱<?= number_format($previous_balance, 2); ?>
                    </strong>
                </div>
                <div>
                    <span class="text-gray-500 block uppercase font-semibold mb-0.5">Advances Deducted:</span>
                    <strong class="text-orange-600 text-sm">
                        -₱<?= number_format($ca_deduction, 2); ?>
                    </strong>
                </div>
                <div class="bg-white/80 p-2 rounded-lg border border-emerald-200">
                    <span class="text-indigo-700 block uppercase font-bold text-[10px] mb-0.5">Remaining Balance:</span>
                    <strong class="text-indigo-700 text-sm font-black">
                        ₱<?= number_format($remaining_balance, 2); ?>
                    </strong>
                    <span class="text-[10px] text-indigo-500 block">Carried to next cycle</span>
                </div>
            </div>
        </div>

        <!-- Driver and Ticket Info -->
        <div class="grid grid-cols-2 gap-5 mb-5 text-xs">
            <div class="bg-gray-50 p-3.5 border border-gray-200 rounded-lg">
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-1.5">Driver Information</p>
                <p class="font-bold text-base text-gray-900 mb-0.5">
                    <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                </p>
                <p class="text-gray-600"><span class="font-medium">Driver ID:</span> DRV-<?= str_pad($driver_id, 4, '0', STR_PAD_LEFT); ?></p>
                <p class="text-gray-600 mt-0.5"><span class="font-medium">Licence Number:</span> <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
            </div>

            <div class="bg-gray-50 p-3.5 border border-gray-200 rounded-lg">
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-1.5">Voucher Details</p>
                <table class="w-full text-xs">
                    <tbody>
                        <tr>
                            <td class="text-gray-600 py-1 border-b border-gray-200">Date Settled:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200"><?= htmlspecialchars($settled_date); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1 border-b border-gray-200">Distance Rate:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200">₱10.00 / km</td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1">Total Net Payable:</td>
                            <td class="text-right font-bold text-emerald-700">₱<?= number_format($net_pay, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====== SETTLED TRIPS BREAKDOWN ====== -->
        <div class="mb-5">
            <div class="flex justify-between items-center mb-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">
                    <i class="fa-solid fa-route text-emerald-600 mr-1.5"></i>Settled Trips (<?= count($settledTrips); ?>)
                </h4>
                <span class="text-[11px] text-gray-500">Rate: ₱10.00 / km</span>
            </div>
            <?php if (!empty($settledTrips)): 
                $totalKmSettled = 0;
                $totalPaySettled = 0;
            ?>
            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-gray-700 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="py-2 px-3 text-left">#</th>
                        <th class="py-2 px-3 text-left">Dispatch / Ref</th>
                        <th class="py-2 px-3 text-left">Destination</th>
                        <th class="py-2 px-3 text-right">Distance</th>
                        <th class="py-2 px-3 text-right">Rate</th>
                        <th class="py-2 px-3 text-right">Trip Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($settledTrips as $idx => $trip): 
                        $km = floatval($trip['distance_km']);
                        $pay = floatval($trip['pay_amount']);
                        $totalKmSettled += $km;
                        $totalPaySettled += $pay;
                    ?>
                    <tr class="hover:bg-gray-50/80">
                        <td class="py-1.5 px-3 text-gray-500"><?= $idx + 1; ?></td>
                        <td class="py-1.5 px-3 font-mono font-medium text-gray-800"><?= htmlspecialchars($trip['ticket_number'] ?: ('TRIP-#' . $trip['id'])); ?></td>
                        <td class="py-1.5 px-3 font-semibold text-gray-900"><?= htmlspecialchars($trip['destination']); ?></td>
                        <td class="py-1.5 px-3 text-right text-gray-700"><?= number_format($km, 1); ?> km</td>
                        <td class="py-1.5 px-3 text-right text-gray-500">₱10 / km</td>
                        <td class="py-1.5 px-3 text-right font-bold text-emerald-700">₱<?= number_format($pay, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-bold border-t border-gray-300">
                    <tr>
                        <td colspan="3" class="py-2 px-3 text-gray-700">Total Settled Trips:</td>
                        <td class="py-2 px-3 text-right text-gray-900"><?= number_format($totalKmSettled, 1); ?> km</td>
                        <td></td>
                        <td class="py-2 px-3 text-right text-emerald-700 text-sm">₱<?= number_format($totalPaySettled, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-500 italic">
                No individual trip dispatches recorded for this settlement.
            </div>
            <?php endif; ?>
        </div>

        <!-- ====== PENDING / IN-TRANSIT TRIPS ====== -->
        <?php if (!empty($pendingTrips)): ?>
        <div class="mb-5">
            <div class="flex justify-between items-center mb-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800">
                    <i class="fa-solid fa-truck-fast text-amber-600 mr-1.5"></i>Pending In-Transit Trips (<?= count($pendingTrips); ?>)
                </h4>
                <span class="text-[10px] text-amber-700 font-medium">To be settled in next cycle upon delivery</span>
            </div>
            <table class="w-full text-xs border border-amber-200 rounded-lg overflow-hidden bg-amber-50/40">
                <thead class="bg-amber-100/70 text-amber-900 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="py-1.5 px-3 text-left">Dispatch #</th>
                        <th class="py-1.5 px-3 text-left">Destination</th>
                        <th class="py-1.5 px-3 text-left">Current Status</th>
                        <th class="py-1.5 px-3 text-right">Distance</th>
                        <th class="py-1.5 px-3 text-right">Est. Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                    <?php foreach ($pendingTrips as $pt): ?>
                    <tr>
                        <td class="py-1.5 px-3 font-mono text-gray-800"><?= htmlspecialchars($pt['ticket_number'] ?: ('DISP-#' . $pt['id'])); ?></td>
                        <td class="py-1.5 px-3 font-semibold text-gray-900"><?= htmlspecialchars($pt['destination']); ?></td>
                        <td class="py-1.5 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200/80 text-amber-900">
                                <?= htmlspecialchars($pt['status']); ?>
                            </span>
                        </td>
                        <td class="py-1.5 px-3 text-right text-gray-700"><?= number_format(floatval($pt['distance_km']), 1); ?> km</td>
                        <td class="py-1.5 px-3 text-right font-bold text-amber-800">₱<?= number_format(floatval($pt['pay_amount']), 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Notice -->
        <div class="bg-gray-100 p-3 border border-gray-300 text-[11px] text-gray-700 italic mb-8 rounded-lg">
            <strong>Disbursement Policy:</strong> This voucher confirms authorization and disbursement of driver payroll for the itemized deliveries listed above. Unclaimed gross earnings have been cleared. <?php if ($remaining_balance > 0): ?>A remaining balance of <strong>₱<?= number_format($remaining_balance, 2); ?></strong> is credited to the driver's account for the next cycle.<?php endif; ?>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-12 mt-12 pt-6 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-8 mb-1.5"></div>
                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Driver Signature</p>
                <p class="text-[10px] text-gray-500">Date Received: ________/____/________</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-8 mb-1.5"></div>
                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Cashier/Admin Authorized Signature</p>
                <p class="text-[10px] text-gray-500">Date Disbursed: ________/____/________</p>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full text-center opacity-40 pb-6">
            <p class="text-[10px] font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-3 mt-10">INTERNAL USE ONLY • SSV TRUCKING SYSTEM • GENERATED ON <?= date('Y-m-d H:i:s'); ?></p>
        </div>

    </div>

</body>

</html>
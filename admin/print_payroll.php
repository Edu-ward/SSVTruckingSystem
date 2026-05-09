<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access. Admin only.");
}

if (!isset($_GET['driver_id'])) {
    die("No driver selected.");
}

$driver_id = $_GET['driver_id'];

$stmt = $pdo->prepare("SELECT first_name, last_name, cdl_number FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch();

if (!$driver) {
    die("Driver not found.");
}

$stmt2 = $pdo->prepare("SELECT * FROM driver_payroll WHERE driver_id = ?");
$stmt2->execute([$driver_id]);
$payroll = $stmt2->fetch() ?: ['total_amount' => 0, 'amount_claimed' => 0];

$available_balance = max(0, $payroll['total_amount'] - $payroll['amount_claimed']);

if ($available_balance <= 0) {
    die("No available balance to claim.");
}

$ticket_number = 'PAY-' . date('Y') . '-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));

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
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-gray-900">

    <div class="no-print bg-gray-900 text-white p-4 flex justify-between items-center fixed top-0 w-full z-10 shadow-md">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-print text-orange-400"></i>
            <span class="font-semibold">Print Preview - Payroll Ticket</span>
        </div>
        <div class="space-x-2">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">Close Tab</button>
            <button onclick="window.print()" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 rounded text-sm font-semibold transition shadow"><i class="fa-solid fa-print mr-2"></i> Print Now</button>
        </div>
    </div>

    <div class="waybill-container mt-20 relative">
        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <div class="flex items-center mb-1">
                    <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-16 w-auto mr-4">
                    <h1 class="text-4xl font-bold tracking-tight">SSV Trucking</h1>
                </div>
                <p class="text-sm text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-sm text-gray-600">Driver Payroll Authorization</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-widest mb-1">CLAIM TICKET</h2>
                <p class="text-sm text-gray-500 font-mono tracking-widest mt-2">
                    <?= htmlspecialchars($ticket_number); ?>
                </p>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 p-6 rounded mb-8">
            <div class="flex justify-between items-center mb-4 border-b border-orange-200 pb-4">
                <h3 class="text-xl font-bold text-orange-800 uppercase tracking-wider">Payroll Claim Amount</h3>
                <span class="text-4xl font-bold text-gray-900">
                    ₱<?= number_format($available_balance, 2); ?>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 block mb-1">Total Lifetime Earnings:</span>
                    <strong class="text-gray-800">
                        ₱<?= number_format($payroll['total_amount'], 2); ?>
                    </strong>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Total Previously Claimed:</span>
                    <strong class="text-gray-800">
                        ₱<?= number_format($payroll['amount_claimed'], 2); ?>
                    </strong>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-12">
            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Driver Details</p>
                <p class="font-semibold text-xl text-gray-800 mb-1">
                    <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                </p>
                <p class="text-sm text-gray-600"><span class="font-medium">Driver ID:</span>
                    DRV-<?= str_pad($driver_id, 4, '0', STR_PAD_LEFT); ?>
                </p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-medium">CDL Number:</span>
                    <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?>
                </p>
            </div>

            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Ticket Info</p>
                <table class="w-full text-sm mt-1">
                    <tbody>
                        <tr>
                            <td class="text-gray-600 py-1.5 border-b border-gray-200">Date Generated:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200">
                                <?= date('F d, Y - h:i A'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1.5 border-b border-gray-200">Valid Until:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200">
                                <?= date('F d, Y', strtotime('+3 days')); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1.5">Status:</td>
                            <td class="text-right font-bold text-orange-600 uppercase">PENDING DISBURSEMENT</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gray-100 p-4 border border-gray-300 text-sm text-gray-700 italic mb-12 rounded">
            <strong>Important Notice:</strong> This ticket serves as a valid request for payroll disbursement for the amount stated above. Present this ticket to the SSV Trucking accounting or payroll office. This ticket is valid for 3 days from the date of generation.
        </div>

        <div class="grid grid-cols-2 gap-12 mt-20 pt-8 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Driver Signature</p>
                <p class="text-xs text-gray-500 mt-1">Date Signed: ________/____/________</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Cashier/Admin Signature</p>
                <p class="text-xs text-gray-500 mt-1">Date Paid: ________/____/________</p>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full text-center opacity-40 pb-8">
            <p class="text-xs font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-4 mt-16">INTERNAL USE ONLY • DOCUMENT GENERATED ON <?= date('Y-m-d H:i:s'); ?></p>
        </div>

    </div>

</body>

</html>
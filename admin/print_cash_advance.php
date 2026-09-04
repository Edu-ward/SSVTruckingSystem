<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized Access. Please log in.");
}

if (!isset($_GET['id'])) {
    die("No cash advance selected.");
}

$ca_id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT ca.*, 
           d.first_name, d.last_name, d.cdl_number, d.phone,
           t.truck_code
    FROM cash_advances ca
    JOIN drivers d ON d.id = ca.driver_id
    LEFT JOIN trucks t ON t.id = d.truck_id
    WHERE ca.id = ?
");
$stmt->execute([$ca_id]);
$advance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$advance) {
    die("Cash advance record not found.");
}

// Check authorization: Admin can view any ticket, Driver can only view their own
if ($_SESSION['role'] !== 'Admin' && (!isset($_SESSION['driver_id']) || $_SESSION['driver_id'] != $advance['driver_id'])) {
    die("Unauthorized access to this cash advance ticket.");
}

$driver_id = $advance['driver_id'];
$ticket_number = 'CA-' . date('Y', strtotime($advance['requested_at'] ?? 'now')) . '-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($advance['id'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Advance Ticket - <?= htmlspecialchars($ticket_number); ?></title>
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

            .waybill-container {
                box-shadow: none !important;
                margin: 0 auto !important;
                padding: 24px !important;
                border: none !important;
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

    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print bg-gray-900 text-white p-4 flex justify-between items-center fixed top-0 w-full z-10 shadow-md">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-hand-holding-dollar text-orange-400 text-lg"></i>
            <span class="font-semibold">Print Preview — Cash Advance Voucher</span>
            <span class="text-xs font-mono bg-gray-800 px-2.5 py-1 rounded text-gray-300"><?= htmlspecialchars($ticket_number); ?></span>
        </div>
        <div class="space-x-2">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">Close Tab</button>
            <button onclick="window.print()" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 rounded text-sm font-semibold transition shadow flex-inline items-center">
                <i class="fa-solid fa-print mr-2"></i> Print Now
            </button>
        </div>
    </div>

    <!-- Printable Voucher Container -->
    <div class="waybill-container mt-20 relative">

        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <div class="flex items-center mb-1">
                    <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-16 w-auto mr-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">SSV Trucking</h1>
                        <p class="text-xs font-semibold tracking-wider text-orange-600 uppercase">Hauling & Logistics Services</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-xs text-gray-500">Official Cash Advance Disbursement Ticket</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-orange-100 text-orange-800 border border-orange-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                    <?= htmlspecialchars($advance['status']); ?> VOUCHER
                </span>
                <h2 class="text-2xl font-black text-gray-800 uppercase tracking-widest">CASH ADVANCE</h2>
                <p class="text-sm text-gray-500 font-mono tracking-widest mt-1 font-bold">
                    <?= htmlspecialchars($ticket_number); ?>
                </p>
            </div>
        </div>

        <!-- Main Amount Highlight Box -->
        <div class="bg-orange-50 border-2 border-orange-300 p-6 rounded-2xl mb-8">
            <div class="flex justify-between items-center mb-4 border-b border-orange-200 pb-4">
                <div>
                    <h3 class="text-xs font-bold text-orange-700 uppercase tracking-widest">Cash Advance Amount</h3>
                    <p class="text-xs text-orange-600 mt-0.5">Approved and authorized for disbursement</p>
                </div>
                <span class="text-4xl font-extrabold text-orange-600">
                    ₱<?= number_format($advance['amount'], 2); ?>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-500 block mb-1 uppercase font-semibold">Purpose / Reason:</span>
                    <strong class="text-gray-800 text-sm">
                        <?= !empty($advance['reason']) ? htmlspecialchars($advance['reason']) : 'Not specified'; ?>
                    </strong>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1 uppercase font-semibold">Payroll Deduction Policy:</span>
                    <p class="text-gray-700 italic">
                        This amount will be automatically deducted from the driver's upcoming payroll earnings.
                    </p>
                </div>
            </div>
        </div>

        <!-- Driver & Ticket Details Grid -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <!-- Driver Details -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-id-card text-blue-500"></i> Driver Details
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Full Name:</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($advance['first_name'] . ' ' . $advance['last_name']); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Driver ID:</span>
                        <span class="font-mono font-semibold text-gray-800">DRV-<?= str_pad($driver_id, 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">License (CDL):</span>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($advance['cdl_number'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Phone Number:</span>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($advance['phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Assigned Truck:</span>
                        <span class="font-bold text-blue-600"><?= htmlspecialchars($advance['truck_code'] ?? 'Unassigned'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Ticket Info -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-file-invoice text-orange-500"></i> Ticket Metadata
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Ticket Number:</span>
                        <span class="font-mono font-bold text-gray-900"><?= htmlspecialchars($ticket_number); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Date Requested:</span>
                        <span class="font-medium text-gray-900"><?= date('M d, Y - h:i A', strtotime($advance['requested_at'])); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Date Approved:</span>
                        <span class="font-medium text-gray-900"><?= !empty($advance['resolved_at']) ? date('M d, Y - h:i A', strtotime($advance['resolved_at'])) : date('M d, Y - h:i A'); ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1.5">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-bold text-green-600 uppercase"><?= htmlspecialchars($advance['status']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Printed Date:</span>
                        <span class="font-mono text-gray-600 text-xs"><?= date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notice Box -->
        <div class="bg-gray-100 p-4 border border-gray-300 text-xs text-gray-700 mb-12 rounded-xl leading-relaxed">
            <strong>Acknowledgment & Agreement:</strong> By signing below, the driver confirms receipt of the cash advance amount indicated on this ticket and authorizes SSV Trucking management to deduct the exact amount from their upcoming payroll payout. The admin/cashier confirms that the funds have been disbursed in accordance with company policy.
        </div>

        <!-- Signatures Grid -->
        <div class="grid grid-cols-2 gap-12 mt-16 pt-8 border-t-2 border-gray-200">
            <div class="text-center">
                <div class="border-b-2 border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-bold text-gray-800 uppercase tracking-wide">Driver Signature / Received By</p>
                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($advance['first_name'] . ' ' . $advance['last_name']); ?></p>
                <p class="text-xs text-gray-400 mt-1">Date Signed: ________/____/________</p>
            </div>
            <div class="text-center">
                <div class="border-b-2 border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-bold text-gray-800 uppercase tracking-wide">Cashier / Admin Signature</p>
                <p class="text-xs text-gray-500 mt-1">Authorized SSV Personnel</p>
                <p class="text-xs text-gray-400 mt-1">Date Paid: ________/____/________</p>
            </div>
        </div>

        <!-- Footer watermark -->
        <div class="absolute bottom-4 left-0 w-full text-center opacity-40">
            <p class="text-[10px] font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-3">
                SSV TRUCKING SYSTEM &bull; INTERNAL ACCOUNTING DOCUMENT &bull; GENERATED ON <?= date('Y-m-d H:i:s'); ?>
            </p>
        </div>

    </div>

    <!-- Auto Print Script -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 600);
        };
    </script>

</body>

</html>

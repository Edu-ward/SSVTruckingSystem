<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

if (!isset($_GET['id'])) {
    die("Ticket ID not specified.");
}

$ticket_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT d.*, 
           t.truck_code, 
           CONCAT(tr.first_name, ' ', tr.last_name) AS driver_name, 
           tr.phone AS driver_phone
    FROM dispatches d
    LEFT JOIN trucks t ON d.truck_id = t.id
    LEFT JOIN drivers tr ON d.driver_id = tr.id
    WHERE d.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Waybill - <?= htmlspecialchars($ticket['ticket_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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

        .barcode {
            font-family: 'Libre Barcode 39', cursive;
            font-size: 4.5rem;
            line-height: 4.5rem;
        }
    </style>
</head>

<body class="text-gray-900">

    <div class="no-print bg-gray-900 text-white p-4 flex justify-between items-center fixed top-0 w-full z-10 shadow-md">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-print text-blue-400"></i>
            <span class="font-semibold">Print Preview</span>
        </div>
        <div class="space-x-2">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">Close Tab</button>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-sm font-semibold transition shadow"><i class="fa-solid fa-print mr-2"></i> Print Now</button>
        </div>
    </div>

    <div class="waybill-container mt-20 relative">
        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight mb-1"><i class="fa-solid fa-truck-fast text-gray-800"></i> SSV Trucking</h1>
                <p class="text-sm text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-sm text-gray-600">Operations Waybill Record</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-widest mb-1">TICKET</h2>
                <div class="barcode">*<?= htmlspecialchars($ticket['ticket_number']); ?>*</div>
                <p class="text-xs text-gray-500 font-mono tracking-widest mt-1"><?= htmlspecialchars($ticket['ticket_number']); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Carrier Details</p>
                <p class="font-semibold text-lg text-gray-800 mb-1"><?= htmlspecialchars($ticket['driver_name']); ?></p>
                <p class="text-sm text-gray-600"><i class="fa-solid fa-phone text-gray-400 w-4"></i> <?= htmlspecialchars($ticket['driver_phone'] ?? 'N/A'); ?></p>
                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-600">Assigned Vehicle:</span>
                    <strong class="text-gray-900 bg-white border border-gray-300 px-3 py-1 rounded shadow-sm"><?= htmlspecialchars($ticket['truck_code']); ?></strong>
                </div>
            </div>

            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Dispatch Details</p>
                <table class="w-full text-sm mt-1">
                    <tbody>
                        <tr>
                            <td class="text-gray-600 py-1.5 border-b border-gray-200">Date Issued:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200"><?= date('F d, Y', strtotime($ticket['dispatch_date'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1.5 border-b border-gray-200">Current Status:</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200 uppercase"><?= htmlspecialchars($ticket['status']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1.5 border-b border-gray-200">Cargo Wgt (lbs):</td>
                            <td class="text-right font-medium text-gray-900 border-b border-gray-200"><?= number_format($ticket['weight'], 2); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1.5">Est. Driver Pay:</td>
                            <td class="text-right font-bold text-gray-900">₱<?= number_format($ticket['pay_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border border-gray-300 rounded overflow-hidden mb-12">
            <div class="bg-gray-100 px-4 py-2 border-b border-gray-300">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Routing Information</h3>
            </div>
            <div class="p-6 relative">
                <div class="flex items-center space-x-6 mb-8">
                    <div class="w-12 h-12 bg-white rounded-full border-2 border-gray-300 flex items-center justify-center font-bold text-lg text-gray-500 shadow-sm">A</div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Origin / Loading Point</p>
                        <p class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($ticket['origin']); ?></p>
                    </div>
                </div>

                <div class="w-0.5 h-12 bg-gray-200 absolute left-12 top-16"></div>

                <div class="flex items-center space-x-6">
                    <div class="w-12 h-12 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-lg shadow-md">B</div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Destination / Drop-off</p>
                        <p class="text-xl font-bold text-black uppercase tracking-wide underline decoration-2 decoration-gray-300 underline-offset-4"><?= htmlspecialchars($ticket['destination']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-12 mt-20 pt-8 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Driver Signature</p>
                <p class="text-xs text-gray-500 mt-1">Date Received: ________/____/________</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-12 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Admin Authorization</p>
                <p class="text-xs text-gray-500 mt-1">Stamp & Validated by SSV Fleet Mgmt.</p>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full text-center opacity-40">
            <p class="text-xs font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-4 mt-16">INTERNAL USE ONLY • DOCUMENT GENERATED ON <?= date('Y-m-d H:i:s'); ?></p>
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
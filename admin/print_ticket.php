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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            .no-print {
                display: none !important;
            }

            .waybill-container {
                margin: 0 !important;
                padding: 12mm 16mm !important;
                box-shadow: none !important;
                width: 210mm !important;
                max-width: 210mm !important;
                height: 297mm !important;
                max-height: 297mm !important;
                box-sizing: border-box !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
        }

        body {
            background: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }

        .waybill-container {
            background: #fff;
            width: 210mm;
            max-width: 100%;
            margin: 20px auto;
            padding: 30px 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
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

    <div class="waybill-container mt-16 sm:mt-20">
        <!-- Header -->
        <div class="flex justify-between items-center border-b-2 border-gray-900 pb-4 mb-5">
            <div class="flex items-center">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-14 w-auto mr-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">SSV Trucking</h1>
                    <p class="text-xs text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                    <p class="text-xs text-gray-500 font-medium">Operations Waybill Record</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800 uppercase tracking-widest">WAYBILL TICKET</h2>
                <p class="text-sm font-bold text-blue-700 font-mono tracking-wider mt-1"><?= htmlspecialchars($ticket['ticket_number']); ?></p>
            </div>
        </div>

        <!-- Carrier & Dispatch Details -->
        <div class="grid grid-cols-2 gap-5 mb-5">
            <div class="bg-gray-50 p-3.5 border border-gray-200 rounded-lg">
                <p class="text-[11px] text-gray-500 uppercase font-bold tracking-wider mb-1">Carrier Details</p>
                <p class="font-bold text-base text-gray-900 mb-1"><?= htmlspecialchars($ticket['driver_name']); ?></p>
                <p class="text-xs text-gray-600"><i class="fa-solid fa-phone text-gray-400 w-4"></i> <?= htmlspecialchars($ticket['driver_phone'] ?? 'N/A'); ?></p>
                <div class="mt-2.5 pt-2.5 border-t border-gray-200 flex justify-between items-center text-xs">
                    <span class="text-gray-600">Assigned Vehicle:</span>
                    <strong class="text-gray-900 bg-white border border-gray-300 px-2.5 py-0.5 rounded font-mono shadow-sm"><?= htmlspecialchars($ticket['truck_code']); ?></strong>
                </div>
            </div>

            <div class="bg-gray-50 p-3.5 border border-gray-200 rounded-lg">
                <p class="text-[11px] text-gray-500 uppercase font-bold tracking-wider mb-1">Dispatch Details</p>
                <table class="w-full text-xs mt-1">
                    <tbody>
                        <tr>
                            <td class="text-gray-600 py-1 border-b border-gray-200">Date Issued:</td>
                            <td class="text-right font-semibold text-gray-900 border-b border-gray-200"><?= date('F d, Y', strtotime($ticket['dispatch_date'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1 border-b border-gray-200">Current Status:</td>
                            <td class="text-right font-bold text-green-700 border-b border-gray-200 uppercase"><?= htmlspecialchars($ticket['status']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1 border-b border-gray-200">Load Volume:</td>
                            <td class="text-right font-bold text-gray-900 border-b border-gray-200"><?= number_format($ticket['cubic_meters'] ?? 0, 2); ?> cu.m</td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1">Client's Bill:</td>
                            <td class="text-right font-bold text-gray-900">₱<?= number_format($ticket['pay_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Routing Information -->
        <div class="border border-gray-300 rounded-lg overflow-hidden mb-5">
            <div class="bg-gray-100 px-4 py-2 border-b border-gray-300">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Routing Information</h3>
            </div>
            <div class="p-4 relative">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-9 h-9 bg-white rounded-full border-2 border-gray-300 flex items-center justify-center font-bold text-sm text-gray-600 shadow-sm flex-shrink-0">A</div>
                    <div class="flex-1">
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">Origin / Loading Point</p>
                        <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($ticket['origin']); ?></p>
                    </div>
                </div>

                <div class="w-0.5 h-6 bg-gray-300 absolute left-[33px] top-[45px]"></div>

                <div class="flex items-center space-x-4">
                    <div class="w-9 h-9 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-sm shadow flex-shrink-0">B</div>
                    <div class="flex-1">
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">Destination / Drop-off</p>
                        <p class="text-base font-bold text-black uppercase tracking-wide underline decoration-2 decoration-gray-300 underline-offset-2"><?= htmlspecialchars($ticket['destination']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 mt-5 pt-4 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-800 w-full h-10 mb-1.5"></div>
                <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Driver Signature</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Date Received: ________/____/________</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-800 w-full h-10 mb-1.5"></div>
                <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Customer's Checker</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Validated by Customer's Checker</p>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-6 text-center opacity-50">
            <p class="text-[10px] font-mono uppercase tracking-widest border-t border-dashed border-gray-300 pt-3">INTERNAL USE ONLY • DOCUMENT GENERATED ON <?= date('Y-m-d H:i:s'); ?></p>
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
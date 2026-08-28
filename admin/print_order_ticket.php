<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

if (!isset($_GET['id'])) die("Order ID not specified.");

$order_id = intval($_GET['id']);
$stmt = $pdo->prepare("
    SELECT o.*, u.username AS checker_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.checker_id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) die("Order not found.");

$_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$gravelTypeLabels = [];
foreach ($_gravel_rows as $_g) {
    $gravelTypeLabels[$_g['type_key']] = $_g['label'];
}
$gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Ticket — <?= htmlspecialchars($order['order_number']) ?></title>
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
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <div class="flex items-center mb-1">
                    <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-16 w-auto mr-4">
                    <h1 class="text-4xl font-bold tracking-tight">SSV Trucking</h1>
                </div>
                <p class="text-sm text-gray-600">San Leonardo, Nueva Ecija, Philippines</p>
                <p class="text-sm text-gray-600">Gravel Delivery Order</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-widest mb-1">ORDER TICKET</h2>
                <p class="text-sm text-gray-500 font-mono tracking-widest mt-2"><?= htmlspecialchars($order['order_number']) ?></p>
                <p class="text-xs text-gray-400 mt-1">Issued: <?= date('F d, Y', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <!-- Client + Order info grid -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Client Details</p>
                <p class="font-semibold text-xl text-gray-800"><?= htmlspecialchars($order['client_name']) ?></p>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600"><span class="font-semibold text-gray-800">Destination:</span> <?= htmlspecialchars($order['destination']) ?></p>
                </div>
            </div>
            <div class="bg-gray-50 p-4 border border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Order Details</p>
                <table class="w-full text-sm">
                    <?php
                    $reqCm = floatval($order['cubic_meters_required'] ?? 0) > 0 ? floatval($order['cubic_meters_required']) : floatval($order['trucks_required']);
                    $doneCm = floatval($order['cubic_meters_fulfilled'] ?? 0) > 0 ? floatval($order['cubic_meters_fulfilled']) : floatval($order['trucks_fulfilled']);
                    ?>
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
                        <td class="text-right font-bold text-green-700"><?= number_format($doneCm, 2) ?> cu.m</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($order['notes'])): ?>
            <div class="border border-gray-300 rounded p-4 mb-8 bg-yellow-50">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wide mb-1">Notes / Special Instructions</p>
                <p class="text-sm text-gray-800"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Truck scan log -->
        <div class="border border-gray-300 rounded overflow-hidden mb-12">
            <div class="bg-gray-100 px-4 py-2 border-b border-gray-300 flex justify-between items-center">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Truck Delivery Log</h3>
                <span class="text-xs text-gray-500"><?= number_format($doneCm, 2) ?> of <?= number_format($reqCm, 2) ?> cu.m fulfilled</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Truck</th>
                        <th class="px-4 py-2 text-left">Checker</th>
                        <th class="px-4 py-2 text-left">Scanned At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $scans = $pdo->prepare("SELECT os.*, t.truck_code, u.username AS checker_name FROM order_scans os JOIN trucks t ON t.id = os.truck_id JOIN users u ON u.id = os.checker_id WHERE os.order_id = ? ORDER BY os.scanned_at ASC");
                    $scans->execute([$order_id]);
                    $scanRows = $scans->fetchAll();
                    if (empty($scanRows)):
                    ?>
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400 italic">No scans recorded yet.</td>
                        </tr>
                        <?php else: foreach ($scanRows as $i => $scan): ?>
                            <tr class="border-t border-gray-100">
                                <td class="px-4 py-2 text-gray-500"><?= $i + 1 ?></td>
                                <td class="px-4 py-2 font-bold text-gray-800"><?= htmlspecialchars($scan['truck_code']) ?></td>
                                <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($scan['checker_name']) ?></td>
                                <td class="px-4 py-2 text-gray-600"><?= date('M d, Y H:i', strtotime($scan['scanned_at'])) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                    <!-- Blank rows for remaining trucks -->
                    <?php for ($r = count($scanRows); $r < $order['trucks_required']; $r++): ?>
                        <tr class="border-t border-dashed border-gray-200">
                            <td class="px-4 py-3 text-gray-300"><?= $r + 1 ?></td>
                            <td class="px-4 py-3">
                                <div class="border-b border-dotted border-gray-300 h-4 w-32"></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="border-b border-dotted border-gray-300 h-4 w-24"></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="border-b border-dotted border-gray-300 h-4 w-28"></div>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Signature blocks -->
        <div class="grid grid-cols-3 gap-10 mt-16 pt-8 border-t border-gray-300">
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-10 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Admin Authorized</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-900 w-full h-10 mb-2"></div>
                <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Checker</p>
                <p class="text-xs text-gray-500 mt-1"><?= $order['checker_name'] ? htmlspecialchars($order['checker_name']) : '________________' ?></p>
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
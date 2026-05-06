<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Checker') {
    header("Location: ../index.php");
    exit;
}

$checker_id = $_SESSION['user_id'];

// --- HANDLE RFID SCAN SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'scan_truck') {
        $order_id  = intval($_POST['order_id']);
        $rfid_tag  = trim($_POST['rfid_tag']);

        // Find truck by RFID
        $ts = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE rfid_tag = ?");
        $ts->execute([$rfid_tag]);
        $truck = $ts->fetch();

        if (!$truck) {
            $scanError = "❌ No truck found for RFID tag: " . htmlspecialchars($rfid_tag);
        } else {
            // Fetch order
            $os = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND status IN ('Pending','In Progress')");
            $os->execute([$order_id]);
            $order = $os->fetch();

            if (!$order) {
                $scanError = "❌ Order not found or already fulfilled/cancelled.";
            } elseif ($order['checker_id'] && $order['checker_id'] != $checker_id) {
                $scanError = "❌ You are not the assigned checker for this order.";
            } else {
                // Check for duplicate scan in same order
                $dup = $pdo->prepare("SELECT id FROM order_scans WHERE order_id = ? AND truck_id = ?");
                $dup->execute([$order_id, $truck['id']]);
                if ($dup->fetch()) {
                    $scanError = "⚠️ Truck <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> has already been scanned for this order.";
                } else {
                    // Record scan
                    $pdo->prepare("INSERT INTO order_scans (order_id, truck_id, checker_id) VALUES (?, ?, ?)")
                        ->execute([$order_id, $truck['id'], $checker_id]);

                    // Increment fulfilled count
                    $pdo->prepare("UPDATE orders SET trucks_fulfilled = trucks_fulfilled + 1 WHERE id = ?")
                        ->execute([$order_id]);

                    // Update status if now in progress
                    $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ? AND status = 'Pending'")
                        ->execute([$order_id]);

                    // Check if fulfilled
                    $check = $pdo->prepare("SELECT trucks_required, trucks_fulfilled FROM orders WHERE id = ?");
                    $check->execute([$order_id]);
                    $updated = $check->fetch();
                    if ($updated['trucks_fulfilled'] >= $updated['trucks_required']) {
                        $pdo->prepare("UPDATE orders SET status = 'Fulfilled' WHERE id = ?")
                            ->execute([$order_id]);
                        $scanSuccess = "✅ <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> scanned. Order <strong>Fulfilled!</strong>";
                    } else {
                        $remaining = $updated['trucks_required'] - $updated['trucks_fulfilled'];
                        $scanSuccess = "✅ <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> scanned. <strong>$remaining truck(s)</strong> remaining.";
                    }
                }
            }
        }
    }
}

// --- DATA QUERIES ---
$myOrders = $pdo->prepare("
    SELECT o.*, u.username AS checker_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.checker_id
    WHERE o.checker_id = ? OR o.checker_id IS NULL
    ORDER BY
        FIELD(o.status, 'In Progress', 'Pending', 'Fulfilled', 'Cancelled'),
        o.created_at DESC
");
$myOrders->execute([$checker_id]);
$myOrders = $myOrders->fetchAll();

$totalMyOrders   = count($myOrders);
$pendingCount    = count(array_filter($myOrders, fn($o) => $o['status'] === 'Pending'));
$inProgressCount = count(array_filter($myOrders, fn($o) => $o['status'] === 'In Progress'));
$fulfilledToday  = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'Fulfilled' AND DATE(created_at) = CURDATE()");
$fulfilledToday->execute();
$fulfilledTodayCount = $fulfilledToday->fetchColumn();

// Active orders the checker can scan for (assigned to them or unassigned)
$activeOrders = array_filter($myOrders, fn($o) => in_array($o['status'], ['Pending', 'In Progress']));

include '../includes/header.php';

$gravelTypeLabels = [
    "S1_regular" => "S1 Regular", "S1_crushed" => "S1 Crushed",
    "3_4_regular" => "3/4 Regular", "3_4_crushed" => "3/4 Crushed",
    "G1_regular" => "G1 Regular", "G1_crushed" => "G1 Crushed",
    "38_regular" => "3/8 Regular", "38_crushed" => "3/8 Crushed",
    "base_course" => "Base Course", "river_mix" => "River Mix",
    "garden_soil" => "Garden Soil"
];
?>

<div class="max-w-7xl mx-auto px-6 py-8">

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center space-x-2">
            <i class="fa-solid fa-clipboard-check text-indigo-500"></i>
            <span>Checker Dashboard</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan truck RFID tags to confirm gravel deliveries against active orders.</p>
    </div>

    <!-- Flash messages -->
    <?php if (isset($scanSuccess)): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-xl px-5 py-4 flex items-start space-x-3 shadow-sm">
        <i class="fa-solid fa-circle-check text-xl mt-0.5 flex-shrink-0"></i>
        <p class="text-sm font-medium"><?= $scanSuccess ?></p>
    </div>
    <?php elseif (isset($scanError)): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-xl px-5 py-4 flex items-start space-x-3 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5 flex-shrink-0"></i>
        <p class="text-sm font-medium"><?= $scanError ?></p>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-clipboard-list text-indigo-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $totalMyOrders ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">My Orders</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-hourglass-half text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $pendingCount ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pending</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-truck-fast text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $inProgressCount ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">In Progress</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $fulfilledTodayCount ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fulfilled Today</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: RFID Scanner Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden sticky top-6">
                <div class="bg-indigo-600 p-5 text-white">
                    <div class="flex items-center space-x-2 mb-1">
                        <i class="fa-solid fa-wifi text-indigo-200 text-xl"></i>
                        <h2 class="font-bold text-lg">RFID Scanner</h2>
                    </div>
                    <p class="text-indigo-200 text-xs">Select an order then scan the truck's RFID card.</p>
                </div>
                <form method="POST" action="dashboard.php" class="p-5 space-y-4" id="scanForm">
                    <input type="hidden" name="action" value="scan_truck">

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Select Order <span class="text-red-500">*</span></label>
                        <select name="order_id" id="scanOrderSelect" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">— Select active order —</option>
                            <?php foreach ($activeOrders as $ao): ?>
                                <option value="<?= $ao['id'] ?>"
                                    data-trucks-req="<?= $ao['trucks_required'] ?>"
                                    data-trucks-done="<?= $ao['trucks_fulfilled'] ?>">
                                    <?= htmlspecialchars($ao['order_number']) ?> · <?= htmlspecialchars($ao['destination']) ?> (<?= $ao['trucks_fulfilled'] ?>/<?= $ao['trucks_required'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mini progress bar -->
                    <div id="scanProgress" class="hidden">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Fulfillment Progress</span>
                            <span id="scanProgressText">0/0</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div id="scanProgressBar" class="bg-indigo-500 h-2 rounded-full transition-all" style="width:0%"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Scan Truck RFID <span class="text-red-500">*</span></label>
                        <input type="text" name="rfid_tag" id="rfidScanInput" required autofocus autocomplete="off"
                            placeholder="Click here and scan RFID card..."
                            class="w-full border border-indigo-300 dark:border-indigo-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:text-gray-100 font-mono text-sm transition-colors">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="rfidScanFeedback">Waiting for scan...</p>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2 text-sm">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Confirm Scan</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Orders List -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-base font-bold text-gray-800 dark:text-gray-200">Active & Recent Orders</h2>

            <?php if (empty($myOrders)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-400 dark:text-gray-500">
                <i class="fa-solid fa-clipboard-list text-5xl mb-4 opacity-30"></i>
                <p class="font-medium">No orders assigned to you yet.</p>
                <p class="text-sm mt-1">Contact your admin to get assigned to an order.</p>
            </div>
            <?php else: ?>
            <?php foreach ($myOrders as $order):
                $pct = $order['trucks_required'] > 0 ? round(($order['trucks_fulfilled'] / $order['trucks_required']) * 100) : 0;
                $gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];
                $statusColors = [
                    'Pending'     => ['bar' => 'bg-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', 'border' => 'border-l-yellow-400'],
                    'In Progress' => ['bar' => 'bg-indigo-500', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200', 'border' => 'border-l-indigo-400'],
                    'Fulfilled'   => ['bar' => 'bg-green-500',  'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',   'border' => 'border-l-green-400'],
                    'Cancelled'   => ['bar' => 'bg-red-400',    'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',           'border' => 'border-l-red-400'],
                ];
                $sc = $statusColors[$order['status']] ?? $statusColors['Pending'];

                // Fetch scans for this order
                $scanStmt = $pdo->prepare("SELECT os.*, t.truck_code FROM order_scans os JOIN trucks t ON t.id = os.truck_id WHERE os.order_id = ? ORDER BY os.scanned_at DESC");
                $scanStmt->execute([$order['id']]);
                $orderScans = $scanStmt->fetchAll();
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-l-4 border-gray-100 dark:border-gray-700 <?= $sc['border'] ?> overflow-hidden">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-bold text-gray-900 dark:text-gray-100 font-mono"><?= htmlspecialchars($order['order_number']) ?></div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($order['client_name']) ?> · <span class="font-medium"><?= htmlspecialchars($order['destination']) ?></span></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $sc['badge'] ?>"><?= $order['status'] ?></span>
                    </div>

                    <div class="flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400 mb-4">
                        <span class="flex items-center space-x-1">
                            <i class="fa-solid fa-layer-group text-gray-400"></i>
                            <span><?= htmlspecialchars($gravelLabel) ?></span>
                        </span>
                        <span class="flex items-center space-x-1">
                            <i class="fa-solid fa-truck text-gray-400"></i>
                            <span><?= $order['trucks_fulfilled'] ?>/<?= $order['trucks_required'] ?> trucks</span>
                        </span>
                        <?php if ($order['checker_name']): ?>
                        <span class="flex items-center space-x-1">
                            <i class="fa-solid fa-user-shield text-indigo-400"></i>
                            <span><?= htmlspecialchars($order['checker_name']) ?></span>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Progress -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Fulfillment Progress</span>
                            <span class="font-semibold"><?= $pct ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="<?= $sc['bar'] ?> h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>

                    <!-- Scan log -->
                    <?php if (!empty($orderScans)): ?>
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Scanned Trucks</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($orderScans as $scan): ?>
                            <span class="inline-flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full px-3 py-1 text-xs font-medium">
                                <i class="fa-solid fa-truck text-green-500 text-[10px]"></i>
                                <span><?= htmlspecialchars($scan['truck_code']) ?></span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    // Update progress bar preview when order selection changes
    const scanOrderSelect = document.getElementById('scanOrderSelect');
    const scanProgress    = document.getElementById('scanProgress');
    const scanProgressBar = document.getElementById('scanProgressBar');
    const scanProgressTxt = document.getElementById('scanProgressText');
    const rfidScanInput   = document.getElementById('rfidScanInput');

    if (scanOrderSelect) {
        scanOrderSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) { scanProgress.classList.add('hidden'); return; }
            const req  = parseInt(opt.dataset.trucksReq) || 0;
            const done = parseInt(opt.dataset.trucksDone) || 0;
            const pct  = req > 0 ? Math.round((done / req) * 100) : 0;
            scanProgressText.innerText = done + '/' + req;
            scanProgressBar.style.width = pct + '%';
            scanProgress.classList.remove('hidden');
            rfidScanInput.focus();
        });
    }

    // Auto-trim and uppercase RFID input
    if (rfidScanInput) {
        rfidScanInput.addEventListener('input', function() {
            document.getElementById('rfidScanFeedback').innerText = 'RFID: ' + this.value;
        });
    }

    // Toggle dark/light icon on load
    document.addEventListener("DOMContentLoaded", function() {
        if (document.documentElement.classList.contains('dark')) {
            document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
        }
    });
</script>

<?php include '../includes/scripts.php'; ?>

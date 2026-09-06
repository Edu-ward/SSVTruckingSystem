<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Checker') {
    header("Location: ../index.php");
    exit;
}

// ── CSRF Token Generation ──
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$checker_id = $_SESSION['user_id'];

$pdo->exec("CREATE TABLE IF NOT EXISTS checkers (
    id INT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
)");

$stmt_profile = $pdo->prepare("SELECT u.username, c.first_name, c.last_name, c.phone FROM users u LEFT JOIN checkers c ON u.id = c.id WHERE u.id = ?");
$stmt_profile->execute([$checker_id]);
$checker_profile = $stmt_profile->fetch();
if ($checker_profile && $checker_profile['first_name'] === null && $checker_profile['last_name'] === null) {
    $stmt_check_exists = $pdo->prepare("SELECT id FROM checkers WHERE id = ?");
    $stmt_check_exists->execute([$checker_id]);
    if (!$stmt_check_exists->fetch()) {
        $pdo->prepare("INSERT IGNORE INTO checkers (id, first_name, last_name, phone) VALUES (?, '', '', '')")->execute([$checker_id]);
    }
}

$pdo->exec("ALTER TABLE dispatches ADD COLUMN IF NOT EXISTS cubic_meters DECIMAL(10,2) DEFAULT 0.00");
$pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS cubic_meters_required DECIMAL(10,2) DEFAULT 0.00");
$pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS cubic_meters_fulfilled DECIMAL(10,2) DEFAULT 0.00");
$pdo->exec("UPDATE orders SET cubic_meters_required = trucks_required WHERE (cubic_meters_required IS NULL OR cubic_meters_required = 0.00) AND trucks_required > 0");
$pdo->exec("UPDATE orders SET cubic_meters_fulfilled = trucks_fulfilled WHERE (cubic_meters_fulfilled IS NULL OR cubic_meters_fulfilled = 0.00) AND trucks_fulfilled > 0");

$checker_full_name = ($checker_profile && !empty($checker_profile['first_name']))
    ? ($checker_profile['first_name'] . ' ' . $checker_profile['last_name'])
    : $checker_profile['username'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }

    if ($_POST['action'] === 'scan_truck') {

        $order_id  = intval($_POST['order_id']);
        $truck_id  = intval($_POST['truck_id'] ?? 0);

        $ts = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE id = ?");
        $ts->execute([$truck_id]);
        $truck = $ts->fetch();

        if (!$truck) {
            $_SESSION['error'] = "❌ Please select a valid truck.";
        } else {
            $os = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND status IN ('Pending','In Progress')");
            $os->execute([$order_id]);
            $order = $os->fetch();

            if (!$order) {
                $_SESSION['error'] = "❌ Order not found or already fulfilled/cancelled.";
            } elseif ($order['checker_id'] && $order['checker_id'] != $checker_id) {
                $_SESSION['error'] = "❌ You are not the assigned checker for this order.";
            } else {
                $dup = $pdo->prepare("SELECT id FROM order_scans WHERE order_id = ? AND truck_id = ?");
                $dup->execute([$order_id, $truck['id']]);
                if ($dup->fetch()) {
                    $_SESSION['error'] = "⚠️ Truck <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> has already been scanned for this order.";
                } else {
                    $dispStmt = $pdo->prepare("SELECT cubic_meters FROM dispatches WHERE truck_id = ? ORDER BY id DESC LIMIT 1");
                    $dispStmt->execute([$truck['id']]);
                    $dispRow = $dispStmt->fetch();
                    $scannedCm = ($dispRow && floatval($dispRow['cubic_meters']) > 0) ? floatval($dispRow['cubic_meters']) : 10.00;

                    $pdo->prepare("INSERT INTO order_scans (order_id, truck_id, checker_id) VALUES (?, ?, ?)")
                        ->execute([$order_id, $truck['id'], $checker_id]);

                    $pdo->prepare("UPDATE orders SET trucks_fulfilled = trucks_fulfilled + 1, cubic_meters_fulfilled = cubic_meters_fulfilled + ? WHERE id = ?")
                        ->execute([$scannedCm, $order_id]);

                    $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ? AND status = 'Pending'")
                        ->execute([$order_id]);

                    $driverStmt = $pdo->prepare("SELECT id FROM drivers WHERE truck_id = ?");
                    $driverStmt->execute([$truck['id']]);
                    $assignedDriver = $driverStmt->fetch();

                    if ($assignedDriver) {
                        $activeDispatchStmt = $pdo->prepare("SELECT id FROM dispatches WHERE driver_id = ? AND truck_id = ? AND status IN ('In Transit', 'Loading', 'Unloading') ORDER BY id DESC LIMIT 1");
                        $activeDispatchStmt->execute([$assignedDriver['id'], $truck['id']]);
                        $activeDispatch = $activeDispatchStmt->fetch();

                        if ($activeDispatch) {
                            $nowTs = time();
                            $isOnTime = 1;
                            $dCheck = $pdo->prepare("SELECT transit_start_time, estimated_arrival_time, destination FROM dispatches WHERE id = ?");
                            $dCheck->execute([$activeDispatch['id']]);
                            $dRow = $dCheck->fetch();
                            if ($dRow) {
                                if (!empty($dRow['estimated_arrival_time'])) {
                                    $isOnTime = ($nowTs <= strtotime($dRow['estimated_arrival_time'])) ? 1 : 0;
                                } elseif (!empty($dRow['transit_start_time'])) {
                                    $dstStmt = $pdo->prepare("SELECT distance_km FROM destinations WHERE name = ?");
                                    $dstStmt->execute([$dRow['destination']]);
                                    $distK = floatval($dstStmt->fetchColumn() ?? 20);
                                    $oneWayK = max(5, ($distK > 40 ? $distK / 2 : $distK));
                                    $etaM = max(25, round(($oneWayK / 35) * 60) + 15);
                                    $isOnTime = ($nowTs <= (strtotime($dRow['transit_start_time']) + ($etaM * 60))) ? 1 : 0;
                                }
                            }

                            $pdo->prepare("UPDATE dispatches SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE id = ?")->execute([$isOnTime, $activeDispatch['id']]);
                            $pdo->prepare("UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1")->execute([$isOnTime, $assignedDriver['id']]);
                            $pdo->prepare("UPDATE trucks SET status = 'Idle', current_location = 'San Leonardo (Garage)' WHERE id = ?")->execute([$truck['id']]);
                            $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$assignedDriver['id']]);
                        } else {
                            $pdo->prepare(
                                "INSERT INTO driver_trips (driver_id, destination, trip_date, status, order_id, transit_end_time, is_on_time) VALUES (?, ?, CURDATE(), 'Delivered', ?, NOW(), 1)"
                            )->execute([$assignedDriver['id'], $order['destination'], $order_id]);
                        }
                    }

                    $check = $pdo->prepare("SELECT trucks_required, trucks_fulfilled, cubic_meters_required, cubic_meters_fulfilled FROM orders WHERE id = ?");
                    $check->execute([$order_id]);
                    $updated = $check->fetch();
                    $reqCm = floatval($updated['cubic_meters_required'] > 0 ? $updated['cubic_meters_required'] : $updated['trucks_required']);
                    $doneCm = floatval($updated['cubic_meters_fulfilled'] > 0 ? $updated['cubic_meters_fulfilled'] : $updated['trucks_fulfilled']);

                    if ($doneCm >= $reqCm) {
                        $pdo->prepare("UPDATE orders SET status = 'Fulfilled' WHERE id = ?")
                            ->execute([$order_id]);
                        $_SESSION['success'] = "✅ <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> logged (" . number_format($scannedCm, 2) . " cu.m). Order <strong>Fulfilled!</strong>";
                        log_activity($pdo, 'Scanned Truck', 'Scanned truck ' . $truck['truck_code'] . ' for order ID ' . $order_id . ' — Order Fulfilled');
                    } else {
                        $remainingCm = max(0, $reqCm - $doneCm);
                        $_SESSION['success'] = "✅ <strong>" . htmlspecialchars($truck['truck_code']) . "</strong> logged (" . number_format($scannedCm, 2) . " cu.m). <strong>" . number_format($remainingCm, 2) . " cu.m</strong> remaining.";
                        log_activity($pdo, 'Scanned Truck', 'Scanned truck ' . $truck['truck_code'] . ' for order ID ' . $order_id . ' (' . number_format($scannedCm, 2) . ' cu.m)');
                    }
                }
            }
        }
    }
    header("Location: dashboard.php");
    exit;
}

$myOrders = $pdo->prepare("
    SELECT o.*, COALESCE(CONCAT(c.first_name, ' ', c.last_name), u.username) AS checker_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.checker_id
    LEFT JOIN checkers c ON u.id = c.id
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

$activeOrders = array_filter($myOrders, fn($o) => in_array($o['status'], ['Pending', 'In Progress']));

$dispatchedTrucks = $pdo->query("
    SELECT t.id AS truck_id, t.truck_code, d.id AS dispatch_id, d.ticket_number, d.cubic_meters, d.destination,
           CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name
    FROM trucks t
    LEFT JOIN dispatches d ON d.truck_id = t.id AND d.status IN ('Pending', 'In Transit', 'Loading', 'Unloading')
    LEFT JOIN drivers dr ON dr.id = d.driver_id
    ORDER BY (d.id IS NOT NULL) DESC, t.truck_code ASC
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';

$_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$gravelTypeLabels = [];
foreach ($_gravel_rows as $_g) {
    $gravelTypeLabels[$_g['type_key']] = $_g['label'];
}
?>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center space-x-2">
            <i class="fa-solid fa-clipboard-check text-indigo-500"></i>
            <span>Checker Dashboard</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan truck RFID tags to confirm gravel deliveries against active orders.</p>
    </div>



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
        <?php include 'views/modals.php'; ?>
        <div class="lg:col-span-1 order-2 lg:order-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden sticky top-6">
                <!-- Panel Header -->
                <div class="bg-indigo-600 p-5 text-white">
                    <div class="flex items-center space-x-2 mb-1">
                        <i class="fa-solid fa-truck-ramp-box text-indigo-200 text-xl"></i>
                        <h2 class="font-bold text-lg">Log Delivery</h2>
                    </div>
                    <p class="text-indigo-200 text-xs">Select an active order, then scan RFID or choose a truck manually.</p>
                </div>

                <!-- Mode Toggle Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <button id="tabRfid" onclick="switchMode('rfid')"
                        class="flex-1 py-3 text-xs font-bold uppercase tracking-wide flex items-center justify-center gap-2 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 transition">
                        <i class="fa-solid fa-wifi"></i> RFID Scan
                    </button>
                    <button id="tabManual" onclick="switchMode('manual')"
                        class="flex-1 py-3 text-xs font-bold uppercase tracking-wide flex items-center justify-center gap-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                        <i class="fa-solid fa-hand-pointer"></i> Manual Select
                    </button>
                </div>

                <!-- Shared Form -->
                <form method="POST" action="dashboard.php" class="p-5 space-y-4" id="scanForm">
                    <input type="hidden" name="action" value="scan_truck">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="truck_id" id="hiddenTruckId">

                    <!-- Order Selector (shared) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            Select Order <span class="text-red-500">*</span>
                        </label>
                        <select name="order_id" id="scanOrderSelect" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">— Select active order —</option>
                            <?php foreach ($activeOrders as $ao):
                                $aoReqCm = floatval($ao['cubic_meters_required'] ?? 0) > 0 ? floatval($ao['cubic_meters_required']) : floatval($ao['trucks_required']);
                                $aoDoneCm = floatval($ao['cubic_meters_fulfilled'] ?? 0) > 0 ? floatval($ao['cubic_meters_fulfilled']) : floatval($ao['trucks_fulfilled']);
                            ?>
                                <option value="<?= $ao['id'] ?>"
                                    data-cm-req="<?= $aoReqCm ?>"
                                    data-cm-done="<?= $aoDoneCm ?>">
                                    <?= htmlspecialchars($ao['order_number']) ?> · <?= htmlspecialchars($ao['destination']) ?>
                                    (<?= number_format($aoDoneCm, 2) ?>/<?= number_format($aoReqCm, 2) ?> cu.m)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Progress Bar -->
                    <div id="scanProgress" class="hidden">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Fulfillment Progress</span>
                            <span id="scanProgressText">0/0</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div id="scanProgressBar" class="bg-indigo-500 h-2 rounded-full transition-all" style="width:0%"></div>
                        </div>
                    </div>

                    <!-- ── RFID MODE ── -->
                    <div id="rfidMode">
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            RFID Tag <span class="text-red-500">*</span>
                        </label>

                        <!-- Scanner input -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-wifi text-indigo-400"></i>
                            </span>
                            <input type="text" id="rfidInput" placeholder="Scan RFID tag here…" autocomplete="off"
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        </div>

                        <!-- Scan status feedback -->
                        <div id="rfidStatus" class="hidden mt-2 rounded-lg px-3 py-2 text-sm flex items-center gap-2"></div>

                        <!-- Scanning indicator -->
                        <div id="rfidScanning" class="hidden mt-2 flex items-center gap-2 text-indigo-500 text-sm">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Looking up truck…</span>
                        </div>

                        <!-- Resolved truck card -->
                        <div id="rfidTruckCard" class="hidden mt-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-truck text-indigo-500 text-lg"></i>
                                <div>
                                    <div id="rfidTruckCode" class="font-bold text-gray-900 dark:text-gray-100 text-sm"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Ready to confirm</div>
                                </div>
                            </div>
                            <button type="button" onclick="clearRfid()"
                                class="text-gray-400 hover:text-red-500 transition text-lg leading-none"
                                title="Clear">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Point the RFID reader at the truck tag — it will auto-detect.
                        </p>
                    </div>

                    <!-- ── MANUAL MODE ── -->
                    <div id="manualMode" class="hidden">
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            Select Dispatched Truck <span class="text-red-500">*</span>
                        </label>
                        <select id="truckSelect"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">— Select dispatched truck —</option>
                            <?php foreach ($dispatchedTrucks as $dt): ?>
                                <option value="<?= $dt['truck_id'] ?>">
                                    <?= htmlspecialchars($dt['truck_code']) ?>
                                    <?php if (!empty($dt['ticket_number'])): ?>
                                        · Ticket <?= htmlspecialchars($dt['ticket_number']) ?>
                                        (<?= number_format($dt['cubic_meters'] ?? 0, 2) ?> cu.m)<?= !empty($dt['driver_name']) ? ' · ' . htmlspecialchars($dt['driver_name']) : '' ?>
                                    <?php else: ?>
                                        · (Idle / Unassigned)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" id="confirmBtn"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2 text-sm">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Confirm Delivery</span>
                    </button>
                </form>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold">
                        <?= strtoupper(substr($checker_profile['first_name'] ?? 'C', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($checker_full_name) ?></div>
                        <div class="text-xs text-gray-500">Field Checker</div>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fa-solid fa-phone w-5 text-indigo-400"></i>
                        <span><?= htmlspecialchars($checker_profile['phone'] ?? 'No contact') ?></span>
                    </div>
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fa-solid fa-user w-5 text-indigo-400"></i>
                        <span>@<?= htmlspecialchars($checker_profile['username']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4 order-1 lg:order-2">
            <h2 class="text-base font-bold text-gray-800 dark:text-gray-200">Active & Recent Orders</h2>

            <?php if (empty($myOrders)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-400 dark:text-gray-500">
                    <i class="fa-solid fa-clipboard-list text-5xl mb-4 opacity-30"></i>
                    <p class="font-medium">No orders assigned to you yet.</p>
                    <p class="text-sm mt-1">Contact your admin to get assigned to an order.</p>
                </div>
            <?php else: ?>
                <?php foreach ($myOrders as $order):
                    $reqCm = floatval($order['cubic_meters_required'] ?? 0) > 0 ? floatval($order['cubic_meters_required']) : floatval($order['trucks_required']);
                    $doneCm = floatval($order['cubic_meters_fulfilled'] ?? 0) > 0 ? floatval($order['cubic_meters_fulfilled']) : floatval($order['trucks_fulfilled']);
                    $pct = $reqCm > 0 ? round(($doneCm / $reqCm) * 100) : 0;
                    $gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];
                    $statusColors = [
                        'Pending'     => ['bar' => 'bg-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', 'border' => 'border-l-yellow-400'],
                        'In Progress' => ['bar' => 'bg-indigo-500', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200', 'border' => 'border-l-indigo-400'],
                        'Fulfilled'   => ['bar' => 'bg-green-500',  'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',   'border' => 'border-l-green-400'],
                        'Cancelled'   => ['bar' => 'bg-red-400',    'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',           'border' => 'border-l-red-400'],
                    ];
                    $sc = $statusColors[$order['status']] ?? $statusColors['Pending'];

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
                                    <i class="fa-solid fa-cube text-gray-400"></i>
                                    <span><?= number_format($doneCm, 2) ?>/<?= number_format($reqCm, 2) ?> cu.m</span>
                                </span>
                                <?php if ($order['checker_name']): ?>
                                    <span class="flex items-center space-x-1">
                                        <i class="fa-solid fa-user-shield text-indigo-400"></i>
                                        <span><?= htmlspecialchars($order['checker_name']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Fulfillment Progress</span>
                                    <span class="font-semibold"><?= $pct ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                    <div class="<?= $sc['bar'] ?> h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>

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
    // ── Mode switching ──────────────────────────────────────────────
    let currentMode = 'rfid';

    function switchMode(mode) {
        currentMode = mode;
        const rfidEl = document.getElementById('rfidMode');
        const manualEl = document.getElementById('manualMode');
        const tabRfid = document.getElementById('tabRfid');
        const tabManu = document.getElementById('tabManual');

        if (mode === 'rfid') {
            rfidEl.classList.remove('hidden');
            manualEl.classList.add('hidden');
            tabRfid.classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
            tabRfid.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            tabManu.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
            tabManu.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            // Clear hidden truck id when switching
            document.getElementById('hiddenTruckId').value = '';
            updateConfirmBtn();
            document.getElementById('rfidInput').focus();
        } else {
            manualEl.classList.remove('hidden');
            rfidEl.classList.add('hidden');
            tabManu.classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
            tabManu.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            tabRfid.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
            tabRfid.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            document.getElementById('truckSelect').focus();
        }
    }

    // ── Order progress bar ──────────────────────────────────────────
    const scanOrderSelect = document.getElementById('scanOrderSelect');
    const scanProgress = document.getElementById('scanProgress');
    const scanProgressBar = document.getElementById('scanProgressBar');
    const scanProgressText = document.getElementById('scanProgressText');

    if (scanOrderSelect) {
        scanOrderSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) {
                scanProgress.classList.add('hidden');
                return;
            }
            const req = parseFloat(opt.dataset.cmReq) || 0;
            const done = parseFloat(opt.dataset.cmDone) || 0;
            const pct = req > 0 ? Math.round((done / req) * 100) : 0;
            scanProgressText.innerText = done.toFixed(2) + ' / ' + req.toFixed(2) + ' cu.m';
            scanProgressBar.style.width = Math.min(100, pct) + '%';
            scanProgress.classList.remove('hidden');
        });
    }

    // ── Confirm button state ────────────────────────────────────────
    function updateConfirmBtn() {
        const btn = document.getElementById('confirmBtn');
        if (currentMode === 'rfid') {
            btn.disabled = !document.getElementById('hiddenTruckId').value;
        } else {
            btn.disabled = false;
        }
    }

    // ── RFID scanning logic ─────────────────────────────────────────
    let rfidDebounce = null;

    document.getElementById('rfidInput').addEventListener('input', function() {
        clearTimeout(rfidDebounce);
        const val = this.value.trim();
        if (!val) {
            clearRfid();
            return;
        }
        // Debounce: RFID readers typically dump all chars quickly
        rfidDebounce = setTimeout(() => lookupRfid(val), 350);
    });

    // Also allow pressing Enter to trigger lookup immediately
    document.getElementById('rfidInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(rfidDebounce);
            lookupRfid(this.value.trim());
        }
    });

    function lookupRfid(tag) {
        if (!tag) return;
        setRfidStatus('scanning');
        fetch('get_truck_by_rfid.php?rfid=' + encodeURIComponent(tag))
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('hiddenTruckId').value = data.truck_id;
                    document.getElementById('rfidTruckCode').textContent = data.truck_code;
                    setRfidStatus('found');
                } else {
                    document.getElementById('hiddenTruckId').value = '';
                    setRfidStatus('error', data.message || 'No truck found for this RFID tag.');
                }
                updateConfirmBtn();
            })
            .catch(() => {
                setRfidStatus('error', 'Network error. Please try again.');
                updateConfirmBtn();
            });
    }

    function setRfidStatus(state, msg) {
        const statusEl = document.getElementById('rfidStatus');
        const scanningEl = document.getElementById('rfidScanning');
        const cardEl = document.getElementById('rfidTruckCard');

        statusEl.classList.add('hidden');
        scanningEl.classList.add('hidden');
        cardEl.classList.add('hidden');

        if (state === 'scanning') {
            scanningEl.classList.remove('hidden');
        } else if (state === 'found') {
            cardEl.classList.remove('hidden');
        } else if (state === 'error') {
            statusEl.className = 'mt-2 rounded-lg px-3 py-2 text-sm flex items-center gap-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700';
            statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i><span>' + (msg || 'Unknown error') + '</span>';
            statusEl.classList.remove('hidden');
        }
    }

    function clearRfid() {
        document.getElementById('rfidInput').value = '';
        document.getElementById('hiddenTruckId').value = '';
        document.getElementById('rfidTruckCard').classList.add('hidden');
        document.getElementById('rfidStatus').classList.add('hidden');
        document.getElementById('rfidScanning').classList.add('hidden');
        updateConfirmBtn();
        document.getElementById('rfidInput').focus();
    }

    // ── Form submission: populate hidden truck_id ───────────────────
    document.getElementById('scanForm').addEventListener('submit', function(e) {
        if (currentMode === 'manual') {
            const sel = document.getElementById('truckSelect');
            if (!sel.value) {
                e.preventDefault();
                sel.focus();
                return;
            }
            document.getElementById('hiddenTruckId').value = sel.value;
        }
        if (!document.getElementById('hiddenTruckId').value) {
            e.preventDefault();
            if (currentMode === 'rfid') {
                setRfidStatus('error', 'Please scan a valid RFID tag first.');
            }
        }
    });

    // ── Manual select change ────────────────────────────────────────
    document.getElementById('truckSelect').addEventListener('change', updateConfirmBtn);

    // ── Theme icon ──────────────────────────────────────────────────
    document.addEventListener("DOMContentLoaded", function() {
        if (document.documentElement.classList.contains('dark')) {
            const icon = document.getElementById('themeIcon');
            if (icon) icon.classList.replace('fa-moon', 'fa-sun');
        }
        // Start in RFID mode
        switchMode('rfid');
        updateConfirmBtn();
    });
</script>

</div><!-- close #main-content -->
<?php include '../includes/scripts.php'; ?>
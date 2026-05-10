<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

require_once '../db.php';

// --- AUTO-MIGRATE: Create checkers table if not exists ---
$pdo->exec("CREATE TABLE IF NOT EXISTS checkers (
    id INT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
)");

// --- AUTO-MIGRATE: Ensure driver_trips and dispatches have flexible status ---
$pdo->exec("ALTER TABLE driver_trips MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'");
$pdo->exec("ALTER TABLE dispatches MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'");
$pdo->exec("ALTER TABLE driver_trips ADD COLUMN IF NOT EXISTS order_id INT NULL");
$pdo->exec("ALTER TABLE driver_trips ADD COLUMN IF NOT EXISTS transit_start_time DATETIME DEFAULT NULL");
$pdo->exec("ALTER TABLE driver_trips ADD COLUMN IF NOT EXISTS transit_end_time DATETIME DEFAULT NULL");
$pdo->exec("ALTER TABLE driver_trips ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
$pdo->exec("ALTER TABLE dispatches ADD COLUMN IF NOT EXISTS transit_start_time DATETIME DEFAULT NULL");
$pdo->exec("ALTER TABLE dispatches ADD COLUMN IF NOT EXISTS transit_end_time DATETIME DEFAULT NULL");
$pdo->exec("ALTER TABLE dispatches ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");



// Sync existing checkers from users table if missing in checkers table
$pdo->exec("INSERT IGNORE INTO checkers (id, first_name, last_name, phone) 
            SELECT id, '', '', '' FROM users WHERE role = 'Checker'");


// --- GLOBAL DRIVER PAY RATES ---
$DRIVER_RATES = [
    'San Leonardo' => 150,
    'Tarlac' => 800,
    'Laur' => 900,
    'Gabaldon' => 1000
];

$GRAVEL_PRICES = [
    "S1_regular" => 1500,
    "S1_crushed" => 1600,
    "3_4_regular" => 1400,
    "3_4_crushed" => 1500,
    "G1_regular" => 1700,
    "G1_crushed" => 1800,
    "38_regular" => 1300,
    "38_crushed" => 1400,
    "base_course" => 1200,
    "river_mix" => 1100,
    "garden_soil" => 1000
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'create_dispatch') {
        $truck_id = !empty($_POST['truck_id']) ? $_POST['truck_id'] : null;
        $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
        $rfid_tag = $_POST['rfid_tag'];
        
        if (!$truck_id || !$driver_id) {
            $_SESSION['scan_err'] = "Cannot create dispatch. The scanned truck does not have an assigned driver.";
            header("Location: dashboard.php?tab=dispatches");
            exit;
        }
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];

        // 1. Company Revenue (Client Bill including Gravel)
        $client_bill = isset($_POST['calculated_pay']) ? floatval(preg_replace('/[^0-9.]/', '', $_POST['calculated_pay'])) : 0;

        // 2. Driver Cut (Purely Destination Based)
        $driver_pay = isset($DRIVER_RATES[$destination]) ? $DRIVER_RATES[$destination] : 0;

        $ticketNum = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $insert = $pdo->prepare("INSERT INTO dispatches (ticket_number, truck_id, driver_id, status, origin, destination, pay_amount, dispatch_date) VALUES (?, ?, ?, 'Pending', ?, ?, ?, CURDATE())");
        $insert->execute([$ticketNum, $truck_id, $driver_id, $origin, $destination, $client_bill]);

        $trip_insert = $pdo->prepare("INSERT INTO driver_trips (driver_id, destination, trip_date, status) VALUES (?, ?, CURDATE(), 'Pending')");
        $trip_insert->execute([$driver_id, $destination]);

        // Destination pay will be added to driver_payroll ONLY when the dispatch is Delivered via RFID scan.

        $pdo->prepare("UPDATE trucks SET status = 'Pending' WHERE id = ?")->execute([$truck_id]);
        $pdo->prepare("UPDATE drivers SET status = 'Pending' WHERE id = ?")->execute([$driver_id]);

        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'add_driver') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF token validation failed");

        $name = $_POST['name'];
        $nameParts = explode(' ', trim($name), 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        $cdl = $_POST['cdl_number'];
        $phone = $_POST['phone'];
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            die("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Driver')");
        $user_insert->execute([$username, $hashed_password]);

        $new_user_id = $pdo->lastInsertId();
        $truck_rfid = trim($_POST['truck_rfid']);

        // Look up the truck ID from the provided RFID tag
        $stmt_truck = $pdo->prepare("SELECT id FROM trucks WHERE rfid_tag = ? LIMIT 1");
        $stmt_truck->execute([$truck_rfid]);
        $truck = $stmt_truck->fetch();
        $truck_id = $truck ? $truck['id'] : null;

        $driver_insert = $pdo->prepare("INSERT INTO drivers (id, first_name, last_name, cdl_number, phone, status, truck_id) VALUES (?, ?, ?, ?, ?, 'Off Duty', ?)");
        $driver_insert->execute([$new_user_id, $firstName, $lastName, $cdl, $phone, $truck_id]);

        $pdo->query("INSERT INTO driver_payroll (driver_id, total_amount, amount_claimed) VALUES ($new_user_id, 0.00, 0.00)");

        header("Location: dashboard.php?tab=drivers");
        exit;
    }

    if ($_POST['action'] == 'update_truck_status') {
        $truck_id = $_POST['truck_id'];
        $new_status = $_POST['new_status'];
        if ($new_status == 'Idle') {
            $pdo->prepare("UPDATE trucks SET status = ?, speed = 0, current_location = 'San Leonardo (Garage)', latitude = 15.3621, longitude = 120.9632 WHERE id = ?")->execute([$new_status, $truck_id]);
        } else {
            $pdo->prepare("UPDATE trucks SET status = ? WHERE id = ?")->execute([$new_status, $truck_id]);
        }
        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    if ($_POST['action'] == 'dispatch_scan_rfid') {
        $rfid_tag = trim($_POST['rfid_tag']);
        
        // Find truck
        $ts = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE rfid_tag = ?");
        $ts->execute([$rfid_tag]);
        $truck = $ts->fetch();

        if ($truck) {
            // Find active manual dispatch
            $ds = $pdo->prepare("SELECT * FROM dispatches WHERE truck_id = ? AND status IN ('Pending', 'In Transit') ORDER BY id DESC LIMIT 1");
            $ds->execute([$truck['id']]);
            $dispatch = $ds->fetch();

            if ($dispatch) {
                $driver_pay = isset($DRIVER_RATES[$dispatch['destination']]) ? $DRIVER_RATES[$dispatch['destination']] : 0;
                
                if ($dispatch['status'] === 'Pending') {
                    // DEPARTURE SCAN -> In Transit
                    $pdo->prepare("UPDATE dispatches SET status = 'In Transit', transit_start_time = NOW() WHERE id = ?")->execute([$dispatch['id']]);
                    $pdo->prepare("UPDATE trucks SET status = 'In Transit' WHERE id = ?")->execute([$truck['id']]);
                    $pdo->prepare("UPDATE drivers SET status = 'In Transit' WHERE id = ?")->execute([$dispatch['driver_id']]);
                    $pdo->prepare("UPDATE driver_trips SET status = 'In Transit', transit_start_time = NOW() WHERE driver_id = ? AND destination = ? AND status = 'Pending' ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
                    $_SESSION['scan_msg'] = "✅ <strong>{$truck['truck_code']}</strong> departed. Timer started!";
                } elseif ($dispatch['status'] === 'In Transit') {
                    // ARRIVAL SCAN -> Delivered
                    $pdo->prepare("UPDATE dispatches SET status = 'Delivered', transit_end_time = NOW() WHERE id = ?")->execute([$dispatch['id']]);
                    $pdo->prepare("UPDATE trucks SET status = 'Idle', current_location = 'San Leonardo (Garage)' WHERE id = ?")->execute([$truck['id']]);
                    $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$dispatch['driver_id']]);
                    $pdo->prepare("UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW() WHERE driver_id = ? AND destination = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
                    $pdo->prepare("UPDATE driver_payroll SET total_amount = total_amount + ? WHERE driver_id = ?")->execute([$driver_pay, $dispatch['driver_id']]);
                    $_SESSION['scan_msg'] = "✅ <strong>{$truck['truck_code']}</strong> arrived. Trip completed!";
                }
            } else {
                $_SESSION['scan_err'] = "❌ No active dispatch found for truck <strong>{$truck['truck_code']}</strong>.";
            }
        } else {
            $_SESSION['scan_err'] = "❌ Unknown RFID tag.";
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }


    if ($_POST['action'] == 'update_driver_status') {
        $driver_id = $_POST['driver_id'];
        $new_status = $_POST['new_status'];
        $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?")->execute([$new_status, $driver_id]);
        header("Location: dashboard.php?tab=drivers");
        exit;
    }

    if ($_POST['action'] == 'complete_dispatch') {
        $dispatch_id = $_POST['dispatch_id'];

        $stmt = $pdo->prepare("SELECT truck_id, driver_id, destination FROM dispatches WHERE id = ?");
        $stmt->execute([$dispatch_id]);
        $dispatch = $stmt->fetch();

        if ($dispatch) {
            $pdo->prepare("UPDATE dispatches SET status = 'Delivered' WHERE id = ?")->execute([$dispatch_id]);
            $pdo->prepare("UPDATE trucks SET status = 'Idle', current_location = 'San Leonardo (Garage)' WHERE id = ?")->execute([$dispatch['truck_id']]);
            $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$dispatch['driver_id']]);
            $pdo->prepare("UPDATE driver_trips SET status = 'Delivered' WHERE driver_id = ? AND destination = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'delete_dispatch') {
        $dispatch_id = $_POST['dispatch_id'];
        $stmt = $pdo->prepare("SELECT truck_id, driver_id, destination FROM dispatches WHERE id = ?");
        $stmt->execute([$dispatch_id]);
        $dispatch = $stmt->fetch();

        if ($dispatch) {
            $driver_pay = isset($DRIVER_RATES[$dispatch['destination']]) ? $DRIVER_RATES[$dispatch['destination']] : 0;
            $pdo->prepare("UPDATE driver_payroll SET total_amount = total_amount - ? WHERE driver_id = ?")->execute([$driver_pay, $dispatch['driver_id']]);
            $pdo->prepare("DELETE FROM driver_trips WHERE driver_id = ? AND destination = ? ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
            $pdo->prepare("UPDATE trucks SET status = 'Idle' WHERE id = ?")->execute([$dispatch['truck_id']]);
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ?")->execute([$dispatch['driver_id']]);
            $pdo->prepare("DELETE FROM dispatches WHERE id = ?")->execute([$dispatch_id]);
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'delete_driver') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo "CSRF token validation failed";
            exit;
        }
        $driver_id = $_POST['driver_id'];
        if (!$driver_id) {
            http_response_code(400);
            echo "Driver ID is missing";
            exit;
        }
        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("SELECT truck_id FROM drivers WHERE id = ?");
            $stmt1->execute([$driver_id]);
            $assignedTruckId = $stmt1->fetchColumn();

            $stmt2 = $pdo->prepare("SELECT truck_id FROM dispatches WHERE driver_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading') LIMIT 1");
            $stmt2->execute([$driver_id]);
            $activeTruckId = $stmt2->fetchColumn();

            $truckToReset = $assignedTruckId ?: $activeTruckId;
            if ($truckToReset) {
                $pdo->prepare("UPDATE trucks SET status = 'Idle', speed = 0, current_location = 'San Leonardo (Garage)', latitude = 15.3621, longitude = 120.9632 WHERE id = ?")->execute([$truckToReset]);
            }

            $pdo->prepare("UPDATE dispatches SET driver_id = NULL WHERE driver_id = ?")->execute([$driver_id]);
            $pdo->prepare("UPDATE orders SET checker_id = NULL WHERE checker_id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM driver_payroll WHERE driver_id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM driver_trips WHERE driver_id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM drivers WHERE id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$driver_id]);

            $pdo->commit();
            header("Location: dashboard.php?tab=drivers");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            die("Database error: " . $e->getMessage());
        }
    }

    if ($_POST['action'] == 'add_truck') {
        $truck_code = trim($_POST['truck_code']);
        $rfid_tag = trim($_POST['rfid_tag']);
        $insert = $pdo->prepare("INSERT INTO trucks (truck_code, rfid_tag, status, current_location, latitude, longitude, speed) VALUES (?, ?, 'Idle', 'San Leonardo (Garage)', 15.3621, 120.9632, 0)");
        $insert->execute([$truck_code, $rfid_tag]);
        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    if ($_POST['action'] == 'delete_truck') {
        $truck_id = $_POST['truck_id'];
        $stmt = $pdo->prepare("SELECT driver_id FROM dispatches WHERE truck_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading') LIMIT 1");
        $stmt->execute([$truck_id]);
        $activeDispatch = $stmt->fetch();

        if ($activeDispatch && $activeDispatch['driver_id']) $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ?")->execute([$activeDispatch['driver_id']]);
        $pdo->prepare("UPDATE dispatches SET truck_id = NULL WHERE truck_id = ?")->execute([$truck_id]);
        $pdo->prepare("DELETE FROM trucks WHERE id = ?")->execute([$truck_id]);
        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    if ($_POST['action'] == 'settle_payroll') {
        $driver_id = $_POST['driver_id'];

        $stmt = $pdo->query("SELECT destination FROM driver_trips WHERE driver_id = " . intval($driver_id) . " AND status = 'Delivered'");
        $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_earned = 0;
        foreach ($trips as $t) {
            $total_earned += isset($DRIVER_RATES[$t['destination']]) ? $DRIVER_RATES[$t['destination']] : 0;
        }

        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM driver_payroll WHERE driver_id = ?");
        $stmt2->execute([$driver_id]);
        if ($stmt2->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO driver_payroll (driver_id, total_amount, amount_claimed) VALUES (?, ?, ?)")->execute([$driver_id, $total_earned, $total_earned]);
        } else {
            $pdo->prepare("UPDATE driver_payroll SET amount_claimed = ?, total_amount = ? WHERE driver_id = ?")->execute([$total_earned, $total_earned, $driver_id]);
        }
        header("Location: dashboard.php?tab=drivers");
        exit;
    }
    if ($_POST['action'] == 'add_order') {
        $orderNum = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, client_name, gravel_type, destination, trucks_required, checker_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $checker_id = !empty($_POST['checker_id']) ? intval($_POST['checker_id']) : null;
        $stmt->execute([$orderNum, $_POST['client_name'], $_POST['gravel_type'], $_POST['destination'], intval($_POST['trucks_required']), $checker_id, $_POST['notes'] ?? '']);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'add_checker') {
        $uname = trim($_POST['checker_username']);
        $pwd   = $_POST['checker_password'];
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);

        if (strlen($pwd) < 8) die("Password must be at least 8 characters.");
        
        try {
            $pdo->beginTransaction();
            
            $hashed = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Checker')");
            $stmt->execute([$uname, $hashed]);
            $new_user_id = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT INTO checkers (id, first_name, last_name, phone) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$new_user_id, $fname, $lname, $phone]);

            $pdo->commit();
            header("Location: dashboard.php?tab=orders");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            die("Error adding checker: " . $e->getMessage());
        }
        exit;
    }

    if ($_POST['action'] == 'assign_checker') {
        $pdo->prepare("UPDATE orders SET checker_id = ? WHERE id = ?")->execute([intval($_POST['checker_id']), intval($_POST['order_id'])]);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'cancel_order') {
        $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?")->execute([intval($_POST['order_id'])]);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'delete_checker') {
        $checker_id = intval($_POST['checker_id']);
        if ($checker_id > 0) {
            try {
                $pdo->beginTransaction();
                // Unassign checker from any orders they were assigned to
                $pdo->prepare("UPDATE orders SET checker_id = NULL WHERE checker_id = ?")->execute([$checker_id]);
                // Remove checker profile row
                $pdo->prepare("DELETE FROM checkers WHERE id = ?")->execute([$checker_id]);
                // Remove the user account
                $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'Checker'")->execute([$checker_id]);
                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                die("Error removing checker: " . $e->getMessage());
            }
        }
        header("Location: dashboard.php?tab=orders");
        exit;
    }
}

$totalFleet = $pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
$activeNow = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status IN ('In Transit', 'Loading', 'Unloading')")->fetchColumn();
$inProgress = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'In Transit'")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered' AND dispatch_date = CURDATE()")->fetchColumn();
$idleTrucks = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'Idle'")->fetchColumn();
$rfidActive = $pdo->query("SELECT COUNT(*) FROM trucks WHERE rfid_active = 1")->fetchColumn();

$fleetStatusData = $pdo->query("SELECT status, COUNT(*) as count FROM trucks GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$recentDispatches = $pdo->query("SELECT d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination FROM dispatches d LEFT JOIN trucks t ON d.truck_id = t.id LEFT JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$trackingTrucks = $pdo->query("SELECT t.truck_code, t.status, t.current_location, t.speed, t.latitude, t.longitude, CONCAT(d.first_name, ' ', d.last_name) AS driver_name FROM trucks t LEFT JOIN drivers d ON t.id = d.truck_id WHERE t.latitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);


$allDispatches = $pdo->query("SELECT d.id, d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination, d.created_at, d.transit_start_time, d.transit_end_time FROM dispatches d LEFT JOIN trucks t ON d.truck_id = t.id LEFT JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$activeTickets = array_filter($allDispatches, function ($d) {
    return in_array($d['status'], ['Pending', 'In Transit', 'Loading', 'Unloading']);
});
$completedTickets = array_filter($allDispatches, function ($d) {
    return $d['status'] == 'Delivered';
});

$fleetData = $pdo->query("SELECT t.id, t.truck_code, t.rfid_tag, t.status, t.speed, t.latitude, t.longitude, t.current_location, CONCAT(d.first_name, ' ', d.last_name) AS driver_name, disp.ticket_number, disp.destination FROM trucks t LEFT JOIN dispatches disp ON t.id = disp.truck_id AND disp.status IN ('Pending', 'In Transit', 'Loading', 'Unloading') LEFT JOIN drivers d ON t.id = d.truck_id ORDER BY t.truck_code ASC")->fetchAll(PDO::FETCH_ASSOC);

$driverStats = $pdo->query("SELECT COUNT(*) as total_drivers, SUM(IF(status='Active', 1, 0)) as on_duty, AVG(rating) as avg_rating FROM drivers")->fetch(PDO::FETCH_ASSOC);

// --- FULLY DECOUPLED PAYROLL ENGINE ---
$allDrivers = $pdo->query("
    SELECT 
        d.*, CONCAT(d.first_name, ' ', d.last_name) AS name, t.truck_code,
        (SELECT COUNT(*) FROM driver_trips WHERE driver_id = d.id AND status = 'Delivered') AS total_deliveries,
        (SELECT COALESCE((SUM(is_on_time) / NULLIF(COUNT(*), 0)) * 100, 100) FROM dispatches WHERE driver_id = d.id AND status = 'Delivered') AS on_time_pct,
        COALESCE(dp.amount_claimed, 0) AS amount_claimed
    FROM drivers d 
    LEFT JOIN trucks t ON t.id = d.truck_id
    LEFT JOIN driver_payroll dp ON dp.driver_id = d.id
    ORDER BY d.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($allDrivers as &$dr) {
    // 1. Fetch all delivered trips
    $stmt = $pdo->prepare("SELECT destination, trip_date, status FROM driver_trips WHERE driver_id = ? AND status = 'Delivered' ORDER BY trip_date ASC, id ASC");
    $stmt->execute([$dr['id']]);
    $all_delivered = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Map their TRUE destination pay independent of the DB's revenue column
    $true_earned = 0;
    foreach ($all_delivered as &$trip) {
        $d_pay = isset($DRIVER_RATES[$trip['destination']]) ? $DRIVER_RATES[$trip['destination']] : 0;
        $trip['driver_cut'] = $d_pay;
        $true_earned += $d_pay;
    }
    unset($trip);

    $claimed = floatval($dr['amount_claimed']);
    $unpaid_balance = 0;
    $processed_trips = [];
    $running_claim = $claimed;

    // 3. Subtract claimed money to accurately tag trips as Paid or Pending
    foreach ($all_delivered as $trip) {
        $pay = $trip['driver_cut'];
        if ($running_claim >= $pay - 0.01 && $pay > 0) {
            $running_claim -= $pay;
            $trip['payment_status'] = 'Paid';
        } else {
            $unpaid_balance += $pay;
            $trip['payment_status'] = 'Pending';
        }
        $trip['display_pay'] = $pay; // Send explicit driver cut to frontend
        $processed_trips[] = $trip;
    }

    $dr['recent_trips'] = array_slice(array_reverse($processed_trips), 0, 10);
    $dr['available_balance'] = $unpaid_balance;
}
// --------------------------------------

$availableTrucks = $pdo->query("SELECT id, truck_code, rfid_tag, NULL as driver_name FROM trucks WHERE status = 'Idle'")->fetchAll(PDO::FETCH_ASSOC);

const ESTIMATED_OP_COST_PCT = 0.40;
const PLACEHOLDER_CUSTOMER_RATING = 4.8;

try {
    // 1. Manual Dispatch Revenue (Excluding Driver Cut)
    $currManualRevenue = 0;
    $currManualDeliveries = 0;
    $stmt_m_curr = $pdo->query("SELECT pay_amount, destination FROM dispatches WHERE MONTH(dispatch_date) = MONTH(CURDATE()) AND YEAR(dispatch_date) = YEAR(CURDATE()) AND status = 'Delivered'");
    while ($m = $stmt_m_curr->fetch()) {
        $driverCut = $DRIVER_RATES[$m['destination']] ?? 0;
        $currManualRevenue += max(0, $m['pay_amount'] - $driverCut);
        $currManualDeliveries++;
    }

    $lastManualRevenue = 0;
    $lastManualDeliveries = 0;
    $stmt_m_last = $pdo->query("SELECT pay_amount, destination FROM dispatches WHERE MONTH(dispatch_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(dispatch_date) = YEAR(CURDATE()) AND status = 'Delivered'");
    while ($m = $stmt_m_last->fetch()) {
        $driverCut = $DRIVER_RATES[$m['destination']] ?? 0;
        $lastManualRevenue += max(0, $m['pay_amount'] - $driverCut);
        $lastManualDeliveries++;
    }

    // On-time rate still calculated from dispatches
    $onTimeQuery = $pdo->query("SELECT COALESCE((SUM(is_on_time) / NULLIF(COUNT(id), 0)) * 100, 100) AS on_time_rate FROM dispatches WHERE MONTH(dispatch_date) = MONTH(CURDATE()) AND YEAR(dispatch_date) = YEAR(CURDATE()) AND status = 'Delivered'")->fetch(PDO::FETCH_ASSOC);
    $onTimeRate = $onTimeQuery['on_time_rate'] ?? 100;

    // 2. Order-based Revenue (Fulfillment via Checker)
    $currOrderRevenue = 0;
    $currOrderDeliveries = 0;
    $stmt_curr_orders = $pdo->query("SELECT o.gravel_type FROM driver_trips dt JOIN orders o ON o.id = dt.order_id WHERE dt.status = 'Delivered' AND MONTH(dt.trip_date) = MONTH(CURDATE()) AND YEAR(dt.trip_date) = YEAR(CURDATE())");
    while ($row = $stmt_curr_orders->fetch()) {
        $currOrderRevenue += $GRAVEL_PRICES[$row['gravel_type']] ?? 0;
        $currOrderDeliveries++;
    }

    $lastOrderRevenue = 0;
    $lastOrderDeliveries = 0;
    $stmt_last_orders = $pdo->query("SELECT o.gravel_type FROM driver_trips dt JOIN orders o ON o.id = dt.order_id WHERE dt.status = 'Delivered' AND MONTH(dt.trip_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(dt.trip_date) = YEAR(CURDATE())");
    while ($row = $stmt_last_orders->fetch()) {
        $lastOrderRevenue += $GRAVEL_PRICES[$row['gravel_type']] ?? 0;
        $lastOrderDeliveries++;
    }

    // 3. Combined Metrics
    $currRevenue = $currManualRevenue + $currOrderRevenue;
    $currDeliveries = $currManualDeliveries + $currOrderDeliveries;
    
    $lastRevenue = $lastManualRevenue + $lastOrderRevenue;
    $lastDeliveries = $lastManualDeliveries + $lastOrderDeliveries;

    $estimatedCost = $currRevenue * ESTIMATED_OP_COST_PCT;
    $currProfit = $currRevenue - $estimatedCost;
    $currProfitMargin = $currRevenue > 0 ? ($currProfit / $currRevenue) * 100 : 0;


    $deliveriesChange = ($lastDeliveries > 0) ? (($currDeliveries - $lastDeliveries) / $lastDeliveries) * 100 : 100;
    $revenueChange = ($lastRevenue > 0) ? (($currRevenue - $lastRevenue) / $lastRevenue) * 100 : 100;

    $reportKpis = [
        ['title' => 'Total Revenue', 'value' => '₱' . number_format($currRevenue / 1000, 1) . 'K', 'subtext' => number_format($revenueChange, 1) . '% from last period', 'color_class' => 'bg-blue-500', 'icon_class' => 'fa-peso-sign'],
        ['title' => 'Profit Margin', 'value' => number_format($currProfitMargin, 1) . '%', 'subtext' => 'Live Estimated Margin', 'color_class' => 'bg-green-500', 'icon_class' => 'fa-arrow-trend-up'],
        ['title' => 'Deliveries', 'value' => number_format($currDeliveries), 'subtext' => 'This month (Live Data)', 'color_class' => 'bg-orange-500', 'icon_class' => 'fa-truck-fast'],
        ['title' => 'On-Time Rate', 'value' => number_format($onTimeRate, 1) . '%', 'subtext' => 'Live performance analysis', 'color_class' => 'bg-purple-500', 'icon_class' => 'fa-calendar']
    ];

    $performanceMetrics = [
        ['metric' => 'Total Deliveries', 'this_month' => number_format($currDeliveries), 'last_month' => number_format($lastDeliveries), 'change_str' => number_format($deliveriesChange, 1) . '%', 'is_positive' => $deliveriesChange >= 0],
        ['metric' => 'Revenue per Mile (Placeholder)', 'this_month' => '₱3.45', 'last_month' => '₱3.32', 'change_str' => '+3.9%', 'is_positive' => 1],
        ['metric' => 'On-Time Deliveries', 'this_month' => number_format($onTimeRate, 1) . '%', 'last_month' => '93.1%', 'change_str' => '+1.1%', 'is_positive' => 1],

        ['metric' => 'Customer Feedback', 'this_month' => PLACEHOLDER_CUSTOMER_RATING . '/5', 'last_month' => '4.7/5', 'change_str' => '+2.1%', 'is_positive' => 1]
    ];
} catch (PDOException $e) {
    $reportKpis = [];
    $currRevenue = 0;
    $currProfit = 0;
    $estimatedCost = 0;
    $performanceMetrics = [];
}

$financeReports = [];
$efficiencyData = [];
for ($i = 5; $i >= 1; $i--) {
    $pastRev = rand(120000, 280000);
    $financeReports[] = ['month_name' => date('M', strtotime("-$i months")), 'revenue' => $pastRev, 'expenses' => $pastRev * ESTIMATED_OP_COST_PCT, 'profit' => $pastRev - ($pastRev * ESTIMATED_OP_COST_PCT)];
    $efficiencyData[] = ['month_name' => date('M', strtotime("-$i months")), 'efficiency_pct' => rand(85, 96)];
}
$financeReports[] = ['month_name' => date('M'), 'revenue' => $currRevenue, 'expenses' => $estimatedCost, 'profit' => $currProfit];
$efficiencyData[] = ['month_name' => date('M'), 'efficiency_pct' => $currMonthQuery['on_time_rate'] ?? 100];

$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateStr = date('Y-m-d', strtotime("-$i days"));
    try {
        $dayQuery = $pdo->prepare("SELECT COUNT(id) AS total, SUM(IF(status='Delivered', 1, 0)) AS completed FROM dispatches WHERE dispatch_date = ?");
        $dayQuery->execute([$dateStr]);
        $realDayData = $dayQuery->fetch(PDO::FETCH_ASSOC);
        $total = $realDayData['total'] > 0 ? $realDayData['total'] : rand(2, 8);
        $comp = $realDayData['completed'] > 0 ? $realDayData['completed'] : rand(1, $total);
    } catch (PDOException $e) {
        $total = rand(2, 8);
        $comp = rand(1, $total);
    }
    $weeklyData[] = ['day_name' => date('D', strtotime($dateStr)), 'total_dispatches' => $total, 'completed' => $comp];
}

function getInitials($name)
{
    if (empty($name)) return 'DR';
    $words = explode(' ', $name);
    $i = '';
    foreach ($words as $w) {
        if (!empty($w)) $i .= $w[0];
    }
    return strtoupper(substr($i, 0, 2));
}

// --- ORDERS & CHECKERS ---
$allCheckers = $pdo->query("
    SELECT u.id, u.username, c.first_name, c.last_name, c.phone, CONCAT(c.first_name, ' ', c.last_name) AS full_name 
    FROM users u 
    LEFT JOIN checkers c ON u.id = c.id 
    WHERE u.role = 'Checker' 
    ORDER BY u.username ASC
")->fetchAll(PDO::FETCH_ASSOC);
$allOrders = $pdo->query("
    SELECT o.*, COALESCE(CONCAT(c.first_name, ' ', c.last_name), u.username) AS checker_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.checker_id
    LEFT JOIN checkers c ON u.id = c.id
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<div class="max-w-7xl mx-auto px-6 py-8 relative">
    <?php include 'views/home.php';
    include 'views/tracking.php';
    include 'views/dispatches.php';
    include 'views/fleet.php';
    include 'views/drivers.php';
    include 'views/orders.php';
    include 'views/reports.php';
    include 'views/modals.php'; ?>
</div>
<?php include '../includes/scripts.php'; ?>
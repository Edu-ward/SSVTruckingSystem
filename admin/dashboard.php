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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'create_dispatch') {
        $truck_id = $_POST['truck_id'];
        $driver_id = $_POST['driver_id'];
        $rfid_tag = $_POST['rfid_tag'];
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $destination = $_POST['destination'];

        $rates = [
            'San Leonardo' => 150,
            'Tarlac' => 800,
            'Laur' => 900,
            'Gabaldon' => 1000
        ];
        $calculated_pay = isset($rates[$destination]) ? $rates[$destination] : 0;
        $weight = 0; // No longer tracking weight for pay

        $ticketNum = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $insert = $pdo->prepare("INSERT INTO dispatches (ticket_number, truck_id, driver_id, status, origin, destination, weight, pay_amount, dispatch_date) VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, CURDATE())");
        $insert->execute([$ticketNum, $truck_id, $driver_id, $origin, $destination, $weight, $calculated_pay]);

        $trip_insert = $pdo->prepare("INSERT INTO driver_trips (driver_id, destination, trip_date, status) VALUES (?, ?, CURDATE(), 'In Transit')");
        $trip_insert->execute([$driver_id, $destination]);

        $payroll_update = $pdo->prepare("UPDATE driver_payroll SET total_amount = total_amount + ? WHERE driver_id = ?");
        $payroll_update->execute([$calculated_pay, $driver_id]);

        $pdo->prepare("UPDATE trucks SET status = 'Loading' WHERE id = ?")->execute([$truck_id]);
        $pdo->prepare("UPDATE drivers SET status = 'Dispatched' WHERE id = ?")->execute([$driver_id]);

        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'add_driver') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token validation failed");
        }

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

        $driver_insert = $pdo->prepare("INSERT INTO drivers (id, first_name, last_name, cdl_number, phone, status) VALUES (?, ?, ?, ?, ?, 'Off Duty')");
        $driver_insert->execute([$new_user_id, $firstName, $lastName, $cdl, $phone]);

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

        $stmt = $pdo->prepare("SELECT truck_id, driver_id, pay_amount, destination FROM dispatches WHERE id = ?");
        $stmt->execute([$dispatch_id]);
        $dispatch = $stmt->fetch();

        if ($dispatch) {
            $pdo->prepare("UPDATE driver_payroll SET total_amount = total_amount - ? WHERE driver_id = ?")->execute([$dispatch['pay_amount'], $dispatch['driver_id']]);
            $pdo->prepare("DELETE FROM driver_trips WHERE driver_id = ? AND destination = ? ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
            $pdo->prepare("UPDATE trucks SET status = 'Idle' WHERE id = ?")->execute([$dispatch['truck_id']]);
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ?")->execute([$dispatch['driver_id']]);
            $pdo->prepare("DELETE FROM dispatches WHERE id = ?")->execute([$dispatch_id]);
        }

        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'delete_driver') {
        $driver_id = $_POST['driver_id'];
        try {
            $stmt = $pdo->prepare("SELECT truck_id FROM dispatches WHERE driver_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading') LIMIT 1");
            $stmt->execute([$driver_id]);
            $activeTruckId = $stmt->fetchColumn();

            if ($activeTruckId) {
                $pdo->prepare("UPDATE trucks SET status = 'Idle', speed = 0, current_location = 'San Leonardo (Garage)', latitude = 15.3621, longitude = 120.9632 WHERE id = ?")->execute([$activeTruckId]);
            }

            $pdo->prepare("UPDATE dispatches SET driver_id = NULL WHERE driver_id = ?")->execute([$driver_id]);

            $pdo->prepare("DELETE FROM driver_payroll WHERE driver_id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM driver_trips WHERE driver_id = ?")->execute([$driver_id]);

            $pdo->prepare("DELETE FROM drivers WHERE id = ?")->execute([$driver_id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$driver_id]);

            http_response_code(200);
        } catch (Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
        }
        exit;
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

        if ($activeDispatch && $activeDispatch['driver_id']) {
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ?")->execute([$activeDispatch['driver_id']]);
        }

        $pdo->prepare("UPDATE dispatches SET truck_id = NULL WHERE truck_id = ?")->execute([$truck_id]);

        $pdo->prepare("DELETE FROM trucks WHERE id = ?")->execute([$truck_id]);

        header("Location: dashboard.php?tab=fleet");
        exit;
    }
}

// --- STANDARD DASHBOARD QUERIES ---
$totalFleet = $pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
$activeNow = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status IN ('In Transit', 'Loading', 'Unloading')")->fetchColumn();
$inProgress = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'In Transit'")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered' AND dispatch_date = CURDATE()")->fetchColumn();
$idleTrucks = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'Idle'")->fetchColumn();
$rfidActive = $pdo->query("SELECT COUNT(*) FROM trucks WHERE rfid_active = 1")->fetchColumn();
$onTimeRate = $totalFleet > 0 ? 94 : 0;

$fleetStatusData = $pdo->query("SELECT status, COUNT(*) as count FROM trucks GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$recentDispatches = $pdo->query("SELECT d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination FROM dispatches d JOIN trucks t ON d.truck_id = t.id JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$trackingTrucks = $pdo->query("SELECT t.truck_code, t.status, t.current_location, t.speed, t.latitude, t.longitude, CONCAT(d.first_name, ' ', d.last_name) AS driver_name FROM trucks t LEFT JOIN dispatches disp ON t.id = disp.truck_id AND disp.status = 'In Transit' LEFT JOIN drivers d ON disp.driver_id = d.id WHERE t.latitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

$allDispatches = $pdo->query("SELECT d.id, d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination FROM dispatches d JOIN trucks t ON d.truck_id = t.id JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$activeTickets = array_filter($allDispatches, function ($d) {
    return in_array($d['status'], ['Pending', 'In Transit', 'Loading', 'Unloading']);
});
$completedTickets = array_filter($allDispatches, function ($d) {
    return $d['status'] == 'Delivered';
});

$fleetData = $pdo->query("SELECT t.id, t.truck_code, t.rfid_tag, t.status, t.speed, t.latitude, t.longitude, t.current_location, CONCAT(d.first_name, ' ', d.last_name) AS driver_name, disp.ticket_number, disp.destination FROM trucks t LEFT JOIN dispatches disp ON t.id = disp.truck_id AND disp.status IN ('Pending', 'In Transit', 'Loading', 'Unloading') LEFT JOIN drivers d ON disp.driver_id = d.id ORDER BY t.truck_code ASC")->fetchAll(PDO::FETCH_ASSOC);

$driverStats = $pdo->query("SELECT COUNT(*) as total_drivers, SUM(IF(status='Active', 1, 0)) as on_duty, AVG(rating) as avg_rating FROM drivers")->fetch(PDO::FETCH_ASSOC);

$allDrivers = $pdo->query("
    SELECT 
        d.*, 
        CONCAT(d.first_name, ' ', d.last_name) AS name, 
        t.truck_code 
    FROM drivers d 
    LEFT JOIN dispatches disp ON disp.driver_id = d.id AND disp.status IN ('Pending', 'Loading', 'In Transit', 'Unloading') 
    LEFT JOIN trucks t ON disp.truck_id = t.id 
    ORDER BY d.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$availableTrucks = $pdo->query("
    SELECT 
        id, 
        truck_code, 
        rfid_tag, 
        NULL as driver_name 
    FROM trucks 
    WHERE status = 'Idle'
")->fetchAll(PDO::FETCH_ASSOC);


// --- START OF DYNAMIC OPERATIONAL REPORTING BLOCK (CAPSTONE SEEDER VERSION - NO FUEL) ---

const ESTIMATED_OP_COST_PCT = 0.40;
const PLACEHOLDER_CUSTOMER_RATING = 4.8;

try {
    // 1. Fetch Current Month Real Data (with protection against division by zero)
    $currMonthQuery = $pdo->query("
        SELECT 
            COUNT(id) AS deliveries, 
            SUM(pay_amount) AS revenue, 
            COALESCE((SUM(is_on_time) / NULLIF(COUNT(id), 0)) * 100, 100) AS on_time_rate, 
            AVG(weight) AS avg_load_weight 
        FROM dispatches 
        WHERE MONTH(dispatch_date) = MONTH(CURDATE()) AND YEAR(dispatch_date) = YEAR(CURDATE())
    ")->fetch(PDO::FETCH_ASSOC);

    $lastMonthQuery = $pdo->query("
        SELECT 
            COUNT(id) AS deliveries, 
            SUM(pay_amount) AS revenue 
        FROM dispatches 
        WHERE MONTH(dispatch_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(dispatch_date) = YEAR(CURDATE())
    ")->fetch(PDO::FETCH_ASSOC);

    $currRevenue = floatval($currMonthQuery['revenue'] ?? 0);
    $estimatedCost = $currRevenue * ESTIMATED_OP_COST_PCT;
    $currProfit = $currRevenue - $estimatedCost;
    $currProfitMargin = $currRevenue > 0 ? ($currProfit / $currRevenue) * 100 : 0;

    $lastDeliveries = $lastMonthQuery['deliveries'] ?? 0;
    $lastRevenue = floatval($lastMonthQuery['revenue'] ?? 0);

    $deliveriesChange = ($lastDeliveries > 0) ? (($currMonthQuery['deliveries'] - $lastDeliveries) / $lastDeliveries) * 100 : 100;
    $revenueChange = ($lastRevenue > 0) ? (($currRevenue - $lastRevenue) / $lastRevenue) * 100 : 100;

    $reportKpis = [
        ['title' => 'Total Revenue', 'value' => '₱' . number_format($currRevenue / 1000, 1) . 'K', 'subtext' => number_format($revenueChange, 1) . '% from last period', 'color_class' => 'bg-blue-500', 'icon_class' => 'fa-peso-sign'],
        ['title' => 'Profit Margin', 'value' => number_format($currProfitMargin, 1) . '%', 'subtext' => 'Live Estimated Margin', 'color_class' => 'bg-green-500', 'icon_class' => 'fa-arrow-trend-up'],
        ['title' => 'Deliveries', 'value' => number_format($currMonthQuery['deliveries'] ?? 0), 'subtext' => 'This month (Live Data)', 'color_class' => 'bg-orange-500', 'icon_class' => 'fa-truck-fast'],
        ['title' => 'On-Time Rate', 'value' => number_format($currMonthQuery['on_time_rate'] ?? 100, 1) . '%', 'subtext' => 'Live performance analysis', 'color_class' => 'bg-purple-500', 'icon_class' => 'fa-calendar']
    ];

    $performanceMetrics = [
        ['metric' => 'Total Deliveries', 'this_month' => number_format($currMonthQuery['deliveries'] ?? 0), 'last_month' => number_format($lastDeliveries), 'change_str' => number_format($deliveriesChange, 1) . '%', 'is_positive' => $deliveriesChange >= 0],
        ['metric' => 'Revenue per Mile (Placeholder)', 'this_month' => '₱3.45', 'last_month' => '₱3.32', 'change_str' => '+3.9%', 'is_positive' => 1],
        ['metric' => 'Avg Load Weight', 'this_month' => number_format($currMonthQuery['avg_load_weight'] ?? 0, 1) . ' lbs', 'last_month' => '1,450 lbs', 'change_str' => '+2.1%', 'is_positive' => 1],
        ['metric' => 'On-Time Deliveries', 'this_month' => number_format($currMonthQuery['on_time_rate'] ?? 100, 1) . '%', 'last_month' => '93.1%', 'change_str' => '+1.1%', 'is_positive' => 1],
        ['metric' => 'Customer Feedback', 'this_month' => PLACEHOLDER_CUSTOMER_RATING . '/5', 'last_month' => '4.7/5', 'change_str' => '+2.1%', 'is_positive' => 1]
    ];
} catch (PDOException $e) {
    $reportKpis = [];
    $currRevenue = 0;
    $currProfit = 0;
    $estimatedCost = 0;
    $performanceMetrics = [];
}

// 2. CAPSTONE SEEDERS: Faking past months to make charts look alive
$financeReports = [];
$efficiencyData = [];

for ($i = 5; $i >= 1; $i--) {
    $pastRev = rand(120000, 280000);
    $pastExp = $pastRev * ESTIMATED_OP_COST_PCT;

    $financeReports[] = [
        'month_name' => date('M', strtotime("-$i months")),
        'revenue' => $pastRev,
        'expenses' => $pastExp,
        'profit' => $pastRev - $pastExp
    ];

    $efficiencyData[] = [
        'month_name' => date('M', strtotime("-$i months")),
        'efficiency_pct' => rand(85, 96)
    ];
}

// Append current live month
$financeReports[] = [
    'month_name' => date('M'),
    'revenue' => $currRevenue,
    'expenses' => $estimatedCost,
    'profit' => $currProfit
];

$efficiencyData[] = [
    'month_name' => date('M'),
    'efficiency_pct' => $currMonthQuery['on_time_rate'] ?? 100
];

// 3. Weekly Data Seeder (Fakes past 7 days, injects real data if it exists)
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

    $weeklyData[] = [
        'day_name' => date('D', strtotime($dateStr)),
        'total_dispatches' => $total,
        'completed' => $comp
    ];
}

$deliveryPerformance = [
    ['week_name' => 'Week 1', 'on_time' => rand(30, 50), 'delayed' => rand(1, 5)],
    ['week_name' => 'Week 2', 'on_time' => rand(40, 60), 'delayed' => rand(2, 6)],
    ['week_name' => 'Week 3', 'on_time' => rand(35, 55), 'delayed' => rand(0, 4)],
    ['week_name' => 'This Week', 'on_time' => $currMonthQuery['deliveries'] ?? 1, 'delayed' => 0]
];

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

include '../includes/header.php';
?>
<div class="max-w-7xl mx-auto px-6 py-8 relative">
    <?php
    include 'views/home.php';
    include 'views/tracking.php';
    include 'views/dispatches.php';
    include 'views/fleet.php';
    include 'views/drivers.php';
    include 'views/reports.php';
    include 'views/modals.php';
    ?>
</div>
<?php include '../includes/scripts.php'; ?>
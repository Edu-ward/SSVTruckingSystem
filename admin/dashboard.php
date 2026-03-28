<?php
session_start();

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
        $weight = floatval($_POST['weight']);

        // SECURITY: Always calculate money on the backend! 
        $rate_per_lb = 2.50;
        $calculated_pay = $weight * $rate_per_lb;

        $ticketNum = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 1. Insert into main dispatches table
        $insert = $pdo->prepare("INSERT INTO dispatches (ticket_number, truck_id, driver_id, status, origin, destination, weight, pay_amount, dispatch_date) VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, CURDATE())");
        $insert->execute([$ticketNum, $truck_id, $driver_id, $origin, $destination, $weight, $calculated_pay]);

        // 2. Insert into driver_trips so it shows up on the Driver Panel
        $trip_insert = $pdo->prepare("INSERT INTO driver_trips (driver_id, destination, trip_date, status) VALUES (?, ?, CURDATE(), 'In Transit')");
        $trip_insert->execute([$driver_id, $destination]);

        // 3. Update the driver's payroll total
        $payroll_update = $pdo->prepare("UPDATE driver_payroll SET total_amount = total_amount + ? WHERE driver_id = ?");
        $payroll_update->execute([$calculated_pay, $driver_id]);

        // 4. Update fleet and driver status
        $pdo->prepare("UPDATE trucks SET status = 'Loading', current_driver_id = ? WHERE id = ?")->execute([$driver_id, $truck_id]);
        $pdo->prepare("UPDATE drivers SET status = 'Dispatched' WHERE id = ?")->execute([$driver_id]);

        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'add_driver') {
        $name = $_POST['name'];
        $cdl = $_POST['cdl_number'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Driver')");
        $user_insert->execute([$username, $hashed_password]);

        $new_user_id = $pdo->lastInsertId();

        $driver_insert = $pdo->prepare("INSERT INTO drivers (id, name, cdl_number, phone, email, status) VALUES (?, ?, ?, ?, ?, 'Off Duty')");
        $driver_insert->execute([$new_user_id, $name, $cdl, $phone, $email]);

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

    if ($_POST['action'] == 'delete_driver') {
        $driver_id = $_POST['driver_id'];
        $pdo->prepare("UPDATE trucks SET current_driver_id = NULL, status = 'Idle', speed = 0, current_location = 'San Leonardo (Garage)', latitude = 15.3621, longitude = 120.9632 WHERE current_driver_id = ?")->execute([$driver_id]);
        $pdo->prepare("UPDATE dispatches SET driver_id = NULL WHERE driver_id = ?")->execute([$driver_id]);
        $pdo->prepare("DELETE FROM drivers WHERE id = ?")->execute([$driver_id]);
        exit;
    }

    // --- NEW: ADD TRUCK LOGIC ---
    if ($_POST['action'] == 'add_truck') {
        $truck_code = trim($_POST['truck_code']);
        $rfid_tag = trim($_POST['rfid_tag']);

        $insert = $pdo->prepare("INSERT INTO trucks (truck_code, rfid_tag, status, current_location, latitude, longitude, speed) VALUES (?, ?, 'Idle', 'San Leonardo (Garage)', 15.3621, 120.9632, 0)");
        $insert->execute([$truck_code, $rfid_tag]);

        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    // --- NEW: DELETE TRUCK LOGIC ---
    if ($_POST['action'] == 'delete_truck') {
        $truck_id = $_POST['truck_id'];

        // Step 1: Free up the driver if they are assigned to this truck
        $stmt = $pdo->prepare("SELECT current_driver_id FROM trucks WHERE id = ?");
        $stmt->execute([$truck_id]);
        $truck = $stmt->fetch();

        if ($truck && $truck['current_driver_id']) {
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ?")->execute([$truck['current_driver_id']]);
        }

        // Step 2: Unlink the truck from dispatch history! 
        // (Without this, MySQL blocks the deletion to protect past dispatch records)
        $pdo->prepare("UPDATE dispatches SET truck_id = NULL WHERE truck_id = ?")->execute([$truck_id]);

        // Step 3: Delete the truck
        $pdo->prepare("DELETE FROM trucks WHERE id = ?")->execute([$truck_id]);

        header("Location: dashboard.php?tab=fleet");
        exit;
    }
}

$totalFleet = $pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
$activeNow = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status IN ('In Transit', 'Loading', 'Unloading')")->fetchColumn();
$inProgress = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'In Transit'")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered' AND dispatch_date = CURDATE()")->fetchColumn();
$idleTrucks = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'Idle'")->fetchColumn();
$rfidActive = $pdo->query("SELECT COUNT(*) FROM trucks WHERE rfid_active = 1")->fetchColumn();
$onTimeRate = $totalFleet > 0 ? 94 : 0;

$weeklyData = $pdo->query("SELECT day_name, total_dispatches, completed FROM weekly_activity LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
$fleetStatusData = $pdo->query("SELECT status, COUNT(*) as count FROM trucks GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$efficiencyData = $pdo->query("SELECT month_name, efficiency_pct FROM efficiency_trend LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
$recentDispatches = $pdo->query("SELECT d.ticket_number, t.truck_code, dr.name as driver_name, d.status, d.destination FROM dispatches d JOIN trucks t ON d.truck_id = t.id JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$trackingTrucks = $pdo->query("SELECT t.truck_code, t.status, t.current_location, t.speed, t.latitude, t.longitude, d.name as driver_name FROM trucks t LEFT JOIN drivers d ON t.current_driver_id = d.id WHERE t.latitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

$allDispatches = $pdo->query("SELECT d.id, d.ticket_number, t.truck_code, dr.name as driver_name, d.status, d.destination FROM dispatches d JOIN trucks t ON d.truck_id = t.id JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$activeTickets = array_filter($allDispatches, function ($d) {
    return in_array($d['status'], ['Pending', 'In Transit', 'Loading', 'Unloading']);
});
$completedTickets = array_filter($allDispatches, function ($d) {
    return $d['status'] == 'Delivered';
});

$fleetData = $pdo->query("SELECT t.id, t.truck_code, t.rfid_tag, t.status, t.speed, t.latitude, t.longitude, t.current_location, d.name as driver_name, disp.ticket_number, disp.destination FROM trucks t LEFT JOIN drivers d ON t.current_driver_id = d.id LEFT JOIN dispatches disp ON disp.truck_id = t.id AND disp.status IN ('Pending', 'In Transit', 'Loading', 'Unloading') ORDER BY t.truck_code ASC")->fetchAll(PDO::FETCH_ASSOC);

$driverStats = $pdo->query("SELECT COUNT(*) as total_drivers, SUM(IF(status='Active', 1, 0)) as on_duty, AVG(rating) as avg_rating, AVG(hours_this_week) as avg_hours FROM drivers")->fetch(PDO::FETCH_ASSOC);
$allDrivers = $pdo->query("SELECT d.*, t.truck_code FROM drivers d LEFT JOIN trucks t ON t.current_driver_id = d.id ORDER BY d.name ASC")->fetchAll(PDO::FETCH_ASSOC);
$availableTrucks = $pdo->query("SELECT t.id, t.truck_code, t.rfid_tag, d.name as driver_name FROM trucks t LEFT JOIN drivers d ON t.current_driver_id = d.id WHERE t.status = 'Idle'")->fetchAll(PDO::FETCH_ASSOC);

try {
    $reportKpis = $pdo->query("SELECT * FROM report_kpis LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    $financeReports = $pdo->query("SELECT * FROM finance_reports LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    $deliveryPerformance = $pdo->query("SELECT * FROM delivery_performance LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    $fuelConsumption = $pdo->query("SELECT * FROM fuel_consumption LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
    $performanceMetrics = $pdo->query("SELECT * FROM performance_metrics LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $financeReports = [];
}

function getInitials($name)
{
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
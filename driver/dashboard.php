<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: index.php");
    exit;
}

$driver_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_cancel_trip') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    $reason = trim($_POST['reason']);
    if (empty($reason)) {
        $reason = "Maintenance Required";
    }

    try {
        $pdo->beginTransaction();

        // 1. Get driver's active dispatch
        $stmt = $pdo->prepare("SELECT id, truck_id, status FROM dispatches WHERE driver_id = ? AND status NOT IN ('Delivered', 'Cancelled', 'Completed')");
        $stmt->execute([$driver_id]);
        $active_dispatches = $stmt->fetchAll();

        // Check if already requested
        $already_requested = false;
        foreach ($active_dispatches as $dispatch) {
            if ($dispatch['status'] === 'Cancellation Requested') {
                $already_requested = true;
                break;
            }
        }

        if ($already_requested) {
            $_SESSION['error'] = "A cancellation request is already pending for your current trip.";
            $pdo->rollBack();
            header("Location: dashboard.php");
            exit;
        }

        // 2. Mark active dispatches as Cancellation Requested
        foreach ($active_dispatches as $dispatch) {
            $stmtUpdate = $pdo->prepare("UPDATE dispatches SET status = 'Cancellation Requested' WHERE id = ?");
            $stmtUpdate->execute([$dispatch['id']]);
        }

        // 3. Update the driver trip if there's any active driver trip
        $stmtUpdateTrip = $pdo->prepare("UPDATE driver_trips SET status = 'Cancellation Requested' WHERE driver_id = ? AND status NOT IN ('Delivered', 'Cancelled', 'Completed')");
        $stmtUpdateTrip->execute([$driver_id]);

        $pdo->commit();
        if (count($active_dispatches) > 0) {
            $_SESSION['success'] = "Cancellation requested. Please wait for Admin approval.";
        } else {
            $_SESSION['error'] = "No active trip found to cancel.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "An error occurred while processing your request.";
    }
    
    header("Location: dashboard.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM driver_payroll WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$payroll = $stmt->fetch() ?: ['total_amount' => 0, 'amount_claimed' => 0];

$available_balance = max(0, $payroll['total_amount'] - $payroll['amount_claimed']);

// Load driver pay rates from destinations table
$_dest_rows  = $pdo->query("SELECT name, driver_rate FROM destinations WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$driver_rates = [];
foreach ($_dest_rows as $_d) {
    $driver_rates[$_d['name']] = floatval($_d['driver_rate']);
}

$stmt2 = $pdo->prepare("SELECT trip_date, destination, status, created_at, transit_start_time, transit_end_time FROM driver_trips WHERE driver_id = ? ORDER BY trip_date DESC, id DESC");
$stmt2->execute([$driver_id]);
$raw_trips = $stmt2->fetchAll();

$trips = [];
$weekly_salary = 0;
$monthly_salary = 0;
$current_week = date('oW');
$current_month = date('Y-m');

foreach ($raw_trips as $t) {
    $t['pay_amount'] = $driver_rates[$t['destination']] ?? 0;
    $trips[] = $t;
    
    // Only count delivered trips for salary
    if ($t['status'] === 'Delivered') {
        $trip_time = strtotime($t['trip_date']);
        if (date('oW', $trip_time) === $current_week) {
            $weekly_salary += $t['pay_amount'];
        }
        if (date('Y-m', $trip_time) === $current_month) {
            $monthly_salary += $t['pay_amount'];
        }
    }
}

$next_payday = (date('w') == 6) ? 'Today' : date('l, M j', strtotime('next Saturday'));

// Check for pending cancellation
$stmtCancel = $pdo->prepare("SELECT id FROM dispatches WHERE driver_id = ? AND status = 'Cancellation Requested' LIMIT 1");
$stmtCancel->execute([$driver_id]);
$has_pending_cancellation = $stmtCancel->fetch() ? true : false;

// Check for active In Transit dispatch (needed to start GPS tracking)
$stmtTransit = $pdo->prepare("
    SELECT d.id, d.destination, d.truck_id 
    FROM dispatches d 
    WHERE d.driver_id = ? AND d.status = 'In Transit' 
    LIMIT 1
");
$stmtTransit->execute([$driver_id]);
$active_transit = $stmtTransit->fetch();
$is_in_transit   = $active_transit ? true : false;
$active_truck_id = $active_transit ? $active_transit['truck_id'] : null;

// Check for any active dispatch assigned (Pending, Loading, In Transit, Unloading, Cancellation Requested)
$stmtActive = $pdo->prepare("
    SELECT d.id, d.ticket_number, d.destination, d.status, t.truck_code 
    FROM dispatches d 
    JOIN trucks t ON d.truck_id = t.id 
    WHERE d.driver_id = ? AND d.status IN ('Pending', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
    LIMIT 1
");
$stmtActive->execute([$driver_id]);
$active_dispatch = $stmtActive->fetch();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <?php
    include 'views/home.php';
    include 'views/modals.php';
    ?>
</div>

<?php include '../includes/scripts.php'; ?>
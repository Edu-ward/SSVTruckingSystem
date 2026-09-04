<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: index.php");
    exit;
}

// Ensure csrf_token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$driver_id = $_SESSION['user_id'];

$stmtDriver = $pdo->prepare("SELECT d.first_name, d.last_name, d.profile_photo, u.username FROM drivers d JOIN users u ON u.id = d.id WHERE d.id = ?");
$stmtDriver->execute([$driver_id]);
$driverProfile = $stmtDriver->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_cash_advance') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    $ca_amount = floatval($_POST['ca_amount'] ?? 0);
    $ca_reason = trim($_POST['ca_reason'] ?? '');

    if ($ca_amount < 100) {
        $_SESSION['error'] = "Minimum cash advance amount is ₱100.";
    } else {
        try {
            $pdo->prepare("INSERT INTO cash_advances (driver_id, amount, reason) VALUES (?, ?, ?)")
                ->execute([$driver_id, $ca_amount, $ca_reason]);
            // Ensure payroll record exists
            $pdo->prepare("INSERT IGNORE INTO driver_payroll (driver_id, total_amount, amount_claimed) VALUES (?, 0, 0)")
                ->execute([$driver_id]);
            $_SESSION['success'] = "Cash advance request of ₱" . number_format($ca_amount, 2) . " submitted. Awaiting Admin approval.";
            log_activity($pdo, 'Requested Cash Advance', 'Driver requested cash advance of ₱' . number_format($ca_amount, 2));
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to submit cash advance request.";
        }
    }
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_cancel_trip') {
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

        $stmt = $pdo->prepare("SELECT id, truck_id, status FROM dispatches WHERE driver_id = ? AND status NOT IN ('Delivered', 'Cancelled', 'Completed')");
        $stmt->execute([$driver_id]);
        $active_dispatches = $stmt->fetchAll();

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

        foreach ($active_dispatches as $dispatch) {
            $stmtUpdate = $pdo->prepare("UPDATE dispatches SET status = 'Cancellation Requested' WHERE id = ?");
            $stmtUpdate->execute([$dispatch['id']]);
        }
        $stmtUpdateTrip = $pdo->prepare("UPDATE driver_trips SET status = 'Cancellation Requested' WHERE driver_id = ? AND status NOT IN ('Delivered', 'Cancelled', 'Completed')");
        $stmtUpdateTrip->execute([$driver_id]);

        $pdo->commit();
        if (count($active_dispatches) > 0) {
            $_SESSION['success'] = "Cancellation requested. Please wait for Admin approval.";
            log_activity($pdo, 'Requested Cancellation', 'Driver requested trip cancellation');
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
$_dest_rows = $pdo->query("SELECT name FROM destinations WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("
    SELECT 
        dt.id,
        dt.trip_date, 
        dt.destination, 
        dt.status, 
        dt.created_at, 
        dt.transit_start_time, 
        dt.transit_end_time,
        COALESCE(NULLIF(dt.distance_km, 0), dest.distance_km, 0.00) AS distance_km,
        COALESCE(NULLIF(dt.pay_amount, 0), IF(dest.distance_km > 0, ROUND(dest.distance_km * 10, 2), dest.driver_rate), 0.00) AS pay_amount
    FROM driver_trips dt
    LEFT JOIN destinations dest ON dest.name = dt.destination
    WHERE dt.driver_id = ? 
    ORDER BY dt.trip_date DESC, dt.id DESC
");
$stmt2->execute([$driver_id]);
$raw_trips = $stmt2->fetchAll();

$trips = [];
$weekly_trips = 0;
$monthly_trips = 0;
$total_completed_trips = 0;
$current_week = date('oW');
$current_month = date('Y-m');

foreach ($raw_trips as $t) {
    $trips[] = $t;

    if ($t['status'] === 'Delivered') {
        $total_completed_trips++;
        $trip_time = strtotime($t['trip_date'] ?: $t['created_at']);
        if ($trip_time) {
            if (date('oW', $trip_time) === $current_week) {
                $weekly_trips++;
            }
            if (date('Y-m', $trip_time) === $current_month) {
                $monthly_trips++;
            }
        }
    }
}

$stmtCancel = $pdo->prepare("SELECT id FROM dispatches WHERE driver_id = ? AND status = 'Cancellation Requested' LIMIT 1");
$stmtCancel->execute([$driver_id]);
$has_pending_cancellation = $stmtCancel->fetch() ? true : false;


$stmtActive = $pdo->prepare("
    SELECT 
        d.id, d.ticket_number, d.origin, d.destination, d.status, d.cubic_meters, d.created_at, d.transit_start_time, d.transit_end_time, t.truck_code,
        COALESCE(NULLIF(d.pay_amount, 0), IF(dest.distance_km > 0, ROUND(dest.distance_km * 10, 2), dest.driver_rate), 0.00) AS pay_amount,
        COALESCE(dest.distance_km, ROUND(d.pay_amount / 10, 1), 0.00) AS distance_km
    FROM dispatches d 
    JOIN trucks t ON d.truck_id = t.id 
    LEFT JOIN destinations dest ON dest.name = d.destination
    WHERE d.driver_id = ? AND d.status IN ('Pending', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
    LIMIT 1
");
$stmtActive->execute([$driver_id]);
$active_dispatch = $stmtActive->fetch();

// Load payroll and cash advance data for the driver
$payStmt = $pdo->prepare("SELECT total_amount, amount_claimed, remaining_balance FROM driver_payroll WHERE driver_id = ?");
$payStmt->execute([$driver_id]);
$driverPayroll = $payStmt->fetch(PDO::FETCH_ASSOC);
$driverRemainingBalance = floatval($driverPayroll['remaining_balance'] ?? 0);

// Active gross earnings from delivered dispatches (resets to 0 once payroll is settled)
$grossEarnStmt = $pdo->prepare("SELECT COALESCE(SUM(pay_amount), 0) FROM dispatches WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)");
$grossEarnStmt->execute([$driver_id]);
$driverGrossEarnings = floatval($grossEarnStmt->fetchColumn());

$cashAdvStmt = $pdo->prepare("SELECT id, amount, reason, status, is_settled, requested_at, resolved_at FROM cash_advances WHERE driver_id = ? ORDER BY requested_at DESC LIMIT 10");
$cashAdvStmt->execute([$driver_id]);
$driverCashAdvances = $cashAdvStmt->fetchAll(PDO::FETCH_ASSOC);

// Total active approved cash advances for this driver (strictly active / unsettled)
$caSumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM cash_advances WHERE driver_id = ? AND status = 'Approved' AND (is_settled = 0 OR is_settled IS NULL)");
$caSumStmt->execute([$driver_id]);
$totalCashAdvancesClaimed = floatval($caSumStmt->fetchColumn());
$netPay = max(0, $driverGrossEarnings + $driverRemainingBalance - $totalCashAdvancesClaimed);

include '../includes/header.php';
?>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <?php
    include 'views/home.php';
    include 'views/modals.php';
    ?>
</div>
</div>

<?php include '../includes/scripts.php'; ?>
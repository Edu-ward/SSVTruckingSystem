<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: index.php");
    exit;
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$driver_id = $_SESSION['user_id'];

$stmtDriver = $pdo->prepare("
    SELECT 
        d.first_name, d.last_name, d.profile_photo, d.cdl_number, d.phone, d.status, d.truck_id, d.rating, 
        u.username, t.truck_code, t.status AS truck_status 
    FROM drivers d 
    JOIN users u ON u.id = d.id 
    LEFT JOIN trucks t ON t.id = d.truck_id 
    WHERE d.id = ?
");
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
            
            $pdo->prepare("INSERT IGNORE INTO driver_payroll (driver_id, total_amount, amount_claimed) VALUES (?, 0, 0)")
                ->execute([$driver_id]);
            $_SESSION['success'] = "Cash advance request of ₱" . number_format($ca_amount, 2) . " submitted. Awaiting Admin approval.";
            log_activity($pdo, 'Requested Cash Advance', 'Driver requested cash advance of ₱' . number_format($ca_amount, 2));
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to submit cash advance request.";
        }
    }
    header("Location: dashboard.php?tab=cash_advance");
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
        COALESCE(NULLIF(dt.pay_amount, 0), IF(LOWER(dest.name) LIKE '%san leonardo%', 300.00, IF(dest.distance_km > 0, ROUND(300.00 + GREATEST(0, dest.distance_km - IF(LOWER(dest.name) LIKE '%peñaranda%' OR LOWER(dest.name) LIKE '%penaranda%', 6, 12)) * 10, 2), IF(dest.driver_rate > 0, dest.driver_rate, 300.00))), 0.00) AS pay_amount
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

// Bulletproof Monday to Sunday calculations
$dayOfWeek = (int)date('N'); // 1 (Mon) to 7 (Sun)
$thisMonday = date('Y-m-d', strtotime('-' . ($dayOfWeek - 1) . ' days'));
$thisSunday = date('Y-m-d', strtotime('+' . (7 - $dayOfWeek) . ' days'));

// Generate 8 selectable weekly cycles (Monday to Sunday)
$selectableWeeks = [];
for ($w = 0; $w < 8; $w++) {
    $m = date('Y-m-d', strtotime("$thisMonday -$w weeks"));
    $s = date('Y-m-d', strtotime("$thisSunday -$w weeks"));
    $wPrefix = ($w === 0) ? "This Week: " : (($w === 1) ? "Last Week: " : "$w Weeks Ago: ");
    $wLabel = $wPrefix . date('M d', strtotime($m)) . ' – ' . date('M d, Y', strtotime($s)) . ' (Mon–Sun)';
    $selectableWeeks[] = [
        'from'       => $m,
        'to'         => $s,
        'label'      => $wLabel,
        'is_current' => ($w === 0)
    ];
}

$selectedFrom = $_GET['date_from'] ?? $thisMonday;
$selectedTo   = $_GET['date_to']   ?? $thisSunday;

$weeklyFilteredTrips = [];
$weeklyDistanceKm    = 0.0;
$weeklyPayAmount     = 0.0;

foreach ($raw_trips as $t) {
    $trips[] = $t;
    $tDate = date('Y-m-d', strtotime($t['trip_date'] ?: $t['created_at']));

    if ($tDate >= $selectedFrom && $tDate <= $selectedTo) {
        $weeklyFilteredTrips[] = $t;
        $weeklyDistanceKm += floatval($t['distance_km'] ?? 0);
        $weeklyPayAmount += floatval($t['pay_amount'] ?? 0);
    }

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
        COALESCE(NULLIF(d.pay_amount, 0), IF(LOWER(dest.name) LIKE '%san leonardo%', 300.00, IF(dest.distance_km > 0, ROUND(300.00 + GREATEST(0, dest.distance_km - IF(LOWER(dest.name) LIKE '%peñaranda%' OR LOWER(dest.name) LIKE '%penaranda%', 6, 12)) * 10, 2), IF(dest.driver_rate > 0, dest.driver_rate, 300.00))), 0.00) AS pay_amount,
        COALESCE(dest.distance_km, ROUND(d.pay_amount / 10, 1), 0.00) AS distance_km
    FROM dispatches d 
    JOIN trucks t ON d.truck_id = t.id 
    LEFT JOIN destinations dest ON dest.name = d.destination
    WHERE d.driver_id = ? AND d.status IN ('Pending', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
    LIMIT 1
");
$stmtActive->execute([$driver_id]);
$active_dispatch = $stmtActive->fetch();

$payStmt = $pdo->prepare("SELECT total_amount, amount_claimed, remaining_balance FROM driver_payroll WHERE driver_id = ?");
$payStmt->execute([$driver_id]);
$driverPayroll = $payStmt->fetch(PDO::FETCH_ASSOC);
$driverRemainingBalance = floatval($driverPayroll['remaining_balance'] ?? 0);

$grossEarnStmt = $pdo->prepare("SELECT COALESCE(SUM(pay_amount), 0) FROM dispatches WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)");
$grossEarnStmt->execute([$driver_id]);
$driverGrossEarnings = floatval($grossEarnStmt->fetchColumn());

// All cash advances for driver
$cashAdvStmt = $pdo->prepare("SELECT id, amount, reason, status, is_settled, requested_at, resolved_at FROM cash_advances WHERE driver_id = ? ORDER BY requested_at DESC");
$cashAdvStmt->execute([$driver_id]);
$driverCashAdvances = $cashAdvStmt->fetchAll(PDO::FETCH_ASSOC);

$caPendingCount = 0;
$totalCashAdvancesClaimed = 0.0;
$totalCashAdvancesSettled = 0.0;

foreach ($driverCashAdvances as $ca) {
    if ($ca['status'] === 'Pending') $caPendingCount++;
    if ($ca['status'] === 'Approved' && empty($ca['is_settled'])) {
        $totalCashAdvancesClaimed += floatval($ca['amount']);
    }
    if (!empty($ca['is_settled'])) {
        $totalCashAdvancesSettled += floatval($ca['amount']);
    }
}

$netPay = max(0, $driverGrossEarnings + $driverRemainingBalance - $totalCashAdvancesClaimed);

// Delivered trips for payroll
$stmtPayrollTrips = $pdo->prepare("
    SELECT 
        d.id,
        d.ticket_number,
        d.destination,
        d.pay_amount,
        d.cubic_meters,
        d.transit_end_time,
        d.created_at,
        d.is_payroll_paid,
        d.payroll_settled_at,
        t.truck_code,
        dest.distance_km
    FROM dispatches d
    LEFT JOIN trucks t ON t.id = d.truck_id
    LEFT JOIN destinations dest ON dest.name = d.destination
    WHERE d.driver_id = ? AND d.status = 'Delivered'
    ORDER BY d.id DESC
");
$stmtPayrollTrips->execute([$driver_id]);
$payrollTrips = $stmtPayrollTrips->fetchAll(PDO::FETCH_ASSOC);

// Payroll past settlement vouchers / claims
$settleStmt = $pdo->prepare("SELECT settlement_ticket, gross_amount, previous_balance, cash_advance_deduction, net_pay, amount_claimed, remaining_balance, trips_count, settled_at, notes FROM driver_payroll_settlements WHERE driver_id = ? ORDER BY settled_at DESC LIMIT 10");
$settleStmt->execute([$driver_id]);
$payrollSettlements = $settleStmt->fetchAll(PDO::FETCH_ASSOC);

// Driver Notifications aggregation
$driverNotifications = [];

// 1. Dispatch updates
$notifDispStmt = $pdo->prepare("SELECT ticket_number, destination, status, created_at, transit_start_time, transit_end_time FROM dispatches WHERE driver_id = ? ORDER BY id DESC LIMIT 12");
$notifDispStmt->execute([$driver_id]);
foreach ($notifDispStmt->fetchAll(PDO::FETCH_ASSOC) as $nd) {
    $timeStr = $nd['transit_end_time'] ?: ($nd['transit_start_time'] ?: $nd['created_at']);
    $ts = strtotime($timeStr);
    if ($nd['status'] === 'Delivered') {
        $driverNotifications[] = [
            'id' => 'disp_' . $nd['ticket_number'],
            'title' => 'Trip Delivered Successfully',
            'message' => 'Ticket #' . $nd['ticket_number'] . ' to ' . $nd['destination'] . ' has been marked Delivered. Earnings added to payroll.',
            'type' => 'success',
            'icon' => 'fa-circle-check',
            'badge' => 'Delivered',
            'color' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
            'tab' => 'trips',
            'timestamp' => $ts
        ];
    } elseif ($nd['status'] === 'In Transit') {
        $driverNotifications[] = [
            'id' => 'disp_' . $nd['ticket_number'],
            'title' => 'Dispatch In Transit',
            'message' => 'Your trip #' . $nd['ticket_number'] . ' to ' . $nd['destination'] . ' is on the road. Live GPS sharing active.',
            'type' => 'info',
            'icon' => 'fa-truck-fast',
            'badge' => 'In Transit',
            'color' => 'text-blue-500 bg-blue-50 dark:bg-blue-950/30',
            'tab' => 'route',
            'timestamp' => $ts
        ];
    } elseif ($nd['status'] === 'Loading' || $nd['status'] === 'Pending') {
        $driverNotifications[] = [
            'id' => 'disp_' . $nd['ticket_number'],
            'title' => 'New Trip Assigned',
            'message' => 'New dispatch ticket #' . $nd['ticket_number'] . ' assigned: ' . $nd['destination'] . '.',
            'type' => 'info',
            'icon' => 'fa-route',
            'badge' => 'Assigned',
            'color' => 'text-indigo-500 bg-indigo-50 dark:bg-indigo-950/30',
            'tab' => 'dashboard',
            'timestamp' => $ts
        ];
    } elseif ($nd['status'] === 'Cancellation Requested') {
        $driverNotifications[] = [
            'id' => 'disp_' . $nd['ticket_number'],
            'title' => 'Trip Cancellation Requested',
            'message' => 'Cancellation for ticket #' . $nd['ticket_number'] . ' was submitted and is awaiting Admin review.',
            'type' => 'warning',
            'icon' => 'fa-triangle-exclamation',
            'badge' => 'Pending Review',
            'color' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/30',
            'tab' => 'dashboard',
            'timestamp' => $ts
        ];
    }
}

// 2. Cash advance updates
foreach ($driverCashAdvances as $ca) {
    $caTs = strtotime($ca['resolved_at'] ?: $ca['requested_at']);
    if ($ca['status'] === 'Approved') {
        $driverNotifications[] = [
            'id' => 'ca_' . $ca['id'],
            'title' => 'Cash Advance Approved',
            'message' => 'Your cash advance of ₱' . number_format($ca['amount'], 2) . ' was approved! You can now print the voucher in the Cash Advance tab.',
            'type' => 'success',
            'icon' => 'fa-hand-holding-dollar',
            'badge' => 'Approved',
            'color' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
            'tab' => 'cash_advance',
            'timestamp' => $caTs
        ];
    } elseif ($ca['status'] === 'Rejected') {
        $driverNotifications[] = [
            'id' => 'ca_' . $ca['id'],
            'title' => 'Cash Advance Declined',
            'message' => 'Your cash advance request of ₱' . number_format($ca['amount'], 2) . ' was declined by the Admin.',
            'type' => 'error',
            'icon' => 'fa-circle-xmark',
            'badge' => 'Rejected',
            'color' => 'text-rose-500 bg-rose-50 dark:bg-rose-950/30',
            'tab' => 'cash_advance',
            'timestamp' => $caTs
        ];
    } else {
        $driverNotifications[] = [
            'id' => 'ca_' . $ca['id'],
            'title' => 'Cash Advance Submitted',
            'message' => 'Your request of ₱' . number_format($ca['amount'], 2) . ' has been forwarded to Admin for approval.',
            'type' => 'info',
            'icon' => 'fa-clock',
            'badge' => 'Pending',
            'color' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/30',
            'tab' => 'cash_advance',
            'timestamp' => $caTs
        ];
    }
}

// 3. Password reset updates
$notifPrStmt = $pdo->prepare("SELECT id, status, requested_at, resolved_at FROM password_reset_requests WHERE user_id = ? ORDER BY id DESC LIMIT 3");
$notifPrStmt->execute([$driver_id]);
foreach ($notifPrStmt->fetchAll(PDO::FETCH_ASSOC) as $pr) {
    $prTs = strtotime($pr['resolved_at'] ?: $pr['requested_at']);
    if ($pr['status'] === 'Approved') {
        $driverNotifications[] = [
            'id' => 'pr_' . $pr['id'],
            'title' => 'Password Reset Approved',
            'message' => 'Admin has approved your password reset request. You can now set your new password.',
            'type' => 'success',
            'icon' => 'fa-key',
            'badge' => 'Action Required',
            'color' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
            'tab' => 'profile',
            'timestamp' => $prTs
        ];
    }
}

// 4. Payroll settlements
foreach ($payrollSettlements as $ps) {
    $driverNotifications[] = [
        'id' => 'ps_' . $ps['settlement_ticket'],
        'title' => 'Payroll Payout Processed',
        'message' => 'Settlement #' . $ps['settlement_ticket'] . ' completed. Claimed amount: ₱' . number_format($ps['amount_claimed'], 2) . '.',
        'type' => 'success',
        'icon' => 'fa-money-bill-wave',
        'badge' => 'Settled',
        'color' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
        'tab' => 'payroll',
        'timestamp' => strtotime($ps['settled_at'])
    ];
}

// Sort notifications newest first
usort($driverNotifications, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Calculate unread badge count (items within last 72 hours)
$unreadNotificationCount = 0;
$recentThreshold = time() - (72 * 3600);
foreach ($driverNotifications as $n) {
    if ($n['timestamp'] > $recentThreshold) {
        $unreadNotificationCount++;
    }
}
if ($unreadNotificationCount === 0 && count($driverNotifications) > 0) {
    $unreadNotificationCount = min(2, count($driverNotifications));
}

include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-[1400px] mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-8">
    <?php
    include __DIR__ . '/views/home.php';
    include __DIR__ . '/views/modals.php';
    ?>
</div>
</div>

<?php include __DIR__ . '/../includes/scripts.php'; ?>
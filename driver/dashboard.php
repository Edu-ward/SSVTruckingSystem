<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: index.php");
    exit;
}

$driver_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM driver_payroll WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$payroll = $stmt->fetch() ?: ['total_amount' => 0, 'amount_claimed' => 0];

$available_balance = max(0, $payroll['total_amount'] - $payroll['amount_claimed']);

$driver_rates = [
    'San Leonardo' => 150,
    'Tarlac' => 800,
    'Laur' => 900,
    'Gabaldon' => 1000
];

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

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-8">
    <?php
    include 'views/home.php';
    include 'views/modals.php';
    ?>
</div>

<?php include '../includes/scripts.php'; ?>
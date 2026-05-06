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

$stmt2 = $pdo->prepare("SELECT trip_date, destination, status FROM driver_trips WHERE driver_id = ? ORDER BY trip_date DESC, id DESC");
$stmt2->execute([$driver_id]);
$raw_trips = $stmt2->fetchAll();

$trips = [];
foreach ($raw_trips as $t) {
    $t['pay_amount'] = $driver_rates[$t['destination']] ?? 0;
    $trips[] = $t;
}

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-8">
    <?php
    include 'views/home.php';
    include 'views/modals.php';
    ?>
</div>

<?php include '../includes/scripts.php'; ?>
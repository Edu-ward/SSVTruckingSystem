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

$stmt2 = $pdo->prepare("SELECT dispatch_date AS trip_date, destination, status, pay_amount FROM dispatches WHERE driver_id = ? ORDER BY dispatch_date DESC");
$stmt2->execute([$driver_id]);
$trips = $stmt2->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-8">
    <?php
    include 'views/home.php';
    include 'views/modals.php';
    ?>
</div>

<?php include '../includes/scripts.php'; ?>
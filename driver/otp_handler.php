<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT phone FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
    $driver = $stmt->fetch();

    if (!$driver || empty($driver['phone'])) {
        echo json_encode(['success' => false, 'message' => 'No mobile number on file for this account. Cannot verify via SMS.']);
        exit;
    }

    $phone = $driver['phone'];

    $otp = rand(100000, 999999);

    $_SESSION['reset_otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300;

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully',
        'simulated_otp' => $otp,
        'phone_last_4' => substr($phone, -4)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

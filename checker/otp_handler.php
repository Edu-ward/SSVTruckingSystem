<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Checker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checker_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT phone FROM checkers WHERE id = ?");
    $stmt->execute([$checker_id]);
    $checker = $stmt->fetch();

    if (!$checker || empty($checker['phone'])) {
        echo json_encode(['success' => false, 'message' => 'No mobile number on file for this account. Cannot verify via SMS.']);
        exit;
    }

    $phone = $checker['phone'];
    $otp = rand(100000, 999999);

    $_SESSION['reset_otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully',
        'simulated_otp' => $otp,
        'phone_last_4' => substr($phone, -4)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

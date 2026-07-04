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
    $otp_input = trim($_POST['otp'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (empty($otp_input) || empty($new_password)) {
        echo json_encode(['success' => false, 'message' => 'OTP and new password are required']);
        exit;
    }

    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expiry'])) {
        echo json_encode(['success' => false, 'message' => 'No OTP session found. Please request a new code.']);
        exit;
    }

    if (time() > $_SESSION['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new code.']);
        exit;
    }

    if ((string)$otp_input !== (string)$_SESSION['reset_otp']) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP code. Please check and try again.']);
        exit;
    }

    if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.']);
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$hashed_password, $driver_id])) {
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_expiry']);

        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Failed to update password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

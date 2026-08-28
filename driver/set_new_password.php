<?php
session_start();
require '../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check for an approved request
$check = $pdo->prepare("SELECT id FROM password_reset_requests WHERE user_id = ? AND status = 'Approved' ORDER BY id DESC LIMIT 1");
$check->execute([$user_id]);
$req = $check->fetch();

if (!$req) {
    echo json_encode(['success' => false, 'message' => 'No approved reset request found. Please wait for Admin approval.']);
    exit;
}

$new_password = $_POST['new_password'] ?? '';

if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters and include uppercase, lowercase, and a number.']);
    exit;
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
if ($update->execute([$hashed, $user_id])) {
    // Mark the request as resolved
    $pdo->prepare("UPDATE password_reset_requests SET status = 'Rejected', resolved_at = NOW() WHERE user_id = ? AND status = 'Approved'")->execute([$user_id]);
    log_activity($pdo, 'Changed Password', 'Driver reset their password via admin-approved request');
    echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Failed to update password.']);
}

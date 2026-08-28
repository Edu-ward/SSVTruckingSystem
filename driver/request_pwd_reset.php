<?php
session_start();
require '../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ensure table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100),
    role ENUM('Driver','Checker') NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB");

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Driver';

// Check if there's already a pending request
$check = $pdo->prepare("SELECT id, status FROM password_reset_requests WHERE user_id = ? AND status IN ('Pending','Approved') ORDER BY id DESC LIMIT 1");
$check->execute([$user_id]);
$existing = $check->fetch();

if ($existing) {
    if ($existing['status'] === 'Approved') {
        echo json_encode(['success' => true, 'already_approved' => true, 'message' => 'Your request has already been approved. You may now set your new password.']);
    } else {
        echo json_encode(['success' => true, 'already_pending' => true, 'message' => 'You already have a pending request. Please wait for Admin approval.']);
    }
    exit;
}

$insert = $pdo->prepare("INSERT INTO password_reset_requests (user_id, username, role) VALUES (?, ?, 'Driver')");
if ($insert->execute([$user_id, $username])) {
    log_activity($pdo, 'Requested Password Reset', 'Driver requested password reset');
    echo json_encode(['success' => true, 'message' => 'Request sent. Please wait for Admin approval.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit request. Please try again.']);
}

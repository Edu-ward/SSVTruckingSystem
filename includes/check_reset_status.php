<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Driver', 'Checker'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Only run CREATE TABLE once per process (not on every 5-second poll)
static $_prrTableEnsured = false;
if (!$_prrTableEnsured) {
    $_prrTableEnsured = true;
    try {
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
    } catch (Throwable $e) {
        // Table likely already exists or FK mismatch — non-fatal
        error_log('check_reset_status: table ensure error: ' . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT status, requested_at FROM password_reset_requests WHERE user_id = ? AND status IN ('Pending','Approved') ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['status' => 'none']);
} else {
    echo json_encode(['status' => $row['status'], 'requested_at' => $row['requested_at']]);
}

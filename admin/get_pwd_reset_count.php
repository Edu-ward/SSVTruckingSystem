<?php
session_start();
require __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $count = $pdo->query("SELECT COUNT(*) FROM password_reset_requests WHERE status = 'Pending'")->fetchColumn();
    echo json_encode(['count' => (int)$count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}

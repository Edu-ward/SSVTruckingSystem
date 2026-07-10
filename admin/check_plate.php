<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../db.php';

$plate = trim($_GET['plate'] ?? '');

if ($plate === '') {
    header('Content-Type: application/json');
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM trucks WHERE truck_code = ? LIMIT 1');
$stmt->execute([$plate]);

header('Content-Type: application/json');
echo json_encode(['exists' => (bool) $stmt->fetch()]);


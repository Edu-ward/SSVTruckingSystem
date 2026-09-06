<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Checker') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['rfid'])) {
    echo json_encode(['success' => false, 'message' => 'No RFID provided']);
    exit;
}

$rfid = trim($_GET['rfid']);
$stmt = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE rfid_tag = ?");
$stmt->execute([$rfid]);
$truck = $stmt->fetch();

if ($truck) {
    echo json_encode(['success' => true, 'truck_id' => $truck['id'], 'truck_code' => $truck['truck_code']]);
} else {
    echo json_encode(['success' => false, 'message' => 'No truck found for this RFID tag']);
}

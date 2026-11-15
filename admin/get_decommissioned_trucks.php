<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Superadmin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            t.id, 
            t.truck_code, 
            t.rfid_tag, 
            t.rfid_active, 
            t.status, 
            t.speed, 
            t.latitude, 
            t.longitude, 
            t.current_location, 
            (SELECT COUNT(*) FROM dispatches WHERE truck_id = t.id) AS total_dispatches,
            (SELECT MAX(dispatch_date) FROM dispatches WHERE truck_id = t.id) AS last_dispatch_date
        FROM trucks t 
        WHERE t.status = 'Decommissioned'
        ORDER BY t.truck_code ASC
    ");
    $trucks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count'   => count($trucks),
        'trucks'  => $trucks
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

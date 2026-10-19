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
            d.id,
            CONCAT(d.first_name, ' ', d.last_name) AS name,
            d.first_name,
            d.last_name,
            d.cdl_number,
            d.phone,
            d.profile_photo,
            d.status,
            d.rating,
            (SELECT COUNT(*) FROM dispatches WHERE driver_id = d.id AND status = 'Delivered') AS total_deliveries,
            (SELECT MAX(dispatch_date) FROM dispatches WHERE driver_id = d.id) AS last_dispatch_date
        FROM drivers d
        WHERE d.status = 'Resigned'
        ORDER BY d.first_name ASC, d.last_name ASC
    ");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count'   => count($drivers),
        'drivers' => $drivers
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// ── Authentication: Admin only ──
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['truck_code'])) {
    // ── CSRF Validation ──
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        exit;
    }

    $truck_code = trim($_POST['truck_code']);

    try {
        // Update the truck's status
        $stmt = $pdo->prepare("UPDATE trucks SET status = 'In Transit' WHERE truck_code = ? AND status = 'Loading'");
        $stmt->execute([$truck_code]);

        // Update the dispatch ticket status 
        $stmt2 = $pdo->prepare("
            UPDATE dispatches d 
            JOIN trucks t ON d.truck_id = t.id 
            SET d.status = 'In Transit' 
            WHERE t.truck_code = ? AND d.status = 'Pending'
        ");
        $stmt2->execute([$truck_code]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database operation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

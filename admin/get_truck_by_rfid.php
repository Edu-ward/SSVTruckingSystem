<?php
session_start();
require_once '../db.php'; // Make sure this path points to your database connection

header('Content-Type: application/json');

// Only logged-in Admins may query truck data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']);

    // Check if the RFID exists and get assigned driver if any
    $stmt = $pdo->prepare("
        SELECT t.id, t.truck_code, t.status, d.id as driver_id, CONCAT(d.first_name, ' ', d.last_name) as driver_name 
        FROM trucks t 
        LEFT JOIN drivers d ON t.id = d.truck_id 
        WHERE t.rfid_tag = ? LIMIT 1
    ");
    $stmt->execute([$rfid]);
    $truck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($truck) {
        echo json_encode([
            'success'     => true,
            'truck_id'    => $truck['id'],
            'truck_code'  => $truck['truck_code'],
            'status'      => $truck['status'],
            'driver_id'   => $truck['driver_id'],
            'driver_name' => $truck['driver_name'] ?: 'No Driver Assigned'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No truck found for this RFID tag']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No RFID provided']);
}

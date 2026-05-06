<?php
require_once '../db.php'; // Make sure this path points to your database connection

header('Content-Type: application/json');

if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']);

    // Check if the RFID exists and get assigned driver if any
    $stmt = $pdo->prepare("
        SELECT t.id, t.truck_code, d.id as driver_id, CONCAT(d.first_name, ' ', d.last_name) as driver_name 
        FROM trucks t 
        LEFT JOIN drivers d ON t.id = d.truck_id 
        WHERE t.rfid_tag = ? LIMIT 1
    ");
    $stmt->execute([$rfid]);
    $truck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($truck) {
        echo json_encode([
            'success' => true,
            'truck_id' => $truck['id'],
            'truck_code' => $truck['truck_code'],
            'driver_id' => $truck['driver_id'],
            'driver_name' => $truck['driver_name'] ?: 'No Driver Assigned'
        ]);
    } else {
        echo json_encode(['success' => false, 'debug_scanned' => $rfid]);
    }
} else {
    echo json_encode(['success' => false]);
}

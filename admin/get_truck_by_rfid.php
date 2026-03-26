<?php
require_once '../db.php'; // Make sure this path points to your database connection

header('Content-Type: application/json');

if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']);

    // Check if the RFID exists in the trucks table
    $stmt = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE rfid_tag = ? LIMIT 1");
    $stmt->execute([$rfid]);
    $truck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($truck) {
        // Return success and the truck details
        echo json_encode([
            'success' => true,
            'truck_id' => $truck['id'],
            'truck_code' => $truck['truck_code']
        ]);
    } else {
        // RFID not found
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}

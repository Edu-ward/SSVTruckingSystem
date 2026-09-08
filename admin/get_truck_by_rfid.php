<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Only logged-in Admins may query truck data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']);

    // Check if the RFID exists
    $stmt = $pdo->prepare("SELECT id, truck_code, status FROM trucks WHERE rfid_tag = ? LIMIT 1");
    $stmt->execute([$rfid]);
    $truck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($truck) {
        $driverStmt = $pdo->prepare("
            SELECT id, CONCAT(first_name, ' ', last_name) AS name, status 
            FROM drivers 
            WHERE truck_id = ? AND status != 'Resigned'
            ORDER BY id ASC
        ");
        $driverStmt->execute([$truck['id']]);
        $drivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);

        $driverCount = count($drivers);
        $primaryDriver = $driverCount > 0 ? $drivers[0] : null;

        echo json_encode([
            'success'      => true,
            'truck_id'     => $truck['id'],
            'truck_code'   => $truck['truck_code'],
            'status'       => $truck['status'],
            'driver_count' => $driverCount,
            'drivers'      => $drivers,
            'driver_id'    => $primaryDriver ? $primaryDriver['id'] : null,
            'driver_name'  => $primaryDriver ? $primaryDriver['name'] : 'No Driver Assigned'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No truck found for this RFID tag']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No RFID provided']);
}

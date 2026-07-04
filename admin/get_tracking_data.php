<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Only authenticated Admins may poll tracking data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Return only trucks that are actively In Transit with valid coordinates
$stmt = $pdo->query("
    SELECT 
        t.id,
        t.truck_code,
        t.status,
        t.current_location,
        t.speed,
        t.latitude,
        t.longitude,
        CONCAT(d.first_name, ' ', d.last_name) AS driver_name,
        disp.destination,
        disp.ticket_number,
        disp.transit_start_time
    FROM trucks t
    LEFT JOIN drivers d   ON t.id = d.truck_id
    LEFT JOIN dispatches disp ON t.id = disp.truck_id 
        AND disp.status = 'In Transit'
    WHERE t.status = 'In Transit'
      AND t.latitude IS NOT NULL
      AND t.longitude IS NOT NULL
    ORDER BY t.truck_code ASC
");

$trucks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'trucks' => $trucks, 'timestamp' => time()]);

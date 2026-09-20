<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$driver_id = $_SESSION['user_id'];
$lat   = isset($_POST['latitude'])  ? floatval($_POST['latitude'])  : null;
$lng   = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$speed = isset($_POST['speed'])     ? floatval($_POST['speed'])     : 0;


// Enforce Philippine geographic operational limits:
// Latitude: 4.5° N to 21.5° N, Longitude: 116.0° E to 127.0° E
if ($lat === null || $lng === null || $lat < 4.5 || $lat > 21.5 || $lng < 116.0 || $lng > 127.0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Coordinates are outside Philippine operational territory (4.5°N - 21.5°N, 116.0°E - 127.0°E)'
    ]);
    exit;
}


$stmtCheck = $pdo->prepare("
    SELECT d.truck_id 
    FROM dispatches d
    WHERE d.driver_id = ? AND d.status = 'In Transit'
    LIMIT 1
");
$stmtCheck->execute([$driver_id]);
$activeDispatch = $stmtCheck->fetch();

if (!$activeDispatch) {
    
    echo json_encode(['success' => true, 'tracking' => false, 'message' => 'Not in active transit']);
    exit;
}

$truck_id = $activeDispatch['truck_id'];
$location_name = isset($_POST['location_name']) && !empty(trim($_POST['location_name'])) ? trim($_POST['location_name']) : 'In Transit';


$stmt = $pdo->prepare("
    UPDATE trucks 
    SET latitude = ?, longitude = ?, speed = ?, current_location = ?
    WHERE id = ?
");
$stmt->execute([$lat, $lng, $speed, $location_name, $truck_id]);

echo json_encode(['success' => true, 'tracking' => true, 'location' => $location_name]);

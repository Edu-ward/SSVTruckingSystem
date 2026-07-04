<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

// Only authenticated drivers may push location
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

// Validate coordinates are plausible
if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit;
}

// Double-check server-side: only update if driver has an active In Transit dispatch
$stmtCheck = $pdo->prepare("
    SELECT d.truck_id 
    FROM dispatches d
    WHERE d.driver_id = ? AND d.status = 'In Transit'
    LIMIT 1
");
$stmtCheck->execute([$driver_id]);
$activeDispatch = $stmtCheck->fetch();

if (!$activeDispatch) {
    // Driver is not In Transit — silently succeed but don't update
    echo json_encode(['success' => true, 'tracking' => false, 'message' => 'Not in active transit']);
    exit;
}

$truck_id = $activeDispatch['truck_id'];

// Update the truck's GPS coordinates and speed
$stmt = $pdo->prepare("
    UPDATE trucks 
    SET latitude = ?, longitude = ?, speed = ?, current_location = 'In Transit'
    WHERE id = ?
");
$stmt->execute([$lat, $lng, $speed, $truck_id]);

echo json_encode(['success' => true, 'tracking' => true]);

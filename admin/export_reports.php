<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

$allowedPeriods = ['all', 'weekly', 'monthly', 'yearly', 'custom'];
$period = in_array($_GET['period'] ?? 'all', $allowedPeriods) ? $_GET['period'] : 'all';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$where = "WHERE 1=1";
$params = [];

if ($period === 'weekly') {
    $where .= " AND trip_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($period === 'monthly') {
    $where .= " AND MONTH(trip_date) = MONTH(CURDATE()) AND YEAR(trip_date) = YEAR(CURDATE())";
} elseif ($period === 'yearly') {
    $where .= " AND YEAR(trip_date) = YEAR(CURDATE())";
} elseif ($period === 'custom' && $start_date && $end_date) {
    $where .= " AND trip_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

// 1. Fetch Trips (Union of dispatches and driver_trips for maximum coverage)
$sql = "SELECT 
            t_all.trip_date, 
            t_all.destination, 
            t_all.status, 
            CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
            tr.truck_code,
            t_all.manual_revenue,
            o.gravel_type
        FROM (
            SELECT trip_date, destination, status, driver_id, order_id, 0 AS manual_revenue FROM driver_trips
            UNION ALL
            SELECT dispatch_date AS trip_date, destination, status, driver_id, NULL AS order_id, pay_amount AS manual_revenue FROM dispatches
        ) AS t_all
        LEFT JOIN drivers dr ON t_all.driver_id = dr.id
        LEFT JOIN trucks tr ON dr.truck_id = tr.id
        LEFT JOIN orders o ON t_all.order_id = o.id
        $where 
        ORDER BY t_all.trip_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Rate mapping from DB
$_dest_rows = $pdo->query("SELECT name, driver_rate FROM destinations WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$rates = [];
foreach ($_dest_rows as $_d) {
    $rates[$_d['name']] = floatval($_d['driver_rate']);
}

// Gravel type reference prices from DB
$_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$gravelPrices = [
    "S1_regular" => 1500, "S1_crushed" => 1600, "3_4_regular" => 1400, "3_4_crushed" => 1500,
    "G1_regular" => 1700, "G1_crushed" => 1800, "38_regular" => 1300, "38_crushed" => 1400,
    "base_course" => 1200, "river_mix" => 1100, "garden_soil" => 1000
];
foreach ($_gravel_rows as $_g) {
    if (!isset($gravelPrices[$_g['type_key']])) {
        $gravelPrices[$_g['type_key']] = 1500;
    }
}

// 2. Prepare CSV
$filename = "SSV_Trip_Report_" . ($period) . "_" . date('Ymd') . ".csv";

// Clear any previous output to ensure a clean file
if (ob_get_length()) ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['Trip Date', 'Truck Code', 'Driver Name', 'Destination', 'Status', 'Revenue (₱)']);

// Data
foreach ($data as $row) {
    // Calculate revenue: manual if exists, otherwise lookup by gravel type
    if ($row['manual_revenue'] > 0) {
        $revenue = floatval($row['manual_revenue']);
    } else {
        $revenue = $gravelPrices[$row['gravel_type']] ?? 0;
    }

    fputcsv($output, [
        $row['trip_date'],
        $row['truck_code'] ?? 'N/A',
        $row['driver_name'] ?? 'Unknown/Deleted',
        $row['destination'],
        $row['status'],
        number_format($revenue, 2)
    ]);
}


fclose($output);
exit;



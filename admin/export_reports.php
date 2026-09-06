<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

$allowedPeriods = ['all', 'weekly', 'monthly', 'yearly', 'custom', 'month'];
$period = in_array($_GET['period'] ?? 'all', $allowedPeriods) ? $_GET['period'] : 'all';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$month = $_GET['month'] ?? null;

$where = "WHERE 1=1";
$params = [];

if ($period === 'weekly') {
    $where .= " AND trip_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($period === 'monthly') {
    $where .= " AND MONTH(trip_date) = MONTH(CURDATE()) AND YEAR(trip_date) = YEAR(CURDATE())";
} elseif ($period === 'month' && !empty($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
    $where .= " AND DATE_FORMAT(trip_date, '%Y-%m') = ?";
    $params[] = $month;
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


// Helper: calculate driver trip pay
if (!function_exists('isWithinSanLeonardo')) {
    function isWithinSanLeonardo(string $destName): bool {
        $d = strtolower(trim($destName));
        if ($d === '') return false;
        if (strpos($d, 'san leonardo') !== false) return true;
        $barangays = [
            'bonifacio', 'burgos', 'castillejos', 'diversion', 'magpapalayoc',
            'mallorca', 'mambangnan', 'nieves', 'san anton', 'san bartolome',
            'san francisco', 'san roque', 'santa cruz', 'sta. cruz', 'tabuating', 'tagumpay'
        ];
        foreach ($barangays as $b) {
            if (preg_match('/\b' . preg_quote($b, '/') . '\b/i', $d)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('getSanLeonardoBoundaryDistance')) {
    function getSanLeonardoBoundaryDistance(string $destName): float {
        $d = strtolower(trim($destName));
        if ($d === '') return 12.0;
        // East border (Peñaranda / Gen. Tinio) is ~3 km one-way = 6 km round-trip
        if (strpos($d, 'peñaranda') !== false || strpos($d, 'penaranda') !== false || strpos($d, 'general tinio') !== false || strpos($d, 'gen. tinio') !== false || strpos($d, 'papaya') !== false) {
            return 6.0;
        }
        // South (Gapan), North (Santa Rosa/Cabanatuan), West (Jaen/San Isidro) ~6 km one-way = 12 km round-trip
        return 12.0;
    }
}

$_settings_raw = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$BASE_TRIP_RATE = floatval($_settings_raw['base_trip_rate'] ?? 300.00);
$RATE_PER_KM    = floatval($_settings_raw['rate_per_km'] ?? 10.00);

if (!function_exists('calculateTripPay')) {
    function calculateTripPay(float $dist_km, string $destName = '', float $customRate = 0.0, ?float $baseRate = null, ?float $perKmRate = null): float {
        global $BASE_TRIP_RATE, $RATE_PER_KM;
        $base = ($baseRate !== null && $baseRate > 0) ? $baseRate : (isset($BASE_TRIP_RATE) && $BASE_TRIP_RATE > 0 ? $BASE_TRIP_RATE : 300.00);
        $perKm = ($perKmRate !== null && $perKmRate >= 0) ? $perKmRate : (isset($RATE_PER_KM) && $RATE_PER_KM >= 0 ? $RATE_PER_KM : 10.00);

        if (isWithinSanLeonardo($destName)) {
            return $customRate > 0 ? $customRate : $base;
        }
        $km = round($dist_km);
        if ($km > 0) {
            $boundaryKm = getSanLeonardoBoundaryDistance($destName);
            $outsideKm = max(0, $km - $boundaryKm);
            $tripBase = ($customRate > 0) ? $customRate : $base;
            return round($tripBase + ($outsideKm * $perKm), 2);
        }
        if ($customRate > 0) {
            return $customRate;
        }
        return $base;
    }
}

// Rate mapping from DB (flat rate within San Leonardo; base + rate/km for distance outside San Leonardo boundary)
$_dest_rows = $pdo->query("SELECT name, driver_rate, distance_km FROM destinations WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$rates = [];
foreach ($_dest_rows as $_d) {
    $km = floatval($_d['distance_km']);
    $rates[$_d['name']] = calculateTripPay($km, $_d['name'], floatval($_d['driver_rate']));
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



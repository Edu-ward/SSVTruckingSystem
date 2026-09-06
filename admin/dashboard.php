<?php
// Catch and display any unhandled error to prevent blank 500 error screen
set_exception_handler(function ($e) {
    echo "<!DOCTYPE html><html><head><title>System Notice</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-900 text-gray-100 p-6 flex items-center justify-center min-h-screen'>";
    echo "<div class='max-w-xl w-full bg-gray-800 border border-amber-500/50 rounded-2xl p-6 shadow-2xl space-y-4'>";
    echo "<div class='flex items-center space-x-3 text-amber-400 font-bold text-lg'><i class='fa-solid fa-triangle-exclamation'></i><span>Dashboard Notice</span></div>";
    echo "<p class='text-sm text-gray-300'>A database query or configuration item needs attention:</p>";
    echo "<div class='p-3 bg-gray-900 rounded-xl font-mono text-xs text-amber-300 overflow-auto'>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p class='text-xs text-gray-400'>File: " . htmlspecialchars(basename($e->getFile())) . " (Line " . $e->getLine() . ")</p>";
    echo "<div class='pt-2 flex gap-3'>";
    echo "<a href='../index.php' class='px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition'>Return to Home</a>";
    echo "<a href='../create_admin.php' class='px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-xl text-xs font-bold transition'>Setup Tool</a>";
    echo "</div></div></body></html>";
    exit;
});

require_once __DIR__ . '/../includes/security_headers.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

// Check if core tables exist; if system_settings is missing, auto-import ssv_trucking.sql
try {
    $hasSettings = $pdo->query("SHOW TABLES LIKE 'system_settings'")->fetch();
    if (!$hasSettings) {
        $sqlPath = __DIR__ . '/../ssv_trucking.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
            foreach ($statements as $stmtSql) {
                if (!empty($stmtSql) && !str_starts_with($stmtSql, '--') && !str_starts_with($stmtSql, '/*')) {
                    try {
                        $pdo->exec($stmtSql);
                    } catch (Throwable $ignore) {}
                }
            }
        }
    }
} catch (Throwable $e) {}

// Safe runtime schema synchronization
try {
    $_ensureCol = function($pdo, $tbl, $col, $def) {
        try {
            $chk = $pdo->query("SHOW COLUMNS FROM `$tbl` LIKE '$col'")->fetch();
            if (!$chk) {
                $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `$col` $def");
            }
        } catch (Throwable $ex) {}
    };

    // Checkers table columns
    $_ensureCol($pdo, 'checkers', 'status', "VARCHAR(50) DEFAULT 'Active'");
    $_ensureCol($pdo, 'checkers', 'phone', "VARCHAR(20) DEFAULT ''");
    $_ensureCol($pdo, 'checkers', 'first_name', "VARCHAR(100) DEFAULT ''");
    $_ensureCol($pdo, 'checkers', 'last_name', "VARCHAR(100) DEFAULT ''");

    // Drivers table columns
    $_ensureCol($pdo, 'drivers', 'status', "VARCHAR(50) DEFAULT 'Off Duty'");
    $_ensureCol($pdo, 'drivers', 'profile_photo', "VARCHAR(255) DEFAULT NULL");
    $_ensureCol($pdo, 'drivers', 'rating', "DECIMAL(3, 2) DEFAULT 5.00");
    $_ensureCol($pdo, 'drivers', 'truck_id', "INT DEFAULT NULL");

    // Trucks table columns
    $_ensureCol($pdo, 'trucks', 'rfid_active', "TINYINT(1) DEFAULT 1");
    $_ensureCol($pdo, 'trucks', 'status', "VARCHAR(50) DEFAULT 'Idle'");

    // Orders table columns
    $_ensureCol($pdo, 'orders', 'contact_number', 'VARCHAR(50) DEFAULT NULL');
    $_ensureCol($pdo, 'orders', 'landmark', 'VARCHAR(255) DEFAULT NULL');
    $_ensureCol($pdo, 'orders', 'cubic_meters_required', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'orders', 'cubic_meters_fulfilled', 'DECIMAL(10,2) DEFAULT 0.00');

    // Dispatches table columns
    $_ensureCol($pdo, 'dispatches', 'client_name', 'VARCHAR(255) DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'contact_number', 'VARCHAR(50) DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'landmark', 'VARCHAR(255) DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'cubic_meters', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'dispatches', 'pay_amount', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'dispatches', 'distance_km', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'dispatches', 'is_payroll_paid', 'TINYINT(1) DEFAULT 0');
    $_ensureCol($pdo, 'dispatches', 'payroll_id', 'INT DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'is_on_time', 'TINYINT(1) DEFAULT 1');
    $_ensureCol($pdo, 'dispatches', 'estimated_arrival_time', 'DATETIME DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'transit_start_time', 'DATETIME DEFAULT NULL');
    $_ensureCol($pdo, 'dispatches', 'transit_end_time', 'DATETIME DEFAULT NULL');

    // Driver Trips table columns
    $_ensureCol($pdo, 'driver_trips', 'pay_amount', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'driver_trips', 'distance_km', 'DECIMAL(10,2) DEFAULT 0.00');
    $_ensureCol($pdo, 'driver_trips', 'is_on_time', 'TINYINT(1) DEFAULT 1');
    $_ensureCol($pdo, 'driver_trips', 'estimated_arrival_time', 'DATETIME DEFAULT NULL');

    // Destinations table columns
    $_ensureCol($pdo, 'destinations', 'is_active', 'TINYINT(1) DEFAULT 1');
    $_ensureCol($pdo, 'destinations', 'driver_rate', 'DECIMAL(10,2) DEFAULT 300.00');
    $_ensureCol($pdo, 'destinations', 'distance_km', 'DECIMAL(10,2) DEFAULT 0.00');

    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_reset_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `username` VARCHAR(100) DEFAULT NULL,
        `role` ENUM('Driver','Checker') NOT NULL,
        `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `resolved_at` TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `cash_advances` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `driver_id` INT NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `reason` TEXT DEFAULT NULL,
        `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        `is_settled` TINYINT(1) NOT NULL DEFAULT 0,
        `settled_at` DATETIME DEFAULT NULL,
        `payroll_id` INT DEFAULT NULL,
        `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `resolved_at` TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `driver_payroll` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `driver_id` INT NOT NULL UNIQUE,
        `total_amount` DECIMAL(10,2) DEFAULT 0.00,
        `amount_claimed` DECIMAL(10,2) DEFAULT 0.00,
        `remaining_balance` DECIMAL(10,2) GENERATED ALWAYS AS (`total_amount` - `amount_claimed`) STORED,
        `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `driver_payroll_settlements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `driver_id` INT NOT NULL,
        `period_start` DATE NOT NULL,
        `period_end` DATE NOT NULL,
        `gross_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `cash_advances_deducted` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `net_pay` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `payment_method` VARCHAR(50) DEFAULT 'Cash',
        `payment_reference` VARCHAR(100) DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `settled_by` INT DEFAULT NULL,
        `settled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

try {
    $_settings_raw = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    $_settings_raw = [];
}
$GARAGE_NAME = $_settings_raw['garage_name'] ?? 'San Leonardo (Quarry Garage)';
$GARAGE_LAT  = floatval($_settings_raw['garage_lat'] ?? 15.359042);
$GARAGE_LNG  = floatval($_settings_raw['garage_lng'] ?? 120.965016);
$OP_COST_PCT = floatval($_settings_raw['op_cost_pct'] ?? 0.40);
$BASE_TRIP_RATE = floatval($_settings_raw['base_trip_rate'] ?? 300.00);
$RATE_PER_KM    = floatval($_settings_raw['rate_per_km'] ?? 10.00);

try {
    $_dest_rows  = $pdo->query("SELECT name, driver_rate, distance_km FROM destinations WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $_dest_rows = [];
}
$DRIVER_RATES = [];
$DISTANCE_KM  = [];
$destinations = [];
foreach ($_dest_rows as $_d) {
    $DRIVER_RATES[$_d['name']] = floatval($_d['driver_rate']);
    $DISTANCE_KM[$_d['name']]  = floatval($_d['distance_km']);
    $destinations[] = $_d;
}

// Helper: detect if a destination is located within San Leonardo municipality
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

// Helper: get the round-trip boundary distance from garage to the San Leonardo border
// Default is 12 km round-trip (e.g. 6 km one-way towards Gapan, Santa Rosa, Jaen, etc.)
// Narrower towards East (Peñaranda ~6 km round-trip)
function getSanLeonardoBoundaryDistance(string $destName): float {
    $d = strtolower(trim($destName));
    if (strpos($d, 'peñaranda') !== false || strpos($d, 'penaranda') !== false || strpos($d, 'general tinio') !== false || strpos($d, 'gen. tinio') !== false || strpos($d, 'papaya') !== false) {
        return 6.0;
    }
    return 12.0; // 12 km round-trip from garage to San Leonardo municipal boundary
}

// Helper: calculate driver trip pay
// - Trips within San Leonardo: flat rate (uses destination custom rate if set > 0, else global base rate)
// - Trips outside San Leonardo: base rate + rate/km for distance outside (deducting boundary distance, e.g. 12 km)
function calculateTripPay(float $dist_km, string $destName = '', float $customRate = 0.0, ?float $baseRate = null, ?float $perKmRate = null): float {
    global $BASE_TRIP_RATE, $RATE_PER_KM, $DRIVER_RATES;
    $base  = ($baseRate !== null && $baseRate > 0) ? $baseRate : (isset($BASE_TRIP_RATE) && $BASE_TRIP_RATE > 0 ? $BASE_TRIP_RATE : 300.00);
    $perKm = ($perKmRate !== null && $perKmRate >= 0) ? $perKmRate : (isset($RATE_PER_KM) && $RATE_PER_KM >= 0 ? $RATE_PER_KM : 10.00);

    if ($customRate <= 0 && !empty($destName) && isset($DRIVER_RATES[$destName])) {
        $customRate = floatval($DRIVER_RATES[$destName]);
    }

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

// Helper: get driver pay for a destination
function getDestinationPay(string $destName, array $DISTANCE_KM, array $DRIVER_RATES): float {
    $km = floatval($DISTANCE_KM[$destName] ?? 0);
    $rate = floatval($DRIVER_RATES[$destName] ?? 0);
    return calculateTripPay($km, $destName, $rate);
}

// Helper: compute whether delivery arrival is on-time based on ETA
function computeIsOnTime(array $dispatch, ?string $nowTime = null): int {
    global $DISTANCE_KM;
    $nowTs = $nowTime ? strtotime($nowTime) : time();
    if (!empty($dispatch['estimated_arrival_time'])) {
        return ($nowTs <= strtotime($dispatch['estimated_arrival_time'])) ? 1 : 0;
    }
    $start = !empty($dispatch['transit_start_time']) ? $dispatch['transit_start_time'] : ($dispatch['created_at'] ?? null);
    if ($start) {
        $dist = floatval($dispatch['distance_km'] ?? ($DISTANCE_KM[$dispatch['destination']] ?? 20));
        $oneWay = max(5, round($dist > 40 ? ($dist / 2) : $dist));
        $etaMins = max(25, round(($oneWay / 35) * 60) + 15);
        $expectedTs = strtotime($start) + ($etaMins * 60);
        return ($nowTs <= $expectedTs) ? 1 : 0;
    }
    return 1;
}

$_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$gravelTypes = [];
foreach ($_gravel_rows as $_g) {
    $gravelTypes[$_g['type_key']] = $_g['label'];
}


if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }

    // ── General Trip Rates: Update ──
    if ($_POST['action'] === 'update_trip_rates') {
        $baseRate  = floatval($_POST['base_trip_rate'] ?? 300.00);
        $perKmRate = floatval($_POST['rate_per_km'] ?? 10.00);
        if ($baseRate < 0 || $perKmRate < 0) {
            $_SESSION['dest_error'] = 'Trip rates cannot be negative.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute(['base_trip_rate', number_format($baseRate, 2, '.', ''), 'Base flat rate for trips within San Leonardo (PHP)']);
                $stmt->execute(['rate_per_km', number_format($perKmRate, 2, '.', ''), 'Rate per kilometer for distance outside San Leonardo boundary (PHP)']);
                $_SESSION['dest_success'] = "General Trip Rates updated: Base Flat Rate = ₱" . number_format($baseRate, 2) . ", Rate/km = ₱" . number_format($perKmRate, 2) . ".";
                log_activity($pdo, 'Updated Trip Rates', "Updated base rate to ₱{$baseRate} and rate/km to ₱{$perKmRate}");
            } catch (Exception $e) {
                $_SESSION['dest_error'] = 'Failed to update trip rates: ' . $e->getMessage();
            }
        }
        header('Location: dashboard.php?tab=settings');
        exit;
    }

    // ── Destination: Add ──
    if ($_POST['action'] === 'add_destination') {
        $dname  = trim($_POST['dest_name'] ?? '');
        $dkm    = floatval($_POST['dest_distance_km'] ?? 0);
        $drate  = floatval($_POST['dest_driver_rate'] ?? 0);
        if (!$dname) {
            $_SESSION['dest_error'] = 'Destination name is required.';
        } else {
            try {
                $pdo->prepare("INSERT INTO destinations (name, distance_km, driver_rate) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE distance_km = VALUES(distance_km), driver_rate = VALUES(driver_rate)")
                    ->execute([$dname, $dkm, $drate]);
                $_SESSION['dest_success'] = "Destination '" . htmlspecialchars($dname) . "' added successfully.";
                log_activity($pdo, 'Added Destination', "Added destination: $dname (distance: {$dkm} km, rate: ₱{$drate})");
            } catch (Exception $e) {
                $_SESSION['dest_error'] = 'Failed to add destination. Name may already exist.';
            }
        }
        header('Location: dashboard.php?tab=settings');
        exit;
    }

    // ── Destination: Edit ──
    if ($_POST['action'] === 'edit_destination') {
        $did    = intval($_POST['dest_id'] ?? 0);
        $dname  = trim($_POST['dest_name'] ?? '');
        $dkm    = floatval($_POST['dest_distance_km'] ?? 0);
        $drate  = floatval($_POST['dest_driver_rate'] ?? 0);
        $active = intval($_POST['dest_is_active'] ?? 1);
        if (!$did || !$dname) {
            $_SESSION['dest_error'] = 'Invalid destination data.';
        } else {
            try {
                $pdo->prepare("UPDATE destinations SET name = ?, distance_km = ?, driver_rate = ?, is_active = ? WHERE id = ?")
                    ->execute([$dname, $dkm, $drate, $active, $did]);
                $payText = calculateTripPay($dkm, $dname, $drate);
                $_SESSION['dest_success'] = "Destination updated: '" . htmlspecialchars($dname) . "' — " . ($dkm > 0 ? number_format($dkm, 1) . " km (₱" . number_format($payText, 2) . " pay)" : "Flat rate ₱" . number_format($drate, 2)) . ".";
                log_activity($pdo, 'Edited Destination', "Updated destination ID $did: $dname (distance: {$dkm} km, rate: ₱{$drate})");
            } catch (Exception $e) {
                $_SESSION['dest_error'] = 'Failed to update destination.';
            }
        }
        header('Location: dashboard.php?tab=settings');
        exit;
    }

    if ($_POST['action'] == 'create_dispatch') {
        $truck_id = !empty($_POST['truck_id']) ? $_POST['truck_id'] : null;
        $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
        $rfid_tag = $_POST['rfid_tag'];

        if (!$truck_id || !$driver_id) {
            $_SESSION['scan_err'] = "Cannot create dispatch. The scanned truck does not have an assigned driver.";
            header("Location: dashboard.php?tab=dispatches");
            exit;
        }
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $cubic_meters = !empty($_POST['cubic_meters']) ? floatval($_POST['cubic_meters']) : 0.00;
        $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
        $client_name = !empty($_POST['client_name']) ? trim($_POST['client_name']) : null;
        $contact_number = !empty($_POST['contact_number']) ? trim($_POST['contact_number']) : null;
        $landmark = !empty($_POST['landmark']) ? trim($_POST['landmark']) : null;

        if ($order_id) {
            $ordStmt = $pdo->prepare("SELECT client_name, contact_number, landmark FROM orders WHERE id = ?");
            $ordStmt->execute([$order_id]);
            $ordData = $ordStmt->fetch(PDO::FETCH_ASSOC);
            if ($ordData) {
                if (empty($client_name)) $client_name = $ordData['client_name'] ?? null;
                if (empty($contact_number)) $contact_number = $ordData['contact_number'] ?? null;
                if (empty($landmark)) $landmark = $ordData['landmark'] ?? null;
            }
        }

        // Distance-based pay: 10 PHP per km (one-way only) - whole number rounded
        $dist_km = 0;
        if (!empty($_POST['distance_km']) && floatval($_POST['distance_km']) > 0) {
            $dist_km = round(floatval($_POST['distance_km']));
        } elseif (isset($DISTANCE_KM[$destination]) && floatval($DISTANCE_KM[$destination]) > 0) {
            $dist_km = round(floatval($DISTANCE_KM[$destination]));
        }

        // Fallback lookup if distance is still 0 (check common provincial town names in destination string)
        if ($dist_km <= 0) {
            $destLower = strtolower($destination);
            $presetTownKm = [
                'peñaranda' => 20, 'penaranda' => 20,
                'general tinio' => 30, 'gen. tinio' => 30, 'papaya' => 30,
                'gapan' => 22, 'san isidro' => 24, 'jaen' => 20,
                'santa rosa' => 24, 'sta. rosa' => 24, 'cabanatuan' => 44,
                'palayan' => 50, 'talavera' => 60, 'san leonardo' => 30,
                'tarlac' => 160, 'laur' => 180, 'gabaldon' => 200,
                'dingalan' => 230, 'baler' => 280, 'san miguel' => 52,
                'san ildefonso' => 68, 'san rafael' => 90, 'baliuag' => 104
            ];
            foreach ($presetTownKm as $town => $kmVal) {
                if (strpos($destLower, $town) !== false) {
                    $dist_km = $kmVal;
                    break;
                }
            }
        }

        // Whole number rounding
        $dist_km = round($dist_km);
        if ($dist_km < 1 && !empty($destination)) {
            $dist_km = 1;
        }

        // Calculate driver pay:
        // - Trips within San Leonardo: ₱300 flat
        // - Trips outside San Leonardo: ₱300 base + ₱10/km for distance outside boundary (garage to boundary distance deducted)
        $driver_pay = calculateTripPay($dist_km, $destination, floatval($DRIVER_RATES[$destination] ?? 0));
        if (!empty($_POST['pay_amount']) && floatval($_POST['pay_amount']) > 0) {
            $postedPay = floatval($_POST['pay_amount']);
            if (isWithinSanLeonardo($destination)) {
                $driver_pay = 300.00;
            } else {
                $driver_pay = $postedPay;
            }
        }

        // Calculate ETA to site (one-way distance, ~35 km/h + 15 min buffer)
        $oneWayKm = floatval($dist_km > 0 ? ($dist_km > 40 ? $dist_km / 2 : $dist_km) : 20.0);
        $etaMinutes = max(25, round(($oneWayKm / 35) * 60) + 15);
        if (!empty($_POST['estimated_arrival_time'])) {
            $estimated_arrival_time = date('Y-m-d H:i:s', strtotime($_POST['estimated_arrival_time']));
        } else {
            $estimated_arrival_time = date('Y-m-d H:i:s', strtotime("+{$etaMinutes} minutes"));
        }

        $ticketNum = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $insert = $pdo->prepare("INSERT INTO dispatches (ticket_number, truck_id, driver_id, client_name, contact_number, status, origin, destination, landmark, pay_amount, dispatch_date, cubic_meters, order_id, transit_start_time, estimated_arrival_time) VALUES (?, ?, ?, ?, ?, 'In Transit', ?, ?, ?, ?, CURDATE(), ?, ?, NOW(), ?)");
        $insert->execute([$ticketNum, $truck_id, $driver_id, $client_name, $contact_number, $origin, $destination, $landmark, $driver_pay, $cubic_meters, $order_id, $estimated_arrival_time]);
        $new_dispatch_id = $pdo->lastInsertId();

        $trip_insert = $pdo->prepare("INSERT INTO driver_trips (driver_id, destination, trip_date, status, order_id, transit_start_time, estimated_arrival_time, distance_km, pay_amount) VALUES (?, ?, CURDATE(), 'In Transit', ?, NOW(), ?, ?, ?)");
        $trip_insert->execute([$driver_id, $destination, $order_id, $estimated_arrival_time, $dist_km, $driver_pay]);

        // Keep destinations table in sync so future lookups have this distance and rate
        $chkDest = $pdo->prepare("SELECT id, distance_km FROM destinations WHERE name = ?");
        $chkDest->execute([$destination]);
        $destRow = $chkDest->fetch(PDO::FETCH_ASSOC);
        if (!$destRow) {
            $insDest = $pdo->prepare("INSERT INTO destinations (name, distance_km, driver_rate) VALUES (?, ?, ?)");
            $insDest->execute([$destination, $dist_km, $driver_pay]);
        } elseif (floatval($destRow['distance_km']) <= 0 && $dist_km > 0) {
            $updDest = $pdo->prepare("UPDATE destinations SET distance_km = ?, driver_rate = ? WHERE id = ?");
            $updDest->execute([$dist_km, $driver_pay, $destRow['id']]);
        }

        if ($order_id) {
            $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ? AND status = 'Pending'")->execute([$order_id]);
        }

        $pdo->prepare("UPDATE trucks SET status = 'In Transit' WHERE id = ?")->execute([$truck_id]);
        $pdo->prepare("UPDATE drivers SET status = 'In Transit' WHERE id = ?")->execute([$driver_id]);

        $_SESSION['auto_print_id'] = $new_dispatch_id;
        $_SESSION['success'] = "Dispatch ticket <strong>{$ticketNum}</strong> created. Truck is now <strong>In Transit</strong>.";
        log_activity($pdo, 'Created Dispatch', "Created dispatch {$ticketNum} to {$destination}");
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'add_driver') {
        $name = $_POST['name'];
        $nameParts = explode(' ', trim($name), 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        $cdl = $_POST['cdl_number'];
        $phone = $_POST['phone'];
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            die("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Driver')");
        $user_insert->execute([$username, $hashed_password]);

        $new_user_id = $pdo->lastInsertId();
        $truck_rfid = trim($_POST['truck_rfid']);

        $stmt_truck = $pdo->prepare("SELECT id FROM trucks WHERE rfid_tag = ? LIMIT 1");
        $stmt_truck->execute([$truck_rfid]);
        $truck = $stmt_truck->fetch();
        $truck_id = $truck ? $truck['id'] : null;

        $driver_insert = $pdo->prepare("INSERT INTO drivers (id, first_name, last_name, cdl_number, phone, status, truck_id) VALUES (?, ?, ?, ?, ?, 'Off Duty', ?)");
        $driver_insert->execute([$new_user_id, $firstName, $lastName, $cdl, $phone, $truck_id]);

        $_SESSION['success'] = "Driver <strong>" . htmlspecialchars($name) . "</strong> added successfully.";
        log_activity($pdo, 'Registered Driver', 'Registered driver: ' . $name);
        header("Location: dashboard.php?tab=drivers");
        exit;
    }

    if ($_POST['action'] == 'update_truck_status') {
        $truck_id = $_POST['truck_id'];
        $new_status = $_POST['new_status'];
        if ($new_status == 'Idle') {
            $pdo->prepare("UPDATE trucks SET status = ?, speed = 0, current_location = ?, latitude = ?, longitude = ? WHERE id = ?")->execute([$new_status, $GARAGE_NAME, $GARAGE_LAT, $GARAGE_LNG, $truck_id]);
        } else {
            $pdo->prepare("UPDATE trucks SET status = ? WHERE id = ?")->execute([$new_status, $truck_id]);
        }
        $_SESSION['success'] = "Truck status updated to <strong>" . htmlspecialchars($new_status) . "</strong>.";
        log_activity($pdo, 'Updated Truck Status', 'Updated truck ID ' . $truck_id . ' status to ' . $new_status);
        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    if ($_POST['action'] == 'dispatch_scan_rfid') {
        $rfid_tag = trim($_POST['rfid_tag']);

        $ts = $pdo->prepare("SELECT id, truck_code FROM trucks WHERE rfid_tag = ?");
        $ts->execute([$rfid_tag]);
        $truck = $ts->fetch();

        if ($truck) {
            $ds = $pdo->prepare("SELECT * FROM dispatches WHERE truck_id = ? AND status IN ('Pending', 'In Transit') ORDER BY id DESC LIMIT 1");
            $ds->execute([$truck['id']]);
            $dispatch = $ds->fetch();

            if ($dispatch) {
                if ($dispatch['status'] === 'In Transit' || $dispatch['status'] === 'Pending') {
                    $isOnTime = computeIsOnTime($dispatch, date('Y-m-d H:i:s'));
                    $pdo->prepare("UPDATE dispatches SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE id = ?")->execute([$isOnTime, $dispatch['id']]);
                    $pdo->prepare("UPDATE trucks SET status = 'Idle', current_location = ? WHERE id = ?")->execute([$GARAGE_NAME, $truck['id']]);
                    $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$dispatch['driver_id']]);
                    $pdo->prepare("UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status IN ('In Transit', 'Pending') ORDER BY id DESC LIMIT 1")->execute([$isOnTime, $dispatch['driver_id'], $dispatch['destination']]);

                    // Update order progress
                    $order_id = $dispatch['order_id'] ?? null;
                    if (!$order_id) {
                        $stmt_find_ord = $pdo->prepare("SELECT id FROM orders WHERE destination = ? AND status IN ('Pending', 'In Progress') ORDER BY id ASC LIMIT 1");
                        $stmt_find_ord->execute([$dispatch['destination']]);
                        $found_ord = $stmt_find_ord->fetch();
                        if ($found_ord) {
                            $order_id = $found_ord['id'];
                            $pdo->prepare("UPDATE dispatches SET order_id = ? WHERE id = ?")->execute([$order_id, $dispatch['id']]);
                        }
                    }

                    if ($order_id) {
                        $scannedCm = floatval(($dispatch['cubic_meters'] ?? 0) > 0 ? $dispatch['cubic_meters'] : 10.00);
                        $admin_id = $_SESSION['user_id'];

                        $dupScan = $pdo->prepare("SELECT id FROM order_scans WHERE order_id = ? AND truck_id = ?");
                        $dupScan->execute([$order_id, $truck['id']]);
                        if (!$dupScan->fetch()) {
                            $pdo->prepare("INSERT INTO order_scans (order_id, truck_id, checker_id) VALUES (?, ?, ?)")
                                ->execute([$order_id, $truck['id'], $admin_id]);
                        }

                        $pdo->prepare("UPDATE orders SET trucks_fulfilled = trucks_fulfilled + 1, cubic_meters_fulfilled = cubic_meters_fulfilled + ? WHERE id = ?")
                            ->execute([$scannedCm, $order_id]);

                        $chkOrd = $pdo->prepare("SELECT trucks_required, trucks_fulfilled, cubic_meters_required, cubic_meters_fulfilled FROM orders WHERE id = ?");
                        $chkOrd->execute([$order_id]);
                        $updatedOrd = $chkOrd->fetch();

                        $reqCm = floatval($updatedOrd['cubic_meters_required'] > 0 ? $updatedOrd['cubic_meters_required'] : $updatedOrd['trucks_required']);
                        $doneCm = floatval($updatedOrd['cubic_meters_fulfilled'] > 0 ? $updatedOrd['cubic_meters_fulfilled'] : $updatedOrd['trucks_fulfilled']);

                        if ($doneCm >= $reqCm) {
                            $pdo->prepare("UPDATE orders SET status = 'Fulfilled' WHERE id = ?")->execute([$order_id]);
                        } else {
                            $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ? AND status = 'Pending'")->execute([$order_id]);
                        }
                    }

                    $_SESSION['scan_msg'] = "✅ <strong>{$truck['truck_code']}</strong> arrived. Trip completed and Order progress updated!";
                    log_activity($pdo, 'RFID Scan Delivery', 'Truck ' . $truck['truck_code'] . ' arrived — dispatch completed via RFID scan');
                }
            } else {
                $_SESSION['scan_err'] = "❌ No active dispatch found for truck <strong>{$truck['truck_code']}</strong>.";
            }
        } else {
            $_SESSION['scan_err'] = "❌ Unknown RFID tag.";
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }


    if ($_POST['action'] == 'update_driver_status') {
        $driver_id = $_POST['driver_id'];
        $new_status = $_POST['new_status'];
        $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?")->execute([$new_status, $driver_id]);
        $_SESSION['success'] = "Driver status updated to <strong>" . htmlspecialchars($new_status) . "</strong>.";
        log_activity($pdo, 'Updated Driver Status', 'Updated driver ID ' . $driver_id . ' status to ' . $new_status);
        header("Location: dashboard.php?tab=drivers");
        exit;
    }

    if ($_POST['action'] == 'complete_dispatch') {
        $dispatch_id = $_POST['dispatch_id'];

        $stmt = $pdo->prepare("SELECT * FROM dispatches WHERE id = ?");
        $stmt->execute([$dispatch_id]);
        $dispatch = $stmt->fetch();

        if ($dispatch) {
            $isOnTime = computeIsOnTime($dispatch, date('Y-m-d H:i:s'));
            $pdo->prepare("UPDATE dispatches SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE id = ?")->execute([$isOnTime, $dispatch_id]);
            $pdo->prepare("UPDATE trucks SET status = 'Idle', current_location = ? WHERE id = ?")->execute([$GARAGE_NAME, $dispatch['truck_id']]);
            $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$dispatch['driver_id']]);
            $pdo->prepare("UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1")->execute([$isOnTime, $dispatch['driver_id'], $dispatch['destination']]);

            // Update order progress
            $order_id = $dispatch['order_id'] ?? null;
            if (!$order_id) {
                $stmt_find_ord = $pdo->prepare("SELECT id FROM orders WHERE destination = ? AND status IN ('Pending', 'In Progress') ORDER BY id ASC LIMIT 1");
                $stmt_find_ord->execute([$dispatch['destination']]);
                $found_ord = $stmt_find_ord->fetch();
                if ($found_ord) {
                    $order_id = $found_ord['id'];
                    $pdo->prepare("UPDATE dispatches SET order_id = ? WHERE id = ?")->execute([$order_id, $dispatch['id']]);
                }
            }

            if ($order_id && !empty($dispatch['truck_id'])) {
                $scannedCm = floatval(($dispatch['cubic_meters'] ?? 0) > 0 ? $dispatch['cubic_meters'] : 10.00);
                $admin_id = $_SESSION['user_id'];

                $dupScan = $pdo->prepare("SELECT id FROM order_scans WHERE order_id = ? AND truck_id = ?");
                $dupScan->execute([$order_id, $dispatch['truck_id']]);
                if (!$dupScan->fetch()) {
                    $pdo->prepare("INSERT INTO order_scans (order_id, truck_id, checker_id) VALUES (?, ?, ?)")
                        ->execute([$order_id, $dispatch['truck_id'], $admin_id]);
                }

                $pdo->prepare("UPDATE orders SET trucks_fulfilled = trucks_fulfilled + 1, cubic_meters_fulfilled = cubic_meters_fulfilled + ? WHERE id = ?")
                    ->execute([$scannedCm, $order_id]);

                $chkOrd = $pdo->prepare("SELECT trucks_required, trucks_fulfilled, cubic_meters_required, cubic_meters_fulfilled FROM orders WHERE id = ?");
                $chkOrd->execute([$order_id]);
                $updatedOrd = $chkOrd->fetch();

                $reqCm = floatval($updatedOrd['cubic_meters_required'] > 0 ? $updatedOrd['cubic_meters_required'] : $updatedOrd['trucks_required']);
                $doneCm = floatval($updatedOrd['cubic_meters_fulfilled'] > 0 ? $updatedOrd['cubic_meters_fulfilled'] : $updatedOrd['trucks_fulfilled']);

                if ($doneCm >= $reqCm) {
                    $pdo->prepare("UPDATE orders SET status = 'Fulfilled' WHERE id = ?")->execute([$order_id]);
                } else {
                    $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ? AND status = 'Pending'")->execute([$order_id]);
                }
            }

            $_SESSION['success'] = "Dispatch completed. Truck returned to garage and order progress updated.";
            log_activity($pdo, 'Completed Dispatch', 'Completed dispatch ID ' . $dispatch_id);
        } else {
            $_SESSION['error'] = "Failed to complete dispatch: Dispatch not found.";
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    if ($_POST['action'] == 'delete_dispatch' || $_POST['action'] == 'cancel_dispatch') {
        $dispatch_id = $_POST['dispatch_id'];
        $stmt = $pdo->prepare("SELECT truck_id, driver_id, destination FROM dispatches WHERE id = ?");
        $stmt->execute([$dispatch_id]);
        $dispatch = $stmt->fetch();

        if ($dispatch) {
            // Soft cancel - preserve records in database without deleting
            $pdo->prepare("UPDATE driver_trips SET status = 'Cancelled' WHERE driver_id = ? AND destination = ? AND status != 'Completed' ORDER BY id DESC LIMIT 1")->execute([$dispatch['driver_id'], $dispatch['destination']]);
            $pdo->prepare("UPDATE trucks SET status = 'Idle', speed = 0 WHERE id = ?")->execute([$dispatch['truck_id']]);
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ? AND status != 'Resigned'")->execute([$dispatch['driver_id']]);
            $pdo->prepare("UPDATE dispatches SET status = 'Cancelled' WHERE id = ?")->execute([$dispatch_id]);
            $_SESSION['success'] = "Dispatch cancelled successfully. Record has been preserved.";
            log_activity($pdo, 'Cancelled Dispatch', 'Cancelled dispatch ID ' . $dispatch_id);
        } else {
            $_SESSION['error'] = "Failed to cancel dispatch: Dispatch not found.";
        }
        header("Location: dashboard.php?tab=dispatches");
        exit;
    }



    if ($_POST['action'] == 'resign_driver' || $_POST['action'] == 'delete_driver') {
        $driver_id = $_POST['driver_id'];
        if (!$driver_id) {
            http_response_code(400);
            echo "Driver ID is missing";
            exit;
        }
        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("SELECT truck_id FROM drivers WHERE id = ?");
            $stmt1->execute([$driver_id]);
            $assignedTruckId = $stmt1->fetchColumn();

            $stmt2 = $pdo->prepare("SELECT truck_id FROM dispatches WHERE driver_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading') LIMIT 1");
            $stmt2->execute([$driver_id]);
            $activeTruckId = $stmt2->fetchColumn();

            $truckToReset = $assignedTruckId ?: $activeTruckId;
            if ($truckToReset) {
                $pdo->prepare("UPDATE trucks SET status = 'Idle', speed = 0, current_location = ?, latitude = ?, longitude = ? WHERE id = ?")->execute([$GARAGE_NAME, $GARAGE_LAT, $GARAGE_LNG, $truckToReset]);
            }

            // Cancel any active/in-progress dispatches and trips, preserving their records
            $pdo->prepare("UPDATE dispatches SET status = 'Cancelled' WHERE driver_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading')")->execute([$driver_id]);
            $pdo->prepare("UPDATE driver_trips SET status = 'Cancelled' WHERE driver_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading')")->execute([$driver_id]);

            // Mark driver as Resigned and clear truck assignment (NO rows deleted from drivers, users, payroll, or trips)
            $pdo->prepare("UPDATE drivers SET status = 'Resigned', truck_id = NULL WHERE id = ?")->execute([$driver_id]);

            $pdo->commit();
            $_SESSION['success'] = "Driver marked as Resigned successfully. All historical trips, payroll, and logs are preserved.";
            log_activity($pdo, 'Resigned Driver', 'Marked driver ID ' . $driver_id . ' as Resigned');
            header("Location: dashboard.php?tab=drivers");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = "Failed to update driver status. Please try again.";
            header("Location: dashboard.php?tab=drivers");
            exit;
        }
    }

    if ($_POST['action'] == 'reset_driver_password') {
        $driver_id = $_POST['driver_id'];
        $new_password = $_POST['new_password'];

        if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $_SESSION['error'] = "Password must be at least 8 characters long and contain uppercase, lowercase, and numeric characters.";
            header("Location: dashboard.php?tab=drivers");
            exit;
        }


        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $driver_id]);

            $_SESSION['success'] = "Driver's password reset successfully.";
            log_activity($pdo, 'Reset Driver Password', 'Reset password for driver ID ' . $driver_id);
            header("Location: dashboard.php?tab=drivers");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to reset password. Please try again.";
            header("Location: dashboard.php?tab=drivers");
            exit;
        }
    }

    if ($_POST['action'] == 'add_truck') {
        $truck_code = trim($_POST['truck_code']);
        $rfid_tag   = trim($_POST['rfid_tag']);

        $dupPlate = $pdo->prepare("SELECT id FROM trucks WHERE truck_code = ? LIMIT 1");
        $dupPlate->execute([$truck_code]);
        if ($dupPlate->fetch()) {
            $_SESSION['fleet_err'] = "A truck with plate number <strong>{$truck_code}</strong> already exists.";
            header("Location: dashboard.php?tab=fleet&open=addTruck");
            exit;
        }

        $dupRfid = $pdo->prepare("SELECT id FROM trucks WHERE rfid_tag = ? LIMIT 1");
        $dupRfid->execute([$rfid_tag]);
        if ($dupRfid->fetch()) {
            $_SESSION['fleet_err'] = "RFID tag <strong>{$rfid_tag}</strong> is already registered to another truck.";
            header("Location: dashboard.php?tab=fleet&open=addTruck");
            exit;
        }

        $insert = $pdo->prepare("INSERT INTO trucks (truck_code, rfid_tag, status, current_location, latitude, longitude, speed) VALUES (?, ?, 'Idle', ?, ?, ?, 0)");
        $insert->execute([$truck_code, $rfid_tag, $GARAGE_NAME, $GARAGE_LAT, $GARAGE_LNG]);
        $_SESSION['success'] = "Truck <strong>" . htmlspecialchars($truck_code) . "</strong> registered successfully.";
        log_activity($pdo, 'Registered Truck', 'Registered truck: ' . $truck_code);
        header("Location: dashboard.php?tab=fleet");
        exit;
    }

    if ($_POST['action'] == 'decommission_truck' || $_POST['action'] == 'delete_truck') {
        $truck_id = $_POST['truck_id'];
        $stmt = $pdo->prepare("SELECT driver_id FROM dispatches WHERE truck_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading') LIMIT 1");
        $stmt->execute([$truck_id]);
        $activeDispatch = $stmt->fetch();

        if ($activeDispatch && $activeDispatch['driver_id']) {
            $pdo->prepare("UPDATE drivers SET status = 'Off Duty' WHERE id = ? AND status != 'Resigned'")->execute([$activeDispatch['driver_id']]);
        }
        // Release any driver assigned to this truck
        $pdo->prepare("UPDATE drivers SET truck_id = NULL WHERE truck_id = ?")->execute([$truck_id]);
        // Cancel active dispatches on this truck
        $pdo->prepare("UPDATE dispatches SET status = 'Cancelled' WHERE truck_id = ? AND status IN ('Pending', 'Loading', 'In Transit', 'Unloading')")->execute([$truck_id]);
        // Mark truck as Decommissioned (NO rows deleted from trucks)
        $pdo->prepare("UPDATE trucks SET status = 'Decommissioned', rfid_active = 0, speed = 0 WHERE id = ?")->execute([$truck_id]);

        $_SESSION['success'] = "Truck marked as Decommissioned. All historical dispatches and logs are safely preserved.";
        log_activity($pdo, 'Decommissioned Truck', 'Decommissioned truck ID ' . $truck_id);
        header("Location: dashboard.php?tab=fleet");
        exit;
    }


    if ($_POST['action'] == 'add_order') {
        $orderNum = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $cubic_meters_required = !empty($_POST['cubic_meters_required']) ? floatval($_POST['cubic_meters_required']) : (isset($_POST['trucks_required']) ? floatval($_POST['trucks_required']) : 1.00);
        $trucks_req = max(1, (int)ceil($cubic_meters_required));
        $contact_number = !empty($_POST['contact_number']) ? trim($_POST['contact_number']) : null;
        $landmark = !empty($_POST['landmark']) ? trim($_POST['landmark']) : null;
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, client_name, contact_number, gravel_type, destination, landmark, trucks_required, cubic_meters_required, checker_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $checker_id = !empty($_POST['checker_id']) ? intval($_POST['checker_id']) : null;
        $stmt->execute([$orderNum, $_POST['client_name'], $contact_number, $_POST['gravel_type'], $_POST['destination'], $landmark, $trucks_req, $cubic_meters_required, $checker_id, $_POST['notes'] ?? '']);
        $_SESSION['success'] = "Order <strong>{$orderNum}</strong> created successfully.";
        log_activity($pdo, 'Created Order', 'Created order ' . $orderNum . ' for ' . $_POST['client_name']);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'add_checker') {
        $uname = trim($_POST['checker_username']);
        $pwd   = $_POST['checker_password'];
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);

        if (strlen($pwd) < 8) die("Password must be at least 8 characters.");

        try {
            $pdo->beginTransaction();

            $hashed = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Checker')");
            $stmt->execute([$uname, $hashed]);
            $new_user_id = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT INTO checkers (id, first_name, last_name, phone) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$new_user_id, $fname, $lname, $phone]);

            $pdo->commit();
            $_SESSION['success'] = "Checker <strong>" . htmlspecialchars($fname . ' ' . $lname) . "</strong> added successfully.";
            log_activity($pdo, 'Registered Checker', 'Registered checker: ' . $fname . ' ' . $lname);
            header("Location: dashboard.php?tab=orders");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = "Failed to add checker. The username may already exist.";
            header("Location: dashboard.php?tab=orders");
        }
        exit;
    }

    if ($_POST['action'] == 'assign_checker') {
        $pdo->prepare("UPDATE orders SET checker_id = ? WHERE id = ?")->execute([intval($_POST['checker_id']), intval($_POST['order_id'])]);
        $_SESSION['success'] = "Checker assigned successfully.";
        log_activity($pdo, 'Assigned Checker', 'Assigned checker ID ' . $_POST['checker_id'] . ' to order ID ' . $_POST['order_id']);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'cancel_order') {
        $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?")->execute([intval($_POST['order_id'])]);
        $_SESSION['success'] = "Order status updated to Cancelled.";
        log_activity($pdo, 'Cancelled Order', 'Cancelled order ID ' . $_POST['order_id']);
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'resign_checker' || $_POST['action'] == 'delete_checker') {
        $checker_id = intval($_POST['checker_id']);
        if ($checker_id > 0) {
            try {
                $pdo->beginTransaction();
                // Unassign from pending orders so another checker can be assigned, but keep completed orders intact
                $pdo->prepare("UPDATE orders SET checker_id = NULL WHERE checker_id = ? AND status = 'Pending'")->execute([$checker_id]);
                // Mark checker as Resigned (NO rows deleted from checkers or users)
                $pdo->prepare("UPDATE checkers SET status = 'Resigned' WHERE id = ?")->execute([$checker_id]);
                $pdo->commit();
                $_SESSION['success'] = "Checker marked as Resigned successfully. All past order scans and records are preserved.";
                log_activity($pdo, 'Resigned Checker', 'Marked checker ID ' . $checker_id . ' as Resigned');
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $_SESSION['error'] = "Failed to update checker status. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Failed to update checker: Invalid ID.";
        }
        header("Location: dashboard.php?tab=orders");
        exit;
    }

    if ($_POST['action'] == 'switch_truck') {
        $driver_id = $_POST['driver_id'];
        $new_truck_id = $_POST['new_truck_id'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT truck_id FROM drivers WHERE id = ?");
            $stmt->execute([$driver_id]);
            $driver = $stmt->fetch();
            $old_truck_id = $driver['truck_id'] ?? null;

            if ($old_truck_id) {
                $pdo->prepare("UPDATE trucks SET status = 'Maintenance' WHERE id = ?")->execute([$old_truck_id]);
            }

            $pdo->prepare("UPDATE drivers SET truck_id = NULL WHERE truck_id = ?")->execute([$new_truck_id]);
            $pdo->prepare("UPDATE drivers SET truck_id = ? WHERE id = ?")->execute([$new_truck_id, $driver_id]);

            $stmt = $pdo->prepare("SELECT id, status, destination, order_id FROM dispatches WHERE driver_id = ? AND status NOT IN ('Delivered', 'Cancelled', 'Completed')");
            $stmt->execute([$driver_id]);
            $active_dispatches = $stmt->fetchAll();

            foreach ($active_dispatches as $dispatch) {
                $pdo->prepare("UPDATE dispatches SET truck_id = ? WHERE id = ?")->execute([$new_truck_id, $dispatch['id']]);

                if ($dispatch['status'] === 'Cancellation Requested') {
                    // Mark the cancelled trip attempt as Cancelled for driver salary
                    $pdo->prepare("UPDATE driver_trips SET status = 'Cancelled' WHERE driver_id = ? AND status = 'Cancellation Requested'")->execute([$driver_id]);

                    // Reset dispatch to Pending with new truck & create new clean trip entry
                    $pdo->prepare("UPDATE dispatches SET status = 'Pending' WHERE id = ?")->execute([$dispatch['id']]);
                    $pdo->prepare("UPDATE trucks SET status = 'Pending' WHERE id = ?")->execute([$new_truck_id]);

                    $pdo->prepare("INSERT INTO driver_trips (driver_id, destination, trip_date, status, order_id) VALUES (?, ?, CURDATE(), 'Pending', ?)")
                        ->execute([$driver_id, $dispatch['destination'], $dispatch['order_id'] ?? null]);
                } else {
                    $pdo->prepare("UPDATE trucks SET status = ? WHERE id = ?")->execute([$dispatch['status'], $new_truck_id]);
                }
            }

            $pdo->commit();
            $_SESSION['success'] = "Truck switched successfully.";
            log_activity($pdo, 'Switched Truck', 'Switched truck for driver ID ' . $driver_id . ' to truck ID ' . $new_truck_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to switch truck. Please try again.";
        }

        $allowedTabs = ['dashboard', 'dispatches', 'fleet', 'drivers', 'orders', 'tracking', 'reports', 'settings'];
        $redirect_tab = (isset($_POST['redirect_tab']) && in_array($_POST['redirect_tab'], $allowedTabs)) ? $_POST['redirect_tab'] : 'drivers';
        header("Location: dashboard.php?tab=" . $redirect_tab);
        exit;
    }

    if ($_POST['action'] == 'approve_cancel') {
        $dispatch_id = $_POST['dispatch_id'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT truck_id, driver_id, destination FROM dispatches WHERE id = ?");
            $stmt->execute([$dispatch_id]);
            $dispatch = $stmt->fetch();

            if ($dispatch) {
                $pdo->prepare("UPDATE dispatches SET status = 'Cancelled' WHERE id = ?")->execute([$dispatch_id]);

                $pdo->prepare("UPDATE trucks SET status = 'Maintenance' WHERE id = ?")->execute([$dispatch['truck_id']]);

                $pdo->prepare("UPDATE drivers SET status = 'Active' WHERE id = ?")->execute([$dispatch['driver_id']]);

                $pdo->prepare("UPDATE driver_trips SET status = 'Cancelled' WHERE driver_id = ? AND status = 'Cancellation Requested'")->execute([$dispatch['driver_id']]);

                $_SESSION['success'] = "Trip cancellation request approved successfully.";
                log_activity($pdo, 'Approved Cancellation', 'Approved trip cancellation for dispatch ID ' . $dispatch_id);
            } else {
                $_SESSION['error'] = "Cancellation request not found.";
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to approve cancellation.";
        }

        header("Location: dashboard.php?tab=dispatches");
        exit;
    }

    // ── Cash Advance: Approve ──
    if ($_POST['action'] === 'approve_cash_advance') {
        $ca_id = intval($_POST['ca_id']);
        try {
            $pdo->beginTransaction();
            $caStmt = $pdo->prepare("SELECT driver_id, amount FROM cash_advances WHERE id = ? AND status = 'Pending'");
            $caStmt->execute([$ca_id]);
            $ca = $caStmt->fetch();
            if ($ca) {
                $pdo->prepare("UPDATE cash_advances SET status = 'Approved', resolved_at = NOW() WHERE id = ?")->execute([$ca_id]);
                // Deduct from payroll: insert or update driver_payroll, reduce total_amount by advance
                $pdo->prepare("INSERT INTO driver_payroll (driver_id, total_amount, amount_claimed) VALUES (?, 0, ?) ON DUPLICATE KEY UPDATE amount_claimed = amount_claimed + ?")
                    ->execute([$ca['driver_id'], $ca['amount'], $ca['amount']]);
            }
            $pdo->commit();
            $_SESSION['auto_print_cash_advance_id'] = $ca_id;
            $_SESSION['success'] = 'Cash advance approved and deducted from payroll. Opening print ticket...';
            log_activity($pdo, 'Approved Cash Advance', 'Approved cash advance ID ' . $ca_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Failed to approve cash advance.';
        }
        header('Location: dashboard.php?tab=cash_advances');
        exit;
    }

    // ── Cash Advance: Reject ──
    if ($_POST['action'] === 'reject_cash_advance') {
        $ca_id = intval($_POST['ca_id']);
        $pdo->prepare("UPDATE cash_advances SET status = 'Rejected', resolved_at = NOW() WHERE id = ? AND status = 'Pending'")->execute([$ca_id]);
        $_SESSION['success'] = 'Cash advance request rejected.';
        log_activity($pdo, 'Rejected Cash Advance', 'Rejected cash advance ID ' . $ca_id);
        header('Location: dashboard.php?tab=cash_advances');
        exit;
    }

    // ── Driver Payroll: Settle Payroll (Full or Partial) ──
    if ($_POST['action'] === 'settle_driver_payroll') {
        $driver_id = intval($_POST['driver_id']);
        $notes = trim($_POST['notes'] ?? '');
        try {
            $pdo->beginTransaction();

            // Fetch driver details
            $dStmt = $pdo->prepare("SELECT id, first_name, last_name, cdl_number FROM drivers WHERE id = ?");
            $dStmt->execute([$driver_id]);
            $driverData = $dStmt->fetch(PDO::FETCH_ASSOC);

            if (!$driverData) {
                throw new Exception("Driver not found.");
            }

            // Fetch existing remaining balance carried over from prior settlements
            $curBalStmt = $pdo->prepare("SELECT remaining_balance FROM driver_payroll WHERE driver_id = ?");
            $curBalStmt->execute([$driver_id]);
            $previousBalance = floatval($curBalStmt->fetchColumn() ?: 0);

            // Calculate unclaimed delivered gross earnings
            $unclaimedStmt = $pdo->prepare("SELECT id, pay_amount FROM dispatches WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)");
            $unclaimedStmt->execute([$driver_id]);
            $unclaimedDispatches = $unclaimedStmt->fetchAll(PDO::FETCH_ASSOC);

            $grossAmount = 0.00;
            $dispatchIds = [];
            foreach ($unclaimedDispatches as $disp) {
                $grossAmount += floatval($disp['pay_amount']);
                $dispatchIds[] = $disp['id'];
            }

            // Calculate unsettled approved cash advances
            $caUnsettledStmt = $pdo->prepare("SELECT id, amount FROM cash_advances WHERE driver_id = ? AND status = 'Approved' AND (is_settled = 0 OR is_settled IS NULL)");
            $caUnsettledStmt->execute([$driver_id]);
            $unsettledCAs = $caUnsettledStmt->fetchAll(PDO::FETCH_ASSOC);

            $cashAdvanceDeduction = 0.00;
            $caIds = [];
            foreach ($unsettledCAs as $caRow) {
                $cashAdvanceDeduction += floatval($caRow['amount']);
                $caIds[] = $caRow['id'];
            }

            // Total net payable available to driver
            $totalPayable = max(0, $grossAmount + $previousBalance - $cashAdvanceDeduction);
            $tripsCount = count($unclaimedDispatches);

            if ($grossAmount <= 0 && $previousBalance <= 0 && $cashAdvanceDeduction <= 0) {
                throw new Exception("No unclaimed earnings, prior balance, or advances to settle for this driver.");
            }

            // Check if partial claim is specified
            if (isset($_POST['claimed_amount']) && is_numeric($_POST['claimed_amount'])) {
                $rawClaimed = floatval($_POST['claimed_amount']);
                $disbursedAmount = min($totalPayable, max(0, $rawClaimed));
            } else {
                $disbursedAmount = $totalPayable;
            }

            // Remaining balance carried forward
            $newRemainingBalance = max(0, $totalPayable - $disbursedAmount);

            // Generate unique settlement ticket
            $ticketNumber = 'PAY-' . date('Y') . '-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));

            // Insert settlement record with partial claim tracking
            $settleInsert = $pdo->prepare("
                INSERT INTO driver_payroll_settlements 
                (settlement_ticket, driver_id, gross_amount, previous_balance, cash_advance_deduction, net_pay, amount_claimed, remaining_balance, trips_count, settled_by, notes, settled_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $settleInsert->execute([
                $ticketNumber,
                $driver_id,
                $grossAmount,
                $previousBalance,
                $cashAdvanceDeduction,
                $totalPayable,
                $disbursedAmount,
                $newRemainingBalance,
                $tripsCount,
                $_SESSION['user_id'] ?? null,
                $notes
            ]);
            $settlementId = $pdo->lastInsertId();

            // Mark dispatches as paid
            if (!empty($dispatchIds)) {
                $inQuery = implode(',', array_fill(0, count($dispatchIds), '?'));
                $paidStmt = $pdo->prepare("UPDATE dispatches SET is_payroll_paid = 1, payroll_settled_at = NOW(), payroll_id = ? WHERE id IN ($inQuery)");
                $paidStmt->execute(array_merge([$settlementId], $dispatchIds));
            }

            // Mark driver_trips for this driver as paid
            $pdo->prepare("UPDATE driver_trips SET is_payroll_paid = 1, payroll_settled_at = NOW(), payroll_id = ? WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)")
                ->execute([$settlementId, $driver_id]);

            // Mark cash advances as settled
            if (!empty($caIds)) {
                $inCaQuery = implode(',', array_fill(0, count($caIds), '?'));
                $caSettledStmt = $pdo->prepare("UPDATE cash_advances SET is_settled = 1, settled_at = NOW(), payroll_id = ? WHERE id IN ($inCaQuery)");
                $caSettledStmt->execute(array_merge([$settlementId], $caIds));
            }

            // Update cumulative totals and update remaining_balance in driver_payroll
            $pdo->prepare("
                INSERT INTO driver_payroll (driver_id, total_amount, amount_claimed, remaining_balance) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    total_amount = total_amount + VALUES(total_amount),
                    amount_claimed = amount_claimed + VALUES(amount_claimed),
                    remaining_balance = VALUES(remaining_balance)
            ")->execute([$driver_id, $grossAmount, $disbursedAmount, $newRemainingBalance]);

            $pdo->commit();

            $_SESSION['auto_print_payroll_settlement_id'] = $settlementId;
            if ($newRemainingBalance > 0) {
                $_SESSION['success'] = "Payroll partially settled for {$driverData['first_name']} {$driverData['last_name']}! Disbursed: ₱" . number_format($disbursedAmount, 2) . ". Remaining Balance: ₱" . number_format($newRemainingBalance, 2) . " carried forward.";
            } else {
                $_SESSION['success'] = "Payroll settled for {$driverData['first_name']} {$driverData['last_name']}! Gross earnings reset to ₱0.00.";
            }
            log_activity($pdo, 'Settled Driver Payroll', "Settled payroll ticket {$ticketNumber} for driver {$driverData['first_name']} {$driverData['last_name']} (Disbursed: ₱" . number_format($disbursedAmount, 2) . ", Rem. Balance: ₱" . number_format($newRemainingBalance, 2) . ")");
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Failed to settle payroll: ' . $e->getMessage();
        }
        header('Location: dashboard.php?tab=drivers');
        exit;
    }

    // ── Driver Payroll: Add / Adjust Remaining Balance ──
    if ($_POST['action'] === 'adjust_driver_balance') {
        $driver_id = intval($_POST['driver_id']);
        $adjustment_type = $_POST['adjustment_type'] ?? 'add'; // 'add' or 'set'
        $amount = floatval($_POST['amount'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        try {
            $pdo->beginTransaction();
            $dStmt = $pdo->prepare("SELECT first_name, last_name FROM drivers WHERE id = ?");
            $dStmt->execute([$driver_id]);
            $driverData = $dStmt->fetch(PDO::FETCH_ASSOC);
            if (!$driverData) throw new Exception("Driver not found.");

            $curStmt = $pdo->prepare("SELECT remaining_balance FROM driver_payroll WHERE driver_id = ?");
            $curStmt->execute([$driver_id]);
            $oldBal = floatval($curStmt->fetchColumn() ?: 0);

            if ($adjustment_type === 'set') {
                $newBal = max(0, $amount);
            } else {
                $newBal = max(0, $oldBal + $amount);
            }

            $pdo->prepare("
                INSERT INTO driver_payroll (driver_id, remaining_balance)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE remaining_balance = VALUES(remaining_balance)
            ")->execute([$driver_id, $newBal]);

            $pdo->commit();
            $_SESSION['success'] = "Updated remaining balance for {$driverData['first_name']} {$driverData['last_name']} to ₱" . number_format($newBal, 2);
            log_activity($pdo, 'Adjusted Driver Balance', "Adjusted remaining balance for {$driverData['first_name']} {$driverData['last_name']} from ₱" . number_format($oldBal, 2) . " to ₱" . number_format($newBal, 2) . ($notes ? " ($notes)" : ""));
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Failed to adjust balance: ' . $e->getMessage();
        }
        header('Location: dashboard.php?tab=drivers');
        exit;
    }

    // ── Password Reset: Approve ──
    if ($_POST['action'] === 'approve_pwd_reset') {
        $req_id = intval($_POST['req_id']);
        $pdo->prepare("UPDATE password_reset_requests SET status = 'Approved', resolved_at = NOW() WHERE id = ? AND status = 'Pending'")->execute([$req_id]);
        $_SESSION['success'] = 'Password reset request approved. The user can now set their new password.';
        log_activity($pdo, 'Approved Password Reset', 'Approved password reset request ID ' . $req_id);
        header('Location: dashboard.php?tab=pwd_requests');
        exit;
    }

    // ── Password Reset: Reject ──
    if ($_POST['action'] === 'reject_pwd_reset') {
        $req_id = intval($_POST['req_id']);
        $pdo->prepare("UPDATE password_reset_requests SET status = 'Rejected', resolved_at = NOW() WHERE id = ? AND status = 'Pending'")->execute([$req_id]);
        $_SESSION['success'] = 'Password reset request rejected.';
        log_activity($pdo, 'Rejected Password Reset', 'Rejected password reset request ID ' . $req_id);
        header('Location: dashboard.php?tab=pwd_requests');
        exit;
    }
}

try {
    $pwdResetRequests = $pdo->query("SELECT r.id, r.user_id, r.username, r.role, r.status, r.requested_at FROM password_reset_requests r WHERE r.status = 'Pending' ORDER BY r.requested_at ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $pwdResetRequests = [];
}
$pendingPwdResetCount = count($pwdResetRequests);

$totalFleet = $pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
$activeNow = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status IN ('In Transit', 'Loading', 'Unloading')")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered' AND dispatch_date = CURDATE()")->fetchColumn();
$idleTrucks = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'Idle'")->fetchColumn();
$rfidActive = $pdo->query("SELECT COUNT(*) FROM trucks WHERE rfid_active = 1")->fetchColumn();

$fleetStatusData = $pdo->query("SELECT status, COUNT(*) as count FROM trucks GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$recentDispatches = $pdo->query("SELECT d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination FROM dispatches d LEFT JOIN trucks t ON d.truck_id = t.id LEFT JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$trackingTrucks = $pdo->query("SELECT t.truck_code, t.status, t.current_location, t.speed, t.latitude, t.longitude, CONCAT(d.first_name, ' ', d.last_name) AS driver_name FROM trucks t LEFT JOIN drivers d ON t.id = d.truck_id WHERE t.status != 'Idle' AND t.latitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

$allDispatches = $pdo->query("SELECT d.id, d.ticket_number, d.driver_id, d.cubic_meters, d.order_id, o.order_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination, d.created_at, d.transit_start_time, d.transit_end_time, COALESCE(NULLIF(d.client_name, ''), o.client_name) AS client_name, COALESCE(NULLIF(d.contact_number, ''), o.contact_number) AS contact_number, COALESCE(NULLIF(d.landmark, ''), o.landmark) AS landmark FROM dispatches d LEFT JOIN trucks t ON d.truck_id = t.id LEFT JOIN drivers dr ON d.driver_id = dr.id LEFT JOIN orders o ON d.order_id = o.id ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$activeTickets = array_filter($allDispatches, function ($d) {
    return in_array($d['status'], ['Pending', 'In Transit', 'Loading', 'Unloading']);
});
$cancellationRequests = array_filter($allDispatches, function ($d) {
    return $d['status'] === 'Cancellation Requested';
});
$completedTickets = array_filter($allDispatches, function ($d) {
    return in_array($d['status'], ['Delivered', 'Cancelled']);
});

$fleetData = $pdo->query("SELECT t.id, t.truck_code, t.rfid_tag, t.status, t.speed, t.latitude, t.longitude, t.current_location, CONCAT(d.first_name, ' ', d.last_name) AS driver_name, disp.ticket_number, disp.destination FROM trucks t LEFT JOIN dispatches disp ON t.id = disp.truck_id AND disp.status IN ('Pending', 'In Transit', 'Loading', 'Unloading') LEFT JOIN drivers d ON t.id = d.truck_id ORDER BY t.truck_code ASC")->fetchAll(PDO::FETCH_ASSOC);

$allDrivers = $pdo->query("
    SELECT 
        d.*, CONCAT(d.first_name, ' ', d.last_name) AS name, t.truck_code
    FROM drivers d 
    LEFT JOIN trucks t ON t.id = d.truck_id
    ORDER BY d.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Helper function to calculate weekly driver performance metrics
if (!function_exists('computeDriverPerformanceStats')) {
    function computeDriverPerformanceStats($driverTrips, $onTimePct = 100, $rating = 5.0)
    {
        $currentWeekKey = date('o-\WW');
        $totalLifetimeKm = 0;
        $totalLifetimeDispatches = 0;
        $totalTransitMinutes = 0;
        $transitCount = 0;

        $weeklyMap = [];

        foreach ($driverTrips as $t) {
            if (($t['status'] ?? '') !== 'Delivered') continue;

            $totalLifetimeDispatches++;
            $dist = floatval($t['distance_km'] ?? 0);
            $pay = floatval($t['pay_amount'] ?? 0);
            $totalLifetimeKm += $dist;

            $dateStr = !empty($t['trip_date']) ? $t['trip_date'] : (!empty($t['transit_end_time']) ? date('Y-m-d', strtotime($t['transit_end_time'])) : (!empty($t['transit_start_time']) ? date('Y-m-d', strtotime($t['transit_start_time'])) : (!empty($t['created_at']) ? date('Y-m-d', strtotime($t['created_at'])) : date('Y-m-d'))));
            $time = strtotime($dateStr);
            if (!$time) $time = time();

            $weekKey = date('o-\WW', $time);
            $year = (int)date('o', $time);
            $weekNum = (int)date('W', $time);

            $dto = new DateTime();
            $dto->setISODate($year, $weekNum);
            $weekStart = $dto->format('M d');
            $dto->modify('+6 days');
            $weekEnd = $dto->format('M d, Y');
            $weekLabel = "{$weekStart} – {$weekEnd} (Week {$weekNum})";

            if (!isset($weeklyMap[$weekKey])) {
                $weeklyMap[$weekKey] = [
                    'week_key'   => $weekKey,
                    'week_label' => $weekLabel,
                    'year'       => $year,
                    'week_num'   => $weekNum,
                    'dispatches' => 0,
                    'total_km'   => 0.0,
                    'total_pay'  => 0.0,
                ];
            }

            $weeklyMap[$weekKey]['dispatches']++;
            $weeklyMap[$weekKey]['total_km'] += $dist;
            $weeklyMap[$weekKey]['total_pay'] += $pay;

            if (!empty($t['transit_start_time']) && !empty($t['transit_end_time'])) {
                $t1 = strtotime($t['transit_start_time']);
                $t2 = strtotime($t['transit_end_time']);
                if ($t2 > $t1 && ($t2 - $t1) < 86400 * 3) {
                    $totalTransitMinutes += round(($t2 - $t1) / 60);
                    $transitCount++;
                }
            }
        }

        krsort($weeklyMap);
        $weeklyHistory = array_values($weeklyMap);

        $thisWeekStats = $weeklyMap[$currentWeekKey] ?? [
            'week_key'   => $currentWeekKey,
            'week_label' => 'This Week',
            'dispatches' => 0,
            'total_km'   => 0.0,
            'total_pay'  => 0.0,
        ];

        $activeWeeksCount = count($weeklyHistory);
        $avgKmPerWeek = $activeWeeksCount > 0 ? round($totalLifetimeKm / $activeWeeksCount, 1) : 0.0;
        $avgDispatchesPerWeek = $activeWeeksCount > 0 ? round($totalLifetimeDispatches / $activeWeeksCount, 1) : 0.0;
        $avgTransitMins = $transitCount > 0 ? round($totalTransitMinutes / $transitCount) : 0;

        return [
            'this_week_km'              => $thisWeekStats['total_km'],
            'this_week_dispatches'      => $thisWeekStats['dispatches'],
            'this_week_pay'             => $thisWeekStats['total_pay'],
            'avg_km_per_week'           => $avgKmPerWeek,
            'avg_dispatches_per_week'   => $avgDispatchesPerWeek,
            'total_lifetime_km'         => round($totalLifetimeKm, 1),
            'total_lifetime_dispatches' => $totalLifetimeDispatches,
            'on_time_pct'               => floatval($onTimePct),
            'rating'                    => floatval($rating),
            'avg_transit_minutes'       => $avgTransitMins,
            'active_weeks_count'        => $activeWeeksCount,
            'weekly_history'            => $weeklyHistory,
        ];
    }
}

foreach ($allDrivers as &$dr) {
    // Fetch unique trips for this driver without cartesian dispatches duplicate
    $stmt = $pdo->prepare("
        SELECT 
            dt.id,
            dt.destination, 
            dt.trip_date, 
            dt.status, 
            dt.transit_start_time, 
            dt.transit_end_time,
            dt.estimated_arrival_time,
            COALESCE(NULLIF(dt.distance_km, 0), dest.distance_km, 0.00) AS distance_km,
            COALESCE(NULLIF(dt.pay_amount, 0), IF(LOWER(dest.name) LIKE '%san leonardo%', 300.00, IF(dest.distance_km > 0, ROUND(300.00 + GREATEST(0, dest.distance_km - 12) * 10, 2), IF(dest.driver_rate > 0, dest.driver_rate, 300.00))), 0.00) AS pay_amount,
            COALESCE(dt.is_on_time, 1) AS is_on_time,
            dt.created_at
        FROM driver_trips dt
        LEFT JOIN destinations dest ON dest.name = dt.destination
        WHERE dt.driver_id = ? 
        ORDER BY dt.trip_date DESC, dt.id DESC
    ");
    $stmt->execute([$dr['id']]);
    $allTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compute duration for each trip
    foreach ($allTrips as &$rt) {
        $rt['duration'] = null;
        if (!empty($rt['transit_start_time']) && !empty($rt['transit_end_time'])) {
            try {
                $start = new DateTime($rt['transit_start_time']);
                $end = new DateTime($rt['transit_end_time']);
                $diff = $start->diff($end);
                $parts = [];
                if ($diff->days > 0) $parts[] = $diff->days . 'd';
                if ($diff->h > 0) $parts[] = $diff->h . 'h';
                $parts[] = $diff->i . 'm';
                $rt['duration'] = !empty($parts) ? implode(' ', $parts) : '0m';
            } catch (Exception $e) {}
        }
    }
    unset($rt);

    $deliveredTrips = array_values(array_filter($allTrips, fn($t) => ($t['status'] ?? '') === 'Delivered'));
    $deliveredCount = count($deliveredTrips);
    $onTimeCount    = count(array_filter($deliveredTrips, fn($t) => intval($t['is_on_time'] ?? 1) === 1));
    $onTimePct      = $deliveredCount > 0 ? round(($onTimeCount / $deliveredCount) * 100, 1) : 100.0;
    $rating         = $deliveredCount > 0 ? min(5.0, max(1.0, round(($onTimePct / 100) * 5.0, 1))) : 5.0;

    $dr['total_deliveries']  = $deliveredCount;
    $dr['on_time_pct']       = $onTimePct;
    $dr['rating']            = $rating;
    $dr['all_trips']         = $allTrips;
    $dr['recent_trips']      = $allTrips;
    $dr['total_lifetime_km'] = round(array_sum(array_map(fn($t) => floatval($t['distance_km'] ?? 0), $deliveredTrips)), 1);

    // Performance analytics (kilometers per week, completed dispatches per week, etc.)
    $dr['performance_stats'] = computeDriverPerformanceStats($dr['all_trips'], $onTimePct, $rating);

    // Payroll info
    $payStmt = $pdo->prepare("SELECT total_amount, amount_claimed, remaining_balance FROM driver_payroll WHERE driver_id = ?");
    $payStmt->execute([$dr['id']]);
    $payRow = $payStmt->fetch(PDO::FETCH_ASSOC);
    $dr['payroll_total']      = floatval($payRow['total_amount'] ?? 0);
    $dr['payroll_claimed']    = floatval($payRow['amount_claimed'] ?? 0);
    $dr['remaining_balance']  = floatval($payRow['remaining_balance'] ?? 0);

    // Active approved cash advances (strictly active / unsettled ones)
    $caSumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM cash_advances WHERE driver_id = ? AND status = 'Approved' AND (is_settled = 0 OR is_settled IS NULL)");
    $caSumStmt->execute([$dr['id']]);
    $dr['approved_cash_advances'] = floatval($caSumStmt->fetchColumn());

    // Calculate gross earnings from unclaimed delivered dispatches (resets to 0 upon settlement)
    $earnStmt = $pdo->prepare("SELECT COALESCE(SUM(pay_amount), 0) AS gross FROM dispatches WHERE driver_id = ? AND status = 'Delivered' AND (is_payroll_paid = 0 OR is_payroll_paid IS NULL)");
    $earnStmt->execute([$dr['id']]);
    $dr['gross_earnings'] = floatval($earnStmt->fetchColumn());
    $dr['net_earnings']   = max(0, $dr['gross_earnings'] + $dr['remaining_balance'] - $dr['approved_cash_advances']);

    // Cash advances for this driver
    $caStmt = $pdo->prepare("SELECT id, amount, reason, status, is_settled, requested_at, resolved_at FROM cash_advances WHERE driver_id = ? ORDER BY requested_at DESC LIMIT 5");
    $caStmt->execute([$dr['id']]);
    $dr['cash_advances'] = $caStmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($dr);

$driverStats = [
    'total_drivers' => count($allDrivers),
    'on_duty'       => count(array_filter($allDrivers, fn($d) => ($d['status'] ?? '') === 'Active')),
    'avg_rating'    => !empty($allDrivers) ? round(array_sum(array_column($allDrivers, 'rating')) / count($allDrivers), 1) : 5.0
];

// All pending cash advances (for badge & review)
$pendingCashAdvances = $pdo->query("
    SELECT ca.*, 
           COALESCE(NULLIF(TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))), ''), u.username, CONCAT('Driver #', ca.driver_id)) AS driver_name,
           d.cdl_number, d.phone, u.username
    FROM cash_advances ca 
    LEFT JOIN drivers d ON ca.driver_id = d.id 
    LEFT JOIN users u ON ca.driver_id = u.id
    WHERE ca.status = 'Pending' 
    ORDER BY ca.requested_at ASC
")->fetchAll(PDO::FETCH_ASSOC);
$pendingCashAdvanceCount = count($pendingCashAdvances);

// Recently approved cash advances (for re-printing tickets)
$recentApprovedCashAdvances = $pdo->query("
    SELECT ca.*, 
           COALESCE(NULLIF(TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))), ''), u.username, CONCAT('Driver #', ca.driver_id)) AS driver_name,
           d.cdl_number, d.phone, u.username
    FROM cash_advances ca 
    LEFT JOIN drivers d ON ca.driver_id = d.id 
    LEFT JOIN users u ON ca.driver_id = u.id
    WHERE ca.status = 'Approved' 
    ORDER BY ca.resolved_at DESC, ca.id DESC 
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// Full history of all cash advances
$allCashAdvances = $pdo->query("
    SELECT ca.*, 
           COALESCE(NULLIF(TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))), ''), u.username, CONCAT('Driver #', ca.driver_id)) AS driver_name,
           d.cdl_number, d.phone, u.username
    FROM cash_advances ca 
    LEFT JOIN drivers d ON ca.driver_id = d.id 
    LEFT JOIN users u ON ca.driver_id = u.id
    ORDER BY ca.requested_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$availableTrucks = $pdo->query("SELECT id, truck_code, rfid_tag, NULL as driver_name FROM trucks WHERE status = 'Idle'")->fetchAll(PDO::FETCH_ASSOC);


try {
    $requestedReportMonth = !empty($_GET['report_month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['report_month']) ? $_GET['report_month'] : date('Y-m');
    $currMonthStr = $requestedReportMonth;
    $isHistoricalReport = ($currMonthStr !== date('Y-m'));
    $currMonthTimestamp = strtotime($currMonthStr . '-01');
    $currMonthLabel = date('F Y', $currMonthTimestamp);
    
    $lastMonthTimestamp = strtotime('-1 month', $currMonthTimestamp);
    $lastMonthStr = date('Y-m', $lastMonthTimestamp);
    $lastMonthLabel = date('F Y', $lastMonthTimestamp);

    // 1. Current (selected) month deliveries & salaries
    $stmt_sal_curr = $pdo->prepare("
        SELECT d.destination 
        FROM dispatches d 
        WHERE d.status = 'Delivered' 
          AND DATE_FORMAT(COALESCE(d.transit_end_time, d.dispatch_date, d.created_at), '%Y-%m') = ?
    ");
    $stmt_sal_curr->execute([$currMonthStr]);
    $currRows = $stmt_sal_curr->fetchAll(PDO::FETCH_ASSOC);
    $currDeliveries = count($currRows);
    $driverSalariesCurr = 0;
    foreach ($currRows as $row) {
        $driverSalariesCurr += getDestinationPay($row['destination'], $DISTANCE_KM, $DRIVER_RATES);
    }
    if ($currDeliveries === 0) {
        $stmt_sal_curr_dt = $pdo->prepare("
            SELECT destination 
            FROM driver_trips 
            WHERE status = 'Delivered' 
              AND DATE_FORMAT(COALESCE(transit_end_time, trip_date, created_at), '%Y-%m') = ?
        ");
        $stmt_sal_curr_dt->execute([$currMonthStr]);
        $currDtRows = $stmt_sal_curr_dt->fetchAll(PDO::FETCH_ASSOC);
        $currDeliveries = count($currDtRows);
        foreach ($currDtRows as $row) {
            $driverSalariesCurr += getDestinationPay($row['destination'], $DISTANCE_KM, $DRIVER_RATES);
        }
    }

    // 2. Previous month deliveries & salaries
    $stmt_sal_last = $pdo->prepare("
        SELECT d.destination 
        FROM dispatches d 
        WHERE d.status = 'Delivered' 
          AND DATE_FORMAT(COALESCE(d.transit_end_time, d.dispatch_date, d.created_at), '%Y-%m') = ?
    ");
    $stmt_sal_last->execute([$lastMonthStr]);
    $lastRows = $stmt_sal_last->fetchAll(PDO::FETCH_ASSOC);
    $lastDeliveries = count($lastRows);
    $driverSalariesLast = 0;
    foreach ($lastRows as $row) {
        $driverSalariesLast += getDestinationPay($row['destination'], $DISTANCE_KM, $DRIVER_RATES);
    }
    if ($lastDeliveries === 0) {
        $stmt_sal_last_dt = $pdo->prepare("
            SELECT destination 
            FROM driver_trips 
            WHERE status = 'Delivered' 
              AND DATE_FORMAT(COALESCE(transit_end_time, trip_date, created_at), '%Y-%m') = ?
        ");
        $stmt_sal_last_dt->execute([$lastMonthStr]);
        $lastDtRows = $stmt_sal_last_dt->fetchAll(PDO::FETCH_ASSOC);
        $lastDeliveries = count($lastDtRows);
        foreach ($lastDtRows as $row) {
            $driverSalariesLast += getDestinationPay($row['destination'], $DISTANCE_KM, $DRIVER_RATES);
        }
    }

    $salariesChange = ($driverSalariesLast > 0) ? (($driverSalariesCurr - $driverSalariesLast) / $driverSalariesLast) * 100 : ($driverSalariesCurr > 0 ? 100 : 0);
    $deliveriesChange = ($lastDeliveries > 0) ? (($currDeliveries - $lastDeliveries) / $lastDeliveries) * 100 : ($currDeliveries > 0 ? 100 : 0);

    // 3. On-time rates
    $stmt_ontime = $pdo->prepare("
        SELECT COALESCE((SUM(is_on_time) / NULLIF(COUNT(id), 0)) * 100, 100) AS on_time_rate 
        FROM dispatches 
        WHERE DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') = ? 
          AND status = 'Delivered'
    ");
    $stmt_ontime->execute([$currMonthStr]);
    $onTimeRate = floatval($stmt_ontime->fetchColumn() ?? 100);

    $stmt_ontime_last = $pdo->prepare("
        SELECT COALESCE((SUM(is_on_time) / NULLIF(COUNT(id), 0)) * 100, 100) AS on_time_rate 
        FROM dispatches 
        WHERE DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') = ? 
          AND status = 'Delivered'
    ");
    $stmt_ontime_last->execute([$lastMonthStr]);
    $onTimeLastRate = floatval($stmt_ontime_last->fetchColumn() ?? 100);
    $onTimeChange = $onTimeRate - $onTimeLastRate;

    // 4. Volume delivered (cubic meters)
    $stmt_cm_curr = $pdo->prepare("
        SELECT COALESCE(SUM(cubic_meters), 0) 
        FROM dispatches 
        WHERE status = 'Delivered' 
          AND DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') = ?
    ");
    $stmt_cm_curr->execute([$currMonthStr]);
    $currMonthCm = floatval($stmt_cm_curr->fetchColumn());

    $stmt_cm_last = $pdo->prepare("
        SELECT COALESCE(SUM(cubic_meters), 0) 
        FROM dispatches 
        WHERE status = 'Delivered' 
          AND DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') = ?
    ");
    $stmt_cm_last->execute([$lastMonthStr]);
    $lastMonthCm = floatval($stmt_cm_last->fetchColumn());

    $cmChange = ($lastMonthCm > 0) ? (($currMonthCm - $lastMonthCm) / $lastMonthCm) * 100 : ($currMonthCm > 0 ? 100 : 0);

    $subtextPeriod = $isHistoricalReport ? "Month: $currMonthLabel" : "This month (Live Data)";

    $reportKpis = [
        ['title' => 'Driver Payroll', 'value' => '₱' . number_format($driverSalariesCurr / 1000, 1) . 'K', 'subtext' => number_format($salariesChange, 1) . '% vs ' . date('M', $lastMonthTimestamp), 'color_class' => 'bg-blue-500', 'icon_class' => 'fa-wallet'],
        ['title' => 'Volume Delivered', 'value' => number_format($currMonthCm, 2) . ' cu.m', 'subtext' => number_format($cmChange, 1) . '% vs ' . date('M', $lastMonthTimestamp), 'color_class' => 'bg-green-500', 'icon_class' => 'fa-cube'],
        ['title' => 'Deliveries', 'value' => number_format($currDeliveries), 'subtext' => $subtextPeriod, 'color_class' => 'bg-orange-500', 'icon_class' => 'fa-truck-fast'],
        ['title' => 'On-Time Rate', 'value' => number_format($onTimeRate, 1) . '%', 'subtext' => ($onTimeChange >= 0 ? '+' : '') . number_format($onTimeChange, 1) . '% vs ' . date('M', $lastMonthTimestamp), 'color_class' => 'bg-purple-500', 'icon_class' => 'fa-calendar']
    ];

    $performanceMetrics = [
        ['metric' => 'Total Deliveries', 'this_month' => number_format($currDeliveries), 'last_month' => number_format($lastDeliveries), 'change_str' => ($deliveriesChange >= 0 ? '+' : '') . number_format($deliveriesChange, 1) . '%', 'is_positive' => $deliveriesChange >= 0],
        ['metric' => 'Volume Delivered', 'this_month' => number_format($currMonthCm, 2) . ' cu.m', 'last_month' => number_format($lastMonthCm, 2) . ' cu.m', 'change_str' => ($cmChange >= 0 ? '+' : '') . number_format($cmChange, 1) . '%', 'is_positive' => $cmChange >= 0],
        ['metric' => 'Driver Payroll (Salaries)', 'this_month' => '₱' . number_format($driverSalariesCurr, 2), 'last_month' => '₱' . number_format($driverSalariesLast, 2), 'change_str' => ($salariesChange >= 0 ? '+' : '') . number_format($salariesChange, 1) . '%', 'is_positive' => $salariesChange >= 0],
        ['metric' => 'On-Time Deliveries', 'this_month' => number_format($onTimeRate, 1) . '%', 'last_month' => number_format($onTimeLastRate, 1) . '%', 'change_str' => ($onTimeChange >= 0 ? '+' : '') . number_format($onTimeChange, 1) . '%', 'is_positive' => $onTimeChange >= 0]
    ];
} catch (PDOException $e) {
    $reportKpis = [];
    $performanceMetrics = [];
    $onTimeRate = 100;
    $currMonthLabel = date('F Y');
    $lastMonthLabel = date('F Y', strtotime('-1 month'));
    $isHistoricalReport = false;
}

// 5. Available past months & Monthly Archive
$availableReportMonths = [];
for ($m = 0; $m < 12; $m++) {
    $mVal = date('Y-m', strtotime("-$m months"));
    $availableReportMonths[$mVal] = date('F Y', strtotime("-$m months"));
}
try {
    $dbMonths = $pdo->query("
        SELECT DISTINCT DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') AS m_val 
        FROM dispatches 
        WHERE status = 'Delivered' AND COALESCE(transit_end_time, dispatch_date, created_at) IS NOT NULL
        UNION
        SELECT DISTINCT DATE_FORMAT(COALESCE(transit_end_time, trip_date, created_at), '%Y-%m') AS m_val 
        FROM driver_trips 
        WHERE status = 'Delivered' AND COALESCE(transit_end_time, trip_date, created_at) IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dbMonths as $dbm) {
        if (!empty($dbm) && preg_match('/^\d{4}-\d{2}$/', $dbm)) {
            $availableReportMonths[$dbm] = date('F Y', strtotime($dbm . '-01'));
        }
    }
} catch (PDOException $e) {}
krsort($availableReportMonths);

// Build monthly archive summaries
$monthlyArchive = [];
try {
    $stmtArchiveDisp = $pdo->query("
        SELECT 
            DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') AS ym,
            COUNT(id) AS delivery_count,
            COALESCE(SUM(cubic_meters), 0) AS total_cm,
            COALESCE((SUM(is_on_time) / NULLIF(COUNT(id), 0)) * 100, 100) AS on_time_pct,
            destination
        FROM dispatches 
        WHERE status = 'Delivered'
        GROUP BY ym, destination
    ");
    $archiveDispRows = $stmtArchiveDisp->fetchAll(PDO::FETCH_ASSOC);
    $aggArchive = [];
    foreach ($archiveDispRows as $adr) {
        $ym = $adr['ym'];
        if (!$ym) continue;
        if (!isset($aggArchive[$ym])) {
            $aggArchive[$ym] = [
                'ym' => $ym,
                'deliveries' => 0,
                'volume_cm' => 0,
                'payroll' => 0,
                'on_time_pct' => 100,
            ];
        }
        $cnt = intval($adr['delivery_count']);
        $aggArchive[$ym]['deliveries'] += $cnt;
        $aggArchive[$ym]['volume_cm'] += floatval($adr['total_cm']);
        $aggArchive[$ym]['payroll'] += getDestinationPay($adr['destination'], $DISTANCE_KM, $DRIVER_RATES) * $cnt;
        $aggArchive[$ym]['on_time_pct'] = floatval($adr['on_time_pct']);
    }

    foreach ($availableReportMonths as $ym => $label) {
        $row = $aggArchive[$ym] ?? [
            'ym' => $ym,
            'deliveries' => 0,
            'volume_cm' => 0,
            'payroll' => 0,
            'on_time_pct' => 100
        ];
        $row['label'] = $label;
        $monthlyArchive[] = $row;
    }
} catch (PDOException $e) {}

// Trailing 6 months ending on selected month for charts
$financeReports = [];
$efficiencyData = [];
for ($i = 5; $i >= 1; $i--) {
    $mTimestamp = strtotime("-$i months", $currMonthTimestamp);
    $mDate = date('Y-m', $mTimestamp);
    $mLabel = date('M Y', $mTimestamp);
    try {
        $mStmt = $pdo->prepare("
            SELECT destination 
            FROM dispatches 
            WHERE status = 'Delivered' 
              AND DATE_FORMAT(COALESCE(transit_end_time, dispatch_date, created_at), '%Y-%m') = ?
        ");
        $mStmt->execute([$mDate]);
        $mRows = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        $mCount = count($mRows);
        $mPay = 0;
        foreach ($mRows as $mr) {
            $mPay += getDestinationPay($mr['destination'], $DISTANCE_KM, $DRIVER_RATES);
        }

        if ($mCount === 0) {
            $mDtStmt = $pdo->prepare("
                SELECT destination 
                FROM driver_trips 
                WHERE status = 'Delivered' 
                  AND DATE_FORMAT(COALESCE(transit_end_time, trip_date, created_at), '%Y-%m') = ?
            ");
            $mDtStmt->execute([$mDate]);
            $mDtRows = $mDtStmt->fetchAll(PDO::FETCH_ASSOC);
            $mCount = count($mDtRows);
            foreach ($mDtRows as $mdtr) {
                $mPay += getDestinationPay($mdtr['destination'], $DISTANCE_KM, $DRIVER_RATES);
            }
        }

        $payroll = $mPay;
        $deliv = $mCount;
    } catch (PDOException $e) {
        $payroll = 0;
        $deliv = 0;
    }
    $financeReports[] = [
        'month_name' => $mLabel,
        'payroll' => $payroll,
        'deliveries' => $deliv
    ];
    $efficiencyData[] = [
        'month_name' => $mLabel,
        'efficiency_pct' => 100
    ];
}
$financeReports[] = [
    'month_name' => date('M Y', $currMonthTimestamp),
    'payroll' => $driverSalariesCurr,
    'deliveries' => $currDeliveries
];
$efficiencyData[] = [
    'month_name' => date('M Y', $currMonthTimestamp),
    'efficiency_pct' => $onTimeRate
];

$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateStr = date('Y-m-d', strtotime("-$i days"));
    try {
        $dayQuery = $pdo->prepare("SELECT COUNT(id) AS total, SUM(IF(status='Delivered', 1, 0)) AS completed FROM dispatches WHERE dispatch_date = ?");
        $dayQuery->execute([$dateStr]);
        $realDayData = $dayQuery->fetch(PDO::FETCH_ASSOC);
        $total = $realDayData['total'] > 0 ? $realDayData['total'] : rand(2, 8);
        $comp = $realDayData['completed'] > 0 ? $realDayData['completed'] : rand(1, $total);
    } catch (PDOException $e) {
        $total = rand(2, 8);
        $comp = rand(1, $total);
    }
    $weeklyData[] = ['day_name' => date('D', strtotime($dateStr)), 'total_dispatches' => $total, 'completed' => $comp];
}

function getInitials($name)
{
    if (empty($name)) return 'DR';
    $words = explode(' ', $name);
    $i = '';
    foreach ($words as $w) {
        if (!empty($w)) $i .= $w[0];
    }
    return strtoupper(substr($i, 0, 2));
}

try {
    $allCheckers = $pdo->query("
        SELECT u.id, u.username, c.first_name, c.last_name, c.phone, COALESCE(c.status, 'Active') AS status, CONCAT(c.first_name, ' ', c.last_name) AS full_name 
        FROM users u 
        LEFT JOIN checkers c ON u.id = c.id 
        WHERE u.role = 'Checker' 
        ORDER BY u.username ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    try {
        $allCheckers = $pdo->query("
            SELECT u.id, u.username, c.first_name, c.last_name, c.phone, 'Active' AS status, CONCAT(c.first_name, ' ', c.last_name) AS full_name 
            FROM users u 
            LEFT JOIN checkers c ON u.id = c.id 
            WHERE u.role = 'Checker' 
            ORDER BY u.username ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e2) {
        $allCheckers = [];
    }
}

try {
    $allOrders = $pdo->query("
        SELECT o.*, COALESCE(CONCAT(c.first_name, ' ', c.last_name), u.username) AS checker_name
        FROM orders o
        LEFT JOIN users u ON u.id = o.checker_id
        LEFT JOIN checkers c ON u.id = c.id
        ORDER BY o.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $allOrders = [];
}

try {
    $activityLogs = $pdo->query("
        SELECT al.*, u.username AS current_username
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 100
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $activityLogs = [];
}

// All destinations (incl. inactive) for Settings tab
try {
    $allDestinations = $pdo->query("SELECT * FROM destinations ORDER BY is_active DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $allDestinations = [];
}

include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 relative">
    <?php include __DIR__ . '/views/home.php';
    include __DIR__ . '/views/tracking.php';
    include __DIR__ . '/views/dispatches.php';
    include __DIR__ . '/views/fleet.php';
    include __DIR__ . '/views/drivers.php';
    include __DIR__ . '/views/orders.php';
    include __DIR__ . '/views/reports.php';
    include __DIR__ . '/views/activity_logs.php';
    include __DIR__ . '/views/pwd_requests.php';
    include __DIR__ . '/views/cash_advances.php';
    include __DIR__ . '/views/settings.php';
    include __DIR__ . '/views/modals.php'; ?>
</div>
</div><!-- close #main-content -->
<?php if (isset($_SESSION['auto_print_id'])):
    $print_id = intval($_SESSION['auto_print_id']);
    unset($_SESSION['auto_print_id']);
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const printWin = window.open('print_ticket.php?id=<?= $print_id; ?>', '_blank');
            setTimeout(() => {
                window.focus();
                const scannerInput = document.getElementById('dispatchScannerRfidInput');
                if (scannerInput) {
                    scannerInput.focus();
                    scannerInput.select();
                }
            }, 300);
        });
    </script>
<?php endif; ?>
<?php if (isset($_SESSION['auto_print_cash_advance_id'])):
    $ca_print_id = intval($_SESSION['auto_print_cash_advance_id']);
    unset($_SESSION['auto_print_cash_advance_id']);
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.open('print_cash_advance.php?id=<?= $ca_print_id; ?>', '_blank');
        });
    </script>
<?php endif; ?>
<?php if (isset($_SESSION['auto_print_payroll_settlement_id'])):
    $payroll_settle_id = intval($_SESSION['auto_print_payroll_settlement_id']);
    unset($_SESSION['auto_print_payroll_settlement_id']);
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.open('print_payroll.php?settlement_id=<?= $payroll_settle_id; ?>', '_blank');
        });
    </script>
<?php endif; ?>
<?php include __DIR__ . '/../includes/scripts.php'; ?>
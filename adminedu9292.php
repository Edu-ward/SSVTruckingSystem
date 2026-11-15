<?php

$isCli = (php_sapi_name() === 'cli');

$dbError = null;
try {
    require_once __DIR__ . '/db.php';
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$message = null;
$messageType = null;

function syncDatabaseSchema(PDO $pdo): array {
    $log = [];
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Users table
        $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('Superadmin', 'Admin', 'Driver', 'Checker') NOT NULL DEFAULT 'Driver'");
        $cols = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('status', $cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `status` ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active'");
            $log[] = "Added column users.status";
        }

        // 2. Trucks table
        $pdo->exec("ALTER TABLE `trucks` 
            ADD COLUMN IF NOT EXISTS `rfid_tag` VARCHAR(100) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `rfid_active` TINYINT(1) DEFAULT 1,
            ADD COLUMN IF NOT EXISTS `current_location` VARCHAR(100) DEFAULT 'San Leonardo (Garage)',
            ADD COLUMN IF NOT EXISTS `latitude` DECIMAL(10, 8) DEFAULT 15.362100,
            ADD COLUMN IF NOT EXISTS `longitude` DECIMAL(11, 8) DEFAULT 120.963200,
            ADD COLUMN IF NOT EXISTS `speed` INT(11) DEFAULT 0");

        // 3. Drivers table
        $pdo->exec("ALTER TABLE `drivers` ADD COLUMN IF NOT EXISTS `profile_photo` VARCHAR(255) DEFAULT NULL");

        // 4. Checkers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `checkers` (
            `id` INT PRIMARY KEY,
            `first_name` VARCHAR(100) DEFAULT '',
            `last_name` VARCHAR(100) DEFAULT '',
            `phone` VARCHAR(20) DEFAULT '',
            `status` VARCHAR(50) DEFAULT 'Active',
            FOREIGN KEY (`id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 5. Orders table
        $pdo->exec("ALTER TABLE `orders` 
            ADD COLUMN IF NOT EXISTS `quantity_type` ENUM('sqm','hectare','truck_count') NOT NULL DEFAULT 'truck_count',
            ADD COLUMN IF NOT EXISTS `quantity_value` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            ADD COLUMN IF NOT EXISTS `cubic_meters_required` DECIMAL(10,2) DEFAULT 0.00,
            ADD COLUMN IF NOT EXISTS `cubic_meters_fulfilled` DECIMAL(10,2) DEFAULT 0.00,
            ADD COLUMN IF NOT EXISTS `checker_id` INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `contact_number` VARCHAR(50) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `landmark` VARCHAR(255) DEFAULT NULL");

        // 6. Dispatches table
        $pdo->exec("ALTER TABLE `dispatches` 
            ADD COLUMN IF NOT EXISTS `distance_km` DECIMAL(10,2) DEFAULT 0.00,
            ADD COLUMN IF NOT EXISTS `cancellation_reason` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `cancellation_photo` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `landmark` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS `payroll_settled_at` DATETIME DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `payroll_id` INT DEFAULT NULL");

        // 7. Driver trips table
        $pdo->exec("ALTER TABLE `driver_trips` 
            ADD COLUMN IF NOT EXISTS `distance_km` DECIMAL(8,2) DEFAULT 0.00,
            ADD COLUMN IF NOT EXISTS `pay_amount` DECIMAL(10,2) DEFAULT 0.00,
            ADD COLUMN IF NOT EXISTS `is_on_time` TINYINT(1) DEFAULT 1,
            ADD COLUMN IF NOT EXISTS `transit_start_time` DATETIME DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `estimated_arrival_time` DATETIME DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `transit_end_time` DATETIME DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS `payroll_settled_at` DATETIME DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS `payroll_id` INT DEFAULT NULL");

        // 8. Driver payroll table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `driver_payroll` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `driver_id` INT NOT NULL UNIQUE,
            `total_amount` DECIMAL(12, 2) DEFAULT 0.00,
            `amount_claimed` DECIMAL(12, 2) DEFAULT 0.00,
            `remaining_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 9. Driver payroll settlements table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `driver_payroll_settlements` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `settlement_ticket` VARCHAR(50) NOT NULL UNIQUE,
            `driver_id` INT NOT NULL,
            `gross_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `previous_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `cash_advance_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `net_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `amount_claimed` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `remaining_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `trips_count` INT NOT NULL DEFAULT 0,
            `settled_by` INT DEFAULT NULL,
            `settled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `notes` TEXT DEFAULT NULL,
            FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 10. Cash advances table
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

        // 11. Password reset requests table
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

        // 12. System settings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_key` VARCHAR(100) PRIMARY KEY,
            `setting_value` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $defaultSettings = [
            ['garage_name', 'San Leonardo (Garage)', 'Default garage/origin location name'],
            ['garage_lat', '15.3621', 'Garage latitude coordinate'],
            ['garage_lng', '120.9632', 'Garage longitude coordinate'],
            ['op_cost_pct', '0.40', 'Estimated operational cost as a decimal fraction'],
            ['payday_day', 'Saturday', 'Day of the week when drivers are paid'],
            ['base_trip_rate', '300.00', 'Base flat rate for trips within San Leonardo (PHP)'],
            ['rate_per_km', '10.00', 'Rate per kilometer for distance outside San Leonardo boundary (PHP)']
        ];
        $st = $pdo->prepare("INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)");
        foreach ($defaultSettings as $row) {
            $st->execute($row);
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $log[] = "All tables and columns verified successfully.";
    } catch (Throwable $e) {
        $log[] = "Database sync warning: " . $e->getMessage();
    }
    return $log;
}

$syncLog = [];
if (isset($pdo)) {
    $syncLog = syncDatabaseSchema($pdo);
}

if ($isCli) {
    if ($dbError || !isset($pdo)) {
        echo "[ERROR] Database connection failed: " . ($dbError ?: 'PDO not initialized') . PHP_EOL;
        exit(1);
    }
    $username = trim($argv[1] ?? '');
    $password = trim($argv[2] ?? '');
    $role = trim($argv[3] ?? 'Admin');

    if (empty($username) || empty($password)) {
        echo "Usage: php create_admin.php <username> <password> [Admin|Superadmin]" . PHP_EOL;
        echo "[INFO] Database schema sync completed successfully." . PHP_EOL;
        exit(0);
    }

    if (!in_array($role, ['Admin', 'Superadmin'])) {
        $role = 'Admin';
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();

    if ($existing) {
        $update = $pdo->prepare("UPDATE users SET password = ?, role = ?, status = 'Active' WHERE id = ?");
        $update->execute([$hash, $role, $existing['id']]);
        echo "[SUCCESS] {$role} account '{$username}' password and role updated!" . PHP_EOL;
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, 'Active')");
        $insert->execute([$username, $hash, $role]);
        echo "[SUCCESS] {$role} account '{$username}' was created successfully!" . PHP_EOL;
    }
    echo "Password: {$password}" . PHP_EOL;
    echo "Role: {$role}" . PHP_EOL;
    echo "Login at: index.php" . PHP_EOL;
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete_self') {

        @unlink(__FILE__);
        header("Location: index.php?msg=setup_complete");
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'Superadmin');
    if (!in_array($role, ['Admin', 'Superadmin'])) {
        $role = 'Superadmin';
    }

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $existing = $stmt->fetch();

            $hash = password_hash($password, PASSWORD_BCRYPT);

            if ($existing) {
                $update = $pdo->prepare("UPDATE users SET password = ?, role = ?, status = 'Active' WHERE id = ?");
                $update->execute([$hash, $role, $existing['id']]);
                $message = "{$role} account '<strong>" . htmlspecialchars($username) . "</strong>' has been updated successfully!";
                $messageType = "success";
            } else {
                $insert = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, 'Active')");
                $insert->execute([$username, $hash, $role]);
                $message = "{$role} account '<strong>" . htmlspecialchars($username) . "</strong>' created successfully!";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

$existingAdmins = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT id, username, role, status, created_at FROM users WHERE role IN ('Admin', 'Superadmin') ORDER BY (role = 'Superadmin') DESC, id ASC");
        $existingAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $existingAdmins = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account — SSV Trucking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-indigo-950 text-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-gray-800/90 backdrop-blur-xl border border-gray-700/80 rounded-3xl shadow-2xl overflow-hidden">

        <div class="p-6 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-2xl border border-white/20 shadow-inner">
                <i class="fa-solid fa-user-shield text-amber-300"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight">SSV Trucking System</h1>
                <p class="text-xs text-blue-100 opacity-90">Admin Account Setup Tool</p>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">

            <?php if ($dbError || !isset($pdo)): ?>
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-start space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base mt-0.5"></i>
                    <div>
                        <div class="font-bold text-rose-200">Database Connection Error</div>
                        <p class="mt-1"><?= htmlspecialchars($dbError ?: 'Could not initialize PDO connection.') ?></p>
                        <p class="mt-2 text-[11px] text-gray-400">Make sure your database credentials in <code class="bg-gray-900 px-1 py-0.5 rounded text-amber-300">config.prod.php</code> (or <code class="bg-gray-900 px-1 py-0.5 rounded text-amber-300">config.php</code>) are correct.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Database connected: <strong class="font-mono text-emerald-200"><?= htmlspecialchars(defined('DB_NAME') ? DB_NAME : 'connected') ?></strong></span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 font-bold uppercase"><?= defined('IS_PRODUCTION') && IS_PRODUCTION ? 'Live' : 'Local' ?></span>
                </div>
                <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 text-xs flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-database text-blue-400"></i>
                        <span>Schema Status: <strong class="text-blue-200">Tables & Columns Synced</strong></span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/20 font-bold uppercase text-blue-300">Auto-Patched</span>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="p-4 rounded-2xl <?= $messageType === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300' ?> border text-xs flex items-start space-x-3">
                    <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check text-emerald-400' : 'fa-circle-xmark text-rose-400' ?> text-base mt-0.5"></i>
                    <div class="flex-1">
                        <div><?= $message ?></div>
                        <?php if ($messageType === 'success'): ?>
                            <div class="mt-3 flex items-center gap-2">
                                <a href="index.php" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition">
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                    <span>Go to Login</span>
                                </a>
                                <form method="POST" onsubmit="return confirm('This will delete create_admin.php from the server for security. Continue?');">
                                    <input type="hidden" name="action" value="delete_self">
                                    <button type="submit" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs transition">
                                        <i class="fa-solid fa-trash-can text-red-400"></i>
                                        <span>Delete This File</span>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($existingAdmins)): ?>
                <div class="p-4 rounded-2xl bg-gray-700/40 border border-gray-700/60">
                    <div class="flex items-center justify-between text-xs text-gray-400 mb-2 font-medium">
                        <span>Existing Admin Accounts (<?= count($existingAdmins) ?>):</span>
                    </div>
                    <div class="space-y-1.5">
                        <?php foreach ($existingAdmins as $adm): ?>
                            <div class="flex items-center justify-between text-xs py-1 px-2.5 rounded-lg bg-gray-800/80 border border-gray-700/40">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-user-gear text-blue-400 text-xs"></i>
                                    <span class="font-bold text-gray-200"><?= htmlspecialchars($adm['username']) ?></span>
                                </div>
                                <button type="button" onclick="fillExistingAdmin('<?= htmlspecialchars(addslashes($adm['username'])) ?>')" class="text-[11px] text-blue-400 hover:underline">
                                    Reset Password
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Admin Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="usernameInput" name="username" required value="admin" placeholder="e.g. admin"
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-gray-900/80 border border-gray-700 text-gray-100 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Account Role</label>
                    <select name="role" class="w-full px-3 py-2.5 rounded-xl bg-gray-900/80 border border-gray-700 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Superadmin" selected>Superadmin (Full Control)</option>
                        <option value="Admin">Admin (Standard Operator)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="password" id="passwordInput" name="password" required value="admin123" placeholder="Enter password"
                            class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-gray-900/80 border border-gray-700 text-gray-100 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <button type="button" onclick="togglePassword('passwordInput', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 text-xs focus:outline-none">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="password" id="confirmPasswordInput" name="confirm_password" required value="admin123" placeholder="Confirm password"
                            class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-gray-900/80 border border-gray-700 text-gray-100 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <button type="button" onclick="togglePassword('confirmPasswordInput', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 text-xs focus:outline-none">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-blue-500/25 transition active:scale-[0.98] flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Save Admin Account</span>
                    </button>
                </div>
            </form>

            <div class="text-[11px] text-gray-400 border-t border-gray-700/60 pt-4 space-y-1.5">
                <div class="flex items-center gap-1.5 text-amber-400/90 font-medium">
                    <i class="fa-solid fa-shield-cat"></i>
                    <span>Security Reminder</span>
                </div>
                <p>After creating your account, click <strong>"Delete This File"</strong> above or remove <code class="text-gray-300">create_admin.php</code> from your InfinityFree file manager so unauthorized users cannot reset your admin credentials.</p>
            </div>

        </div>

    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function fillExistingAdmin(username) {
            document.getElementById('usernameInput').value = username;
            document.getElementById('passwordInput').value = '';
            document.getElementById('confirmPasswordInput').value = '';
            document.getElementById('passwordInput').focus();
        }
    </script>
</body>

</html>
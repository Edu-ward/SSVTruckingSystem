<?php
// ============================================================
// SSV Trucking System — Admin Account Setup Tool
// Usage:
// 1. Web: Open in your browser (e.g., http://localhost/CAPSTONE/create_admin.php or http://ssvtrucking.rf.gd/create_admin.php)
// 2. CLI: Run `php create_admin.php` in terminal
// IMPORTANT: Delete or rename this file after creating your admin account for security!
// ============================================================

$isCli = (php_sapi_name() === 'cli');

// Database connection
$dbError = null;
try {
    require_once __DIR__ . '/db.php';
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$message = null;
$messageType = null;

// Handle CLI execution
if ($isCli) {
    if ($dbError || !isset($pdo)) {
        echo "[ERROR] Database connection failed: " . ($dbError ?: 'PDO not initialized') . PHP_EOL;
        exit(1);
    }
    $username = $argv[1] ?? 'admin';
    $password = $argv[2] ?? 'adminpass99';

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();

    if ($existing) {
        $update = $pdo->prepare("UPDATE users SET password = ?, role = 'Admin' WHERE id = ?");
        $update->execute([$hash, $existing['id']]);
        echo "[SUCCESS] Admin account '{$username}' password was updated!" . PHP_EOL;
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Admin')");
        $insert->execute([$username, $hash]);
        echo "[SUCCESS] Admin account '{$username}' was created successfully!" . PHP_EOL;
    }
    echo "Password: {$password}" . PHP_EOL;
    echo "Login at: index.php" . PHP_EOL;
    exit(0);
}

// Handle Web POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete_self') {
        // Self-destruct for security
        @unlink(__FILE__);
        header("Location: index.php?msg=setup_complete");
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

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
                $update = $pdo->prepare("UPDATE users SET password = ?, role = 'Admin' WHERE id = ?");
                $update->execute([$hash, $existing['id']]);
                $message = "Admin account '<strong>" . htmlspecialchars($username) . "</strong>' password has been updated successfully!";
                $messageType = "success";
            } else {
                $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Admin')");
                $insert->execute([$username, $hash]);
                $message = "Admin account '<strong>" . htmlspecialchars($username) . "</strong>' created successfully!";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Check existing admins
$existingAdmins = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT id, username, created_at FROM users WHERE role = 'Admin' ORDER BY id ASC");
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

        <!-- Top Branding Header -->
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

            <!-- Database Status Indicator -->
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
            <?php endif; ?>

            <!-- Feedback Alert -->
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

            <!-- Existing Admins List -->
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

            <!-- Account Form -->
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

            <!-- Quick Info / Security Advice -->
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
<?php
session_start();
require 'db.php';

// Only allow Admin or direct run (local)
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Admin') {
    die("Unauthorized.");
}

$errors = [];
$success = [];

try {
    // 1. Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(30) NOT NULL UNIQUE,
            client_name VARCHAR(100) NOT NULL,
            gravel_type VARCHAR(50) NOT NULL,
            destination VARCHAR(100) NOT NULL,
            trucks_required INT NOT NULL DEFAULT 1,
            trucks_fulfilled INT NOT NULL DEFAULT 0,
            checker_id INT NULL,
            status ENUM('Pending','In Progress','Fulfilled','Cancelled') DEFAULT 'Pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $success[] = "✅ Table <strong>orders</strong> created (or already exists).";

    // Patch: add missing columns if table existed before this update
    $patchCols = [
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS checker_id INT NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS trucks_fulfilled INT NOT NULL DEFAULT 0",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes TEXT",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS status ENUM('Pending','In Progress','Fulfilled','Cancelled') NOT NULL DEFAULT 'Pending'",
    ];
    foreach ($patchCols as $sql) {
        try { $pdo->exec($sql); } catch (PDOException $e) { /* already exists, ignore */ }
    }

    // Add FK only if it doesn't exist (ignore duplicate key errors)
    try {
        $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_checker FOREIGN KEY (checker_id) REFERENCES users(id) ON DELETE SET NULL");
    } catch (PDOException $e) { /* FK already exists, ignore */ }

    $success[] = "✅ Column patch applied to <strong>orders</strong> table.";

    // 2. Create order_scans table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_scans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            truck_id INT NOT NULL,
            checker_id INT NOT NULL,
            scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (truck_id) REFERENCES trucks(id) ON DELETE CASCADE,
            FOREIGN KEY (checker_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $success[] = "✅ Table <strong>order_scans</strong> created (or already exists).";

    // 3. Create a default checker user (skip if exists)
    $check = $pdo->prepare("SELECT id FROM users WHERE username = 'checker1'");
    $check->execute();
    if (!$check->fetch()) {
        $hashed = password_hash('Checker@123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('checker1', ?, 'Checker')")->execute([$hashed]);
        $success[] = "✅ Default checker account created: <strong>checker1 / Checker@123</strong>";
    } else {
        $success[] = "ℹ️ Default checker account <strong>checker1</strong> already exists.";
    }

} catch (PDOException $e) {
    $errors[] = "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SSV Trucking — Setup Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-lg w-full">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">🔧 SSV Setup — Checker & Orders</h1>
        <p class="text-sm text-gray-500 mb-6">Run this once to create the required database tables.</p>

        <?php foreach ($success as $msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-3 text-sm"><?= $msg ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $msg): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-3 text-sm"><?= $msg ?></div>
        <?php endforeach; ?>

        <?php if (empty($errors)): ?>
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                <p class="font-bold mb-1">✅ Setup Complete!</p>
                <p>You can now <a href="index.php" class="underline font-semibold">go to the login page</a> and sign in as a Checker.</p>
                <p class="mt-2 text-xs text-blue-600">⚠️ Delete or restrict access to this file after setup.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

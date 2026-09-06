<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Ensure MySQL functions (NOW(), CURDATE(), TIMESTAMPDIFF) use Philippine Time (UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    error_log("SSV DB Connection Failed: " . $e->getMessage());
    die("A database error occurred. Please contact the system administrator.");
}

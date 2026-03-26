<?php
require 'db.php';

$username = 'admin';
$password = 'admin123';
$role = 'Admin';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $role]);

    echo "Success! Admin user created. You can now delete this file and log in.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

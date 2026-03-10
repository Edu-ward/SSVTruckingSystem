<?php
require 'db.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);

    echo "Success! Admin user created. You can now delete this file and log in.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

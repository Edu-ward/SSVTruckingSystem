<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'Admin') {
            header("Location: admin/dashboard.php");
            exit;
        } elseif ($user['role'] == 'Driver') {
            header("Location: driver/driver_panel.php");
            exit;
        } else {
            header("Location: index.php?error=unauthorized");
            exit;
        }
    } else {
        header("Location: index.php?error=invalid");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}

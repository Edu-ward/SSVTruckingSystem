<?php
require_once __DIR__ . '/includes/security_headers.php';
require 'db.php';
require_once __DIR__ . '/includes/activity_log.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── CSRF Validation (timing-safe) ──
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }

    // ── Brute-Force Protection ──
    $maxAttempts = 5;
    $lockoutSeconds = 900; // 15 minutes

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_failed_login'] = 0;
    }

    // Check if currently locked out
    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $elapsed = time() - $_SESSION['last_failed_login'];
        if ($elapsed < $lockoutSeconds) {
            $remaining = ceil(($lockoutSeconds - $elapsed) / 60);
            header("Location: index.php?error=locked&minutes=" . $remaining);
            exit;
        }
        // Lockout expired — reset
        $_SESSION['login_attempts'] = 0;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // ── Successful login — reset counters & regenerate session ──
        $_SESSION['login_attempts'] = 0;
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        log_activity($pdo, 'Login', 'Logged in successfully as ' . $user['role']);

        if ($user['role'] == 'Admin') {
            header("Location: admin/dashboard.php");
            exit;
        } elseif ($user['role'] == 'Driver') {
            header("Location: driver/dashboard.php");
            exit;
        } elseif ($user['role'] == 'Checker') {
            header("Location: checker/dashboard.php");
            exit;
        } else {
            header("Location: index.php?error=unauthorized");
            exit;
        }
    } else {
        // ── Failed login — increment counter ──
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_failed_login'] = time();
        log_activity($pdo, 'Failed Login', 'Failed login attempt for username: ' . $username);
        header("Location: index.php?error=invalid");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}


<?php
require_once __DIR__ . '/includes/security_headers.php';

// Destroy all session data
$_SESSION = [];

// Expire the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
header("Location: index.php");
exit;
?>
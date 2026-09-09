<?php
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/activity_log.php';


if (isset($_SESSION['user_id'])) {
    $actionMsg = isset($_GET['tab_closed']) ? 'Auto-logged out (Tab closed)' : 'Logged out';
    log_activity($pdo, 'Logout', $actionMsg);
}


$_SESSION = [];


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

$redirectUrl = "index.php";
if (isset($_GET['tab_closed'])) {
    $redirectUrl .= "?error=tab_closed";
}

header("Location: " . $redirectUrl);
exit;
?>
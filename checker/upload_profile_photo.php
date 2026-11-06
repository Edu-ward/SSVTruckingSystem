<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Checker') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    }
    die("CSRF token validation failed.");
}

$checker_id = $_SESSION['user_id'];

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    $msg = "No file uploaded or an upload error occurred.";
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
    $_SESSION['error'] = $msg;
    header("Location: dashboard.php");
    exit;
}

$file = $_FILES['profile_photo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024;

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (!in_array($mime, $allowed_types)) {
    $msg = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
    $_SESSION['error'] = $msg;
    header("Location: dashboard.php");
    exit;
}

if ($file['size'] > $max_size) {
    $msg = "File size exceeds the 5MB limit.";
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
    $_SESSION['error'] = $msg;
    header("Location: dashboard.php");
    exit;
}

$ext_map = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];
$ext = $ext_map[$mime];
$upload_dir = __DIR__ . '/../assets/uploads/checker_photos/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$filename = 'checker_' . $checker_id . '_' . time() . '.' . $ext;
$dest = $upload_dir . $filename;

// Clean up previous photo for this checker
foreach (glob($upload_dir . 'checker_' . $checker_id . '*.*') as $old_file) {
    if (is_file($old_file)) {
        @unlink($old_file);
    }
}

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    $msg = "Failed to save the photo. Please check folder permissions.";
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
    $_SESSION['error'] = $msg;
    header("Location: dashboard.php");
    exit;
}

$photo_path = 'assets/uploads/checker_photos/' . $filename;

// Ensure checkers table has column and record
try {
    $chkCol = $pdo->query("SHOW COLUMNS FROM `checkers` LIKE 'profile_photo'")->fetch();
    if (!$chkCol) {
        $pdo->exec("ALTER TABLE `checkers` ADD COLUMN `profile_photo` VARCHAR(255) DEFAULT NULL");
    }
} catch (Throwable $e) {}

$stmt = $pdo->prepare("SELECT id FROM checkers WHERE id = ?");
$stmt->execute([$checker_id]);
if ($stmt->fetch()) {
    $updateStmt = $pdo->prepare("UPDATE checkers SET profile_photo = ? WHERE id = ?");
    $updateStmt->execute([$photo_path, $checker_id]);
} else {
    $insertStmt = $pdo->prepare("INSERT INTO checkers (id, profile_photo) VALUES (?, ?)");
    $insertStmt->execute([$checker_id, $photo_path]);
}

$_SESSION['profile_photo'] = $photo_path;
log_activity($pdo, 'Updated Profile Photo', 'Checker uploaded a new profile photo.');
$_SESSION['success'] = "Profile photo updated successfully!";

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['success' => true, 'photo_url' => '../' . $photo_path . '?v=' . time()]);
    exit;
}

header("Location: dashboard.php");
exit;

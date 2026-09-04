<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/activity_log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    die("CSRF token validation failed.");
}

$driver_id = $_SESSION['user_id'];

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "No file uploaded or an upload error occurred.";
    header("Location: dashboard.php");
    exit;
}

$file = $_FILES['profile_photo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 2 * 1024 * 1024;

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (!in_array($mime, $allowed_types)) {
    $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
    header("Location: dashboard.php");
    exit;
}

if ($file['size'] > $max_size) {
    $_SESSION['error'] = "File size exceeds the 2MB limit.";
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
$upload_dir = __DIR__ . '/../src/uploads/driver_photos/';
$filename   = 'driver_' . $driver_id . '_' . time() . '.' . $ext;
$dest       = $upload_dir . $filename;

// Remove any older photos for this driver
foreach (glob($upload_dir . 'driver_' . $driver_id . '*.*') as $old_file) {
    if (is_file($old_file)) {
        @unlink($old_file);
    }
}

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    $_SESSION['error'] = "Failed to save the photo. Please check folder permissions.";
    header("Location: dashboard.php");
    exit;
}

$photo_path = 'src/uploads/driver_photos/' . $filename;
$stmt = $pdo->prepare("UPDATE drivers SET profile_photo = ? WHERE id = ?");
$stmt->execute([$photo_path, $driver_id]);

$_SESSION['profile_photo'] = $photo_path;
log_activity($pdo, 'Updated Profile Photo', 'Driver uploaded a new profile photo.');
$_SESSION['success'] = "Profile photo updated successfully!";
header("Location: dashboard.php");
exit;


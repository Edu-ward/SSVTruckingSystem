<?php

date_default_timezone_set('Asia/Manila');


if (file_exists(__DIR__ . '/config.prod.php')) {
    require_once __DIR__ . '/config.prod.php';
}


$envHost = $_SERVER['DB_HOST'] ?? $_SERVER['REDIRECT_DB_HOST'] ?? getenv('DB_HOST') ?: null;
$envName = $_SERVER['DB_NAME'] ?? $_SERVER['REDIRECT_DB_NAME'] ?? getenv('DB_NAME') ?: null;
$envUser = $_SERVER['DB_USER'] ?? $_SERVER['REDIRECT_DB_USER'] ?? getenv('DB_USER') ?: null;
$envPass = $_SERVER['DB_PASS'] ?? $_SERVER['REDIRECT_DB_PASS'] ?? getenv('DB_PASS') ?: null;

if (!defined('DB_HOST'))    define('DB_HOST',    $envHost ?: 'localhost');
if (!defined('DB_NAME'))    define('DB_NAME',    $envName ?: 'ssv_trucking');
if (!defined('DB_USER'))    define('DB_USER',    $envUser ?: 'root');
if (!defined('DB_PASS'))    define('DB_PASS',    $envPass !== null ? $envPass : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');


if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1');
}

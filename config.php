<?php
// ============================================================
// SSV Trucking System — Database Configuration
// Reads from environment variables if set (production),
// otherwise falls back to local XAMPP defaults.
// To configure on InfinityFree: set these in the Softaculous
// env or use a .htaccess SetEnv directive (see deploy/README.md)
// ============================================================

define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'ssv_trucking');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── Environment detection ──
// IS_PRODUCTION is true when the DB_HOST env var is explicitly set by the server.
define('IS_PRODUCTION', (bool) getenv('DB_HOST'));

<?php
// ============================================================
// SSV Trucking System — Centralized Security Headers
// Include this file at the VERY TOP of every entry-point PHP
// file, BEFORE any output or session_start() call.
// ============================================================

// ── Session Cookie Hardening ──
ini_set('session.cookie_httponly', 1);
// Secure flag: ON in production (HTTPS), OFF locally (HTTP/XAMPP)
ini_set('session.cookie_secure',   defined('IS_PRODUCTION') && IS_PRODUCTION ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// ── HTTP Security Headers ──
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// ── Start Session (with hardened settings above) ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

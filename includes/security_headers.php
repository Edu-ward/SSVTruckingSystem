<?php
// ============================================================
// SSV Trucking System — Centralized Security Headers
// Include this file at the VERY TOP of every entry-point PHP
// file, BEFORE any output or session_start() call.
// ============================================================

// ── Session Cookie Hardening ──
ini_set('session.cookie_httponly', 1);       // Prevent JS access to session cookie
ini_set('session.cookie_secure', 0);        // Set to 1 when using HTTPS in production
ini_set('session.use_strict_mode', 1);      // Reject uninitialized session IDs
ini_set('session.cookie_samesite', 'Lax'); // Lax allows same-site POST form submissions to carry the session cookie

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

<?php

function log_activity(PDO $pdo, string $action, string $details = ''): void
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, username, role, action, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $_SESSION['user_id']  ?? null,
            $_SESSION['username'] ?? 'System',
            $_SESSION['role']     ?? 'Unknown',
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);
    } catch (PDOException $e) {
        
        error_log("Activity log insert failed: " . $e->getMessage());
    }
}

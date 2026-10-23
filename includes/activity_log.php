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

function get_action_category(string $action): string
{
    $act = strtolower($action);
    if (str_contains($act, 'login') || str_contains($act, 'logout') || str_contains($act, 'password') || str_contains($act, 'auth') || str_contains($act, 'session')) {
        return 'Security';
    }
    if (str_contains($act, 'dispatch') || str_contains($act, 'ticket') || str_contains($act, 'trip') || str_contains($act, 'waybill') || str_contains($act, 'destination') || str_contains($act, 'delivery')) {
        return 'Dispatches';
    }
    if (str_contains($act, 'truck') || str_contains($act, 'fleet') || str_contains($act, 'vehicle') || str_contains($act, 'rfid') || str_contains($act, 'maintenance')) {
        return 'Fleet';
    }
    if (str_contains($act, 'driver') || str_contains($act, 'license') || str_contains($act, 'cdl') || str_contains($act, 'checker')) {
        return 'Personnel';
    }
    if (str_contains($act, 'payroll') || str_contains($act, 'cash advance') || str_contains($act, 'advance') || str_contains($act, 'salary') || str_contains($act, 'balance') || str_contains($act, 'payment') || str_contains($act, 'settle')) {
        return 'Payroll';
    }
    if (str_contains($act, 'order') || str_contains($act, 'customer') || str_contains($act, 'client')) {
        return 'Orders';
    }
    return 'System';
}

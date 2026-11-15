<?php
/**
 * Queue feature patch script.
 * Run once, then delete this file.
 */

// ── 1. admin/dashboard.php ─────────────────────────────────────────────────
$file = __DIR__ . '/admin/dashboard.php';
$src  = file_get_contents($file);

// Path A – RFID scan completion (dispatch_scan_rfid)  line ~1337
$src = str_replace(
    "UPDATE drivers SET status = 'Active' WHERE id = ?\")->execute([\$dispatch['driver_id']]);\r\n                    \$pdo->prepare(\"UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status IN ('In Transit', 'Pending') ORDER BY id DESC LIMIT 1\")->execute([\$isOnTime, \$dispatch['driver_id'], \$dispatch['destination']]);",
    "UPDATE drivers SET status = 'Active', queue_entry_time = NOW() WHERE id = ?\")->execute([\$dispatch['driver_id']]);\r\n                    \$pdo->prepare(\"UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status IN ('In Transit', 'Pending') ORDER BY id DESC LIMIT 1\")->execute([\$isOnTime, \$dispatch['driver_id'], \$dispatch['destination']]);",
    $src
);

// Path B – manual complete_dispatch  line ~1424
$src = str_replace(
    "UPDATE drivers SET status = 'Active' WHERE id = ?\")->execute([\$dispatch['driver_id']]);\r\n            \$pdo->prepare(\"UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1\")->execute([\$isOnTime, \$dispatch['driver_id'], \$dispatch['destination']]);",
    "UPDATE drivers SET status = 'Active', queue_entry_time = NOW() WHERE id = ?\")->execute([\$dispatch['driver_id']]);\r\n            \$pdo->prepare(\"UPDATE driver_trips SET status = 'Delivered', transit_end_time = NOW(), is_on_time = ? WHERE driver_id = ? AND destination = ? AND status = 'In Transit' ORDER BY id DESC LIMIT 1\")->execute([\$isOnTime, \$dispatch['driver_id'], \$dispatch['destination']]);",
    $src
);

file_put_contents($file, $src);
echo "✅ admin/dashboard.php patched\n";

// ── 2. checker/dashboard.php ───────────────────────────────────────────────
$file2 = __DIR__ . '/checker/dashboard.php';
$src2  = file_get_contents($file2);

// checker scan_truck completion  line ~169
$src2 = str_replace(
    "UPDATE drivers SET status = 'Active' WHERE id = ?\")->execute([\$dispatchedDriverId]);",
    "UPDATE drivers SET status = 'Active', queue_entry_time = NOW() WHERE id = ?\")->execute([\$dispatchedDriverId]);",
    $src2
);

file_put_contents($file2, $src2);
echo "✅ checker/dashboard.php patched\n";

// ── 3. driver/dashboard.php – add queue data query ────────────────────────
$file3 = __DIR__ . '/driver/dashboard.php';
$src3  = file_get_contents($file3);

$queueBlock = <<<'PHPBLOCK'

// ── Queue position (only relevant when driver is idle/active) ──────────────
$driverQueuePosition = null;
$driverQueueTotal    = null;
if (!$active_dispatch && !$driver_is_dispatched) {
    // Count how many active drivers entered the queue BEFORE this driver
    $queueStmt = $pdo->prepare("
        SELECT COUNT(*) + 1 AS position
        FROM drivers
        WHERE status IN ('Active', 'Idle', 'Off Duty')
          AND id != ?
          AND queue_entry_time IS NOT NULL
          AND queue_entry_time <= (
              SELECT COALESCE(queue_entry_time, '1970-01-01') FROM drivers WHERE id = ?
          )
          AND NOT EXISTS (
              SELECT 1 FROM dispatches d2
              WHERE d2.driver_id = drivers.id
                AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
          )
    ");
    $queueStmt->execute([$driver_id, $driver_id]);
    $driverQueuePosition = (int)($queueStmt->fetchColumn() ?: 1);

    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) FROM drivers
        WHERE status IN ('Active', 'Idle', 'Off Duty')
          AND NOT EXISTS (
              SELECT 1 FROM dispatches d2
              WHERE d2.driver_id = drivers.id
                AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
          )
    ");
    $totalStmt->execute();
    $driverQueueTotal = (int)($totalStmt->fetchColumn() ?: 1);

    // If this driver has no queue_entry_time yet, default it to now
    $myQueueEntry = $pdo->prepare("SELECT queue_entry_time FROM drivers WHERE id = ?");
    $myQueueEntry->execute([$driver_id]);
    $myQueueRow = $myQueueEntry->fetchColumn();
    if (!$myQueueRow) {
        $pdo->prepare("UPDATE drivers SET queue_entry_time = NOW() WHERE id = ?")->execute([$driver_id]);
    }
}
PHPBLOCK;

// Insert BEFORE the line that includes the header
$insertBefore = "include __DIR__ . '/../includes/header.php';";
if (strpos($src3, $queueBlock) === false) {
    $src3 = str_replace($insertBefore, $queueBlock . "\r\n" . $insertBefore, $src3);
    file_put_contents($file3, $src3);
    echo "✅ driver/dashboard.php patched\n";
} else {
    echo "ℹ️  driver/dashboard.php already patched\n";
}

echo "\nDone. Delete this file after running.\n";


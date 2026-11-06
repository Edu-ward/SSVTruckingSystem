<?php
/**
 * driver/get_queue_status.php
 * Returns the current driver's queue position and total queue size as JSON.
 * Used by the driver dashboard for live polling.
 * Clears/ignores queue entries from previous days.
 */
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$driver_id = (int)$_SESSION['user_id'];

// Check if driver has an active dispatch right now
$activeStmt = $pdo->prepare("
    SELECT id, ticket_number, status, destination
    FROM dispatches
    WHERE driver_id = ?
      AND status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
    LIMIT 1
");
$activeStmt->execute([$driver_id]);
$activeDispatch = $activeStmt->fetch(PDO::FETCH_ASSOC);

if ($activeDispatch) {
    echo json_encode([
        'mode'           => 'dispatched',
        'dispatch_id'    => (int)$activeDispatch['id'],
        'ticket_number'  => $activeDispatch['ticket_number'],
        'status'         => $activeDispatch['status'],
        'destination'    => $activeDispatch['destination'],
    ]);
    exit;
}

// Check if this driver entered the queue TODAY
$myEntryStmt = $pdo->prepare("SELECT queue_entry_time FROM drivers WHERE id = ?");
$myEntryStmt->execute([$driver_id]);
$myEntry = $myEntryStmt->fetchColumn();

// If no queue entry time or entry is from a past day, driver is idle/not in queue today
if (empty($myEntry) || date('Y-m-d', strtotime($myEntry)) !== date('Y-m-d')) {
    echo json_encode([
        'mode'            => 'idle',
        'position'        => null,
        'total'           => 0,
        'queue_entry_time'=> null,
        'drivers_ahead'   => []
    ]);
    exit;
}

// Driver is active in today's queue
// Position = number of idle drivers whose today's entry time is EARLIER than mine + 1
$posStmt = $pdo->prepare("
    SELECT COUNT(*) + 1 AS position
    FROM drivers
    WHERE id != ?
      AND queue_entry_time IS NOT NULL
      AND DATE(queue_entry_time) = CURDATE()
      AND queue_entry_time <= ?
      AND status NOT IN ('Resigned', 'Suspended', 'On Leave')
      AND NOT EXISTS (
          SELECT 1 FROM dispatches d2
          WHERE d2.driver_id = drivers.id
            AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
      )
");
$posStmt->execute([$driver_id, $myEntry]);
$position = (int)($posStmt->fetchColumn() ?: 1);

// Total idle drivers in today's queue
$totalStmt = $pdo->prepare("
    SELECT COUNT(*) FROM drivers
    WHERE status NOT IN ('Resigned', 'Suspended', 'On Leave')
      AND queue_entry_time IS NOT NULL
      AND DATE(queue_entry_time) = CURDATE()
      AND NOT EXISTS (
          SELECT 1 FROM dispatches d2
          WHERE d2.driver_id = drivers.id
            AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
      )
");
$totalStmt->execute();
$total = (int)($totalStmt->fetchColumn() ?: 1);

// Drivers ahead
$aheadStmt = $pdo->prepare("
    SELECT CONCAT(d.first_name, ' ', d.last_name) AS name, d.queue_entry_time
    FROM drivers d
    WHERE d.id != ?
      AND d.queue_entry_time IS NOT NULL
      AND DATE(d.queue_entry_time) = CURDATE()
      AND d.queue_entry_time <= ?
      AND d.status NOT IN ('Resigned', 'Suspended', 'On Leave')
      AND NOT EXISTS (
          SELECT 1 FROM dispatches d2
          WHERE d2.driver_id = d.id
            AND d2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
      )
    ORDER BY d.queue_entry_time ASC
    LIMIT 5
");
$aheadStmt->execute([$driver_id, $myEntry]);
$ahead = $aheadStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'mode'            => 'queued',
    'position'        => $position,
    'total'           => $total,
    'queue_entry_time'=> $myEntry,
    'drivers_ahead'   => $ahead,
]);

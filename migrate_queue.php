<?php
require_once __DIR__ . '/db.php';

try {
    $chkCol = $pdo->query("SHOW COLUMNS FROM `drivers` LIKE 'queue_entry_time'")->fetch();
    if (!$chkCol) {
        $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `queue_entry_time` DATETIME DEFAULT NULL AFTER `truck_id`");
        echo "Column queue_entry_time added to drivers successfully.\n";
    } else {
        echo "Column queue_entry_time already exists in drivers.\n";
    }

    $chkRfid = $pdo->query("SHOW COLUMNS FROM `drivers` LIKE 'rfid_tag'")->fetch();
    if (!$chkRfid) {
        $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `rfid_tag` VARCHAR(100) DEFAULT NULL AFTER `cdl_number`");
        echo "Column rfid_tag added to drivers successfully.\n";
    } else {
        echo "Column rfid_tag already exists in drivers.\n";
    }

    // Populate initial queue_entry_time for active drivers who don't have an active dispatch
    $pdo->exec("
        UPDATE drivers d
        JOIN users u ON u.id = d.id
        SET d.queue_entry_time = COALESCE(
            (SELECT MAX(dt.transit_end_time) FROM driver_trips dt WHERE dt.driver_id = d.id AND dt.status = 'Delivered'),
            (SELECT MAX(disp.transit_end_time) FROM dispatches disp WHERE disp.driver_id = d.id AND disp.status = 'Delivered'),
            u.created_at
        )
        WHERE d.queue_entry_time IS NULL
          AND d.status IN ('Active', 'Idle')
          AND NOT EXISTS (
              SELECT 1 FROM dispatches disp2 
              WHERE disp2.driver_id = d.id 
                AND disp2.status IN ('Pending', 'Dispatched', 'Loading', 'In Transit', 'Unloading', 'Cancellation Requested')
          )
    ");
    echo "Initial queue_entry_time populated.\n";

} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}

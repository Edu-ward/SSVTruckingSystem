<?php
// includes/driver_duty_sync.php
// ==============================================================================
// Daily Driver Duty Status Synchronization
// Lifecycle Rule:
// - A driver's status is 'Off Duty' by default at the start of every day (after 11:59 PM).
// - When the admin creates the first dispatch ticket for the day, the driver becomes
//   'In Transit' while delivering, and 'Active' upon completion/delivery until 11:59 PM.
// - Once the day ends (midnight), any driver without a new dispatch created on that
//   day automatically resets to 'Off Duty'.
// ==============================================================================

if (!function_exists('syncDailyDriverStatuses')) {
    function syncDailyDriverStatuses(PDO $pdo): void {
        static $syncedInCurrentRequest = false;
        if ($syncedInCurrentRequest) return;
        $syncedInCurrentRequest = true;

        try {
            // 1. Reset drivers to 'Off Duty' if they have NO dispatch ticket created today
            //    and have no currently active / in-transit trip.
            $pdo->exec("
                UPDATE drivers
                SET status = 'Off Duty'
                WHERE status IN ('Active', 'In Transit')
                  AND status NOT IN ('Resigned', 'Suspended', 'On Leave')
                  AND NOT EXISTS (
                      SELECT 1 FROM dispatches disp 
                      WHERE disp.driver_id = drivers.id 
                        AND (DATE(disp.created_at) = CURDATE() OR DATE(disp.dispatch_date) = CURDATE())
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM dispatches disp2
                      WHERE disp2.driver_id = drivers.id
                        AND disp2.status IN ('Pending', 'In Transit', 'Loading', 'Unloading', 'Cancellation Requested')
                  )
            ");

            // 2. Ensure drivers who completed/delivered a dispatch today and have no ongoing trip remain 'Active'
            $pdo->exec("
                UPDATE drivers
                SET status = 'Active'
                WHERE status = 'Off Duty'
                  AND EXISTS (
                      SELECT 1 FROM dispatches disp 
                      WHERE disp.driver_id = drivers.id 
                        AND (DATE(disp.created_at) = CURDATE() OR DATE(disp.dispatch_date) = CURDATE())
                        AND disp.status = 'Delivered'
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM dispatches disp2
                      WHERE disp2.driver_id = drivers.id
                        AND disp2.status IN ('Pending', 'In Transit', 'Loading', 'Unloading', 'Cancellation Requested')
                  )
            ");

            // 3. Ensure drivers currently assigned to an ongoing active trip are 'In Transit'
            $pdo->exec("
                UPDATE drivers
                SET status = 'In Transit'
                WHERE status != 'In Transit'
                  AND status NOT IN ('Resigned', 'Suspended', 'On Leave')
                  AND EXISTS (
                      SELECT 1 FROM dispatches disp
                      WHERE disp.driver_id = drivers.id
                        AND disp.status IN ('Pending', 'In Transit', 'Loading', 'Unloading', 'Cancellation Requested')
                  )
            ");
        } catch (Throwable $e) {
            error_log("Error in syncDailyDriverStatuses: " . $e->getMessage());
        }
    }
}

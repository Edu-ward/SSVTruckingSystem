<?php

if (!function_exists('syncDailyDriverStatuses')) {
    function syncDailyDriverStatuses(PDO $pdo): void {
        static $syncedInCurrentRequest = false;
        if ($syncedInCurrentRequest) return;
        $syncedInCurrentRequest = true;

        try {
            
            
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

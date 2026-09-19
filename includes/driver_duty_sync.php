<?php

if (!function_exists('syncDailyDriverStatuses')) {
    function syncDailyDriverStatuses(PDO $pdo): void {
        static $syncedInCurrentRequest = false;
        if ($syncedInCurrentRequest) return;
        $syncedInCurrentRequest = true;

        // Rate-limit: only run once per hour using a temp file lock.
        // This prevents the 3 heavy UPDATE queries from running on every
        // HTTP request (including GPS polls fired every 10s per driver).
        $lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ssv_duty_sync_' . date('YmdH') . '.lock';
        if (file_exists($lockFile)) {
            return; // Already synced this hour
        }
        // Create the lock file; @file_put_contents silently fails on race — acceptable
        @file_put_contents($lockFile, date('c'));
        // Clean up lock files older than 3 hours to avoid accumulation
        foreach (glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ssv_duty_sync_*.lock') as $old) {
            if (filemtime($old) < time() - 10800) {
                @unlink($old);
            }
        }

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
            // On failure, remove lock so next request can retry
            @unlink($lockFile);
        }
    }
}

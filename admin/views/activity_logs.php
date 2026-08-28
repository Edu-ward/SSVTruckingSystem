<div id="view-activity_logs" class="tab-content hidden">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-violet-500"></i>
                <span>Activity Logs</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Audit trail of all system actions across all users.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Role Filter -->
            <select id="activityLogRoleFilter" onchange="filterActivityLogs()" class="input-field !w-full sm:!w-auto text-sm">
                <option value="">All Roles</option>
                <option value="Admin">Admin</option>
                <option value="Driver">Driver</option>
                <option value="Checker">Checker</option>
            </select>
            <!-- Search -->
            <div class="relative w-full sm:w-56">
                <input type="text" id="activityLogSearch" onkeyup="filterActivityLogs()" placeholder="Search logs..." class="input-field !w-full pl-9 text-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
        $totalLogs = count($activityLogs);
        $todayLogs = count(array_filter($activityLogs, fn($l) => date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')));
        $loginLogs = count(array_filter($activityLogs, fn($l) => $l['action'] === 'Login'));
        $failedLogs = count(array_filter($activityLogs, fn($l) => $l['action'] === 'Failed Login'));
        ?>
        <div class="bg-white dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400">
                    <i class="fa-solid fa-list text-sm"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Entries</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= $totalLogs ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fa-solid fa-calendar-day text-sm"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Today</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= $todayLogs ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-right-to-bracket text-sm"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Logins</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= $loginLogs ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Failed Logins</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= $failedLogs ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="activityLogsTable">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700/50">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Timestamp</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    <?php if (empty($activityLogs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-inbox text-3xl mb-3 block"></i>
                                <p class="text-sm font-medium">No activity logs yet</p>
                                <p class="text-xs mt-1">Actions will appear here as users interact with the system.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activityLogs as $log): ?>
                            <?php
                            $roleColor = match($log['role'] ?? '') {
                                'Admin'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'Driver'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                'Checker' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                default   => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            };
                            $actionIcon = match(true) {
                                str_contains($log['action'], 'Login')        => 'fa-right-to-bracket text-emerald-500',
                                str_contains($log['action'], 'Failed')       => 'fa-triangle-exclamation text-rose-500',
                                str_contains($log['action'], 'Logout')       => 'fa-right-from-bracket text-gray-500',
                                str_contains($log['action'], 'Created')      => 'fa-plus text-blue-500',
                                str_contains($log['action'], 'Registered')   => 'fa-user-plus text-indigo-500',
                                str_contains($log['action'], 'Deleted')      => 'fa-trash text-rose-500',
                                str_contains($log['action'], 'Updated')      => 'fa-pen text-amber-500',
                                str_contains($log['action'], 'RFID')         => 'fa-barcode text-violet-500',
                                str_contains($log['action'], 'Settled')      => 'fa-wallet text-green-500',
                                str_contains($log['action'], 'Approved')     => 'fa-check text-emerald-500',
                                str_contains($log['action'], 'Cancelled')    => 'fa-ban text-rose-500',
                                str_contains($log['action'], 'Switched')     => 'fa-arrows-rotate text-cyan-500',
                                str_contains($log['action'], 'Reset')        => 'fa-key text-amber-500',
                                str_contains($log['action'], 'Assigned')     => 'fa-user-tag text-indigo-500',
                                str_contains($log['action'], 'Changed')      => 'fa-lock text-amber-500',
                                str_contains($log['action'], 'Scanned')      => 'fa-qrcode text-violet-500',
                                default                                      => 'fa-circle-info text-gray-400',
                            };
                            ?>
                            <tr class="activity-log-row hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" 
                                data-role="<?= htmlspecialchars($log['role'] ?? '') ?>"
                                data-search="<?= htmlspecialchars(strtolower(($log['username'] ?? '') . ' ' . ($log['action'] ?? '') . ' ' . ($log['details'] ?? ''))) ?>">
                                <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <div class="font-medium text-gray-700 dark:text-gray-300"><?= date('M j, Y', strtotime($log['created_at'])) ?></div>
                                    <div class="text-[11px] text-gray-400"><?= date('g:i:s A', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($log['username'] ?? 'System') ?></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $roleColor ?>">
                                        <?= htmlspecialchars($log['role'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center space-x-1.5 text-sm font-medium text-gray-800 dark:text-gray-200">
                                        <i class="fa-solid <?= $actionIcon ?> text-xs"></i>
                                        <span><?= htmlspecialchars($log['action']) ?></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                                    <?= htmlspecialchars($log['details'] ?? '—') ?>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400 dark:text-gray-500 font-mono">
                                    <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
            <p class="text-xs text-gray-400 dark:text-gray-500">Showing latest <strong><?= count($activityLogs) ?></strong> entries</p>
            <p class="text-xs text-gray-400 dark:text-gray-500" id="activityLogVisibleCount"></p>
        </div>
    </div>

</div>

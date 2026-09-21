<?php

if (!function_exists('get_action_category')) {
    require_once __DIR__ . '/../../includes/activity_log.php';
}

$exportQuery = http_build_query([
    'tab'             => 'activity_logs',
    'action'          => 'export_csv',
    'log_search'      => $logSearch,
    'log_role'        => $logRole,
    'log_category'    => $logCategory,
    'log_date_preset' => $logDatePreset,
    'log_date_from'   => $logDateFrom,
    'log_date_to'     => $logDateTo,
    'log_limit'       => $logLimit
]);
?>
<div id="view-activity_logs" class="tab-content hidden">

    
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center space-x-2.5">
                <span class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 flex items-center justify-center text-base">
                    <i class="fa-solid fa-clipboard-list"></i>
                </span>
                <span>System Audit & Activity Logs</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Immutable, timestamped audit trail of all actions, dispatches, security events, and modifications across the system.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="dashboard.php?<?= $exportQuery ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold shadow-xs transition hover:border-gray-300" title="Export currently filtered audit logs to CSV format">
                <i class="fa-solid fa-file-csv text-emerald-600 dark:text-emerald-400 text-sm"></i>
                <span>Export CSV</span>
            </a>
            <button type="button" onclick="window.location.href='dashboard.php?tab=activity_logs'" class="inline-flex items-center gap-1.5 px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition" title="Refresh and reset all filters">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 shadow-xs">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center text-violet-600 dark:text-violet-400 text-base flex-shrink-0">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Audit Logs</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-0.5"><?= number_format($statTotalLogs) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 shadow-xs">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-base flex-shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Logged Today</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-0.5"><?= number_format($statTodayLogs) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 shadow-xs">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-base flex-shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Security & Auth</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-0.5"><?= number_format($statSecurityLogs) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 shadow-xs">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400 text-base flex-shrink-0">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Dispatches & Trips</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-0.5"><?= number_format($statDispatchLogs) ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-4 scrollbar-thin">
        <?php
        $categories = [
            ''           => ['label' => 'All Categories', 'icon' => 'fa-layer-group'],
            'Security'   => ['label' => 'Security',       'icon' => 'fa-shield-halved'],
            'Dispatches' => ['label' => 'Dispatches',     'icon' => 'fa-truck-fast'],
            'Fleet'      => ['label' => 'Fleet',          'icon' => 'fa-truck'],
            'Personnel'  => ['label' => 'Personnel',      'icon' => 'fa-users'],
            'Payroll'    => ['label' => 'Payroll',        'icon' => 'fa-money-bill-wave'],
            'Orders'     => ['label' => 'Orders',         'icon' => 'fa-file-invoice'],
            'System'     => ['label' => 'System',         'icon' => 'fa-gears'],
        ];
        foreach ($categories as $catKey => $catMeta):
            $isActive = ($logCategory === $catKey);
            $pillClass = $isActive 
                ? 'bg-violet-600 text-white shadow-xs font-bold ring-2 ring-violet-400' 
                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 font-medium';
        ?>
            <button type="button" data-cat="<?= $catKey ?>" onclick="setActivityCategory('<?= $catKey ?>')" class="cat-pill-btn px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition whitespace-nowrap <?= $pillClass ?>">
                <i class="fa-solid <?= $catMeta['icon'] ?> text-[11px] <?= $isActive ? 'text-white' : 'text-gray-400' ?>"></i>
                <span><?= $catMeta['label'] ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    
    <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs p-4 sm:p-5 mb-6">
        <form method="GET" action="dashboard.php" id="activityLogServerForm" class="space-y-4">
            <input type="hidden" name="tab" value="activity_logs">
            <input type="hidden" name="log_category" id="logCategoryInput" value="<?= htmlspecialchars($logCategory) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <div class="lg:col-span-2 relative">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Search Keywords</label>
                    <div class="relative">
                        <input type="text" name="log_search" id="activityLogSearchInput" value="<?= htmlspecialchars($logSearch) ?>" onkeyup="filterActivityLogsClient()" placeholder="Action, details, user, IP..." class="w-full pl-9 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white dark:focus:bg-gray-700 transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">User Role</label>
                    <select name="log_role" id="activityLogRoleFilter" onchange="filterActivityLogsClient()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="">All Roles</option>
                        <option value="Superadmin" <?= $logRole === 'Superadmin' ? 'selected' : '' ?>>Superadmin</option>
                        <option value="Admin" <?= $logRole === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="Driver" <?= $logRole === 'Driver' ? 'selected' : '' ?>>Driver</option>
                        <option value="Checker" <?= $logRole === 'Checker' ? 'selected' : '' ?>>Checker</option>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Time Range</label>
                    <select name="log_date_preset" id="activityLogDatePreset" onchange="handleDatePresetChange(this.value)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="" <?= empty($logDatePreset) && empty($logDateFrom) ? 'selected' : '' ?>>All Time</option>
                        <option value="today" <?= $logDatePreset === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="yesterday" <?= $logDatePreset === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                        <option value="week" <?= $logDatePreset === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month" <?= $logDatePreset === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="custom" <?= (!empty($logDateFrom) && empty($logDatePreset)) || $logDatePreset === 'custom' ? 'selected' : '' ?>>Custom Range...</option>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Row Limit</label>
                    <select name="log_limit" id="activityLogLimit" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="50" <?= $logLimit == 50 ? 'selected' : '' ?>>50 Rows</option>
                        <option value="100" <?= $logLimit == 100 ? 'selected' : '' ?>>100 Rows</option>
                        <option value="250" <?= $logLimit == 250 ? 'selected' : '' ?>>250 Rows</option>
                        <option value="500" <?= $logLimit == 500 ? 'selected' : '' ?>>500 Rows</option>
                    </select>
                </div>
            </div>

            
            <div id="customDateRangeRow" class="<?= (!empty($logDateFrom) && empty($logDatePreset)) || $logDatePreset === 'custom' ? '' : 'hidden' ?> pt-2 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">From:</span>
                    <input type="date" name="log_date_from" id="logDateFromInput" value="<?= htmlspecialchars($logDateFrom) ?>" onchange="filterActivityLogsClient()" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">To:</span>
                    <input type="date" name="log_date_to" id="logDateToInput" value="<?= htmlspecialchars($logDateTo) ?>" onchange="filterActivityLogsClient()" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs">
                </div>
                <button type="button" onclick="filterActivityLogsClient()" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-xs font-semibold transition">Filter Dates</button>
            </div>

            
            <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                <div class="text-gray-500 dark:text-gray-400">
                    <?php if ($logSearch !== '' || $logRole !== '' || $logCategory !== '' || $logDateFrom !== '' || $logDatePreset !== ''): ?>
                        <span class="inline-flex items-center gap-1.5 text-violet-600 dark:text-violet-400 font-semibold">
                            <i class="fa-solid fa-filter"></i> Filters active: 
                            <?= $logCategory ? htmlspecialchars($logCategory) . ' • ' : '' ?>
                            <?= $logRole ? htmlspecialchars($logRole) . ' • ' : '' ?>
                            <?= $logSearch ? '"' . htmlspecialchars($logSearch) . '"' : '' ?>
                        </span>
                        <a href="dashboard.php?tab=activity_logs" class="ml-2 text-rose-500 hover:underline font-semibold">Clear Filters</a>
                    <?php else: ?>
                        <span>Displaying latest system events in reverse chronological order</span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        <i class="fa-solid fa-filter mr-1"></i> Apply Filter
                    </button>
                    <a href="dashboard.php?tab=activity_logs" class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-medium transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    
    <div class="bg-white dark:bg-gray-800/70 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs border-collapse" id="activityLogsTable">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/90 border-b border-gray-100 dark:border-gray-700/70 text-gray-400 uppercase text-[10px] tracking-wider font-semibold">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Details</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4 text-right">Inspect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-300">
                    <?php if (empty($activityLogs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-14 text-gray-400 dark:text-gray-500">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="fa-solid fa-inbox"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">No activity logs found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or keyword query above.</p>
                                <a href="dashboard.php?tab=activity_logs" class="inline-block mt-3 px-3.5 py-1.5 rounded-lg bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 text-xs font-semibold hover:bg-violet-100 transition">
                                    Reset All Filters
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activityLogs as $log): ?>
                            <?php
                            $logCat = get_action_category($log['action'] ?? '');
                            $roleColor = match($log['role'] ?? '') {
                                'Superadmin' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                'Admin'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                'Driver'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'Checker'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                default      => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            };
                            $catBadge = match($logCat) {
                                'Security'   => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40',
                                'Dispatches' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40',
                                'Fleet'      => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40',
                                'Personnel'  => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40',
                                'Payroll'    => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40',
                                'Orders'     => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/40',
                                default      => 'bg-gray-50 text-gray-600 dark:bg-gray-750 dark:text-gray-400 border border-gray-200 dark:border-gray-700',
                            };
                            $actionIcon = match(true) {
                                str_contains($log['action'], 'Login')        => 'fa-right-to-bracket text-emerald-500',
                                str_contains($log['action'], 'Failed')       => 'fa-triangle-exclamation text-rose-500',
                                str_contains($log['action'], 'Logout')       => 'fa-right-from-bracket text-gray-400',
                                str_contains($log['action'], 'Created')      => 'fa-plus text-blue-500',
                                str_contains($log['action'], 'Registered')   => 'fa-user-plus text-indigo-500',
                                str_contains($log['action'], 'Deleted')      => 'fa-trash text-rose-500',
                                str_contains($log['action'], 'Updated')      => 'fa-pen text-amber-500',
                                str_contains($log['action'], 'RFID')         => 'fa-barcode text-violet-500',
                                str_contains($log['action'], 'Settled')      => 'fa-wallet text-emerald-500',
                                str_contains($log['action'], 'Approved')     => 'fa-circle-check text-emerald-500',
                                str_contains($log['action'], 'Cancelled')    => 'fa-ban text-rose-500',
                                str_contains($log['action'], 'Switched')     => 'fa-arrows-rotate text-cyan-500',
                                str_contains($log['action'], 'Reset')        => 'fa-key text-amber-500',
                                default                                      => 'fa-circle-info text-gray-400',
                            };
                            $logJson = htmlspecialchars(json_encode([
                                'id'         => $log['id'],
                                'created_at' => $log['created_at'],
                                'username'   => $log['username'] ?? 'System',
                                'role'       => $log['role'] ?? 'Unknown',
                                'category'   => $logCat,
                                'action'     => $log['action'],
                                'details'    => $log['details'] ?? '',
                                'ip_address' => $log['ip_address'] ?? '—'
                            ]), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr class="activity-log-row hover:bg-violet-50/40 dark:hover:bg-gray-750 transition-colors cursor-pointer group"
                                onclick="openAuditInspectorModal(<?= $logJson ?>)"
                                data-role="<?= htmlspecialchars($log['role'] ?? '') ?>"
                                data-category="<?= htmlspecialchars($logCat) ?>"
                                data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>"
                                data-search="<?= htmlspecialchars(strtolower(($log['username'] ?? '') . ' ' . ($log['action'] ?? '') . ' ' . ($log['details'] ?? '') . ' ' . ($log['ip_address'] ?? ''))) ?>">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 dark:text-gray-100"><?= date('M j, Y', strtotime($log['created_at'])) ?></div>
                                    <div class="text-[10px] text-gray-400"><?= date('g:i:s A', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center text-[10px] font-bold">
                                            <?= strtoupper(substr($log['username'] ?? 'S', 0, 1)) ?>
                                        </div>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($log['username'] ?? 'System') ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold <?= $roleColor ?>">
                                        <?= htmlspecialchars($log['role'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium <?= $catBadge ?>">
                                        <?= htmlspecialchars($logCat) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 font-bold text-gray-900 dark:text-gray-100">
                                        <i class="fa-solid <?= $actionIcon ?> text-[11px]"></i>
                                        <span><?= htmlspecialchars($log['action']) ?></span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-sm truncate text-gray-600 dark:text-gray-300" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                                    <?= htmlspecialchars($log['details'] ?? '—') ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono text-[11px] text-gray-400 dark:text-gray-500">
                                    <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openAuditInspectorModal(<?= $logJson ?>)" class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/40 transition" title="Inspect Record Details">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="px-5 py-3 bg-gray-50/70 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Loaded <strong><?= count($activityLogs) ?></strong> records (Limit: <?= $logLimit ?>)
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500" id="activityLogVisibleCount">
                Click any row to inspect complete event payload
            </p>
        </div>
    </div>
</div>

<div id="auditInspectorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-xl overflow-hidden relative max-h-[90vh] flex flex-col">
        
        <div class="p-5 border-b border-gray-100 dark:border-gray-700/80 flex justify-between items-center flex-shrink-0 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 flex items-center justify-center flex-shrink-0 text-base">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100" id="ai_action">Event Details</h3>
                        <span id="ai_category_badge" class="px-2 py-0.5 text-[10px] font-bold rounded-full">--</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Log Record #<span id="ai_id" class="font-mono font-bold"></span> &bull; <span id="ai_relative_time"></span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('auditInspectorModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>

        
        <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm">
            
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Initiated By</div>
                    <div class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5" id="ai_user">--</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Actor Role</div>
                    <div class="font-bold text-gray-900 dark:text-gray-100" id="ai_role">--</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Timestamp</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100" id="ai_timestamp">--</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Client IP Address</div>
                    <div class="font-mono font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between">
                        <span id="ai_ip">--</span>
                        <button type="button" onclick="copyAiIp()" class="text-gray-400 hover:text-violet-600 ml-2 text-xs" title="Copy IP">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fa-solid fa-align-left text-violet-500 mr-1"></i> Recorded Event Details
                    </span>
                    <button type="button" onclick="copyAiDetails()" class="text-xs text-violet-600 dark:text-violet-400 hover:underline font-semibold flex items-center gap-1">
                        <i class="fa-regular fa-copy"></i> Copy Text
                    </button>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/80 rounded-xl border border-gray-200 dark:border-gray-700 font-mono text-xs text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap break-words" id="ai_details_text">
                    --
                </div>
            </div>
        </div>

        
        <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700/80 flex justify-end flex-shrink-0">
            <button type="button" onclick="toggleModal('auditInspectorModal', false)" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-650 border border-gray-200 dark:border-gray-600 transition">
                Close
            </button>
        </div>
    </div>
</div>

<div id="view-reports" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm">
                <i class="fa-solid fa-chart-column"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Analytics & Reports</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Monthly breakdown, volume stats, and historical archives</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Historical Month Selector -->
            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/60 p-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                <label for="reportMonthSelect" class="text-xs font-semibold text-gray-500 dark:text-gray-400 pl-2 flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar-days text-blue-500"></i>
                    <span class="hidden sm:inline">Report Month:</span>
                </label>
                <select id="reportMonthSelect" onchange="window.location.href='dashboard.php?tab=reports&report_month=' + this.value" class="border-0 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 text-xs font-bold py-1.5 px-3 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <?php if (!empty($availableReportMonths)): ?>
                        <?php foreach ($availableReportMonths as $ymKey => $ymLabel): ?>
                            <option value="<?= htmlspecialchars($ymKey); ?>" <?= ($ymKey === $currMonthStr) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($ymLabel); ?><?= ($ymKey === date('Y-m')) ? ' (Current)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Export Form -->
            <form class="flex flex-wrap items-center gap-2" action="export_reports.php" method="GET">
                <select name="period" id="exportPeriodSelect" onchange="document.getElementById('custom-date-range').classList.toggle('hidden', this.value !== 'custom');" class="border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer">
                    <option value="month" selected>Selected Month (<?= htmlspecialchars($currMonthLabel ?? 'This Month'); ?>)</option>
                    <option value="all">All Time</option>
                    <option value="weekly">This Week</option>
                    <option value="yearly">This Year</option>
                    <option value="custom">Custom Date Range...</option>
                </select>
                <input type="hidden" name="month" value="<?= htmlspecialchars($currMonthStr ?? date('Y-m')); ?>">

                <div id="custom-date-range" class="hidden flex flex-wrap items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1 bg-white dark:bg-gray-800 shadow-sm transition-all duration-300">
                    <div class="flex items-center">
                        <label for="start_date" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mr-2 cursor-pointer">From</label>
                        <input type="date" id="start_date" name="start_date" class="bg-transparent text-xs font-medium text-gray-700 dark:text-gray-200 border-none focus:ring-0 p-1 outline-none cursor-pointer">
                    </div>
                    <div class="hidden sm:block w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="flex items-center">
                        <label for="end_date" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mx-2 cursor-pointer">To</label>
                        <input type="date" id="end_date" name="end_date" class="bg-transparent text-xs font-medium text-gray-700 dark:text-gray-200 border-none focus:ring-0 p-1 outline-none cursor-pointer">
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center justify-center space-x-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Export CSV</span>
                </button>
            </form>
        </div>
    </div>

    <?php if (!empty($isHistoricalReport)): ?>
        <div class="p-3.5 mb-6 bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-transparent border border-blue-200 dark:border-blue-800/60 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3 text-xs">
                <div class="w-8 h-8 rounded-lg bg-blue-500 text-white flex items-center justify-center text-sm flex-shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <span class="font-bold text-gray-900 dark:text-gray-100">Viewing Archived Report:</span>
                    <span class="text-blue-600 dark:text-blue-400 font-extrabold ml-1"><?= htmlspecialchars($currMonthLabel ?? ''); ?></span>
                    <span class="text-gray-500 dark:text-gray-400 ml-1">(Comparing with <?= htmlspecialchars($lastMonthLabel ?? ''); ?>)</span>
                </div>
            </div>
            <a href="dashboard.php?tab=reports" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition self-start sm:self-auto">
                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                <span>Return to Current Month</span>
            </a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
        <?php foreach ($reportKpis as $kpi): ?>
            <div class="<?= $kpi['color_class']; ?> rounded-xl p-6 text-white relative overflow-hidden shadow-md">
                <div class="text-sm text-white text-opacity-80 mb-1"><?= htmlspecialchars($kpi['title']); ?></div>
                <div class="text-4xl font-bold mb-1"><?= htmlspecialchars($kpi['value']); ?></div>
                <div class="text-xs text-white text-opacity-90"><?= htmlspecialchars($kpi['subtext']); ?></div>
                <i class="fa-solid <?= $kpi['icon_class']; ?> absolute right-4 bottom-4 text-6xl text-white opacity-20"></i>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-2 mb-4">
                <i class="fa-solid fa-truck-fast text-orange-500"></i>
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Monthly Delivered Trips</h3>
            </div>
            <canvas id="deliveredTripsReportChart" height="160"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-2 mb-4">
                <i class="fa-solid fa-wallet text-green-500"></i>
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Driver Payroll Analysis (₱)</h3>
            </div>
            <canvas id="driverPayrollReportChart" height="160"></canvas>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Performance Summary</h3>
            <span class="text-xs font-semibold text-gray-400">Comparing <?= htmlspecialchars($currMonthLabel ?? 'This Month'); ?> vs <?= htmlspecialchars($lastMonthLabel ?? 'Last Month'); ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-semibold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Metric</th>
                        <th class="px-6 py-4"><?= htmlspecialchars($currMonthLabel ?? 'This Month'); ?></th>
                        <th class="px-6 py-4"><?= htmlspecialchars($lastMonthLabel ?? 'Last Month'); ?></th>
                        <th class="px-6 py-4">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($performanceMetrics as $metric):
                        $colorClass = $metric['is_positive'] ? 'text-green-500' : 'text-red-500';
                    ?>
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($metric['metric']); ?></td>
                            <td class="px-6 py-4 font-bold"><?= htmlspecialchars($metric['this_month']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($metric['last_month']); ?></td>
                            <td class="px-6 py-4 <?= $colorClass; ?> font-medium"><?= htmlspecialchars($metric['change_str']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($monthlyArchive)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-blue-500"></i>
                        <span>Historical Monthly Reports Archive</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Summary of deliveries, volume, driver payroll, and efficiency across past months</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg self-start sm:self-auto">
                    <?= count($monthlyArchive); ?> Month<?= count($monthlyArchive) === 1 ? '' : 's'; ?> Available
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-semibold border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">Month Period</th>
                            <th class="px-6 py-4">Deliveries</th>
                            <th class="px-6 py-4">Volume Delivered</th>
                            <th class="px-6 py-4">Driver Payroll</th>
                            <th class="px-6 py-4">On-Time Rate</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($monthlyArchive as $ma): 
                            if (empty($ma['deliveries']) || $ma['deliveries'] <= 0) continue;
                            $isSelected = ($ma['ym'] === $currMonthStr);
                            $isCurrent = ($ma['ym'] === date('Y-m'));
                        ?>
                            <tr class="<?= $isSelected ? 'bg-blue-50/70 dark:bg-blue-950/30 font-medium' : 'hover:bg-gray-50/80 dark:hover:bg-gray-700/50' ?> transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar text-gray-400"></i>
                                        <span class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($ma['label']); ?></span>
                                        <?php if ($isCurrent): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-md">Current</span>
                                        <?php endif; ?>
                                        <?php if ($isSelected && !$isCurrent): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-md">Active View</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-200"><?= number_format($ma['deliveries']); ?></td>
                                <td class="px-6 py-4"><?= number_format($ma['volume_cm'], 2); ?> cu.m</td>
                                <td class="px-6 py-4 font-semibold text-emerald-600 dark:text-emerald-400">₱<?= number_format($ma['payroll'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($ma['deliveries']) && $ma['deliveries'] > 0 && isset($ma['on_time_pct']) && $ma['on_time_pct'] !== null): ?>
                                        <span class="inline-flex items-center gap-1 font-semibold <?= $ma['on_time_pct'] >= 90 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' ?>">
                                            <i class="fa-solid fa-circle-check text-xs"></i>
                                            <?= number_format($ma['on_time_pct'], 1); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 font-bold">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (!$isSelected): ?>
                                            <a href="dashboard.php?tab=reports&report_month=<?= urlencode($ma['ym']); ?>" 
                                               class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-lg transition inline-flex items-center gap-1">
                                                <i class="fa-solid fa-chart-simple text-[10px]"></i>
                                                <span>View Report</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                <span>Viewing</span>
                                            </span>
                                        <?php endif; ?>
                                        <a href="export_reports.php?period=month&month=<?= urlencode($ma['ym']); ?>" 
                                           title="Export CSV for <?= htmlspecialchars($ma['label']); ?>"
                                           class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
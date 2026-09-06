<div id="view-dashboard" class="tab-content block">
    <?php if (($pendingCashAdvanceCount ?? 0) > 0): ?>
    <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/5 dark:from-amber-950/40 dark:via-orange-950/20 dark:to-transparent border border-amber-300/80 dark:border-amber-700/60 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-md shadow-amber-500/30">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm">
                    <?= $pendingCashAdvanceCount ?> Driver Cash Advance Request<?= $pendingCashAdvanceCount > 1 ? 's' : '' ?> Pending Review
                </h4>
                <p class="text-xs text-amber-800 dark:text-amber-300/90 mt-0.5">
                    A total of ₱<?= number_format(array_sum(array_column($pendingCashAdvances ?? [], 'amount')), 2) ?> is awaiting your approval before payroll deduction.
                </p>
            </div>
        </div>
        <button onclick="switchTab('cash_advances')" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition flex items-center gap-1.5 flex-shrink-0 active:scale-95 cursor-pointer">
            <span>Review Requests</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Top Stat Cards (Glassmorphism & Gradients) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-xl shadow-blue-500/20 hover:-translate-y-1 transition-all duration-300 group border border-white/10">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-200">Total Fleet</span>
                    <div class="text-4xl font-extrabold mt-2 mb-1 tracking-tight" data-counter="<?= $totalFleet ?? 0; ?>">
                        <?= $totalFleet ?? 0; ?>
                    </div>
                    <span class="inline-flex items-center text-xs font-medium text-blue-100 bg-white/10 px-2.5 py-0.5 rounded-full backdrop-blur-md">
                        <i class="fa-solid fa-shield-halved mr-1 text-[10px]"></i> Registered Trucks
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <i class="fa-solid fa-truck absolute -right-6 -bottom-6 text-8xl text-white/5 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 p-6 text-white shadow-xl shadow-emerald-500/20 hover:-translate-y-1 transition-all duration-300 group border border-white/10">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Active Now</span>
                    <div class="text-4xl font-extrabold mt-2 mb-1 tracking-tight" data-counter="<?= $activeNow ?? 0; ?>">
                        <?= $activeNow ?? 0; ?>
                    </div>
                    <span class="inline-flex items-center text-xs font-medium text-emerald-100 bg-white/10 px-2.5 py-0.5 rounded-full backdrop-blur-md">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-ping mr-1.5"></span> On the Road
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>
            <i class="fa-solid fa-arrow-trend-up absolute -right-6 -bottom-6 text-8xl text-white/5 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 text-white shadow-xl shadow-amber-500/20 hover:-translate-y-1 transition-all duration-300 group border border-white/10">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-200">Completed Today</span>
                    <div class="text-4xl font-extrabold mt-2 mb-1 tracking-tight" data-counter="<?= $completedToday ?? 0; ?>">
                        <?= $completedToday ?? 0; ?>
                    </div>
                    <span class="inline-flex items-center text-xs font-medium text-amber-100 bg-white/10 px-2.5 py-0.5 rounded-full backdrop-blur-md">
                        <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Dispatches Delivered
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
            </div>
            <i class="fa-solid fa-box-check absolute -right-6 -bottom-6 text-8xl text-white/5 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-500"></i> Weekly Dispatch Activity
                </h3>
                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Past 7 Days</span>
            </div>
            <canvas id="weeklyChart" height="200"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-indigo-500"></i> Fleet Status Distribution
                </h3>
                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Realtime breakdown</span>
            </div>
            <div class="w-full flex justify-center">
                <div style="width: 380px; max-width: 100%;"><canvas id="fleetChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Efficiency & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high text-emerald-500"></i> Delivery Efficiency Trend
                </h3>
                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Target vs Actual</span>
            </div>
            <canvas id="efficiencyChart" height="120"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-6 hover:shadow-md transition">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i> Operational Quick Stats
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3.5 bg-blue-50/70 dark:bg-gray-700/50 rounded-xl border border-blue-100 dark:border-gray-700">
                    <div class="flex items-center text-blue-700 dark:text-blue-300 font-medium text-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center mr-3 text-blue-600 dark:text-blue-400">
                            <i class="fa-solid fa-truck text-sm"></i>
                        </div>
                        Idle Trucks
                    </div>
                    <div class="font-extrabold text-blue-700 dark:text-blue-300 text-lg">
                        <?= $idleTrucks ?? 0; ?>
                    </div>
                </div>
                <div class="flex justify-between items-center p-3.5 bg-emerald-50/70 dark:bg-gray-700/50 rounded-xl border border-emerald-100 dark:border-gray-700">
                    <div class="flex items-center text-emerald-700 dark:text-emerald-300 font-medium text-sm">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center mr-3 text-emerald-600 dark:text-emerald-400">
                            <i class="fa-regular fa-circle-check text-sm"></i>
                        </div>
                        On-Time Rate
                    </div>
                    <div class="font-extrabold text-emerald-700 dark:text-emerald-300 text-lg">
                        <?= (isset($onTimeRate) && $onTimeRate !== null) ? (number_format($onTimeRate, 1) . '%') : '-'; ?>
                    </div>
                </div>
                <div class="flex justify-between items-center p-3.5 bg-purple-50/70 dark:bg-gray-700/50 rounded-xl border border-purple-100 dark:border-gray-700">
                    <div class="flex items-center text-purple-700 dark:text-purple-300 font-medium text-sm">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center mr-3 text-purple-600 dark:text-purple-400">
                            <i class="fa-solid fa-wifi text-sm"></i>
                        </div>
                        RFID Readers Active
                    </div>
                    <div class="font-extrabold text-purple-700 dark:text-purple-300 text-lg">
                        <?= $rfidActive ?? 0; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-gray-400"></i> Recent Dispatch Activity
            </h3>
            <button onclick="switchTab('dispatches')" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                View All <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
            </button>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700/80">
            <?php foreach ($recentDispatches as $dispatch):
                $chipStyle = 'chip-blue';
                if ($dispatch['status'] == 'Pending') $chipStyle = 'chip-amber';
                if ($dispatch['status'] == 'Delivered') $chipStyle = 'chip-emerald';
            ?>
                <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 flex-shrink-0">
                            <i class="fa-solid fa-ticket text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate">
                                <?= htmlspecialchars($dispatch['ticket_number']); ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate">
                                <?= htmlspecialchars($dispatch['truck_code']) . ' • ' . htmlspecialchars($dispatch['driver_name']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-1">
                        <span class="<?= $chipStyle; ?>">
                            <?= htmlspecialchars($dispatch['status']); ?>
                        </span>
                        <div class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate max-w-[200px] sm:max-w-none"><i class="fa-solid fa-location-dot text-[10px] mr-1"></i><?= htmlspecialchars($dispatch['destination']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
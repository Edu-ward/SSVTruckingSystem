<div id="view-dashboard" class="tab-content block">
    <div class="grid grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-500 rounded-xl p-6 text-white relative overflow-hidden shadow">
            <div class="text-sm text-blue-100 mb-1">Total Fleet</div>
            <div class="text-4xl font-bold mb-1">
                <?= $totalFleet; ?>
            </div>
            <div class="text-xs text-blue-100">Trucks</div>
            <i class="fa-solid fa-truck absolute right-4 bottom-4 text-6xl text-blue-400 opacity-50"></i>
        </div>
        <div class="bg-green-500 rounded-xl p-6 text-white relative overflow-hidden shadow">
            <div class="text-sm text-green-100 mb-1">Active Now</div>
            <div class="text-4xl font-bold mb-1">
                <?= $activeNow; ?>
            </div>
            <div class="text-xs text-green-100">On the road</div>
            <i class="fa-solid fa-arrow-trend-up absolute right-4 bottom-4 text-6xl text-green-400 opacity-50"></i>
        </div>
        <div class="bg-orange-500 rounded-xl p-6 text-white relative overflow-hidden shadow">
            <div class="text-sm text-orange-100 mb-1">In Progress</div>
            <div class="text-4xl font-bold mb-1">
                <?= $inProgress; ?>
            </div>
            <div class="text-xs text-orange-100">Active dispatches</div>
            <i class="fa-regular fa-clock absolute right-4 bottom-4 text-6xl text-orange-400 opacity-50"></i>
        </div>
        <div class="bg-teal-500 rounded-xl p-6 text-white relative overflow-hidden shadow">
            <div class="text-sm text-teal-100 mb-1">Completed Today</div>
            <div class="text-4xl font-bold mb-1">
                <?= $completedToday; ?>
            </div>
            <div class="text-xs text-teal-100">Deliveries</div>
            <i class="fa-regular fa-circle-check absolute right-4 bottom-4 text-6xl text-teal-400 opacity-50"></i>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Weekly Dispatch Activity</h3>
            <canvas id="weeklyChart" height="200"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Fleet Status Distribution</h3>
            <div class="w-full flex justify-center">
                <div style="width: 400px;"><canvas id="fleetChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Delivery Efficiency Trend</h3>
            <canvas id="efficiencyChart" height="120"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Stats</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg dark:bg-gray-700">
                    <div class="flex items-center text-blue-700 font-medium">
                        <i class="fa-solid fa-truck text-blue-500 w-6"></i> Idle Trucks
                    </div>
                    <div class="font-bold text-blue-700">
                        <?= $idleTrucks; ?>
                    </div>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg dark:bg-gray-700">
                    <div class="flex items-center text-green-700 font-medium">
                        <i class="fa-regular fa-circle-check text-green-500 w-6"></i> On-Time Rate
                    </div>
                    <div class="font-bold text-green-700">
                        <?= $onTimeRate; ?>%
                    </div>
                </div>
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg dark:bg-gray-700">
                    <div class="flex items-center text-purple-700 font-medium">
                        <i class="fa-solid fa-wifi text-purple-500 w-6"></i> RFID Active
                    </div>
                    <div class="font-bold text-purple-700">
                        <?= $rfidActive; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Recent Dispatch Activity</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <?php foreach ($recentDispatches as $dispatch):
                $dotColor = 'bg-blue-500';
                if ($dispatch['status'] == 'Pending') $dotColor = 'bg-yellow-400';
                if ($dispatch['status'] == 'Delivered') $dotColor = 'bg-green-500';
            ?>
                <div class="p-4 px-6 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">
                    <div class="flex items-center space-x-4">
                        <div class="w-2.5 h-2.5 rounded-full <?= $dotColor; ?>">
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                <?= htmlspecialchars($dispatch['ticket_number']); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?= htmlspecialchars($dispatch['truck_code']) . ' - ' . htmlspecialchars($dispatch['driver_name']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($dispatch['status']); ?></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($dispatch['destination']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
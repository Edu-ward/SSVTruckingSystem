<div id="view-reports" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-4 flex justify-between items-center mb-6">
        <div class="flex items-center space-x-2 text-lg font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-chart-column text-gray-600 dark:text-gray-300 w-5"></i>
            <span>Analytics & Reports</span>
        </div>
        <div class="flex space-x-3">
            <select class="border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 focus:outline-none">
                <option>Monthly</option>
                <option>Weekly</option>
                <option>Yearly</option>
            </select>
            <button class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <i class="fa-solid fa-download"></i>
                <span>Export Report</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-6 mb-6">
        <?php foreach ($reportKpis as $kpi): ?>
            <div class="<?= $kpi['color_class']; ?> rounded-xl p-6 text-white relative overflow-hidden shadow-md">
                <div class="text-sm text-white text-opacity-80 mb-1"><?= htmlspecialchars($kpi['title']); ?></div>
                <div class="text-4xl font-bold mb-1"><?= htmlspecialchars($kpi['value']); ?></div>
                <div class="text-xs text-white text-opacity-90"><?= htmlspecialchars($kpi['subtext']); ?></div>
                <i class="fa-solid <?= $kpi['icon_class']; ?> absolute right-4 bottom-4 text-6xl text-white opacity-20"></i>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700 mb-6">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Revenue & Profit Analysis</h3>
        <canvas id="revenueReportChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Delivery Performance</h3>
            <canvas id="deliveryReportChart" height="150"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Fuel Consumption Trend</h3>
            <canvas id="fuelReportChart" height="150"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Performance Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-semibold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Metric</th>
                        <th class="px-6 py-4">This Month</th>
                        <th class="px-6 py-4">Last Month</th>
                        <th class="px-6 py-4">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($performanceMetrics as $metric):
                        $colorClass = $metric['is_positive'] ? 'text-green-500' : 'text-red-500';
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">
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
</div>
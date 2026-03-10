<div id="view-reports" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow border border-gray-100 p-4 flex justify-between items-center mb-6">
        <div class="flex items-center space-x-2 text-lg font-bold text-gray-800">
            <i class="fa-solid fa-chart-column text-gray-600 w-5"></i>
            <span>Analytics & Reports</span>
        </div>
        <div class="flex space-x-3">
            <select class="border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 focus:outline-none">
                <option>Monthly</option>
                <option>Weekly</option>
                <option>Yearly</option>
            </select>
            <button class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <i class="fa-solid fa-download"></i><span>Export Report</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-6 mb-6">
        <?php foreach ($reportKpis as $kpi): ?>
            <div class="<?php echo $kpi['color_class']; ?> rounded-xl p-6 text-white relative overflow-hidden shadow-md">
                <div class="text-sm text-white text-opacity-80 mb-1"><?php echo htmlspecialchars($kpi['title']); ?></div>
                <div class="text-4xl font-bold mb-1"><?php echo htmlspecialchars($kpi['value']); ?></div>
                <div class="text-xs text-white text-opacity-90"><?php echo htmlspecialchars($kpi['subtext']); ?></div>
                <i class="fa-solid <?php echo $kpi['icon_class']; ?> absolute right-4 bottom-4 text-6xl text-white opacity-20"></i>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-xl shadow p-6 border border-gray-100 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Revenue & Profit Analysis</h3>
        <canvas id="revenueReportChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Delivery Performance</h3>
            <canvas id="deliveryReportChart" height="150"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Fuel Consumption Trend</h3>
            <canvas id="fuelReportChart" height="150"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Performance Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Metric</th>
                        <th class="px-6 py-4">This Month</th>
                        <th class="px-6 py-4">Last Month</th>
                        <th class="px-6 py-4">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($performanceMetrics as $metric):
                        $colorClass = $metric['is_positive'] ? 'text-green-500' : 'text-red-500';
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($metric['metric']); ?></td>
                            <td class="px-6 py-4 font-bold"><?php echo htmlspecialchars($metric['this_month']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($metric['last_month']); ?></td>
                            <td class="px-6 py-4 <?php echo $colorClass; ?> font-medium"><?php echo htmlspecialchars($metric['change_str']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
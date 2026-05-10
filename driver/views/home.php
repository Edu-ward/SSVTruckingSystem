        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-[#4a8df8] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium mb-1">This Week's Earnings</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($weekly_salary, 2); ?></h3>
                    <p class="text-blue-100 text-sm mt-2">Current week (Mon-Sun)</p>
                </div>
                <i class="fa-solid fa-calendar-week absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#2ccb72] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-green-100 text-sm font-medium mb-1">This Month's Earnings</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($monthly_salary, 2); ?></h3>
                    <p class="text-green-100 text-sm mt-2">Total for <?= date('F'); ?></p>
                </div>
                <i class="fa-solid fa-calendar-days absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#fa7d20] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-orange-100 text-sm font-medium mb-1">Next Payday</p>
                    <h3 class="text-4xl font-bold tracking-tight"><?= $next_payday; ?></h3>
                    <p class="text-orange-100 text-sm mt-2">Salary distributions every Saturday</p>
                </div>
                <i class="fa-solid fa-money-bill-wave absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-6">Past Trip History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 px-2 font-medium">Date & Time</th>
                            <th class="pb-3 px-2 font-medium">Destination</th>
                            <th class="pb-3 px-2 font-medium">Duration</th>
                            <th class="pb-3 px-2 font-medium">Earned</th>
                            <th class="pb-3 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-200">
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
                                <?php
                                    $duration = 'N/A';
                                    if (!empty($trip['transit_start_time']) && !empty($trip['transit_end_time'])) {
                                        $start = new DateTime($trip['transit_start_time']);
                                        $end = new DateTime($trip['transit_end_time']);
                                        $diff = $start->diff($end);
                                        $duration = '';
                                        if ($diff->h > 0) $duration .= $diff->h . 'h ';
                                        $duration .= $diff->i . 'm';
                                    }
                                ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition-colors">
                                    <td class="py-4 px-2"><?= date('M d, Y h:i A', strtotime($trip['created_at'] ?? $trip['trip_date'])); ?></td>
                                    <td class="py-4 px-2 font-medium"><?= htmlspecialchars($trip['destination']); ?></td>
                                    <td class="py-4 px-2 text-gray-500 dark:text-gray-400 font-mono text-sm"><?= $duration; ?></td>
                                    <td class="py-4 px-2 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?></td>
                                    <td class="py-4 px-2">
                                        <?php 
                                        $s = isset($trip['status']) ? trim($trip['status']) : '';
                                        if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                        ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fa-solid fa-check mr-1"></i> Delivered
                                            </span>
                                        <?php elseif ($s === 'Cancellation Requested'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 animate-pulse">
                                                <i class="fa-solid fa-clock mr-1"></i> Pending Cancel
                                            </span>
                                        <?php elseif ($s === 'Cancelled'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fa-solid fa-ban mr-1"></i> Cancelled
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fa-solid fa-truck-fast mr-1"></i> <?= htmlspecialchars($s); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-8 px-2 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-road text-4xl mb-3 text-gray-300 block"></i>
                                    No trips recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-[#4a8df8] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Payroll Amount</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($payroll['total_amount'], 2); ?></h3>
                    <p class="text-blue-100 text-sm mt-2">Lifetime earnings</p>
                </div>
                <i class="fa-solid fa-wallet absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#2ccb72] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-green-100 text-sm font-medium mb-1">Payroll Claimed</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($payroll['amount_claimed'], 2); ?></h3>
                    <p class="text-green-100 text-sm mt-2">Successfully withdrawn</p>
                </div>
                <i class="fa-solid fa-hand-holding-dollar absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#fa7d20] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-orange-100 text-sm font-medium mb-1">Available Balance</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($available_balance, 2); ?></h3>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-orange-100 text-sm">Ready to claim</p>
                        <?php if ($available_balance > 0): ?>
                        <a href="print_payroll.php" target="_blank" class="text-sm bg-white text-[#fa7d20] px-3 py-1.5 rounded hover:bg-orange-50 font-medium transition flex items-center shadow-sm relative z-20">
                            <i class="fa-solid fa-eye mr-1.5"></i> View Ticket
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <i class="fa-solid fa-coins absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-6">Past Trip History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 px-2 font-medium">Date</th>
                            <th class="pb-3 px-2 font-medium">Destination</th>
                            <th class="pb-3 px-2 font-medium">Earned</th>
                            <th class="pb-3 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-200">
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition-colors">
                                    <td class="py-4 px-2"><?= date('M d, Y', strtotime($trip['trip_date'])); ?></td>
                                    <td class="py-4 px-2 font-medium"><?= htmlspecialchars($trip['destination']); ?></td>
                                    <td class="py-4 px-2 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?></td>
                                    <td class="py-4 px-2">
                                        <?php 
                                        $s = isset($trip['status']) ? trim($trip['status']) : '';
                                        // If it's empty, we assume it's a completed trip from the scan
                                        if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                        ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fa-solid fa-check mr-1"></i> Delivered
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

        <!-- ACTIVE TRIP SECTION -->
        <div class="mb-8">
            <?php if ($active_dispatch): ?>
                <?php 
                    $status = $active_dispatch['status'];
                    $statusColor = 'bg-blue-150 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
                    $statusIcon = 'fa-circle-info';
                    $statusDesc = 'Your dispatch is pending.';

                    if ($status === 'Loading') {
                        $statusColor = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300';
                        $statusIcon = 'fa-spinner fa-spin';
                        $statusDesc = 'Your truck is currently loading gravel at the site.';
                    } elseif ($status === 'In Transit') {
                        $statusColor = 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
                        $statusIcon = 'fa-truck-fast animate-bounce';
                        $statusDesc = 'You are on the road. GPS location is sharing live with dispatch.';
                    } elseif ($status === 'Unloading') {
                        $statusColor = 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300';
                        $statusIcon = 'fa-dumpster';
                        $statusDesc = 'You have arrived at the destination. Unloading cargo.';
                    } elseif ($status === 'Cancellation Requested') {
                        $statusColor = 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 animate-pulse';
                        $statusIcon = 'fa-triangle-exclamation';
                        $statusDesc = 'Trip cancellation requested. Awaiting Admin confirmation.';
                    }
                ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-blue-100 dark:border-gray-700 overflow-hidden transition-all duration-300 transform hover:scale-[1.01]">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-route text-2xl opacity-90"></i>
                            <div>
                                <h3 class="font-bold text-lg">Active Trip Dispatch</h3>
                                <p class="text-blue-100 text-xs font-mono">Ticket: <?= htmlspecialchars($active_dispatch['ticket_number']); ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white text-blue-700 shadow-sm flex items-center gap-1.5 uppercase">
                            <i class="fa-solid <?= $statusIcon; ?>"></i> <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Destination</span>
                                <span class="text-lg font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-red-500"></i>
                                    <?= htmlspecialchars($active_dispatch['destination']); ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Assigned Truck</span>
                                <span class="text-lg font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    <i class="fa-solid fa-truck text-blue-500"></i>
                                    <?= htmlspecialchars($active_dispatch['truck_code']); ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Load Volume</span>
                                <span class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                                    <i class="fa-solid fa-cube text-indigo-500"></i>
                                    <?= number_format($active_dispatch['cubic_meters'] ?? 0, 2); ?> cu.m
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Dispatch Time</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-clock text-blue-500"></i>
                                    <?= !empty($active_dispatch['transit_start_time']) ? date('M d, Y h:i A', strtotime($active_dispatch['transit_start_time'])) : (!empty($active_dispatch['created_at']) ? date('M d, Y h:i A', strtotime($active_dispatch['created_at'])) : 'Pending Dispatch') ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Arrival Time</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-flag-checkered text-green-500"></i>
                                    <?= !empty($active_dispatch['transit_end_time']) ? date('M d, Y h:i A', strtotime($active_dispatch['transit_end_time'])) : ($active_dispatch['status'] === 'In Transit' ? 'In Transit...' : 'Pending Arrival') ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Dispatch Rate</span>
                                <span class="text-lg font-extrabold text-green-600 dark:text-green-400 flex items-center gap-2">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                    ₱<?= number_format($driver_rates[$active_dispatch['destination']] ?? 0, 2); ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 rounded-xl <?= $statusColor; ?>">
                            <i class="fa-solid fa-info-circle text-lg mt-0.5"></i>
                            <div>
                                <span class="font-bold text-sm block">Current State</span>
                                <p class="text-xs mt-1 leading-relaxed opacity-90"><?= $statusDesc; ?></p>
                            </div>
                        </div>

                        <?php if ($status === 'In Transit'): ?>
                            <div class="mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950/20 text-green-800 dark:text-green-300 border border-green-200/50 dark:border-green-900/30 rounded-xl">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <div class="text-xs font-medium">
                                    <strong>GPS Live Tracking Active:</strong> Your location updates are synchronized with the office. Please do not close or minimize this tab.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- IDLE STATUS CARD -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-4 text-center sm:text-left">
                        <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto sm:mx-0 shadow-inner">
                            <i class="fa-solid fa-house-chimney-user"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-850 dark:text-gray-100 flex items-center gap-2 justify-center sm:justify-start">
                                Status: Idle (At Garage)
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block animate-pulse"></span>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">You are currently active and ready for a new trip assignment.</p>
                        </div>
                    </div>
                    <div class="text-center sm:text-right w-full sm:w-auto">
                        <span class="text-xs bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-3.5 py-1.5 rounded-full font-bold uppercase inline-block">
                            Waiting for Admin
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- EARNINGS STATS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

            <div class="bg-gradient-to-br from-blue-500 to-blue-650 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
                <div class="relative z-10">
                    <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">This Week's Earnings</p>
                    <h3 class="text-3xl font-extrabold tracking-tight">₱<?= number_format($weekly_salary, 2); ?></h3>
                    <p class="text-blue-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                        <i class="fa-regular fa-clock"></i> Current week (Mon-Sun)
                    </p>
                </div>
                <i class="fa-solid fa-calendar-week absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
            </div>

            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
                <div class="relative z-10">
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">This Month's Earnings</p>
                    <h3 class="text-3xl font-extrabold tracking-tight">₱<?= number_format($monthly_salary, 2); ?></h3>
                    <p class="text-emerald-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                        <i class="fa-regular fa-calendar"></i> Total for <?= date('F'); ?>
                    </p>
                </div>
                <i class="fa-solid fa-calendar-days absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
            </div>

            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
                <div class="relative z-10">
                    <p class="text-orange-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Next Payday</p>
                    <h3 class="text-3xl font-extrabold tracking-tight"><?= $next_payday; ?></h3>
                    <p class="text-orange-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                        <i class="fa-solid fa-wallet"></i> Payouts every Saturday
                    </p>
                </div>
                <i class="fa-solid fa-money-bill-wave absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
            </div>

        </div>

        <!-- TRIP HISTORY SECTION -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-850 dark:text-gray-200">Past Trip History</h2>
                <span class="text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full font-bold uppercase">
                    <?= count($trips); ?> Trips
                </span>
            </div>

            <!-- Mobile View: Stacked Cards list (shown on small screens) -->
            <div class="block sm:hidden space-y-3">
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
                            $dispTimeStr = !empty($trip['transit_start_time']) ? date('M d, Y h:i A', strtotime($trip['transit_start_time'])) : (!empty($trip['created_at']) ? date('M d, Y h:i A', strtotime($trip['created_at'])) : date('M d, Y', strtotime($trip['trip_date'])));
                            $arrTimeStr = !empty($trip['transit_end_time']) ? date('M d, Y h:i A', strtotime($trip['transit_end_time'])) : ($trip['status'] === 'Delivered' ? 'Delivered' : 'N/A');
                        ?>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-150 dark:border-gray-800 shadow-sm space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">Dispatch: <?= $dispTimeStr; ?></span>
                                <span class="font-bold text-green-600 dark:text-green-400">₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?></span>
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                Arrival: <?= $arrTimeStr; ?>
                            </div>
                            <div class="flex justify-between items-end pt-1">
                                <div>
                                    <span class="text-xs text-gray-400 dark:text-gray-550 block">Destination</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 text-sm"><?= htmlspecialchars($trip['destination']); ?></span>
                                </div>
                                <div class="text-right">
                                    <?php 
                                    $s = isset($trip['status']) ? trim($trip['status']) : '';
                                    if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                    ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-150 text-green-800 dark:bg-green-900/20 dark:text-green-455">
                                            <i class="fa-solid fa-check mr-1 text-[8px]"></i> Delivered
                                        </span>
                                    <?php elseif ($s === 'Cancellation Requested'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-850 dark:bg-orange-950/20 dark:text-orange-400 animate-pulse">
                                            <i class="fa-solid fa-clock mr-1 text-[8px]"></i> Pending Cancel
                                        </span>
                                    <?php elseif ($s === 'Cancelled'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            <i class="fa-solid fa-ban mr-1 text-[8px]"></i> Cancelled
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                            <i class="fa-solid fa-truck-fast mr-1 text-[8px]"></i> <?= htmlspecialchars($s); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-mono mt-1">Duration: <?= $duration; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-8 text-center text-gray-400">
                        <i class="fa-solid fa-road text-4xl mb-2 opacity-30"></i>
                        <p class="text-sm">No trips recorded yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Desktop View: Grid/Table (shown on md/lg screens) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 px-2 font-medium">Dispatch Date & Time</th>
                            <th class="pb-3 px-2 font-medium">Arrival Date & Time</th>
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
                                    $dispTimeStr = !empty($trip['transit_start_time']) ? date('M d, Y h:i A', strtotime($trip['transit_start_time'])) : (!empty($trip['created_at']) ? date('M d, Y h:i A', strtotime($trip['created_at'])) : date('M d, Y', strtotime($trip['trip_date'])));
                                    $arrTimeStr = !empty($trip['transit_end_time']) ? date('M d, Y h:i A', strtotime($trip['transit_end_time'])) : ($trip['status'] === 'Delivered' ? 'Delivered' : '—');
                                ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition-colors">
                                    <td class="py-4 px-2 text-sm font-medium text-gray-800 dark:text-gray-200"><?= $dispTimeStr; ?></td>
                                    <td class="py-4 px-2 text-sm font-medium text-gray-600 dark:text-gray-400"><?= $arrTimeStr; ?></td>
                                    <td class="py-4 px-2 font-medium"><?= htmlspecialchars($trip['destination']); ?></td>
                                    <td class="py-4 px-2 text-gray-500 dark:text-gray-400 font-mono text-sm"><?= $duration; ?></td>
                                    <td class="py-4 px-2 font-semibold text-green-600 dark:text-green-400">₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?></td>
                                    <td class="py-4 px-2">
                                        <?php 
                                        $s = isset($trip['status']) ? trim($trip['status']) : '';
                                        if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                        ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-455">
                                                <i class="fa-solid fa-check mr-1"></i> Delivered
                                            </span>
                                        <?php elseif ($s === 'Cancellation Requested'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-850 dark:bg-orange-950/20 dark:text-orange-400 animate-pulse">
                                                <i class="fa-solid fa-clock mr-1"></i> Pending Cancel
                                            </span>
                                        <?php elseif ($s === 'Cancelled'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                                <i class="fa-solid fa-ban mr-1"></i> Cancelled
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                                <i class="fa-solid fa-truck-fast mr-1"></i> <?= htmlspecialchars($s); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-8 px-2 text-center text-gray-550 dark:text-gray-400">
                                    <i class="fa-solid fa-road text-4xl mb-3 text-gray-300 block"></i>
                                    No trips recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

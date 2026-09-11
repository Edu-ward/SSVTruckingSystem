<?php

$totalPendingGross       = array_sum(array_column($allDrivers ?? [], 'gross_earnings'));
$totalPendingRemaining   = array_sum(array_column($allDrivers ?? [], 'remaining_balance'));
$totalActiveCaDeductions = array_sum(array_column($allDrivers ?? [], 'approved_cash_advances'));
$totalNetPayable         = array_sum(array_column($allDrivers ?? [], 'net_earnings'));
$driversWithPayable      = count(array_filter($allDrivers ?? [], fn($d) => ($d['net_earnings'] ?? 0) > 0));
$totalSettlementCount    = count($payrollSettlements ?? []);
$totalLifetimeDisbursed  = array_sum(array_column($payrollSettlements ?? [], 'amount_claimed'));
?>
<div id="view-payroll" class="tab-content hidden">

    
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold shadow-sm">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Driver Salaries & Payroll</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage driver compensation, trip earnings, carried balances, cash advance deductions, and disbursements.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            
            <div class="relative flex-1 md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="payrollDriverSearchInput" placeholder="Search driver, CDL, truck..." oninput="filterPayrollTable()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <button type="button" id="payrollSearchClear" onclick="clearPayrollSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            
            <button type="button" onclick="switchTab('cash_advances')" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition flex items-center gap-2 flex-shrink-0 cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600 dark:text-amber-400"></i>
                <span>Cash Advances</span>
                <?php if (($pendingCashAdvanceCount ?? 0) > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white"><?= $pendingCashAdvanceCount ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Net Payable</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalNetPayable, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        <?= $driversWithPayable ?> driver<?= $driversWithPayable !== 1 ? 's' : '' ?> awaiting payout
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-blue-200/70 dark:border-blue-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Unclaimed Gross</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalPendingGross, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        Earned from completed trips
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-amber-200/70 dark:border-amber-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Advance Deductions</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">
                        -₱<?= number_format($totalActiveCaDeductions, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        Auto-deducted at settlement
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-indigo-200/70 dark:border-indigo-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Carried Balances</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalPendingRemaining, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        Held over from past cycles
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-400 to-purple-500"></div>
        </div>
    </div>

    
    <div class="mb-6 flex border-b border-gray-200 dark:border-gray-700 gap-6 text-sm font-semibold">
        <button type="button" onclick="switchPayrollSubTab('active')" id="btnPayrollSubActive" class="pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition flex items-center gap-2">
            <i class="fa-solid fa-users-viewfinder"></i>
            <span>Active Driver Payroll</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold"><?= count($allDrivers ?? []) ?></span>
        </button>
        <button type="button" onclick="switchPayrollSubTab('history')" id="btnPayrollSubHistory" class="pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Settlement History</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-bold"><?= $totalSettlementCount ?></span>
        </button>
    </div>

    
    <div id="payrollSubTabActive" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[10px] sm:text-xs font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3.5 sm:px-6">Driver & Assigned Truck</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-right">Unclaimed Trips</th>
                            <th class="px-4 py-3.5 text-right">Carried Bal.</th>
                            <th class="px-4 py-3.5 text-right">Cash Advances</th>
                            <th class="px-4 py-3.5 text-right">Net Payable</th>
                            <th class="px-4 py-3.5 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        <?php foreach ($allDrivers as $driver):
                            $hasPayable = ($driver['net_earnings'] ?? 0) > 0;
                            $unclaimedTripsCount = count(array_filter($driver['all_trips'] ?? [], fn($t) => ($t['status'] ?? '') === 'Delivered' && empty($t['is_payroll_paid'])));
                            $dPhotoPath = $driver['profile_photo'] ?? null;
                            $dPhotoFull = $dPhotoPath ? (dirname(__DIR__, 2) . '/' . $dPhotoPath) : null;
                            $dPhotoUrl  = ($dPhotoFull && file_exists($dPhotoFull))
                                ? '../' . htmlspecialchars($dPhotoPath) . '?v=' . filemtime($dPhotoFull)
                                : null;
                            $searchMeta = htmlspecialchars(strtolower(($driver['name'] ?? '') . ' ' . ($driver['cdl_number'] ?? '') . ' ' . ($driver['truck_code'] ?? '') . ' ' . ($driver['status'] ?? '')));
                        ?>
                            <tr class="payroll-driver-row hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors" data-search="<?= $searchMeta; ?>">
                                
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <?php if ($dPhotoUrl): ?>
                                            <img src="<?= $dPhotoUrl ?>" alt="<?= htmlspecialchars($driver['name']) ?>" class="w-10 h-10 rounded-xl object-cover shadow-sm flex-shrink-0 border border-gray-200 dark:border-gray-700">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-600 to-teal-700 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-sm flex-shrink-0">
                                                <?= getInitials($driver['name']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate">
                                                <?= htmlspecialchars($driver['name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-2">
                                                <span>CDL: <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></span>
                                                <span>&bull;</span>
                                                <span class="font-medium text-blue-600 dark:text-blue-400">
                                                    <i class="fa-solid fa-truck text-[10px] mr-1"></i><?= htmlspecialchars($driver['truck_code'] ?? 'Unassigned'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full text-white <?= $driver['status'] === 'Active' ? 'bg-emerald-500' : ($driver['status'] === 'Dispatched' ? 'bg-blue-600' : ($driver['status'] === 'Resigned' ? 'bg-amber-600' : 'bg-gray-500')) ?>">
                                        <?= htmlspecialchars($driver['status']); ?>
                                    </span>
                                </td>

                                
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="font-bold text-gray-900 dark:text-gray-100">
                                        ₱<?= number_format($driver['gross_earnings'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        <?= $unclaimedTripsCount ?> trip<?= $unclaimedTripsCount !== 1 ? 's' : '' ?>
                                    </div>
                                </td>

                                
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="font-semibold text-indigo-600 dark:text-indigo-400">
                                        ₱<?= number_format($driver['remaining_balance'] ?? 0, 2); ?>
                                    </div>
                                    <button type="button" onclick="openAdjustBalanceModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['remaining_balance'] ?? 0; ?>)" class="text-[10px] font-semibold text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline">
                                        Adjust
                                    </button>
                                </td>

                                
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <?php if (($driver['approved_cash_advances'] ?? 0) > 0): ?>
                                        <div class="font-bold text-amber-600 dark:text-amber-400">
                                            -₱<?= number_format($driver['approved_cash_advances'] ?? 0, 2); ?>
                                        </div>
                                        <button type="button" onclick="switchTab('cash_advances')" class="text-[10px] text-amber-500 hover:underline">
                                            View Advances
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <span class="text-sm font-black <?= $hasPayable ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' ?>">
                                        ₱<?= number_format($driver['net_earnings'] ?? 0, 2); ?>
                                    </span>
                                </td>

                                
                                <td class="px-4 py-3.5 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <?php if ($hasPayable): ?>
                                            <button type="button" onclick="openSettlePayrollModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['gross_earnings'] ?? 0; ?>, <?= $driver['approved_cash_advances'] ?? 0; ?>, <?= $driver['net_earnings'] ?? 0; ?>, <?= $driver['remaining_balance'] ?? 0; ?>)"
                                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                                <i class="fa-solid fa-money-bill-transfer"></i>
                                                <span>Settle</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" disabled class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 transition flex items-center gap-1.5 cursor-not-allowed">
                                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                <span>Settled</span>
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" onclick="openAdjustBalanceModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['remaining_balance'] ?? 0; ?>)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition"
                                            title="Adjust Carried Balance">
                                            <i class="fa-solid fa-coins text-xs"></i>
                                        </button>

                                        <button type="button" onclick="openPrintDriverTripsModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition"
                                            title="Print Driver Trips Ticket">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="noPayrollSearchResults" class="hidden py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-3 text-lg">
                    <i class="fa-solid fa-search"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No drivers match your search</h4>
                <p class="text-xs text-gray-400 mt-1" id="noPayrollSearchText"></p>
                <button type="button" onclick="clearPayrollSearch()" class="mt-3 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:underline">Clear Search</button>
            </div>
        </div>
    </div>

    
    <div id="payrollSubTabHistory" class="hidden space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base">Settled Vouchers Archive</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Historical records of finalized payroll disbursements and deduction vouchers.</p>
                </div>
                <span class="text-xs text-gray-400 font-medium">
                    Total Disbursed: <strong class="text-emerald-600 dark:text-emerald-400">₱<?= number_format($totalLifetimeDisbursed, 2) ?></strong>
                </span>
            </div>

            <?php if (!empty($payrollSettlements)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[10px] sm:text-xs font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5 sm:px-6">Settlement Ticket</th>
                                <th class="px-4 py-3.5">Date & Time</th>
                                <th class="px-4 py-3.5">Driver</th>
                                <th class="px-4 py-3.5 text-center">Deliveries</th>
                                <th class="px-4 py-3.5 text-right">Gross Pay</th>
                                <th class="px-4 py-3.5 text-right">CA Deductions</th>
                                <th class="px-4 py-3.5 text-right">Disbursed Amount</th>
                                <th class="px-4 py-3.5 text-right">Carried Bal.</th>
                                <th class="px-4 py-3.5 sm:px-6 text-right">Voucher</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <?php foreach ($payrollSettlements as $st): ?>
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3.5 sm:px-6 font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        <?= htmlspecialchars($st['settlement_ticket']); ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 text-xs whitespace-nowrap">
                                        <?= date('M d, Y h:i A', strtotime($st['settled_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">
                                        <div><?= htmlspecialchars($st['driver_name']); ?></div>
                                        <?php if (!empty($st['truck_code'])): ?>
                                            <div class="text-[11px] text-gray-400 font-normal">Truck: <?= htmlspecialchars($st['truck_code']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                        <?= intval($st['trips_count'] ?? 0); ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                        ₱<?= number_format($st['gross_amount'] ?? 0, 2); ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-medium text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                        <?= floatval($st['cash_advance_deduction'] ?? 0) > 0 ? ('-₱' . number_format($st['cash_advance_deduction'], 2)) : '—' ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-extrabold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        ₱<?= number_format($st['amount_claimed'] ?? 0, 2); ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-medium text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                        ₱<?= number_format($st['remaining_balance'] ?? 0, 2); ?>
                                    </td>
                                    <td class="px-4 py-3.5 sm:px-6 text-right whitespace-nowrap">
                                        <button type="button" onclick="window.open('print_payroll.php?settlement_id=<?= $st['id']; ?>', '_blank')" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="fa-solid fa-print text-emerald-500"></i>
                                            <span>Print Voucher</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="py-12 text-center text-gray-400 dark:text-gray-500">
                    <i class="fa-solid fa-receipt text-3xl mb-2 text-gray-300 dark:text-gray-600 block"></i>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">No finalized settlement vouchers yet</p>
                    <p class="text-xs text-gray-400 mt-1">When you settle a driver's payroll, the official payout ticket will be recorded here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
function switchPayrollSubTab(tab) {
    const btnActive = document.getElementById('btnPayrollSubActive');
    const btnHistory = document.getElementById('btnPayrollSubHistory');
    const tabActive = document.getElementById('payrollSubTabActive');
    const tabHistory = document.getElementById('payrollSubTabHistory');

    if (tab === 'active') {
        tabActive.classList.remove('hidden');
        tabHistory.classList.add('hidden');
        btnActive.className = "pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition flex items-center gap-2 font-bold";
        btnHistory.className = "pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition flex items-center gap-2";
    } else {
        tabActive.classList.add('hidden');
        tabHistory.classList.remove('hidden');
        btnHistory.className = "pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition flex items-center gap-2 font-bold";
        btnActive.className = "pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition flex items-center gap-2";
    }
}

function filterPayrollTable() {
    const input = document.getElementById('payrollDriverSearchInput');
    const clearBtn = document.getElementById('payrollSearchClear');
    const query = input ? input.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('#payrollTableBody .payroll-driver-row');
    const noResults = document.getElementById('noPayrollSearchResults');
    const noResultsText = document.getElementById('noPayrollSearchText');

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
    }

    let matchCount = 0;
    rows.forEach(row => {
        const meta = row.getAttribute('data-search') || '';
        if (!query || meta.includes(query)) {
            row.classList.remove('hidden');
            matchCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    if (noResults) {
        if (matchCount === 0 && rows.length > 0) {
            noResults.classList.remove('hidden');
            if (noResultsText) noResultsText.textContent = `No drivers match "${query}".`;
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function clearPayrollSearch() {
    const input = document.getElementById('payrollDriverSearchInput');
    if (input) {
        input.value = '';
        filterPayrollTable();
        input.focus();
    }
}
</script>

</div>

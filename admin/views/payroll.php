<?php

$dayOfWeek = (int)date('N');
$thisMonday = date('Y-m-d', strtotime('-' . ($dayOfWeek - 1) . ' days'));
$thisSaturday = date('Y-m-d', strtotime('+' . (6 - $dayOfWeek) . ' days'));
$thisSunday = date('Y-m-d', strtotime('+' . (7 - $dayOfWeek) . ' days'));

$payrollPayPeriods = [];
for ($w = 0; $w < 12; $w++) {
    $mon = date('Y-m-d', strtotime("$thisMonday -$w weeks"));
    $sat = date('Y-m-d', strtotime("$thisSaturday -$w weeks"));
    $sun = date('Y-m-d', strtotime("$thisSunday -$w weeks"));

    $sameMonth = (date('M', strtotime($mon)) === date('M', strtotime($sun)));
    if ($sameMonth) {
        $datesFormatted = date('F j', strtotime($mon)) . ' – ' . date('j, Y', strtotime($sun));
    } else {
        $datesFormatted = date('F j', strtotime($mon)) . ' – ' . date('F j, Y', strtotime($sun));
    }

    $wPrefix = ($w === 0) ? "This Week: " : (($w === 1) ? "Last Week: " : "$w Weeks Ago: ");
    $shortLabel = ($w === 0) ? "This Week" : (($w === 1) ? "Last Week" : "$w Weeks Ago");
    $label = $wPrefix . $datesFormatted;

    $payrollPayPeriods[] = [
        'from'        => $mon,
        'to'          => $sun,
        'sat'         => $sat,
        'label'       => $label,
        'short_label' => $shortLabel,
        'clean_dates' => $datesFormatted,
        'is_current'  => ($w === 0)
    ];
}

$defaultPeriod = $payrollPayPeriods[0] ?? [
    'from' => $thisMonday,
    'to' => $thisSunday,
    'clean_dates' => date('F j', strtotime($thisMonday)) . ' – ' . date('F j, Y', strtotime($thisSunday))
];

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
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Payroll Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage weekly pay periods, driver compensation, trip earnings, cash advance deductions, and disbursement settlements.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">

            <div class="relative flex-1 md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="payrollDriverSearchInput" oninput="filterPayrollTable()" placeholder="Search driver, CDL, truck..."
                    class="w-full pl-9 pr-8 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-gray-900 dark:text-gray-100 transition shadow-inner">
                <button type="button" id="payrollSearchClear" onclick="clearPayrollSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>


            <button type="button" onclick="switchTab('cash_advances')" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i>
                <span>Cash Advances</span>
                <?php if (($pendingCashAdvanceCount ?? 0) > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white"><?= $pendingCashAdvanceCount ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>


    <div class="mb-6 bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 border border-emerald-800/40 rounded-3xl p-5 sm:p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400 text-xl flex-shrink-0 shadow-inner">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-300">Pay Period:</span>
                        <span id="activePayPeriodBadge" class="text-sm font-extrabold px-3 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">
                            All Delivery Cycles (All Pending)
                        </span>
                    </div>
                    <p class="text-xs text-gray-300 mt-1" id="activePayPeriodSubtext">Evaluating all accumulated unsettled driver trips across all past delivery weeks.</p>
                </div>
            </div>

            <!-- Pay Period Mode Controls -->
            <div class="flex items-center gap-2 self-start md:self-auto bg-gray-800/80 p-1.5 rounded-2xl border border-gray-700/80 backdrop-blur-xs flex-wrap sm:flex-nowrap">
                <button type="button" id="payrollAllCyclesBtn" onclick="selectPayrollAllCycles()"
                    class="px-3 h-8 rounded-xl border border-emerald-400 bg-emerald-500/30 text-emerald-200 hover:bg-emerald-500/40 text-xs font-extrabold active:scale-95 transition cursor-pointer whitespace-nowrap shadow-sm"
                    title="Evaluate All Past Unsettled Trips (All Periods)">
                    <i class="fa-solid fa-infinity mr-1 text-[11px]"></i>
                    <span>All Periods</span>
                </button>

                <div class="h-4 w-px bg-gray-700"></div>

                <select id="payPeriodSelector" onchange="onPayrollPeriodChange(this.value)"
                    class="bg-transparent text-xs font-bold text-gray-200 border-none focus:ring-0 cursor-pointer pr-8 py-1.5 rounded-xl hover:bg-gray-700/50 transition">
                    <option value="ALL" data-label="All Delivery Cycles (All Pending)" data-short="All Periods" class="bg-gray-900 text-emerald-300 font-bold" selected>
                        ★ All Periods (All Pending Payroll)
                    </option>
                    <?php foreach ($payrollPayPeriods as $p): ?>
                        <option value="<?= $p['from'] . '|' . $p['to'] ?>"
                            data-label="<?= htmlspecialchars($p['clean_dates']) ?>"
                            data-short="<?= htmlspecialchars($p['short_label']) ?>"
                            class="bg-gray-900 text-gray-100">
                            <?= htmlspecialchars($p['label']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="CUSTOM" data-label="Custom Date Range" data-short="Custom" class="bg-gray-900 text-gray-100">
                        Custom Date Range...
                    </option>
                </select>

                <div class="h-4 w-px bg-gray-700"></div>

                <button type="button" onclick="shiftPayrollWeek(1)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700/70 text-xs transition" title="Previous Week">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button type="button" id="payrollCurrentWeekBtn" onclick="shiftPayrollWeek(0) "
                    class="px-3 h-8 rounded-xl border border-gray-600 bg-gray-700/50 text-gray-300 hover:text-white hover:bg-gray-700 text-xs font-semibold active:scale-95 transition cursor-pointer whitespace-nowrap"
                    title="Current Week (This Week)">
                    <span id="payrollCurrentWeekBtnText">This Week</span>
                </button>

                <button type="button" onclick="shiftPayrollWeek(-1)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700/70 text-xs transition" title="Next Week">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>


        <div id="payrollCustomDateBar" class="hidden mt-4 pt-4 border-t border-gray-700/60 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs">
                <span class="text-gray-400">From:</span>
                <input type="date" id="payrollCustomDateFrom" value="<?= $thisMonday ?>"
                    class="bg-gray-800 text-white border border-gray-600 rounded-lg px-2.5 py-1 text-xs focus:ring-emerald-500">
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-gray-400">To:</span>
                <input type="date" id="payrollCustomDateTo" value="<?= $thisSunday ?>"
                    class="bg-gray-800 text-white border border-gray-600 rounded-lg px-2.5 py-1 text-xs focus:ring-emerald-500">
            </div>
            <button type="button" onclick="applyPayrollCustomDateRange()"
                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition shadow-sm">
                Apply Filter
            </button>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider" id="kpiNetPayableLabel">Total Net Payable (All Periods)</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5" id="kpiPeriodNetPayable">
                        ₱<?= number_format($totalNetPayable, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block" id="kpiDriversCount">
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
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Trip Gross Earnings</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5" id="kpiPeriodGross">
                        ₱<?= number_format($totalPendingGross, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block" id="kpiTripsCount">
                        Earned from completed deliveries
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
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5" id="kpiActiveAdvances">
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
    </div>


    <div class="mb-6 flex flex-wrap items-center justify-between border-b border-gray-200 dark:border-gray-700 gap-4 text-sm font-semibold">
        <div class="flex items-center gap-4 sm:gap-6 flex-wrap">
            <button type="button" onclick="switchPayrollSubTab('active')" id="btnPayrollSubActive" class="pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition flex items-center gap-2">
                <i class="fa-solid fa-users-viewfinder"></i>
                <span>Driver Payroll Payouts</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold"><?= count($allDrivers ?? []) ?></span>
            </button>
            <button type="button" onclick="switchPayrollSubTab('history')" id="btnPayrollSubHistory" class="pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Settlement History</span>
                <span id="payrollHistoryCountBadge" class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-bold"><?= $totalSettlementCount ?></span>
            </button>
            <div class="pb-3 flex items-center gap-2 text-xs">
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700 hidden sm:inline-block"></span>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs">
                    <i class="fa-solid fa-coins text-emerald-600 dark:text-emerald-400"></i>
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Total Net Payable:</span>
                    <span id="tabBarTotalNetPayable" class="font-extrabold text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm">
                        ₱<?= number_format($totalNetPayable, 2) ?>
                    </span>
                    <span id="tabBarScopeBadge" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold ml-0.5 px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40">
                        All Periods
                    </span>
                </div>
            </div>
        </div>
    </div>


    <div id="payrollSubTabActive" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[10px] sm:text-xs font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-3 sm:px-4 py-3">Driver & Assigned Truck</th>
                            <th class="px-2.5 sm:px-3 py-3 text-center">Status</th>
                            <th class="px-2.5 sm:px-3 py-3 text-right">
                                <span id="thTripsLabel">Trips & Gross</span>
                            </th>
                            <th class="px-2.5 sm:px-3 py-3 text-right">Cash Advances</th>
                            <th class="px-2.5 sm:px-3 py-3 text-right">
                                <span>Total Net Payable</span>
                                <span id="thNetScopeLabel" class="block text-[9px] font-normal normal-case text-emerald-600 dark:text-emerald-400 font-semibold">All periods pending</span>
                            </th>
                            <th class="px-3 sm:px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        <?php foreach ($allDrivers as $driver):
                            $hasPayable = ($driver['net_earnings'] ?? 0) > 0;
                            $unclaimedTripsCount = count(array_filter($driver['all_trips'] ?? [], fn($t) => ($t['status'] ?? '') === 'Delivered' && empty($t['is_payroll_paid'])));

                            $deliveredTripsList = array_values(array_filter($driver['all_trips'] ?? [], fn($t) => ($t['status'] ?? '') === 'Delivered'));
                            $driverTripsJson = htmlspecialchars(json_encode(array_map(function ($t) {
                                return [
                                    'id'          => $t['id'] ?? 0,
                                    'date'        => !empty($t['transit_end_time']) ? substr($t['transit_end_time'], 0, 10) : (!empty($t['trip_date']) ? substr($t['trip_date'], 0, 10) : substr($t['created_at'] ?? '', 0, 10)),
                                    'pay'         => floatval($t['pay_amount'] ?? 0),
                                    'paid'        => !empty($t['is_payroll_paid']) ? 1 : 0,
                                    'destination' => $t['destination'] ?? '',
                                    'ticket'      => $t['ticket_number'] ?? '',
                                    'distance'    => floatval($t['distance_km'] ?? 0),
                                    'dispatch_at' => !empty($t['transit_start_time']) ? $t['transit_start_time'] : ($t['created_at'] ?? ''),
                                    'end_at'      => $t['transit_end_time'] ?? ''
                                ];
                            }, $deliveredTripsList), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');

                            $dPhotoPath = $driver['profile_photo'] ?? null;
                            $dPhotoFull = $dPhotoPath ? (dirname(__DIR__, 2) . '/' . $dPhotoPath) : null;
                            $dPhotoUrl  = ($dPhotoFull && file_exists($dPhotoFull))
                                ? '../' . htmlspecialchars($dPhotoPath) . '?v=' . filemtime($dPhotoFull)
                                : null;
                            $searchMeta = htmlspecialchars(strtolower(($driver['name'] ?? '') . ' ' . ($driver['cdl_number'] ?? '') . ' ' . ($driver['truck_code'] ?? '') . ' ' . ($driver['status'] ?? '')));
                        ?>
                            <tr class="payroll-driver-row hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors"
                                data-search="<?= $searchMeta; ?>"
                                data-driver-id="<?= $driver['id']; ?>"
                                data-driver-name="<?= htmlspecialchars($driver['name']); ?>"
                                data-trips='<?= $driverTripsJson; ?>'
                                data-rembal="<?= floatval($driver['remaining_balance'] ?? 0); ?>"
                                data-advances="<?= floatval($driver['approved_cash_advances'] ?? 0); ?>"
                                data-total-gross="<?= floatval($driver['gross_earnings'] ?? 0); ?>"
                                data-total-net="<?= floatval($driver['net_earnings'] ?? 0); ?>">


                                <td class="px-3 sm:px-4 py-3">
                                    <div class="flex items-center space-x-3 group cursor-pointer" onclick="openDriverWeekTripsModal(this.closest('tr'), 'pending')" title="View driver trips">
                                        <?php if ($dPhotoUrl): ?>
                                            <img src="<?= $dPhotoUrl ?>" alt="<?= htmlspecialchars($driver['name']) ?>" class="w-10 h-10 rounded-xl object-cover shadow-sm flex-shrink-0 border border-gray-200 dark:border-gray-700">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-600 to-teal-700 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-sm flex-shrink-0">
                                                <?= getInitials($driver['name']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                                                <?= htmlspecialchars($driver['name']); ?>
                                                <i class="fa-solid fa-calendar-week text-[10px] text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
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


                                <td class="px-2.5 sm:px-3 py-3 text-center whitespace-nowrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full text-white <?= $driver['status'] === 'Active' ? 'bg-emerald-500' : ($driver['status'] === 'Dispatched' ? 'bg-blue-600' : ($driver['status'] === 'Resigned' ? 'bg-amber-600' : 'bg-gray-500')) ?>">
                                        <?= htmlspecialchars($driver['status']); ?>
                                    </span>
                                </td>


                                <td class="px-2.5 sm:px-3 py-3 text-right whitespace-nowrap cursor-pointer group/trips"
                                    onclick="openDriverWeekTripsModal(this.closest('tr'), 'pending')"
                                    title="Click to view pending trips breakdown">
                                    <div class="inline-flex flex-col items-end p-1.5 -mr-1.5 rounded-xl group-hover/trips:bg-emerald-50/80 dark:group-hover/trips:bg-emerald-950/40 transition-all border border-transparent group-hover/trips:border-emerald-200/60 dark:group-hover/trips:border-emerald-800/40">
                                        <div class="font-bold text-gray-900 dark:text-gray-100 group-hover/trips:text-emerald-600 dark:group-hover/trips:text-emerald-400 transition-colors flex items-center justify-end gap-1.5">
                                            <span class="driver-gross-val">₱<?= number_format($driver['gross_earnings'] ?? 0, 2); ?></span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-emerald-500 opacity-0 group-hover/trips:opacity-100 transition-opacity"></i>
                                        </div>
                                        <div class="text-[11px] text-gray-400 group-hover/trips:text-emerald-600/90 dark:group-hover/trips:text-emerald-400/90 transition-colors driver-trips-val font-medium">
                                            <?= $unclaimedTripsCount ?> trip<?= $unclaimedTripsCount !== 1 ? 's' : '' ?>
                                        </div>
                                    </div>
                                </td>


                                <td class="px-2.5 sm:px-3 py-3 text-right whitespace-nowrap">
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


                                <td class="px-2.5 sm:px-3 py-3 text-right whitespace-nowrap">
                                    <div class="text-sm font-black driver-net-val <?= $hasPayable ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' ?>">
                                        ₱<?= number_format($driver['net_earnings'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 driver-all-pending-val font-semibold">
                                        <?= $hasPayable ? 'All periods pending' : 'No pending balance' ?>
                                    </div>
                                </td>


                                <td class="px-3 sm:px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <span class="driver-settle-btn-container">
                                            <?php if ($hasPayable): ?>
                                                <button type="button" onclick="openSettlePayrollModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['gross_earnings'] ?? 0; ?>, <?= $driver['approved_cash_advances'] ?? 0; ?>, <?= $driver['net_earnings'] ?? 0; ?>, <?= floatval($driver['remaining_balance'] ?? 0); ?>, '', '', 1, 'All Pending Delivery Cycles (All Weeks)')"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer"
                                                    title="Generate ticket and settle all pending payroll for this driver">
                                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                                    <span>Settle</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" disabled class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 transition flex items-center gap-1.5 cursor-not-allowed">
                                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                    <span>Settled</span>
                                                </button>
                                            <?php endif; ?>
                                        </span>

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
                    <tfoot class="bg-gray-50/90 dark:bg-gray-900/60 font-semibold border-t-2 border-gray-200 dark:border-gray-700 text-xs sm:text-sm">
                        <tr>
                            <td colspan="4" class="px-3 sm:px-4 py-3.5 text-right text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wider text-[11px] sm:text-xs">
                                Total Net Payable (<span id="tableFooterScopeLabel">All Periods</span>):
                            </td>
                            <td class="px-2.5 sm:px-3 py-3.5 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm sm:text-base whitespace-nowrap" id="tableFooterTotalNetPayable">
                                ₱<?= number_format($totalNetPayable, 2) ?>
                            </td>
                            <td class="px-3 sm:px-4 py-3.5"></td>
                        </tr>
                    </tfoot>
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

    <!-- Driver Week Trips Modal -->
    <div id="driverWeekTripsModal" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDriverWeekTripsModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-emerald-50/80 dark:bg-emerald-950/30 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base flex items-center gap-2">
                            <span id="dwtm-driver-name">Driver Trips</span>
                        </h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5" id="dwtm-period-label">
                            This Week
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeDriverWeekTripsModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-gray-700 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="px-4 py-2.5 bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 flex-shrink-0">
                <button type="button" id="dwtm-tab-pending" onclick="setDriverTripsFilter('pending')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-sm border border-amber-200 dark:border-amber-700">
                    <i class="fa-solid fa-hourglass-half text-[11px]"></i>
                    <span>Pending Trips</span>
                    <span id="dwtm-badge-pending" class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">0</span>
                </button>
                <button type="button" id="dwtm-tab-all" onclick="setDriverTripsFilter('all')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-list-check text-[11px]"></i>
                    <span>All Trips</span>
                    <span id="dwtm-badge-all" class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">0</span>
                </button>
                <button type="button" id="dwtm-tab-paid" onclick="setDriverTripsFilter('paid')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-circle-check text-[11px]"></i>
                    <span>Settled</span>
                    <span id="dwtm-badge-paid" class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">0</span>
                </button>
            </div>

            <!-- Summary bar -->
            <div class="flex items-center gap-4 px-5 py-2.5 bg-gray-50/40 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700 text-xs flex-shrink-0">
                <span class="flex items-center gap-1.5 font-semibold text-gray-700 dark:text-gray-300">
                    <i class="fa-solid fa-route text-blue-500"></i>
                    <span id="dwtm-trip-count">0 trips</span>
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-coins"></i>
                    <span id="dwtm-total-pay">₱0.00</span>
                </span>
                <span class="ml-auto flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-semibold" id="dwtm-unpaid-badge">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span id="dwtm-unpaid-count">0 pending</span>
                </span>
            </div>

            <!-- Trip list -->
            <div class="overflow-y-auto flex-1 p-4 space-y-2.5 min-h-[200px]" id="dwtm-trip-list">
                <div class="py-10 text-center text-gray-400 dark:text-gray-500" id="dwtm-empty-state">
                    <i class="fa-solid fa-truck text-3xl mb-2 opacity-30 block"></i>
                    <p class="text-sm font-medium">No trips for this period</p>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div id="dwtm-footer-bar" class="p-3.5 bg-gray-50 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-shrink-0">
                <div class="text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Unsettled Payout:</span>
                    <strong class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm ml-1" id="dwtm-unpaid-sum">₱0.00</strong>
                    <div id="dwtm-ca-deduction-note" class="text-[10px] text-amber-600 dark:text-amber-400 font-medium"></div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeDriverWeekTripsModal()" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        Close
                    </button>
                    <div id="dwtm-settle-btn-container"></div>
                </div>
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
                                <th class="px-3 sm:px-4 py-3">Settlement Ticket</th>
                                <th class="px-2.5 sm:px-3 py-3">Date & Time</th>
                                <th class="px-2.5 sm:px-3 py-3">Driver</th>
                                <th class="px-2.5 sm:px-3 py-3 text-center">Deliveries</th>
                                <th class="px-2.5 sm:px-3 py-3 text-right">Gross Pay</th>
                                <th class="px-2.5 sm:px-3 py-3 text-right">CA Deductions</th>
                                <th class="px-2.5 sm:px-3 py-3 text-right">Disbursed Amount</th>
                                <th class="px-3 sm:px-4 py-3 text-right">Voucher</th>
                            </tr>
                        </thead>
                        <tbody id="payrollHistoryTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <?php foreach ($payrollSettlements as $st):
                                $settledDateOnly = substr($st['settled_at'], 0, 10);
                                $stPeriodFrom = '';
                                $stPeriodTo   = '';
                                if (!empty($st['notes']) && preg_match('/\[Pay Period:\s*([^\]]+)\]/', $st['notes'], $m)) {
                                    $pParts = explode('|', trim($m[1]));
                                    if (count($pParts) >= 2) {
                                        $stPeriodFrom = $pParts[0];
                                        $stPeriodTo   = $pParts[1];
                                    }
                                }
                            ?>
                                <tr class="payroll-history-row hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors"
                                    data-date="<?= $settledDateOnly; ?>"
                                    data-period-from="<?= htmlspecialchars($stPeriodFrom); ?>"
                                    data-period-to="<?= htmlspecialchars($stPeriodTo); ?>">
                                    <td class="px-3 sm:px-4 py-3 font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        <?= htmlspecialchars($st['settlement_ticket']); ?>
                                    </td>
                                    <td class="px-2.5 sm:px-3 py-3 text-gray-600 dark:text-gray-300 text-xs whitespace-nowrap">
                                        <?= date('M d, Y h:i A', strtotime($st['settled_at'])); ?>
                                    </td>
                                    <td class="px-2.5 sm:px-3 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">
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
        let currentPayrollFrom = "<?= $defaultPeriod['from']; ?>";
        let currentPayrollTo = "<?= $defaultPeriod['to']; ?>";
        let isPayrollAllCycles = true;

        function updatePayrollQuickBtn(shortText, isCurrent, isAll) {
            const btnAll = document.getElementById('payrollAllCyclesBtn');
            const btnWeek = document.getElementById('payrollCurrentWeekBtn');
            const btnWeekText = document.getElementById('payrollCurrentWeekBtnText');
            const badgeSubtext = document.getElementById('activePayPeriodSubtext');

            if (isAll) {
                if (btnAll) {
                    btnAll.className = "px-3 h-8 rounded-xl border border-emerald-400 bg-emerald-500/30 text-emerald-200 hover:bg-emerald-500/40 text-xs font-extrabold active:scale-95 transition cursor-pointer whitespace-nowrap shadow-sm";
                }
                if (btnWeek) {
                    btnWeek.className = "px-3 h-8 rounded-xl border border-gray-700 bg-gray-800/60 text-gray-400 hover:text-white hover:bg-gray-700 text-xs font-semibold active:scale-95 transition cursor-pointer whitespace-nowrap";
                }
                if (btnWeekText) btnWeekText.textContent = 'This Week';
                if (badgeSubtext) badgeSubtext.textContent = 'Evaluating all accumulated unsettled driver trips across all past delivery weeks.';
            } else {
                if (btnAll) {
                    btnAll.className = "px-3 h-8 rounded-xl border border-gray-700 bg-gray-800/60 text-gray-400 hover:text-emerald-300 hover:bg-gray-700 text-xs font-semibold active:scale-95 transition cursor-pointer whitespace-nowrap";
                }
                if (btnWeekText) btnWeekText.textContent = shortText || 'This Week';
                if (btnWeek) {
                    if (isCurrent) {
                        btnWeek.title = "Current Week (This Week)";
                        btnWeek.className = "px-3 h-8 rounded-xl border border-emerald-400 bg-emerald-500/30 text-emerald-200 hover:bg-emerald-500/40 text-xs font-extrabold active:scale-95 transition cursor-pointer whitespace-nowrap shadow-sm";
                    } else {
                        btnWeek.title = `${shortText} (Click to return to This Week)`;
                        btnWeek.className = "px-3 h-8 rounded-xl border border-teal-400/50 bg-teal-900/30 text-teal-300 hover:bg-teal-800/40 text-xs font-bold active:scale-95 transition cursor-pointer whitespace-nowrap";
                    }
                }
                if (badgeSubtext) badgeSubtext.textContent = `Filtered view for delivery cycle: ${shortText || 'Selected Week'}`;
            }
        }

        function selectPayrollAllCycles() {
            const selector = document.getElementById('payPeriodSelector');
            if (selector) selector.value = 'ALL';
            onPayrollPeriodChange('ALL');
        }

        function onPayrollPeriodChange(val) {
            const selector = document.getElementById('payPeriodSelector');
            const customBar = document.getElementById('payrollCustomDateBar');
            const badge = document.getElementById('activePayPeriodBadge');

            if (val === 'CUSTOM') {
                if (customBar) customBar.classList.remove('hidden');
                if (badge) badge.textContent = 'Custom Date Range';
                updatePayrollQuickBtn('Custom', false, false);
                return;
            }

            if (customBar) customBar.classList.add('hidden');

            if (val === 'ALL') {
                isPayrollAllCycles = true;
                currentPayrollFrom = null;
                currentPayrollTo = null;
                if (badge) badge.textContent = 'All Delivery Cycles (All Pending)';
                updatePayrollQuickBtn('All Periods', false, true);
                recalculatePayrollForPeriod();
                return;
            }

            isPayrollAllCycles = false;
            const parts = val.split('|');
            if (parts.length === 2) {
                currentPayrollFrom = parts[0];
                currentPayrollTo = parts[1];

                const selectedOpt = selector ? selector.options[selector.selectedIndex] : null;
                const label = selectedOpt ? (selectedOpt.getAttribute('data-label') || selectedOpt.text) : `${parts[0]} – ${parts[1]}`;
                const shortLabel = selectedOpt ? (selectedOpt.getAttribute('data-short') || 'This Week') : 'This Week';
                const isCurrent = selector ? (selectedOpt && selectedOpt.getAttribute('data-short') === 'This Week') : false;

                if (badge) badge.textContent = label;
                updatePayrollQuickBtn(shortLabel, isCurrent, false);

                recalculatePayrollForPeriod();
            }
        }

        function shiftPayrollWeek(direction) {
            const selector = document.getElementById('payPeriodSelector');
            if (!selector) return;

            if (direction === 0) {
                for (let i = 0; i < selector.options.length; i++) {
                    if (selector.options[i].getAttribute('data-short') === 'This Week') {
                        selector.selectedIndex = i;
                        onPayrollPeriodChange(selector.value);
                        return;
                    }
                }
                return;
            }

            let newIndex = selector.selectedIndex + direction;
            const maxIndex = selector.options.length - 2;
            if (newIndex >= 1 && newIndex <= maxIndex) {
                selector.selectedIndex = newIndex;
                onPayrollPeriodChange(selector.value);
            }
        }

        function applyPayrollCustomDateRange() {
            const fromInput = document.getElementById('payrollCustomDateFrom');
            const toInput = document.getElementById('payrollCustomDateTo');
            const badge = document.getElementById('activePayPeriodBadge');

            if (!fromInput || !toInput) return;
            const from = fromInput.value;
            const to = toInput.value;

            if (!from || !to) {
                alert('Please select both from and to dates.');
                return;
            }
            if (from > to) {
                alert('The "from" date must be earlier than or equal to the "to" date.');
                return;
            }

            isPayrollAllCycles = false;
            currentPayrollFrom = from;
            currentPayrollTo = to;

            if (badge) {
                badge.textContent = `${from} – ${to}`;
            }
            updatePayrollQuickBtn('Custom', false, false);

            recalculatePayrollForPeriod();
        }

        function recalculatePayrollForPeriod() {
            const rows = document.querySelectorAll('#payrollTableBody .payroll-driver-row');
            const tableBody = document.getElementById('payrollTableBody');
            if (tableBody) {
                tableBody.style.transition = 'opacity 0.15s ease';
                tableBody.style.opacity = '0.5';
                setTimeout(() => {
                    tableBody.style.opacity = '1';
                }, 100);
            }

            let totalGrossSum = 0;
            let totalNetSum = 0;
            let totalTripsSum = 0;
            let payableDriversCount = 0;
            let allDriversTotalNetSum = 0;

            const activeBadge = document.getElementById('activePayPeriodBadge');
            const activePeriodLabel = isPayrollAllCycles ?
                'All Pending Delivery Cycles (All Weeks)' :
                (activeBadge ? activeBadge.textContent.trim() : (currentPayrollFrom && currentPayrollTo ? `${currentPayrollFrom} – ${currentPayrollTo}` : 'Current Week'));

            rows.forEach(row => {
                let tripsData = [];
                try {
                    tripsData = JSON.parse(row.getAttribute('data-trips') || '[]');
                } catch (e) {}

                const remBal = parseFloat(row.getAttribute('data-rembal') || 0);
                const advances = parseFloat(row.getAttribute('data-advances') || 0);
                const totalAllGrossFallback = parseFloat(row.getAttribute('data-total-gross') || 0);
                const totalAllNetFallback = parseFloat(row.getAttribute('data-total-net') || 0);
                const driverId = row.getAttribute('data-driver-id');
                const driverName = row.getAttribute('data-driver-name') || '';

                let periodGross = 0;
                let periodTripsCount = 0;
                let allUnsettledGross = 0;
                let allUnsettledTripsCount = 0;

                tripsData.forEach(t => {
                    if (t.paid === 0) {
                        allUnsettledGross += t.pay;
                        allUnsettledTripsCount++;
                        if (isPayrollAllCycles || (!currentPayrollFrom || !currentPayrollTo) || (t.date >= currentPayrollFrom && t.date <= currentPayrollTo)) {
                            periodGross += t.pay;
                            periodTripsCount++;
                        }
                    }
                });

                if (totalAllGrossFallback > allUnsettledGross && tripsData.length === 0) {
                    allUnsettledGross = totalAllGrossFallback;
                }

                const allUnsettledNet = Math.max(0, allUnsettledGross + remBal - advances);
                const periodNet = isPayrollAllCycles ? allUnsettledNet : Math.max(0, periodGross + remBal - advances);

                allDriversTotalNetSum += allUnsettledNet;

                if (isPayrollAllCycles) {
                    totalGrossSum += allUnsettledGross;
                    totalNetSum += allUnsettledNet;
                    totalTripsSum += allUnsettledTripsCount;
                    if (allUnsettledNet > 0) payableDriversCount++;
                } else {
                    totalGrossSum += periodGross;
                    totalNetSum += periodNet;
                    totalTripsSum += periodTripsCount;
                    if (periodNet > 0) payableDriversCount++;
                }

                // Update driver table cells
                const grossEl = row.querySelector('.driver-gross-val');
                const tripsEl = row.querySelector('.driver-trips-val');
                const netEl = row.querySelector('.driver-net-val');
                const allPendingEl = row.querySelector('.driver-all-pending-val');
                const btnContainer = row.querySelector('.driver-settle-btn-container');

                if (grossEl) {
                    const displayGross = isPayrollAllCycles ? allUnsettledGross : periodGross;
                    grossEl.textContent = `₱${displayGross.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }

                if (tripsEl) {
                    if (isPayrollAllCycles) {
                        tripsEl.textContent = `${allUnsettledTripsCount} pending trip${allUnsettledTripsCount !== 1 ? 's' : ''}`;
                    } else {
                        if (allUnsettledTripsCount > periodTripsCount) {
                            tripsEl.innerHTML = `${periodTripsCount} trip${periodTripsCount !== 1 ? 's' : ''} <span class="text-amber-500 font-semibold" title="Total unsettled trips across all past weeks">(${allUnsettledTripsCount} total)</span>`;
                        } else {
                            tripsEl.textContent = `${periodTripsCount} trip${periodTripsCount !== 1 ? 's' : ''}`;
                        }
                    }
                }

                if (netEl) {
                    const displayNet = isPayrollAllCycles ? allUnsettledNet : periodNet;
                    netEl.textContent = `₱${displayNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    netEl.className = `text-sm font-black driver-net-val ${displayNet > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'}`;
                }

                if (allPendingEl) {
                    if (isPayrollAllCycles) {
                        allPendingEl.innerHTML = allUnsettledNet > 0 ?
                            `<span class="text-emerald-600 dark:text-emerald-400 font-bold">All periods pending</span>` :
                            `<span class="text-gray-400 font-normal">No pending balance</span>`;
                    } else {
                        if (allUnsettledNet > periodNet && periodNet > 0) {
                            allPendingEl.innerHTML = `<span class="text-emerald-700 dark:text-emerald-300 font-bold" title="Total net payable across all past weeks"><i class="fa-solid fa-coins mr-0.5"></i>All Periods: ₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                        } else if (periodNet === 0 && allUnsettledNet > 0) {
                            allPendingEl.innerHTML = `<span class="text-amber-600 dark:text-amber-400 font-extrabold" title="Has pending payroll from previous weeks"><i class="fa-solid fa-clock-rotate-left mr-0.5"></i>Past Pending: ₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                        } else if (allUnsettledNet > 0) {
                            allPendingEl.innerHTML = `<span class="text-gray-400">All Periods: ₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                        } else {
                            allPendingEl.innerHTML = `<span class="text-gray-400 font-normal">No pending balance</span>`;
                        }
                    }
                }

                if (btnContainer) {
                    if (isPayrollAllCycles) {
                        if (allUnsettledNet > 0) {
                            btnContainer.innerHTML = `
                                <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${allUnsettledGross}, ${advances}, ${allUnsettledNet}, ${remBal}, '', '', 1, 'All Pending Delivery Cycles (All Weeks)')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer"
                                    title="Generate ticket and settle all pending payroll for this driver">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                    <span>Settle</span>
                                </button>
                            `;
                        } else {
                            btnContainer.innerHTML = `
                                <button type="button" disabled class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 transition flex items-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                    <span>Settled</span>
                                </button>
                            `;
                        }
                    } else {
                        // Filtered week view
                        if (allUnsettledNet > 0) {
                            if (allUnsettledNet > periodNet && periodNet > 0) {
                                btnContainer.innerHTML = `
                                    <div class="flex flex-col items-end gap-1">
                                        <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${allUnsettledGross}, ${advances}, ${allUnsettledNet}, ${remBal}, '', '', 1, 'All Pending Delivery Cycles (All Weeks)')"
                                            class="px-2.5 py-1 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1 active:scale-95 cursor-pointer"
                                            title="Generate ticket for all pending payroll across all past weeks (₱${allUnsettledNet.toFixed(2)})">
                                            <i class="fa-solid fa-money-bill-transfer"></i>
                                            <span>Settle All (₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})</span>
                                        </button>
                                        <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${periodGross}, ${advances}, ${periodNet}, ${remBal}, '${currentPayrollFrom}', '${currentPayrollTo}', 0, '${escapeJsQuotes(activePeriodLabel)}')"
                                            class="px-2.5 py-1 rounded-xl text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 shadow-sm transition flex items-center gap-1 active:scale-95 cursor-pointer">
                                            Settle Week Only (₱${periodNet.toFixed(2)})
                                        </button>
                                    </div>
                                `;
                            } else if (periodNet === 0 && allUnsettledNet > 0) {
                                btnContainer.innerHTML = `
                                    <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${allUnsettledGross}, ${advances}, ${allUnsettledNet}, ${remBal}, '', '', 1, 'All Pending Delivery Cycles (All Weeks)')"
                                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition flex items-center gap-1 active:scale-95 cursor-pointer"
                                        title="Generate ticket for pending payroll from past weeks">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <span>Settle Past (₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})</span>
                                    </button>
                                `;
                            } else {
                                btnContainer.innerHTML = `
                                    <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${periodGross}, ${advances}, ${periodNet}, ${remBal}, '${currentPayrollFrom}', '${currentPayrollTo}', 0, '${escapeJsQuotes(activePeriodLabel)}')"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                        <span>Settle</span>
                                    </button>
                                `;
                            }
                        } else {
                            btnContainer.innerHTML = `
                                <button type="button" disabled class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 transition flex items-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                    <span>Settled</span>
                                </button>
                            `;
                        }
                    }
                }
            });

            // Update KPI Displays
            const kpiNetEl = document.getElementById('kpiPeriodNetPayable');
            const kpiNetLabel = document.getElementById('kpiNetPayableLabel');
            const kpiGrossEl = document.getElementById('kpiPeriodGross');
            const kpiDriversCountEl = document.getElementById('kpiDriversCount');
            const kpiTripsCountEl = document.getElementById('kpiTripsCount');

            const formattedNet = `₱${totalNetSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            const formattedAllNet = `₱${allDriversTotalNetSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            if (kpiNetEl) {
                if (isPayrollAllCycles) {
                    kpiNetEl.textContent = formattedAllNet;
                } else {
                    kpiNetEl.innerHTML = `${formattedNet} <span class="text-xs font-normal text-gray-400 block mt-0.5">All-Periods: ${formattedAllNet}</span>`;
                }
            }
            if (kpiNetLabel) {
                kpiNetLabel.textContent = isPayrollAllCycles ? 'Total Net Payable (All Periods)' : 'Selected Period Net Payable';
            }
            if (kpiGrossEl) kpiGrossEl.textContent = `₱${totalGrossSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            if (kpiDriversCountEl) kpiDriversCountEl.textContent = `${payableDriversCount} driver${payableDriversCount !== 1 ? 's' : ''} awaiting payout`;
            if (kpiTripsCountEl) kpiTripsCountEl.textContent = `${totalTripsSum} trips ${isPayrollAllCycles ? 'unsettled (all time)' : 'completed in period'}`;

            // Sub-tab bar display
            const tabBarNet = document.getElementById('tabBarTotalNetPayable');
            const tabBarScope = document.getElementById('tabBarScopeBadge');
            if (tabBarNet) {
                tabBarNet.textContent = isPayrollAllCycles ? formattedAllNet : `${formattedNet} (All-Periods: ${formattedAllNet})`;
            }
            if (tabBarScope) {
                tabBarScope.textContent = isPayrollAllCycles ? 'All Periods' : 'Filtered Period';
                tabBarScope.className = isPayrollAllCycles ?
                    "text-[10px] text-emerald-600 dark:text-emerald-400 font-bold ml-0.5 px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40" :
                    "text-[10px] text-blue-600 dark:text-blue-400 font-bold ml-0.5 px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40";
            }

            // Table header scope
            const thNetScope = document.getElementById('thNetScopeLabel');
            if (thNetScope) {
                thNetScope.textContent = isPayrollAllCycles ? 'All periods pending' : 'Selected period net';
            }

            // Table footer display
            const tableFooterNet = document.getElementById('tableFooterTotalNetPayable');
            const tableFooterScope = document.getElementById('tableFooterScopeLabel');
            if (tableFooterNet) {
                tableFooterNet.textContent = isPayrollAllCycles ? formattedAllNet : `${formattedNet} (All-Periods: ${formattedAllNet})`;
            }
            if (tableFooterScope) {
                tableFooterScope.textContent = isPayrollAllCycles ? 'All Periods' : 'Selected Period';
            }

            // Filter History rows to match pay period
            filterHistoryTableForPeriod();
        }

        function filterHistoryTableForPeriod() {
            const historyRows = document.querySelectorAll('#payrollHistoryTableBody .payroll-history-row');
            const badge = document.getElementById('payrollHistoryCountBadge');
            let visibleCount = 0;

            historyRows.forEach(row => {
                const date = row.getAttribute('data-date');
                const pFrom = row.getAttribute('data-period-from');
                const pTo = row.getAttribute('data-period-to');

                if (isPayrollAllCycles || !currentPayrollFrom || !currentPayrollTo) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else if (pFrom && pTo) {
                    if ((pFrom >= currentPayrollFrom && pFrom <= currentPayrollTo) ||
                        (pTo >= currentPayrollFrom && pTo <= currentPayrollTo) ||
                        (pFrom <= currentPayrollFrom && pTo >= currentPayrollTo)) {
                        row.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        row.classList.add('hidden');
                    }
                } else if (date >= currentPayrollFrom && date <= currentPayrollTo) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (badge) badge.textContent = visibleCount;
        }

        function escapeJsQuotes(str) {
            return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

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

        // ── Driver Week Trips Modal ────────────────────────────────────────────
        let currentDwtmContext = null;

        function setDriverTripsFilter(filterType) {
            if (!currentDwtmContext) return;
            currentDwtmContext.filter = filterType;
            renderDriverTripsModal();
        }

        function openDriverWeekTripsModal(row, initialFilter = 'pending') {
            if (!row) return;
            const driverName = row.getAttribute('data-driver-name') || 'Driver';
            const driverId = row.getAttribute('data-driver-id');
            const remBal = parseFloat(row.getAttribute('data-rembal') || 0);
            const advances = parseFloat(row.getAttribute('data-advances') || 0);
            const periodBadge = document.getElementById('activePayPeriodBadge');
            const periodLabel = periodBadge ? periodBadge.textContent.trim() : (isPayrollAllCycles ? 'All Delivery Cycles (All Pending)' : 'This Week');

            let tripsData = [];
            try {
                tripsData = JSON.parse(row.getAttribute('data-trips') || '[]');
            } catch (e) {}

            currentDwtmContext = {
                driverName,
                driverId,
                remBal,
                advances,
                periodLabel,
                tripsData,
                filter: initialFilter
            };

            renderDriverTripsModal();

            const modal = document.getElementById('driverWeekTripsModal');
            if (modal) modal.classList.remove('hidden');
        }

        function renderDriverTripsModal() {
            if (!currentDwtmContext) return;
            const { driverName, driverId, remBal, advances, periodLabel, tripsData, filter } = currentDwtmContext;

            // Populate header
            const nameEl = document.getElementById('dwtm-driver-name');
            const labelEl = document.getElementById('dwtm-period-label');
            if (nameEl) nameEl.textContent = driverName;
            if (labelEl) labelEl.textContent = periodLabel;

            // Filter trips based on pay period scope
            const periodTrips = tripsData.filter(t => {
                if (isPayrollAllCycles) return true;
                if (!currentPayrollFrom || !currentPayrollTo) return true;
                return t.date >= currentPayrollFrom && t.date <= currentPayrollTo;
            });

            const pendingTrips = periodTrips.filter(t => Number(t.paid) === 0);
            const paidTrips = periodTrips.filter(t => Number(t.paid) === 1);

            // Update tab badges
            const bPending = document.getElementById('dwtm-badge-pending');
            const bAll = document.getElementById('dwtm-badge-all');
            const bPaid = document.getElementById('dwtm-badge-paid');
            if (bPending) bPending.textContent = pendingTrips.length;
            if (bAll) bAll.textContent = periodTrips.length;
            if (bPaid) bPaid.textContent = paidTrips.length;

            // Tab button styles
            const tabPending = document.getElementById('dwtm-tab-pending');
            const tabAll = document.getElementById('dwtm-tab-all');
            const tabPaid = document.getElementById('dwtm-tab-paid');

            const activeCls = 'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 shadow-sm border border-emerald-200 dark:border-emerald-600';
            const inactiveCls = 'px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 border border-transparent';

            if (tabPending) tabPending.className = filter === 'pending' ? activeCls : inactiveCls;
            if (tabAll) tabAll.className = filter === 'all' ? activeCls : inactiveCls;
            if (tabPaid) tabPaid.className = filter === 'paid' ? activeCls : inactiveCls;

            // Determine visible trips
            let displayList = periodTrips;
            if (filter === 'pending') {
                displayList = pendingTrips;
            } else if (filter === 'paid') {
                displayList = paidTrips;
            }

            // Summary values for visible subset
            let listGross = 0;
            displayList.forEach(t => {
                listGross += (parseFloat(t.pay) || 0);
            });

            // Compute overall unsettled totals for settle action
            let allUnsettledGross = 0;
            tripsData.forEach(t => {
                if (Number(t.paid) === 0) allUnsettledGross += (parseFloat(t.pay) || 0);
            });
            const allUnsettledNet = Math.max(0, allUnsettledGross + remBal - advances);

            // Summary bar
            const tripCountEl = document.getElementById('dwtm-trip-count');
            const totalPayEl = document.getElementById('dwtm-total-pay');
            const unpaidCountEl = document.getElementById('dwtm-unpaid-count');
            const unpaidBadge = document.getElementById('dwtm-unpaid-badge');

            if (tripCountEl) {
                const label = filter === 'pending' ? 'pending trip' : (filter === 'paid' ? 'settled trip' : 'trip');
                tripCountEl.textContent = `${displayList.length} ${label}${displayList.length !== 1 ? 's' : ''}`;
            }
            if (totalPayEl) {
                totalPayEl.textContent = `₱${listGross.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
            if (unpaidCountEl) {
                unpaidCountEl.textContent = `${pendingTrips.length} pending`;
            }
            if (unpaidBadge) {
                unpaidBadge.classList.toggle('hidden', pendingTrips.length === 0);
            }

            // Modal footer
            const unpaidSumEl = document.getElementById('dwtm-unpaid-sum');
            if (unpaidSumEl) {
                unpaidSumEl.textContent = `₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
            const caNoteEl = document.getElementById('dwtm-ca-deduction-note');
            if (caNoteEl) {
                if (advances > 0) {
                    caNoteEl.textContent = `Gross: ₱${allUnsettledGross.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} less ₱${advances.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} cash advances`;
                } else {
                    caNoteEl.textContent = '';
                }
            }

            const settleBtnContainer = document.getElementById('dwtm-settle-btn-container');
            if (settleBtnContainer) {
                if (allUnsettledNet > 0) {
                    settleBtnContainer.innerHTML = `
                        <button type="button" onclick="closeDriverWeekTripsModal(); openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${allUnsettledGross}, ${advances}, ${allUnsettledNet}, ${remBal}, '', '', 1, 'All Pending Delivery Cycles (All Weeks)')"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                            <span>Settle All Pending (₱${allUnsettledNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})</span>
                        </button>
                    `;
                } else {
                    settleBtnContainer.innerHTML = `
                        <span class="text-xs font-semibold text-gray-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>All Trips Settled</span>
                        </span>
                    `;
                }
            }

            // Build trip list
            const listEl = document.getElementById('dwtm-trip-list');
            if (!listEl) return;
            listEl.innerHTML = '';

            if (displayList.length === 0) {
                let emptyMsg = 'No trips found for this view';
                let emptySub = '';
                if (filter === 'pending') {
                    emptyMsg = 'All trips are settled';
                    emptySub = 'This driver has no pending payroll deliveries awaiting settlement.';
                } else if (filter === 'paid') {
                    emptyMsg = 'No settled trips yet';
                    emptySub = 'Completed deliveries will appear here once settled via disbursement vouchers.';
                }
                listEl.innerHTML = `
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">
                        <i class="fa-solid ${filter === 'pending' ? 'fa-circle-check text-emerald-500' : 'fa-truck text-gray-400'} text-3xl mb-2 opacity-60 block"></i>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">${emptyMsg}</p>
                        ${emptySub ? `<p class="text-xs text-gray-400 mt-1">${emptySub}</p>` : ''}
                    </div>
                `;
            } else {
                displayList.forEach((t, idx) => {
                    const isPaid = Number(t.paid) === 1;
                    const paidBadge = isPaid ?
                        `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40"><i class="fa-solid fa-check mr-1"></i>Settled</span>` :
                        `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40"><i class="fa-solid fa-hourglass-half mr-1"></i>Pending</span>`;

                    const dest = t.destination || 'N/A';
                    const ticket = t.ticket ? `<span class="font-mono text-[10px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-1.5 py-0.5 rounded">#${t.ticket}</span>` : '';
                    const distBadge = (parseFloat(t.distance) > 0) ? `<span class="text-[10px] text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded font-medium"><i class="fa-solid fa-route text-[9px] mr-0.5"></i>${t.distance} km</span>` : '';
                    const dateStr = t.date || '';
                    const fmtDate = dateStr ? (() => {
                        const d = new Date(dateStr + 'T00:00:00');
                        return d.toLocaleDateString('en-PH', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                    })() : '—';
                    const payFmt = `₱${(parseFloat(t.pay) || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                    const card = document.createElement('div');
                    card.className = `flex items-center gap-3 p-3 rounded-xl border ${isPaid ? 'border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/20 dark:bg-emerald-900/10' : 'border-amber-100/90 dark:border-amber-900/30 bg-amber-50/30 dark:bg-amber-900/10'} hover:shadow-sm transition`;
                    card.innerHTML = `
                        <div class="w-7 h-7 rounded-lg ${isPaid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'} flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">${idx + 1}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-rose-500 text-xs"></i>
                                    <span>${dest}</span>
                                </span>
                                ${ticket}
                                ${distBadge}
                            </div>
                            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-2">
                                <i class="fa-regular fa-calendar"></i>
                                <span>${fmtDate}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="font-bold text-sm ${isPaid ? 'text-gray-700 dark:text-gray-300' : 'text-emerald-600 dark:text-emerald-400'}">${payFmt}</span>
                            ${paidBadge}
                        </div>
                    `;
                    listEl.appendChild(card);
                });
            }
        }

        function closeDriverWeekTripsModal() {
            const modal = document.getElementById('driverWeekTripsModal');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDriverWeekTripsModal();
        });

        // Initial calculation for current period
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('view-payroll')) {
                recalculatePayrollForPeriod();
            }
        });
    </script>

</div>
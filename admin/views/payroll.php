<?php

$dayOfWeek = (int)date('N'); // 1 (Mon) to 7 (Sun)
$thisMonday = date('Y-m-d', strtotime('-' . ($dayOfWeek - 1) . ' days'));
$thisSaturday = date('Y-m-d', strtotime('+' . (6 - $dayOfWeek) . ' days'));
$thisSunday = date('Y-m-d', strtotime('+' . (7 - $dayOfWeek) . ' days'));

// Generate 12 past weekly pay periods (Monday to Sunday, covering Monday-Saturday working days)
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

    <!-- Page Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold shadow-sm">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Payroll Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage weekly pay periods, driver compensation, trip earnings, carried balances, cash advance deductions, and disbursement settlements.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Driver Input -->
            <div class="relative flex-1 md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="payrollDriverSearchInput" placeholder="Search driver, CDL, truck..." oninput="filterPayrollTable()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <button type="button" id="payrollSearchClear" onclick="clearPayrollSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Quick Link to Cash Advances -->
            <button type="button" onclick="switchTab('cash_advances')" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition flex items-center gap-2 flex-shrink-0 cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600 dark:text-amber-400"></i>
                <span>Cash Advances</span>
                <?php if (($pendingCashAdvanceCount ?? 0) > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white"><?= $pendingCashAdvanceCount ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <!-- Pay Period Selector Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pay Period:</span>
                        <span id="activePayPeriodBadge" class="text-xs font-extrabold px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <?= htmlspecialchars($defaultPeriod['clean_dates']); ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Select a weekly delivery cycle to evaluate driver trip earnings and disburse wages.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Dropdown selector with dates -->
                <div class="relative min-w-[270px] sm:min-w-[320px]">
                    <select id="payPeriodSelector" onchange="onPayrollPeriodChange(this.value)"
                            class="w-full text-xs font-semibold py-2.5 px-3.5 pr-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 appearance-none shadow-xs">
                        <?php foreach ($payrollPayPeriods as $p): ?>
                            <option value="<?= $p['from'] . '|' . $p['to']; ?>" 
                                    data-label="<?= htmlspecialchars($p['clean_dates']); ?>" 
                                    data-short="<?= htmlspecialchars($p['short_label']); ?>"
                                    <?= $p['is_current'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p['label']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="ALL" data-label="All Delivery Cycles (All Time)" data-short="All Weeks">Show All Past Unsettled Trips</option>
                        <option value="CUSTOM" data-label="Custom Date Range" data-short="Custom">Custom Date Range...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400 text-xs">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Previous / Next Week Quick Shift Buttons -->
                <div class="flex items-center gap-1">
                    <button type="button" onclick="shiftPayrollWeek(1)" title="Previous Week"
                            class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-xs active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" id="payrollCurrentWeekBtn" onclick="shiftPayrollWeek(0)" title="Current Week (This Week)"
                            class="px-3 h-9 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-800/40 text-xs font-bold active:scale-95 transition cursor-pointer whitespace-nowrap">
                        <span id="payrollCurrentWeekBtnText">This Week</span>
                    </button>
                    <button type="button" onclick="shiftPayrollWeek(-1)" title="Next Week"
                            class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-xs active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Bar (Shown when CUSTOM is selected) -->
        <div id="payrollCustomDateBar" class="hidden mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/80 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-days text-emerald-500"></i> Custom Pay Period Range:
                </span>
                <div class="flex items-center gap-1.5">
                    <input type="date" id="payrollCustomDateFrom" value="<?= $thisMonday ?>"
                           class="px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs">
                    <span class="text-gray-400 font-bold">to</span>
                    <input type="date" id="payrollCustomDateTo" value="<?= $thisSunday ?>"
                           class="px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs">
                </div>
                <button type="button" onclick="applyPayrollCustomDateRange()"
                        class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition active:scale-95 cursor-pointer">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- KPI 1: Net Payable -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Pay Period Net Payable</span>
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

        <!-- KPI 2: Period Gross Earnings -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-blue-200/70 dark:border-blue-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Period Trip Gross</span>
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

        <!-- KPI 3: Cash Advance Deductions -->
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

        <!-- KPI 4: Carried Balance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-indigo-200/70 dark:border-indigo-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Carried Balances</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5" id="kpiCarriedBalance">
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

    <!-- Sub Navigation Tabs -->
    <div class="mb-6 flex border-b border-gray-200 dark:border-gray-700 gap-6 text-sm font-semibold">
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
    </div>

    <!-- Sub-tab 1: Active Driver Payroll Table -->
    <div id="payrollSubTabActive" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[10px] sm:text-xs font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3.5 sm:px-6">Driver & Assigned Truck</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-right">Pay Period Trips</th>
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
                            
                            $deliveredTripsList = array_values(array_filter($driver['all_trips'] ?? [], fn($t) => ($t['status'] ?? '') === 'Delivered'));
                            $driverTripsJson = htmlspecialchars(json_encode(array_map(function($t) {
                                return [
                                    'id'    => $t['id'] ?? 0,
                                    'date'  => !empty($t['transit_end_time']) ? substr($t['transit_end_time'], 0, 10) : (!empty($t['trip_date']) ? substr($t['trip_date'], 0, 10) : substr($t['created_at'] ?? '', 0, 10)),
                                    'pay'   => floatval($t['pay_amount'] ?? 0),
                                    'paid'  => !empty($t['is_payroll_paid']) ? 1 : 0
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
                                
                                <!-- Driver & Truck -->
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

                                <!-- Status Badge -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full text-white <?= $driver['status'] === 'Active' ? 'bg-emerald-500' : ($driver['status'] === 'Dispatched' ? 'bg-blue-600' : ($driver['status'] === 'Resigned' ? 'bg-amber-600' : 'bg-gray-500')) ?>">
                                        <?= htmlspecialchars($driver['status']); ?>
                                    </span>
                                </td>

                                <!-- Pay Period Trips & Gross -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="font-bold text-gray-900 dark:text-gray-100 driver-gross-val">
                                        ₱<?= number_format($driver['gross_earnings'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 driver-trips-val">
                                        <?= $unclaimedTripsCount ?> trip<?= $unclaimedTripsCount !== 1 ? 's' : '' ?>
                                    </div>
                                </td>

                                <!-- Carried Balance -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="font-semibold text-indigo-600 dark:text-indigo-400">
                                        ₱<?= number_format($driver['remaining_balance'] ?? 0, 2); ?>
                                    </div>
                                    <button type="button" onclick="openAdjustBalanceModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['remaining_balance'] ?? 0; ?>)" class="text-[10px] font-semibold text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline">
                                        Adjust
                                    </button>
                                </td>

                                <!-- Cash Advances -->
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

                                <!-- Net Payable -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <span class="text-sm font-black driver-net-val <?= $hasPayable ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' ?>">
                                        ₱<?= number_format($driver['net_earnings'] ?? 0, 2); ?>
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3.5 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <span class="driver-settle-btn-container">
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
                                        </span>

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

            <!-- Empty Search Results -->
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

    <!-- Sub-tab 2: Settlement History Archive -->
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
                        <tbody id="payrollHistoryTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <?php foreach ($payrollSettlements as $st): 
                                $settledDateOnly = substr($st['settled_at'], 0, 10);
                            ?>
                                <tr class="payroll-history-row hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors" data-date="<?= $settledDateOnly; ?>">
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
let currentPayrollFrom = "<?= $defaultPeriod['from']; ?>";
let currentPayrollTo   = "<?= $defaultPeriod['to']; ?>";
let isPayrollAllCycles = false;

function updatePayrollQuickBtn(shortText, isCurrent) {
    const btn = document.getElementById('payrollCurrentWeekBtn');
    const btnText = document.getElementById('payrollCurrentWeekBtnText');
    if (btnText) btnText.textContent = shortText || 'This Week';
    if (btn) {
        if (isCurrent) {
            btn.title = "Current Week (This Week)";
            btn.className = "px-3 h-9 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-800/40 text-xs font-bold active:scale-95 transition cursor-pointer whitespace-nowrap";
        } else {
            btn.title = `${shortText} (Click to return to This Week)`;
            btn.className = "px-3 h-9 rounded-xl border border-teal-300 dark:border-teal-700 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-800/40 text-xs font-bold active:scale-95 transition cursor-pointer whitespace-nowrap";
        }
    }
}

function onPayrollPeriodChange(val) {
    const selector = document.getElementById('payPeriodSelector');
    const customBar = document.getElementById('payrollCustomDateBar');
    const badge = document.getElementById('activePayPeriodBadge');
    
    if (val === 'CUSTOM') {
        if (customBar) customBar.classList.remove('hidden');
        if (badge) badge.textContent = 'Custom Date Range';
        updatePayrollQuickBtn('Custom', false);
        return;
    }
    
    if (customBar) customBar.classList.add('hidden');
    
    if (val === 'ALL') {
        isPayrollAllCycles = true;
        currentPayrollFrom = null;
        currentPayrollTo = null;
        if (badge) badge.textContent = 'All Unsettled Delivery Cycles';
        updatePayrollQuickBtn('All Weeks', false);
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
        const isCurrent = selector ? (selector.selectedIndex === 0) : true;

        if (badge) badge.textContent = label;
        updatePayrollQuickBtn(shortLabel, isCurrent);

        recalculatePayrollForPeriod();
    }
}

function shiftPayrollWeek(direction) {
    const selector = document.getElementById('payPeriodSelector');
    if (!selector) return;

    if (direction === 0) {
        // Reset to "This Week" (index 0)
        selector.selectedIndex = 0;
        onPayrollPeriodChange(selector.value);
        return;
    }

    // Move index (direction 1 = older week, -1 = newer week)
    let newIndex = selector.selectedIndex + direction;
    // Keep within the generated weekly period options (exclude ALL and CUSTOM)
    const maxWeeksIndex = selector.options.length - 3;
    if (newIndex >= 0 && newIndex <= maxWeeksIndex) {
        selector.selectedIndex = newIndex;
        onPayrollPeriodChange(selector.value);
    }
}

function applyPayrollCustomDateRange() {
    const fromInput = document.getElementById('payrollCustomDateFrom');
    const toInput   = document.getElementById('payrollCustomDateTo');
    const badge     = document.getElementById('activePayPeriodBadge');

    if (!fromInput || !toInput) return;
    const from = fromInput.value;
    const to   = toInput.value;

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
    currentPayrollTo   = to;

    if (badge) {
        badge.textContent = `${from} – ${to}`;
    }
    updatePayrollQuickBtn('Custom', false);

    recalculatePayrollForPeriod();
}

function recalculatePayrollForPeriod() {
    const rows = document.querySelectorAll('#payrollTableBody .payroll-driver-row');
    let totalGrossSum = 0;
    let totalNetSum = 0;
    let totalTripsSum = 0;
    let payableDriversCount = 0;

    rows.forEach(row => {
        let tripsData = [];
        try {
            tripsData = JSON.parse(row.getAttribute('data-trips') || '[]');
        } catch(e) {}

        const remBal = parseFloat(row.getAttribute('data-rembal') || 0);
        const advances = parseFloat(row.getAttribute('data-advances') || 0);
        const totalAllGross = parseFloat(row.getAttribute('data-total-gross') || 0);
        const totalAllNet = parseFloat(row.getAttribute('data-total-net') || 0);
        const driverId = row.getAttribute('data-driver-id');
        const driverName = row.getAttribute('data-driver-name') || '';

        let periodGross = 0;
        let periodTripsCount = 0;

        if (isPayrollAllCycles) {
            periodGross = totalAllGross;
            periodTripsCount = tripsData.filter(t => t.paid === 0).length;
        } else {
            tripsData.forEach(t => {
                if (t.paid === 0 && t.date >= currentPayrollFrom && t.date <= currentPayrollTo) {
                    periodGross += t.pay;
                    periodTripsCount++;
                }
            });
        }

        const periodNet = Math.max(0, periodGross + remBal - advances);

        totalGrossSum += periodGross;
        totalNetSum += periodNet;
        totalTripsSum += periodTripsCount;
        if (periodNet > 0) payableDriversCount++;

        // Update driver table cells
        const grossEl = row.querySelector('.driver-gross-val');
        const tripsEl = row.querySelector('.driver-trips-val');
        const netEl   = row.querySelector('.driver-net-val');
        const btnContainer = row.querySelector('.driver-settle-btn-container');

        if (grossEl) grossEl.textContent = `₱${periodGross.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        if (tripsEl) tripsEl.textContent = `${periodTripsCount} trip${periodTripsCount !== 1 ? 's' : ''}`;
        
        if (netEl) {
            netEl.textContent = `₱${periodNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            netEl.className = `text-sm font-black driver-net-val ${periodNet > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'}`;
        }

        if (btnContainer) {
            if (periodNet > 0) {
                btnContainer.innerHTML = `
                    <button type="button" onclick="openSettlePayrollModal(${driverId}, '${escapeJsQuotes(driverName)}', ${periodGross}, ${advances}, ${periodNet}, ${remBal})"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
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
        }
    });

    // Update Top Financial KPI Cards
    const kpiNetEl = document.getElementById('kpiPeriodNetPayable');
    const kpiGrossEl = document.getElementById('kpiPeriodGross');
    const kpiDriversCountEl = document.getElementById('kpiDriversCount');
    const kpiTripsCountEl = document.getElementById('kpiTripsCount');

    if (kpiNetEl) kpiNetEl.textContent = `₱${totalNetSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    if (kpiGrossEl) kpiGrossEl.textContent = `₱${totalGrossSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    if (kpiDriversCountEl) kpiDriversCountEl.textContent = `${payableDriversCount} driver${payableDriversCount !== 1 ? 's' : ''} awaiting payout`;
    if (kpiTripsCountEl) kpiTripsCountEl.textContent = `${totalTripsSum} trips completed in period`;

    // Filter History rows to match pay period
    filterHistoryTableForPeriod();
}

function filterHistoryTableForPeriod() {
    const historyRows = document.querySelectorAll('#payrollHistoryTableBody .payroll-history-row');
    const badge = document.getElementById('payrollHistoryCountBadge');
    let visibleCount = 0;

    historyRows.forEach(row => {
        const date = row.getAttribute('data-date');
        if (isPayrollAllCycles || !currentPayrollFrom || !currentPayrollTo) {
            row.classList.remove('hidden');
            visibleCount++;
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

// Initial calculation for current period
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('view-payroll')) {
        recalculatePayrollForPeriod();
    }
});
</script>

</div>

<?php
// view-cash_advances — Driver Cash Advance Requests Management
$totalPendingAmount = array_sum(array_column($pendingCashAdvances ?? [], 'amount'));
$approvedAdvancesList = array_filter($allCashAdvances ?? [], fn($ca) => $ca['status'] === 'Approved');
$totalApprovedAmount = array_sum(array_column($approvedAdvancesList, 'amount'));
$settledAdvancesList = array_filter($approvedAdvancesList, fn($ca) => !empty($ca['is_settled']));
$totalSettledAmount = array_sum(array_column($settledAdvancesList, 'amount'));
$unsettledAdvancesList = array_filter($approvedAdvancesList, fn($ca) => empty($ca['is_settled']));
$totalUnsettledAmount = array_sum(array_column($unsettledAdvancesList, 'amount'));
?>
<div id="view-cash_advances" class="tab-content hidden">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Cash Advance Requests</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage, approve, and track driver cash advance requests. Approved advances deduct automatically upon payroll settlement.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <?php if (!empty($pendingCashAdvances)): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <?= count($pendingCashAdvances) ?> Awaiting Approval (₱<?= number_format($totalPendingAmount, 2) ?>)
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40">
                    <i class="fa-solid fa-check"></i> All Requests Reviewed
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Pending Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-amber-200/70 dark:border-amber-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pending Review</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalPendingAmount, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        <?= count($pendingCashAdvances ?? []) ?> request<?= count($pendingCashAdvances ?? []) !== 1 ? 's' : '' ?> awaiting action
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
        </div>

        <!-- Total Approved Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Approved</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalApprovedAmount, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        <?= count($approvedAdvancesList) ?> advance<?= count($approvedAdvancesList) !== 1 ? 's' : '' ?> granted
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        </div>

        <!-- Unsettled in Payroll Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-blue-200/70 dark:border-blue-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Active Deductions</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalUnsettledAmount, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        <?= count($unsettledAdvancesList) ?> to deduct in next payroll
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
        </div>

        <!-- Settled Payroll Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Settled & Deducted</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5">
                        ₱<?= number_format($totalSettledAmount, 2) ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-block">
                        <?= count($settledAdvancesList) ?> settled in driver payroll
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-gray-400 to-gray-500"></div>
        </div>
    </div>

    <!-- PENDING CASH ADVANCE REQUESTS SECTION -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-amber-200 dark:border-amber-900/50 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-amber-100 dark:border-amber-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-amber-50/60 dark:bg-amber-950/20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-amber-500/20">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pending Driver Requests</h3>
                        <p class="text-xs text-amber-700 dark:text-amber-400 font-medium">
                            <?= count($pendingCashAdvances ?? []) ?> request<?= count($pendingCashAdvances ?? []) !== 1 ? 's' : '' ?> awaiting your approval
                        </p>
                    </div>
                </div>
                <?php if (!empty($pendingCashAdvances)): ?>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-200/80 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 self-start sm:self-auto">
                        Action Required
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($pendingCashAdvances)): ?>
                <div class="p-12 text-center">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-600 dark:text-emerald-400 text-2xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No Pending Requests</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                        When drivers submit cash advance requests from their mobile portal, they will appear here immediately for approval.
                    </p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($pendingCashAdvances as $ca): ?>
                        <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-amber-50/20 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-start sm:items-center space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base"><?= htmlspecialchars($ca['driver_name']); ?></h4>
                                        <?php if (!empty($ca['cdl_number'])): ?>
                                            <span class="text-[11px] px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono">
                                                CDL: <?= htmlspecialchars($ca['cdl_number']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($ca['phone'])): ?>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                                <i class="fa-solid fa-phone text-[10px] mr-0.5"></i> <?= htmlspecialchars($ca['phone']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-1 flex items-center flex-wrap gap-x-3 gap-y-1">
                                        <span class="text-lg font-black text-amber-600 dark:text-amber-400">
                                            ₱<?= number_format($ca['amount'], 2); ?>
                                        </span>
                                        <?php if (!empty($ca['reason'])): ?>
                                            <span class="text-xs text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 px-2.5 py-1 rounded-lg">
                                                <i class="fa-regular fa-comment-dots mr-1 text-gray-400"></i><?= htmlspecialchars($ca['reason']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">No reason provided</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                        <i class="fa-regular fa-clock mr-1"></i>Requested on <?= date('M d, Y \a\t h:i A', strtotime($ca['requested_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2.5 flex-shrink-0 self-end lg:self-center">
                                <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Approve cash advance of ₱<?= number_format($ca['amount'], 2); ?> for <?= htmlspecialchars(addslashes($ca['driver_name'])); ?>?');">
                                    <input type="hidden" name="action" value="approve_cash_advance">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="ca_id" value="<?= $ca['id']; ?>">
                                    <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 shadow-sm transition flex items-center gap-1.5 active:scale-95">
                                        <i class="fa-solid fa-print"></i> Approve & Print Ticket
                                    </button>
                                </form>
                                <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Are you sure you want to REJECT this cash advance request for <?= htmlspecialchars(addslashes($ca['driver_name'])); ?>?');">
                                    <input type="hidden" name="action" value="reject_cash_advance">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="ca_id" value="<?= $ca['id']; ?>">
                                    <button type="submit" class="px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800 transition flex items-center gap-1.5 active:scale-95">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ALL CASH ADVANCES / HISTORY SECTION -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">Cash Advance History & Records</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">All approved, pending, and rejected cash advances across all drivers</p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input type="text" id="caSearchInput" onkeyup="filterCashAdvances()" placeholder="Search driver or reason..." class="pl-8 pr-3 py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 sm:w-56 text-gray-800 dark:text-gray-200">
                </div>
                <select id="caStatusFilter" onchange="filterCashAdvances()" class="px-3 py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-300">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
                <select id="caSettledFilter" onchange="filterCashAdvances()" class="px-3 py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-300">
                    <option value="">All Deductions</option>
                    <option value="unsettled">Active / Unsettled</option>
                    <option value="settled">Settled in Payroll</option>
                </select>
            </div>
        </div>

        <?php if (empty($allCashAdvances)): ?>
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400 text-2xl">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No Records Yet</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">There are no cash advance records in the database.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 uppercase font-semibold text-[10px] tracking-wider">
                            <th class="py-3.5 px-4">Ticket # / ID</th>
                            <th class="py-3.5 px-4">Driver</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Reason</th>
                            <th class="py-3.5 px-4">Requested</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Payroll Deduction</th>
                            <th class="py-3.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300">
                        <?php foreach ($allCashAdvances as $ca):
                            $ticketNum = 'CA-' . date('Y', strtotime($ca['requested_at'] ?? 'now')) . '-' . str_pad($ca['driver_id'], 3, '0', STR_PAD_LEFT) . '-' . str_pad($ca['id'], 4, '0', STR_PAD_LEFT);
                            $isSettled = !empty($ca['is_settled']);
                        ?>
                            <tr class="ca-row hover:bg-gray-50/70 dark:hover:bg-gray-750 transition-colors"
                                data-driver="<?= htmlspecialchars(strtolower($ca['driver_name'] ?? '')) ?>"
                                data-reason="<?= htmlspecialchars(strtolower($ca['reason'] ?? '')) ?>"
                                data-status="<?= htmlspecialchars($ca['status'] ?? '') ?>"
                                data-settled="<?= $isSettled ? 'settled' : 'unsettled' ?>">
                                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-gray-100">
                                    #<?= htmlspecialchars($ticketNum) ?>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center text-[10px] font-bold">
                                            <?= strtoupper(substr($ca['driver_name'] ?? 'D', 0, 1)) ?>
                                        </div>
                                        <span><?= htmlspecialchars($ca['driver_name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-amber-600 dark:text-amber-400">
                                    ₱<?= number_format($ca['amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs truncate text-gray-600 dark:text-gray-300" title="<?= htmlspecialchars($ca['reason'] ?? '') ?>">
                                    <?= !empty($ca['reason']) ? htmlspecialchars($ca['reason']) : '<span class="text-gray-400 italic">None</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <?= date('M d, Y h:i A', strtotime($ca['requested_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($ca['status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Pending
                                        </span>
                                    <?php elseif ($ca['status'] === 'Approved'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i class="fa-solid fa-check text-[10px]"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <i class="fa-solid fa-xmark text-[10px]"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($ca['status'] === 'Approved'): ?>
                                        <?php if ($isSettled): ?>
                                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold" title="Deducted in settlement <?= htmlspecialchars($ca['settled_at'] ?? '') ?>">
                                                <i class="fa-solid fa-circle-check"></i> Settled
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 font-semibold">
                                                <i class="fa-solid fa-clock"></i> Active (Pending Payroll)
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <?php if ($ca['status'] === 'Approved'): ?>
                                        <button onclick="window.open('print_cash_advance.php?id=<?= $ca['id']; ?>', '_blank')"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition inline-flex items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-print"></i>
                                            <span>Print</span>
                                        </button>
                                    <?php elseif ($ca['status'] === 'Pending'): ?>
                                        <div class="inline-flex items-center gap-1">
                                            <form method="POST" action="dashboard.php" class="inline">
                                                <input type="hidden" name="action" value="approve_cash_advance">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <input type="hidden" name="ca_id" value="<?= $ca['id']; ?>">
                                                <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="dashboard.php" class="inline">
                                                <input type="hidden" name="action" value="reject_cash_advance">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <input type="hidden" name="ca_id" value="<?= $ca['id']; ?>">
                                                <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-[11px] italic">No action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="caNoMatches" class="p-8 text-center text-xs text-gray-500 dark:text-gray-400 hidden">
                No cash advance records match your filter criteria.
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterCashAdvances() {
    const search = (document.getElementById('caSearchInput')?.value || '').toLowerCase().trim();
    const status = document.getElementById('caStatusFilter')?.value || '';
    const settled = document.getElementById('caSettledFilter')?.value || '';
    const rows = document.querySelectorAll('.ca-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const driver = (row.dataset.driver || '').toLowerCase();
        const reason = (row.dataset.reason || '').toLowerCase();
        const rowStatus = row.dataset.status || '';
        const rowSettled = row.dataset.settled || '';

        const matchesSearch = !search || driver.includes(search) || reason.includes(search);
        const matchesStatus = !status || rowStatus === status;
        const matchesSettled = !settled || rowSettled === settled;

        if (matchesSearch && matchesStatus && matchesSettled) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noMatchesEl = document.getElementById('caNoMatches');
    if (noMatchesEl) {
        noMatchesEl.classList.toggle('hidden', visibleCount > 0);
    }
}
</script>

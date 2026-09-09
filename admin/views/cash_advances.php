<?php

$totalPendingAmount = array_sum(array_column($pendingCashAdvances ?? [], 'amount'));
$approvedAdvancesList = array_filter($allCashAdvances ?? [], fn($ca) => $ca['status'] === 'Approved');
$totalApprovedAmount = array_sum(array_column($approvedAdvancesList, 'amount'));
$settledAdvancesList = array_filter($approvedAdvancesList, fn($ca) => !empty($ca['is_settled']));
$totalSettledAmount = array_sum(array_column($settledAdvancesList, 'amount'));
$unsettledAdvancesList = array_filter($approvedAdvancesList, fn($ca) => empty($ca['is_settled']));
$totalUnsettledAmount = array_sum(array_column($unsettledAdvancesList, 'amount'));
?>
<div id="view-cash_advances" class="tab-content hidden">

    
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
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            
            <div class="relative flex-1 md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="caSearchInput" placeholder="Search driver, reason, amount, ticket #..." oninput="syncCaSearch(this.value, 'top')" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="caSearchClear" onclick="clearCaSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php if (!empty($pendingCashAdvances)): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <?= count($pendingCashAdvances) ?> Awaiting Approval (₱<?= number_format($totalPendingAmount, 2) ?>)
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40 whitespace-nowrap">
                    <i class="fa-solid fa-check"></i> All Requests Reviewed
                </span>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
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
                    <?php foreach ($pendingCashAdvances as $ca):
                        $pendingSearchMeta = strtolower(implode(' ', array_filter([
                            $ca['driver_name'] ?? '',
                            $ca['cdl_number'] ?? '',
                            $ca['phone'] ?? '',
                            $ca['reason'] ?? '',
                            $ca['amount'] ?? '',
                            'pending'
                        ])));
                    ?>
                        <div class="pending-ca-card p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-amber-50/20 dark:hover:bg-gray-700/50 transition-colors" data-search="<?= htmlspecialchars($pendingSearchMeta) ?>">
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
                                <button type="button"
                                    onclick="openCaConfirmModal('approve', <?= $ca['id']; ?>, '<?= htmlspecialchars(addslashes($ca['driver_name'])); ?>', '<?= number_format($ca['amount'], 2); ?>', '<?= $_SESSION['csrf_token'] ?? '' ?>')"
                                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 shadow-sm transition flex items-center gap-1.5 active:scale-95">
                                    <i class="fa-solid fa-print"></i> Approve & Print Ticket
                                </button>
                                <button type="button"
                                    onclick="openCaConfirmModal('reject', <?= $ca['id']; ?>, '<?= htmlspecialchars(addslashes($ca['driver_name'])); ?>', '<?= number_format($ca['amount'], 2); ?>', '<?= $_SESSION['csrf_token'] ?? '' ?>')"
                                    class="px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800 transition flex items-center gap-1.5 active:scale-95">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="caPendingNoMatches" class="p-8 text-center text-xs text-gray-500 dark:text-gray-400 hidden">
                        <i class="fa-solid fa-magnifying-glass text-xl mb-2 opacity-40 block mx-auto"></i>
                        No pending requests match your search.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 bg-gray-50/50 dark:bg-gray-900/30">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg flex-shrink-0">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">Cash Advance History & Records</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">All approved, pending, and rejected cash advances across all drivers</p>
                </div>
            </div>

            
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <div class="relative w-full sm:w-auto">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input type="text" id="caTableSearchInput" oninput="syncCaSearch(this.value, 'table')" placeholder="Filter records..." class="pl-8 pr-3 py-2 sm:py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-52 text-gray-800 dark:text-gray-200">
                </div>
                <select id="caStatusFilter" onchange="filterCashAdvances()" class="px-3 py-2 sm:py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-300 flex-1 sm:flex-initial">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
                <select id="caSettledFilter" onchange="filterCashAdvances()" class="px-3 py-2 sm:py-1.5 rounded-xl text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:text-gray-300 flex-1 sm:flex-initial">
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
            <div class="divide-y divide-gray-100 dark:divide-gray-700/80">
                <?php foreach ($allCashAdvances as $ca):
                    $ticketNum = 'CA-' . date('Y', strtotime($ca['requested_at'] ?? 'now')) . '-' . str_pad($ca['driver_id'], 3, '0', STR_PAD_LEFT) . '-' . str_pad($ca['id'], 4, '0', STR_PAD_LEFT);
                    $isSettled = !empty($ca['is_settled']);

                    $chipStyle = 'chip-amber';
                    if ($ca['status'] === 'Approved') $chipStyle = 'chip-emerald';
                    if ($ca['status'] === 'Rejected') $chipStyle = 'chip-rose';
                ?>
                    <div class="ca-row p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors"
                        data-driver="<?= htmlspecialchars(strtolower($ca['driver_name'] ?? '')) ?>"
                        data-reason="<?= htmlspecialchars(strtolower($ca['reason'] ?? '')) ?>"
                        data-status="<?= htmlspecialchars($ca['status'] ?? '') ?>"
                        data-settled="<?= $isSettled ? 'settled' : 'unsettled' ?>"
                        data-search="<?= htmlspecialchars(strtolower($ticketNum . ' ' . ($ca['ticket_number'] ?? '') . ' ' . ($ca['driver_name'] ?? '') . ' ' . ($ca['reason'] ?? '') . ' ' . ($ca['amount'] ?? '') . ' ' . ($ca['status'] ?? '') . ' ' . ($isSettled ? 'settled' : 'unsettled active'))) ?>">
                        
                        
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 flex-shrink-0">
                                <i class="fa-solid fa-receipt text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                    <span class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate font-mono">
                                        #<?= htmlspecialchars($ticketNum); ?>
                                    </span>
                                    <span class="font-black text-amber-600 dark:text-amber-400 text-sm">
                                        ₱<?= number_format($ca['amount'], 2); ?>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate mt-0.5">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($ca['driver_name']); ?></span>
                                    <?php if (!empty($ca['reason'])): ?>
                                        <span class="text-gray-400 dark:text-gray-500"> • <?= htmlspecialchars($ca['reason']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-0.5 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span><?= date('M d, Y h:i A', strtotime($ca['requested_at'])); ?></span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center gap-2">
                                <span class="<?= $chipStyle; ?>">
                                    <?= htmlspecialchars($ca['status']); ?>
                                </span>
                                <?php if ($ca['status'] === 'Approved'): ?>
                                    <?php if ($isSettled): ?>
                                        <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200/60 dark:border-emerald-800/40">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Settled
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200/60 dark:border-blue-800/40">
                                            <i class="fa-solid fa-clock text-[10px]"></i> Active
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <?php if ($ca['status'] === 'Approved'): ?>
                                    <button onclick="window.open('print_cash_advance.php?id=<?= $ca['id']; ?>', '_blank')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition inline-flex items-center gap-1 shadow-sm active:scale-95"
                                            title="Print Voucher">
                                        <i class="fa-solid fa-print text-xs"></i>
                                        <span>Print</span>
                                    </button>
                                <?php elseif ($ca['status'] === 'Pending'): ?>
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                            onclick="openCaConfirmModal('approve', <?= $ca['id']; ?>, '<?= htmlspecialchars(addslashes($ca['driver_name'])); ?>', '<?= number_format($ca['amount'], 2); ?>', '<?= $_SESSION['csrf_token'] ?? '' ?>')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition active:scale-95">
                                            Approve
                                        </button>
                                        <button type="button"
                                            onclick="openCaConfirmModal('reject', <?= $ca['id']; ?>, '<?= htmlspecialchars(addslashes($ca['driver_name'])); ?>', '<?= number_format($ca['amount'], 2); ?>', '<?= $_SESSION['csrf_token'] ?? '' ?>')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition active:scale-95">
                                            Reject
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">No action</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="caNoMatches" class="p-8 text-center text-xs text-gray-500 dark:text-gray-400 hidden">
                No cash advance records match your filter criteria.
            </div>
        <?php endif; ?>
    </div>

</div>


<div id="caConfirmModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900 bg-opacity-60 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative flex flex-col">
        
        <div id="caConfirmModalHeader" class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div id="caConfirmIconWrap" class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                    <i id="caConfirmIcon" class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h3 id="caConfirmTitle" class="text-base font-bold text-gray-900 dark:text-gray-100"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <button onclick="closeCaConfirmModal()" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        
        <div class="p-5 sm:p-6 space-y-3">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 flex flex-col gap-1.5">
                <div class="flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-user text-gray-400 w-4 text-center"></i>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Driver:</span>
                    <span id="caConfirmDriver" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-peso-sign text-gray-400 w-4 text-center"></i>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Amount:</span>
                    <span id="caConfirmAmount" class="font-bold text-amber-600 dark:text-amber-400"></span>
                </div>
            </div>
            <p id="caConfirmMessage" class="text-sm text-gray-600 dark:text-gray-300"></p>
        </div>
        
        <div class="px-5 sm:px-6 pb-5 sm:pb-6 flex justify-end gap-3">
            <button type="button" onclick="closeCaConfirmModal()"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Cancel
            </button>
            <button type="button" id="caConfirmBtn" onclick="submitCaAction()"
                class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition active:scale-95">
                Confirm
            </button>
        </div>
    </div>
</div>

<form id="caActionForm" method="POST" action="dashboard.php" class="hidden">
    <input type="hidden" name="action" id="caActionInput">
    <input type="hidden" name="csrf_token" id="caCsrfInput">
    <input type="hidden" name="ca_id" id="caCaIdInput">
</form>

<script>
let _caConfirmType = null;

function openCaConfirmModal(type, caId, driverName, amount, csrfToken) {
    _caConfirmType = type;
    const isApprove = type === 'approve';

    
    document.getElementById('caConfirmDriver').textContent = driverName;
    document.getElementById('caConfirmAmount').textContent = '\u20B1' + amount;
    document.getElementById('caConfirmTitle').textContent = isApprove ? 'Approve & Print Ticket' : 'Reject Cash Advance';
    document.getElementById('caConfirmMessage').textContent = isApprove
        ? 'Are you sure you want to approve this cash advance request? A ticket will be printed automatically.'
        : 'Are you sure you want to reject this cash advance request?';

    
    const iconWrap = document.getElementById('caConfirmIconWrap');
    const icon     = document.getElementById('caConfirmIcon');
    const btn      = document.getElementById('caConfirmBtn');
    if (isApprove) {
        iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400';
        icon.className     = 'fa-solid fa-circle-check';
        btn.className      = 'px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition active:scale-95 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 shadow-sm';
        btn.textContent    = 'Approve & Print';
    } else {
        iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400';
        icon.className     = 'fa-solid fa-circle-xmark';
        btn.className      = 'px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition active:scale-95 bg-rose-600 hover:bg-rose-700';
        btn.textContent    = 'Reject';
    }

    
    document.getElementById('caActionInput').value = isApprove ? 'approve_cash_advance' : 'reject_cash_advance';
    document.getElementById('caCsrfInput').value   = csrfToken;
    document.getElementById('caCaIdInput').value   = caId;

    document.getElementById('caConfirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCaConfirmModal() {
    document.getElementById('caConfirmModal').classList.add('hidden');
    document.body.style.overflow = '';
    _caConfirmType = null;
}

function submitCaAction() {
    document.getElementById('caActionForm').submit();
}


document.getElementById('caConfirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeCaConfirmModal();
});

function syncCaSearch(val, source) {
    const topInput = document.getElementById('caSearchInput');
    const tableInput = document.getElementById('caTableSearchInput');
    if (source === 'top' && tableInput) tableInput.value = val;
    if (source === 'table' && topInput) topInput.value = val;
    filterCashAdvances();
}

function clearCaSearch() {
    const topInput = document.getElementById('caSearchInput');
    const tableInput = document.getElementById('caTableSearchInput');
    if (topInput) topInput.value = '';
    if (tableInput) tableInput.value = '';
    filterCashAdvances();
    if (topInput) topInput.focus();
}

function filterCashAdvances() {
    const topInput = document.getElementById('caSearchInput');
    const tableInput = document.getElementById('caTableSearchInput');
    const clearBtn = document.getElementById('caSearchClear');
    const search = (topInput?.value || tableInput?.value || '').toLowerCase().trim();
    const status = document.getElementById('caStatusFilter')?.value || '';
    const settled = document.getElementById('caSettledFilter')?.value || '';

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', search.length === 0);
    }

    
    const pendingCards = document.querySelectorAll('.pending-ca-card');
    let pendingMatches = 0;
    pendingCards.forEach(card => {
        const meta = card.getAttribute('data-search') || '';
        if (!search || meta.includes(search)) {
            card.style.display = '';
            pendingMatches++;
        } else {
            card.style.display = 'none';
        }
    });
    const pendingNoMatch = document.getElementById('caPendingNoMatches');
    if (pendingNoMatch) {
        pendingNoMatch.classList.toggle('hidden', !(pendingCards.length > 0 && pendingMatches === 0 && search.length > 0));
    }

    
    const rows = document.querySelectorAll('.ca-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const meta = (row.getAttribute('data-search') || '').toLowerCase();
        const driver = (row.dataset.driver || '').toLowerCase();
        const reason = (row.dataset.reason || '').toLowerCase();
        const rowStatus = row.dataset.status || '';
        const rowSettled = row.dataset.settled || '';

        const matchesSearch = !search || meta.includes(search) || driver.includes(search) || reason.includes(search);
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

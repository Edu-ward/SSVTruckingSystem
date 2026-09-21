<?php

$_totalCheckers   = count($allCheckers ?? []);
$_activeCheckers  = count(array_filter($allCheckers ?? [], fn($c) => ($c['status'] ?? 'Active') !== 'Resigned'));
$_resignedCheckers = $_totalCheckers - $_activeCheckers;
$_unassignedActiveOrders = count(array_filter($allOrders ?? [], fn($o) => in_array($o['status'] ?? '', ['Pending','In Progress']) && empty($o['checker_id'])));
?>
<div id="view-checkers" class="tab-content hidden">

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Checker Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage checker accounts, order assignments, and RFID scan activity</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text" id="checkerSearchInput" placeholder="Search name, username, phone..."
                       oninput="filterCheckers()"
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                <button type="button" id="checkerSearchClear" onclick="clearCheckerSearch()"
                        class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <button onclick="toggleModal('addCheckerModal', true)" class="btn-primary text-xs sm:text-sm flex-shrink-0">
                <i class="fa-solid fa-plus"></i><span>Add Checker</span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-teal-200/70 dark:border-teal-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Total Checkers</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $_totalCheckers ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">All registered</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-400 to-cyan-500"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Active</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $_activeCheckers ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">On duty</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-indigo-200/70 dark:border-indigo-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Scans Today</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $checkerScansTodayCount ?? 0 ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">RFID scans logged</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-wifi"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-400 to-purple-500"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-red-200/70 dark:border-red-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Unassigned Orders</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $_unassignedActiveOrders ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Need a checker</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-red-400 to-orange-500"></div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/80">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-base flex items-center gap-2">
                <i class="fa-solid fa-user-check text-teal-500"></i> Checker Roster
            </h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">All registered checker accounts and their current active order assignments</p>
        </div>

        <?php if (empty($allCheckers)): ?>
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500 gap-3">
                <i class="fa-solid fa-user-slash text-4xl opacity-30"></i>
                <p class="text-sm font-medium">No checker accounts registered yet.</p>
                <button onclick="toggleModal('addCheckerModal', true)"
                        class="mt-1 px-4 py-2 rounded-xl text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white transition active:scale-95">
                    <i class="fa-solid fa-plus mr-1"></i> Add First Checker
                </button>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-750 text-left border-b border-gray-100 dark:border-gray-700/80">
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Checker</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Orders</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/80" id="checkerTableBody">
                        <?php foreach ($allCheckers as $checker):
                            $_isActive   = ($checker['status'] ?? 'Active') !== 'Resigned';
                            $_initials   = strtoupper(substr($checker['first_name'] ?? $checker['username'] ?? '?', 0, 1) . substr($checker['last_name'] ?? '', 0, 1));
                            $_name       = trim(($checker['first_name'] ?? '') . ' ' . ($checker['last_name'] ?? '')) ?: ($checker['username'] ?? 'Checker');
                            $_orderCount = $checkerOrderCounts[$checker['id']] ?? 0;
                            $_searchMeta = strtolower($_name . ' ' . ($checker['username'] ?? '') . ' ' . ($checker['phone'] ?? ''));
                        ?>
                        <tr class="checker-row hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition-colors" data-search="<?= htmlspecialchars($_searchMeta) ?>">
                            
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0
                                        <?= $_isActive ? 'bg-teal-100 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400' ?>">
                                        <?= htmlspecialchars($_initials ?: '?') ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm"><?= htmlspecialchars($_name) ?></div>
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500">
                                            @<?= htmlspecialchars($checker['username'] ?? '—') ?> &nbsp;·&nbsp; ID #<?= $checker['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-5 py-4">
                                <?php if (!empty($checker['phone'])): ?>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-[10px] text-teal-500"></i>
                                        <?= htmlspecialchars($checker['phone']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-300 dark:text-gray-600 italic">No phone</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-5 py-4">
                                <?php if ($_orderCount > 0): ?>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-700/50 px-2.5 py-1 rounded-full cursor-default select-none">
                                        <i class="fa-solid fa-clipboard-list text-[10px]"></i>
                                        <?= $_orderCount ?> active order<?= $_orderCount > 1 ? 's' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic select-none">No active orders</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-5 py-4">
                                <?php if ($_isActive): ?>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Resigned
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openCheckerOrdersModal(<?= $checker['id'] ?>, '<?= addslashes($_name) ?>')"
                                            title="View Orders assigned to <?= htmlspecialchars($_name) ?>"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30 transition text-sm">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </button>
                                    <button onclick="openResetCheckerPasswordModal(<?= $checker['id'] ?>, '<?= addslashes($_name) ?>', 'checkers')"
                                            title="Reset Password for <?= htmlspecialchars($_name) ?>"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/30 transition text-sm">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    <button onclick="openResignCheckerModal(<?= $checker['id'] ?>, '<?= addslashes($_name) ?>')"
                                            title="<?= $_isActive ? 'Resign Checker' : 'Already Resigned' ?>"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition text-sm <?= !$_isActive ? 'opacity-30 cursor-not-allowed' : '' ?>"
                                            <?= !$_isActive ? 'disabled' : '' ?>>
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noCheckersMatch" class="hidden">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40 block mx-auto"></i>
                                <p class="text-sm font-medium" id="noCheckersMatchText">No checkers match your search.</p>
                                <button type="button" onclick="clearCheckerSearch()" class="text-xs text-teal-500 hover:underline mt-2 inline-block">Clear search</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
window.allOrdersData = <?= json_encode(array_values($allOrders ?? [])) ?>;
window.gravelTypeLabels = <?= json_encode($gravelTypes ?? []) ?>;

if (typeof escapeHtml !== 'function') {
    window.escapeHtml = function(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
}

function filterCheckers() {
    const input    = document.getElementById('checkerSearchInput');
    const clearBtn = document.getElementById('checkerSearchClear');
    const query    = input ? input.value.toLowerCase().trim() : '';
    const rows     = document.querySelectorAll('#checkerTableBody .checker-row');
    const noMatch  = document.getElementById('noCheckersMatch');
    const noMatchTxt = document.getElementById('noCheckersMatchText');

    if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);

    let count = 0;
    rows.forEach(row => {
        const show = !query || (row.getAttribute('data-search') || '').includes(query);
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });

    if (noMatch) {
        if (count === 0 && rows.length > 0) {
            noMatch.classList.remove('hidden');
            if (noMatchTxt) noMatchTxt.textContent = `No checkers match "${query}".`;
        } else {
            noMatch.classList.add('hidden');
        }
    }
}

function clearCheckerSearch() {
    const input = document.getElementById('checkerSearchInput');
    if (input) { input.value = ''; filterCheckers(); input.focus(); }
}

function openCheckerOrdersModal(checkerId, checkerName) {
    const nameEl = document.getElementById('chko-checker-name');
    if (nameEl) nameEl.textContent = checkerName || ('Checker #' + checkerId);

    const allOrders = window.allOrdersData || [];
    const orders = allOrders.filter(function(o) {
        return parseInt(o.checker_id, 10) === parseInt(checkerId, 10);
    });

    const totalCount = orders.length;
    const activeCount = orders.filter(function(o) {
        return o.status === 'In Progress' || o.status === 'Pending';
    }).length;
    const fulfilledCount = orders.filter(function(o) {
        return o.status === 'Fulfilled';
    }).length;

    const badgeEl = document.getElementById('chko-order-count-badge');
    if (badgeEl) badgeEl.textContent = totalCount + ' order' + (totalCount !== 1 ? 's' : '');

    const statTotal = document.getElementById('chko-stat-total');
    if (statTotal) statTotal.textContent = totalCount;

    const statActive = document.getElementById('chko-stat-active');
    if (statActive) statActive.textContent = activeCount;

    const statFulfilled = document.getElementById('chko-stat-fulfilled');
    if (statFulfilled) statFulfilled.textContent = fulfilledCount;

    const container = document.getElementById('chko-orders-list-container');
    if (!container) return;

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700/60 flex items-center justify-center text-2xl mx-auto mb-3 text-gray-400 dark:text-gray-400">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-200">No Orders Assigned</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">There are currently no orders assigned to ` + (checkerName ? escapeHtml(checkerName) : 'this checker') + `.</p>
            </div>
        `;
    } else {
        const statusConfig = {
            'Pending': 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-700/50',
            'In Progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-700/50',
            'Fulfilled': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-700/50',
            'Cancelled': 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200 dark:border-rose-700/50'
        };

        let html = '<div class="divide-y divide-gray-100 dark:divide-gray-700/70">';

        orders.forEach(function(o) {
            const reqCm = parseFloat(o.cubic_meters_required || o.trucks_required || 0);
            const doneCm = parseFloat(o.cubic_meters_fulfilled || o.trucks_fulfilled || 0);
            const pct = reqCm > 0 ? Math.min(100, Math.round((doneCm / reqCm) * 100)) : 0;
            const gravelLabel = (window.gravelTypeLabels && window.gravelTypeLabels[o.gravel_type]) ? window.gravelTypeLabels[o.gravel_type] : (o.gravel_type || 'Gravel');
            const statusClass = statusConfig[o.status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600';
            const dateStr = o.created_at ? new Date(o.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            html += `
                <div class="py-4 first:pt-0 last:pb-0">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-mono font-extrabold text-sm text-gray-900 dark:text-gray-100">${escapeHtml(o.order_number)}</span>
                                <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border ${statusClass}">
                                    ${escapeHtml(o.status)}
                                </span>
                                ${dateStr ? `<span class="text-[11px] text-gray-400 dark:text-gray-500">${escapeHtml(dateStr)}</span>` : ''}
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex items-center gap-1.5 font-medium mb-1">
                                <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>
                                <span>${escapeHtml(o.client_name || 'Client')}</span>
                                ${o.contact_number ? `<span class="text-gray-300 dark:text-gray-600">•</span><span class="text-gray-500 dark:text-gray-400 flex items-center gap-1"><i class="fa-solid fa-phone text-[9px]"></i> ${escapeHtml(o.contact_number)}</span>` : ''}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 flex items-start gap-1.5">
                                <i class="fa-solid fa-location-dot text-rose-500 text-[10px] mt-0.5 flex-shrink-0"></i>
                                <span class="truncate max-w-md">${escapeHtml(o.destination || 'No destination')} ${o.landmark ? `<span class="text-gray-400 dark:text-gray-500">(${escapeHtml(o.landmark)})</span>` : ''}</span>
                            </div>
                        </div>

                        <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 flex-shrink-0">
                            <div class="text-left sm:text-right">
                                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">${escapeHtml(gravelLabel)}</div>
                                <div class="text-xs font-bold text-gray-900 dark:text-gray-100 mt-0.5">${doneCm.toFixed(2)} / ${reqCm.toFixed(2)} cu.m</div>
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1 sm:ml-auto">
                                    <div class="${pct >= 100 ? 'bg-emerald-500' : 'bg-blue-500'} h-1.5 rounded-full" style="width:${pct}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <a href="print_order_ticket.php?id=${encodeURIComponent(o.id)}" target="_blank"
                                   title="Print Order Ticket"
                                   class="px-2.5 py-1 rounded-lg text-xs font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/30 hover:bg-teal-100 dark:hover:bg-teal-900/50 border border-teal-200 dark:border-teal-700/50 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-print text-[10px]"></i> Ticket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    toggleModal('checkerOrdersModal', true);
}
</script>

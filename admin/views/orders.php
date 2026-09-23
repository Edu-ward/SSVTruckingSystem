<?php
if (isset($gravelTypes) && is_array($gravelTypes)) {
    $gravelTypeLabels = $gravelTypes;
} else {
    $_gravel_rows = $pdo->query("SELECT type_key, label FROM gravel_types WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $gravelTypeLabels = [];
    foreach ($_gravel_rows as $_g) {
        $gravelTypeLabels[$_g['type_key']] = $_g['label'];
    }
}
?>
<div id="view-orders" class="tab-content hidden">

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Orders Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Track and manage client orders, assignments, and dispatch status</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
            
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text" id="orderSearchInput" placeholder="Search order #, client, phone, destination, checker..." oninput="filterOrders()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="orderSearchClear" onclick="clearOrderSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button onclick="toggleModal('addCheckerModal', true)" class="btn-secondary text-xs sm:text-sm flex-1 sm:flex-none">
                    <i class="fa-solid fa-user-shield"></i><span>Add Checker</span>
                </button>
                <button onclick="toggleModal('addOrderModal', true)" class="btn-primary text-xs sm:text-sm flex-1 sm:flex-none">
                    <i class="fa-solid fa-plus"></i><span>Place Order</span>
                </button>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <?php
        $totalOrders  = count($allOrders ?? []);
        $pendingOrders    = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'Pending'));
        $inProgressOrders = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'In Progress'));
        $fulfilledOrders  = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'Fulfilled'));
        ?>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-blue-200/70 dark:border-blue-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Orders</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $totalOrders ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">All orders</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-amber-200/70 dark:border-amber-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pending</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $pendingOrders ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Awaiting dispatch</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-indigo-200/70 dark:border-indigo-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">In Progress</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $inProgressOrders ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">On the road</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-400 to-purple-500"></div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Fulfilled</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $fulfilledOrders ?></div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Delivered</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        </div>
    </div>

    
    
    <div id="checkerFilterBanner" class="hidden items-center justify-between bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 rounded-2xl p-3 sm:p-4 mb-4 text-xs transition-all">
        <div class="flex items-center gap-2.5 text-teal-800 dark:text-teal-200">
            <div class="w-7 h-7 rounded-lg bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center text-teal-600 dark:text-teal-300 flex-shrink-0">
                <i class="fa-solid fa-filter"></i>
            </div>
            <span>Filtered by checker: <strong id="checkerFilterName" class="font-bold text-teal-950 dark:text-teal-100"></strong> (showing assigned orders only)</span>
        </div>
        <button type="button" onclick="clearCheckerOrderFilter()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 text-teal-700 dark:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-900/40 font-bold transition text-xs shadow-sm cursor-pointer">
            <i class="fa-solid fa-xmark"></i> Show All Orders
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">All Orders</h3>
        </div>
        <?php if (empty($allOrders)): ?>
            <div class="p-12 text-center text-gray-400 dark:text-gray-500">
                <i class="fa-solid fa-clipboard-list text-5xl mb-4 opacity-30"></i>
                <p class="text-lg font-medium">No orders placed yet.</p>
                <p class="text-sm mt-1">Click "Place Order" to get started.</p>
            </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/80 text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-3 sm:px-3.5 py-3 text-left whitespace-nowrap">Order #</th>
                        <th class="px-3 sm:px-3.5 py-3 text-left">Client</th>
                        <th class="px-3 sm:px-3.5 py-3 text-left whitespace-nowrap">Gravel Type</th>
                        <th class="px-3 sm:px-3.5 py-3 text-left">Destination</th>
                        <th class="px-3 sm:px-3.5 py-3 text-center whitespace-nowrap">Cubic Meter (cu.m)</th>
                        <th class="px-3 sm:px-3.5 py-3 text-left whitespace-nowrap">Checker</th>
                        <th class="px-3 sm:px-3.5 py-3 text-center whitespace-nowrap">Status</th>
                        <th class="px-3 sm:px-3.5 py-3 text-center whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    <?php foreach ($allOrders as $order):
                        $statusColors = [
                            'Pending'     => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-200',
                            'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200',
                            'Fulfilled'   => 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-200',
                            'Cancelled'   => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200',
                        ];
                        $sc = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                        $reqCm = floatval($order['cubic_meters_required'] ?? 0) > 0 ? floatval($order['cubic_meters_required']) : floatval($order['trucks_required']);
                        $doneCm = floatval($order['cubic_meters_fulfilled'] ?? 0) > 0 ? floatval($order['cubic_meters_fulfilled']) : floatval($order['trucks_fulfilled']);
                        $pct = $reqCm > 0 ? round(($doneCm / $reqCm) * 100) : 0;
                        $gravelLabel = $gravelTypeLabels[$order['gravel_type']] ?? $order['gravel_type'];
                    ?>
                    <tr class="order-row hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors"
                        data-checker-id="<?= htmlspecialchars($order['checker_id'] ?? '') ?>"
                        data-search="<?= htmlspecialchars(strtolower(($order['order_number'] ?? '') . ' ' . ($order['client_name'] ?? '') . ' ' . ($order['contact_number'] ?? '') . ' ' . $gravelLabel . ' ' . ($order['destination'] ?? '') . ' ' . ($order['landmark'] ?? '') . ' ' . ($order['checker_name'] ?? '') . ' ' . ($order['status'] ?? '') . ' ' . ($order['notes'] ?? ''))) ?>">
                        <td class="px-3 sm:px-3.5 py-3 font-mono font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap"><?= htmlspecialchars($order['order_number']) ?></td>
                        <td class="px-3 sm:px-3.5 py-3 text-gray-700 dark:text-gray-300 max-w-[150px]">
                            <div class="font-medium truncate"><?= htmlspecialchars($order['client_name']) ?></div>
                            <?php if (!empty($order['contact_number'])): ?>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5"><i class="fa-solid fa-phone text-[9px]"></i> <?= htmlspecialchars($order['contact_number']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 sm:px-3.5 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= htmlspecialchars($gravelLabel) ?></td>
                        <td class="px-3 sm:px-3.5 py-3 text-gray-700 dark:text-gray-300 max-w-[180px]">
                            <div class="truncate" title="<?= htmlspecialchars($order['destination']) ?>"><?= htmlspecialchars($order['destination']) ?></div>
                            <?php if (!empty($order['landmark'])): ?>
                                <div class="text-[11px] text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-0.5 truncate" title="<?= htmlspecialchars($order['landmark']) ?>"><i class="fa-solid fa-location-dot text-[9px]"></i> <?= htmlspecialchars($order['landmark']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 sm:px-3.5 py-3 whitespace-nowrap">
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-gray-800 dark:text-gray-200 text-xs sm:text-sm mb-1"><?= number_format($doneCm, 2) ?>/<?= number_format($reqCm, 2) ?> cu.m</span>
                                <div class="w-24 sm:w-28 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                    <div class="<?= $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' ?> h-1.5 rounded-full transition-all" style="width:<?= min(100, $pct) ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-3.5 py-3 text-gray-600 dark:text-gray-400 text-xs whitespace-nowrap">
                            <?= $order['checker_name'] ? htmlspecialchars($order['checker_name']) : '<span class="italic text-gray-400">Unassigned</span>' ?>
                        </td>
                        <td class="px-3 sm:px-3.5 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[85px] <?= $sc ?>"><?= htmlspecialchars($order['status']) ?></span>
                        </td>
                        <td class="px-3 sm:px-3.5 py-3 text-center whitespace-nowrap min-w-[110px]">
                            <div class="flex items-center justify-center space-x-2 sm:space-x-2.5">
                                <?php if ($order['status'] !== 'Cancelled' && $order['status'] !== 'Fulfilled'): ?>
                                <button type="button" onclick="openEditOrderModal(<?= htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Order & Pinned Location" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 transition cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" onclick="openAssignCheckerModal(<?= $order['id'] ?>, '<?= addslashes($order['order_number']) ?>')" title="Assign Checker" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 p-1 transition cursor-pointer">
                                    <i class="fa-solid fa-user-shield"></i>
                                </button>
                                <button type="button" onclick="window.open('print_order_ticket.php?id=<?= $order['id'] ?>', '_blank')" title="Print Order Ticket" class="text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 p-1 transition cursor-pointer">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <button type="button" onclick="openCancelOrderModal(<?= $order['id'] ?>, '<?= addslashes($order['order_number']) ?>')" title="Cancel Order" class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 p-1 transition cursor-pointer">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <button type="button" onclick="window.open('print_order_ticket.php?id=<?= $order['id'] ?>', '_blank')" title="Print Order Ticket" class="text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 p-1 transition cursor-pointer">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="noOrdersMatch" class="hidden">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40 block mx-auto"></i>
                            <p class="text-sm font-medium" id="noOrdersMatchText">No orders match your search.</p>
                            <button type="button" onclick="clearOrderSearch()" class="text-xs text-blue-500 hover:underline mt-2 inline-block">Clear search</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Orders 5-per-page Pagination Bar -->
        <div id="ordersPaginationContainer" class="px-4 py-3 bg-gray-50/70 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-gray-500 dark:text-gray-400 font-medium" id="ordersPaginationInfo">
                Showing <strong class="text-gray-800 dark:text-gray-200" id="ordersPageStart">0</strong> to <strong class="text-gray-800 dark:text-gray-200" id="ordersPageEnd">0</strong> of <strong class="text-gray-800 dark:text-gray-200" id="ordersPageTotal">0</strong> orders
            </div>
            <div class="flex items-center gap-1.5" id="ordersPaginationControls">
                <button type="button" id="ordersPrevPageBtn" onclick="changeOrderPage(-1)" class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition flex items-center gap-1 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Prev</span>
                </button>
                <div id="ordersPageNumbers" class="flex items-center gap-1">
                    <!-- Page buttons injected via JS -->
                </div>
                <button type="button" id="ordersNextPageBtn" onclick="changeOrderPage(1)" class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition flex items-center gap-1 cursor-pointer shadow-xs">
                    <span>Next</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Checker Accounts</h3>
        </div>
        <?php if (empty($allCheckers)): ?>
            <div class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm italic">No checker accounts yet.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <?php foreach ($allCheckers as $checker): ?>
            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-sm">
                        <?= strtoupper(substr($checker['username'], 0, 1)) . strtoupper(substr(explode(' ', $checker['full_name'])[0] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-gray-200">
                            <?= htmlspecialchars($checker['full_name'] ?: $checker['username']) ?>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @<?= htmlspecialchars($checker['username']) ?> · ID #<?= $checker['id'] ?>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <?php if (!empty($checker['phone'])): ?>
                    <span class="text-xs text-gray-600 dark:text-gray-400">
                        <i class="fa-solid fa-phone mr-1 opacity-70"></i> <?= htmlspecialchars($checker['phone']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="text-xs <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' : 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' ?> px-2.5 py-1 rounded-full font-semibold">
                        <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'Resigned' : 'Checker' ?>
                    </span>
                    <button onclick="openCheckerOrdersModal(<?= $checker['id'] ?>, '<?= addslashes($checker['full_name'] ?: $checker['username']) ?>')" title="View Orders" class="text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition focus:outline-none">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </button>
                    <button onclick="openResetCheckerPasswordModal(<?= $checker['id'] ?>, '<?= addslashes($checker['full_name'] ?: $checker['username']) ?>', 'orders')" title="Reset Password" class="text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 transition focus:outline-none">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    <button onclick="openResignCheckerModal(<?= $checker['id'] ?>, '<?= addslashes($checker['full_name'] ?: $checker['username']) ?>')" title="<?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'Checker Already Resigned' : 'Resign Checker' ?>" class="text-gray-400 hover:text-amber-600 transition focus:outline-none <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'opacity-40 cursor-not-allowed' : '' ?>" <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-user-xmark"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

<script>
let activeCheckerFilterId = null;
let currentOrderPage = 1;
const ORDERS_PER_PAGE = 5;

function filterOrders(resetPage = true) {
    if (resetPage) {
        currentOrderPage = 1;
    }
    const input = document.getElementById('orderSearchInput');
    const clearBtn = document.getElementById('orderSearchClear');
    const query = input ? input.value.toLowerCase().trim() : '';
    const rows = Array.from(document.querySelectorAll('#view-orders .order-row'));
    const noResults = document.getElementById('noOrdersMatch');
    const noResultsText = document.getElementById('noOrdersMatchText');
    const paginationContainer = document.getElementById('ordersPaginationContainer');

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
    }

    // Filter matching rows
    const matchingRows = [];
    rows.forEach(row => {
        const meta = row.getAttribute('data-search') || '';
        const checkerId = row.getAttribute('data-checker-id') || '';

        const matchesQuery = !query || meta.includes(query);
        const matchesChecker = !activeCheckerFilterId || (checkerId === String(activeCheckerFilterId));

        if (matchesQuery && matchesChecker) {
            matchingRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const totalMatches = matchingRows.length;
    const totalPages = Math.max(1, Math.ceil(totalMatches / ORDERS_PER_PAGE));

    if (currentOrderPage > totalPages) {
        currentOrderPage = totalPages;
    }
    if (currentOrderPage < 1) {
        currentOrderPage = 1;
    }

    // Paginate matching rows (show 5 per page)
    const startIndex = (currentOrderPage - 1) * ORDERS_PER_PAGE;
    const endIndex = startIndex + ORDERS_PER_PAGE;

    matchingRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Update No Results display
    if (noResults) {
        if (totalMatches === 0 && rows.length > 0) {
            noResults.classList.remove('hidden');
            if (noResultsText) {
                if (activeCheckerFilterId && query) {
                    noResultsText.textContent = `No orders for this checker match "${query}".`;
                } else if (activeCheckerFilterId) {
                    noResultsText.textContent = 'No orders currently assigned to this checker.';
                } else {
                    noResultsText.textContent = `No orders match "${query}".`;
                }
            }
        } else {
            noResults.classList.add('hidden');
        }
    }

    // Update Pagination UI
    if (paginationContainer) {
        if (totalMatches === 0) {
            paginationContainer.classList.add('hidden');
        } else {
            paginationContainer.classList.remove('hidden');
            const pageStartEl = document.getElementById('ordersPageStart');
            const pageEndEl   = document.getElementById('ordersPageEnd');
            const pageTotalEl = document.getElementById('ordersPageTotal');
            if (pageStartEl) pageStartEl.textContent = startIndex + 1;
            if (pageEndEl) pageEndEl.textContent = Math.min(endIndex, totalMatches);
            if (pageTotalEl) pageTotalEl.textContent = totalMatches;

            const prevBtn = document.getElementById('ordersPrevPageBtn');
            const nextBtn = document.getElementById('ordersNextPageBtn');
            if (prevBtn) prevBtn.disabled = (currentOrderPage <= 1);
            if (nextBtn) nextBtn.disabled = (currentOrderPage >= totalPages);

            const pageNumContainer = document.getElementById('ordersPageNumbers');
            if (pageNumContainer) {
                pageNumContainer.innerHTML = '';
                for (let p = 1; p <= totalPages; p++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = (p === currentOrderPage)
                        ? 'w-7 h-7 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs'
                        : 'w-7 h-7 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer';
                    btn.textContent = p;
                    btn.onclick = () => {
                        currentOrderPage = p;
                        filterOrders(false);
                    };
                    pageNumContainer.appendChild(btn);
                }
            }
        }
    }
}

function changeOrderPage(delta) {
    currentOrderPage += delta;
    filterOrders(false);
}

function clearOrderSearch() {
    const input = document.getElementById('orderSearchInput');
    if (input) {
        input.value = '';
        filterOrders(true);
        input.focus();
    }
}

function viewOrdersForChecker(checkerId, checkerName) {
    activeCheckerFilterId = checkerId;
    switchTab('orders');

    const banner = document.getElementById('checkerFilterBanner');
    const nameEl = document.getElementById('checkerFilterName');
    if (banner && nameEl) {
        nameEl.textContent = checkerName || ('Checker #' + checkerId);
        banner.classList.remove('hidden');
        banner.classList.add('flex');
    }

    const input = document.getElementById('orderSearchInput');
    if (input) input.value = '';

    filterOrders(true);

    const bannerOrTable = document.getElementById('checkerFilterBanner') || document.getElementById('view-orders');
    if (bannerOrTable) {
        bannerOrTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function clearCheckerOrderFilter() {
    activeCheckerFilterId = null;
    const banner = document.getElementById('checkerFilterBanner');
    if (banner) {
        banner.classList.add('hidden');
        banner.classList.remove('flex');
    }
    filterOrders(true);
}

// Initialise pagination on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => filterOrders(false));
} else {
    filterOrders(false);
}
</script>

</div>

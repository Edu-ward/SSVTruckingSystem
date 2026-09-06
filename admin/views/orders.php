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

    <!-- Header bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-2 text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-clipboard-list text-blue-600 dark:text-blue-400"></i>
            <span>Orders Management</span>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <!-- Order Live Search Bar -->
            <div class="relative flex-1 sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="orderSearchInput" placeholder="Search order #, client, phone, destination, checker..." oninput="filterOrders()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="orderSearchClear" onclick="clearOrderSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <button onclick="toggleModal('addCheckerModal', true)" class="btn-secondary text-sm flex-1 sm:flex-none">
                <i class="fa-solid fa-user-shield"></i><span>Add Checker</span>
            </button>
            <button onclick="toggleModal('addOrderModal', true)" class="btn-primary text-sm flex-1 sm:flex-none">
                <i class="fa-solid fa-plus"></i><span>Place Order</span>
            </button>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
        $totalOrders  = count($allOrders ?? []);
        $pendingOrders    = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'Pending'));
        $inProgressOrders = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'In Progress'));
        $fulfilledOrders  = count(array_filter($allOrders ?? [], fn($o) => $o['status'] === 'Fulfilled'));
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-clipboard-list text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $totalOrders ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Orders</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-hourglass-half text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $pendingOrders ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pending</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-truck-fast text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $inProgressOrders ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">In Progress</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5 flex flex-col items-center justify-center">
            <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $fulfilledOrders ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fulfilled</div>
        </div>
    </div>

    <!-- Orders table -->
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
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Order #</th>
                        <th class="px-6 py-3 text-left">Client</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Gravel Type</th>
                        <th class="px-6 py-3 text-left">Destination</th>
                        <th class="px-6 py-3 text-center whitespace-nowrap">Cubic Meter (cu.m)</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Checker</th>
                        <th class="px-6 py-3 text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 text-center whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
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
                        data-search="<?= htmlspecialchars(strtolower(($order['order_number'] ?? '') . ' ' . ($order['client_name'] ?? '') . ' ' . ($order['contact_number'] ?? '') . ' ' . $gravelLabel . ' ' . ($order['destination'] ?? '') . ' ' . ($order['landmark'] ?? '') . ' ' . ($order['checker_name'] ?? '') . ' ' . ($order['status'] ?? '') . ' ' . ($order['notes'] ?? ''))) ?>">
                        <td class="px-6 py-4 font-mono font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap"><?= htmlspecialchars($order['order_number']) ?></td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            <div class="font-medium"><?= htmlspecialchars($order['client_name']) ?></div>
                            <?php if (!empty($order['contact_number'])): ?>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5"><i class="fa-solid fa-phone text-[10px]"></i> <?= htmlspecialchars($order['contact_number']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= htmlspecialchars($gravelLabel) ?></td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            <div><?= htmlspecialchars($order['destination']) ?></div>
                            <?php if (!empty($order['landmark'])): ?>
                                <div class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-0.5"><i class="fa-solid fa-location-dot text-[10px]"></i> <?= htmlspecialchars($order['landmark']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm mb-1"><?= number_format($doneCm, 2) ?>/<?= number_format($reqCm, 2) ?> cu.m</span>
                                <div class="w-28 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                    <div class="<?= $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' ?> h-1.5 rounded-full transition-all" style="width:<?= min(100, $pct) ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-xs whitespace-nowrap">
                            <?= $order['checker_name'] ? htmlspecialchars($order['checker_name']) : '<span class="italic text-gray-400">Unassigned</span>' ?>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap min-w-[90px] <?= $sc ?>"><?= htmlspecialchars($order['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-3">
                                <?php if ($order['status'] !== 'Cancelled' && $order['status'] !== 'Fulfilled'): ?>
                                <button onclick="openAssignCheckerModal(<?= $order['id'] ?>, '<?= addslashes($order['order_number']) ?>')" title="Assign Checker" class="text-blue-500 hover:text-blue-700 transition">
                                    <i class="fa-solid fa-user-shield"></i>
                                </button>
                                <button onclick="window.open('print_order_ticket.php?id=<?= $order['id'] ?>', '_blank')" title="Print Order Ticket" class="text-gray-400 hover:text-blue-500 transition">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <button onclick="openCancelOrderModal(<?= $order['id'] ?>, '<?= addslashes($order['order_number']) ?>')" title="Cancel Order" class="text-gray-400 hover:text-red-500 transition">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="window.open('print_order_ticket.php?id=<?= $order['id'] ?>', '_blank')" title="Print Order Ticket" class="text-gray-400 hover:text-blue-500 transition">
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
        <?php endif; ?>
    </div>

    <!-- Checkers list -->
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
                <div class="flex items-center space-x-4">
                    <?php if (!empty($checker['phone'])): ?>
                    <span class="text-xs text-gray-600 dark:text-gray-400">
                        <i class="fa-solid fa-phone mr-1 opacity-70"></i> <?= htmlspecialchars($checker['phone']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="text-xs <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' : 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' ?> px-2.5 py-1 rounded-full font-semibold">
                        <?= ($checker['status'] ?? 'Active') === 'Resigned' ? 'Resigned' : 'Checker' ?>
                    </span>
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
function filterOrders() {
    const input = document.getElementById('orderSearchInput');
    const clearBtn = document.getElementById('orderSearchClear');
    const query = input ? input.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('#view-orders .order-row');
    const noResults = document.getElementById('noOrdersMatch');
    const noResultsText = document.getElementById('noOrdersMatchText');

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
    }

    let matchCount = 0;
    rows.forEach(row => {
        const meta = row.getAttribute('data-search') || '';
        if (!query || meta.includes(query)) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (noResults) {
        if (matchCount === 0 && rows.length > 0) {
            noResults.classList.remove('hidden');
            if (noResultsText) noResultsText.textContent = `No orders match "${query}".`;
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function clearOrderSearch() {
    const input = document.getElementById('orderSearchInput');
    if (input) {
        input.value = '';
        filterOrders();
        input.focus();
    }
}
</script>

</div>

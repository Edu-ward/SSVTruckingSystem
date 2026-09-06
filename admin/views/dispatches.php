<div id="view-dispatches" class="tab-content hidden">


    <!-- RFID Scanner Panel -->
    <div class="mb-8 bg-indigo-600 rounded-2xl shadow border border-indigo-700 overflow-hidden flex flex-col md:flex-row">
        <div class="p-5 sm:p-6 md:w-1/3 flex flex-col justify-center text-white border-b md:border-b-0 md:border-r border-indigo-500">
            <div class="flex items-center space-x-3 mb-1">
                <i class="fa-solid fa-wifi text-2xl sm:text-3xl text-indigo-200"></i>
                <h2 class="font-bold text-lg sm:text-xl">Dispatch Scanner</h2>
            </div>
            <p class="text-indigo-200 text-xs sm:text-sm">Scan a truck's RFID card to confirm arrival.</p>
        </div>
        <div class="p-4 sm:p-6 md:w-2/3 bg-white dark:bg-gray-800 flex items-center cursor-pointer" onclick="document.getElementById('dispatchScannerRfidInput')?.focus();">
            <form method="POST" action="dashboard.php" class="w-full flex flex-col sm:flex-row gap-2 sm:gap-3">
                <input type="hidden" name="action" value="dispatch_scan_rfid">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="flex-grow">
                    <input type="text" name="rfid_tag" id="dispatchScannerRfidInput" required autocomplete="off"
                        placeholder="Click here and scan RFID card..."
                        class="w-full border border-indigo-300 dark:border-indigo-700 rounded-xl px-4 py-2.5 sm:py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:text-gray-100 font-mono text-sm transition-colors">
                </div>
                <button type="submit" class="px-5 py-2.5 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition flex items-center justify-center space-x-2 text-sm flex-shrink-0">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Confirm</span>
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center space-x-2 text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">
                <i class="fa-regular fa-file-lines text-blue-500"></i>
                <span>Dispatch Tickets</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- Dispatch Live Search Bar -->
                <div class="relative flex-1 sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="dispatchSearchInput" placeholder="Search tickets, trucks, drivers, clients, destinations..." oninput="filterDispatches()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <button type="button" id="dispatchSearchClear" onclick="clearDispatchSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button onclick="toggleModal('dispatchModal', true)" class="btn-primary text-sm w-full sm:w-auto">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Dispatch</span>
                </button>
            </div>
        </div>

        <div class="flex flex-wrap sm:inline-flex bg-gray-100 dark:bg-gray-700/70 p-1 rounded-xl sm:rounded-full gap-1 mb-6 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 w-full sm:w-auto">
            <button id="btn-tab-active" onclick="switchDispatchTab('active')" class="flex-1 sm:flex-none px-3.5 sm:px-6 py-2 rounded-lg sm:rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition text-center font-semibold">
                Active (<?= count($activeTickets); ?>)
            </button>
            <button id="btn-tab-requests" onclick="switchDispatchTab('requests')" class="flex-1 sm:flex-none px-3.5 sm:px-6 py-2 rounded-lg sm:rounded-full hover:text-gray-900 dark:text-gray-100 transition relative text-center">
                Requests (<?= count($cancellationRequests); ?>)
                <?php if (count($cancellationRequests) > 0): ?>
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-ping"></span>
                <?php endif; ?>
            </button>
            <button id="btn-tab-completed" onclick="switchDispatchTab('completed')" class="flex-1 sm:flex-none px-3.5 sm:px-6 py-2 rounded-lg sm:rounded-full hover:text-gray-900 dark:text-gray-100 transition text-center">
                Completed/Cancelled (<?= count($completedTickets); ?>)
            </button>
        </div>

        <div id="dispatch-grid-active" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($activeTickets as $ticket):
                $statusClass = 'bg-blue-500';
                if ($ticket['status'] == 'Pending') $statusClass = 'bg-yellow-500';
                $searchMeta = strtolower(implode(' ', array_filter([
                    $ticket['ticket_number'] ?? '',
                    $ticket['truck_code'] ?? '',
                    $ticket['driver_name'] ?? '',
                    $ticket['client_name'] ?? '',
                    $ticket['contact_number'] ?? '',
                    $ticket['destination'] ?? '',
                    $ticket['landmark'] ?? '',
                    $ticket['order_number'] ?? '',
                    $ticket['status'] ?? ''
                ])));
            ?>
                <div class="dispatch-card border border-gray-100 dark:border-gray-700 rounded-xl p-6 shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition" data-search="<?= htmlspecialchars($searchMeta) ?>">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800 dark:text-gray-200">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?= htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <?php if ($ticket['status'] === 'Cancellation Requested'): ?>
                                <span class="bg-orange-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm animate-pulse">
                                    cancellation requested
                                </span>
                            <?php else: ?>
                                <span class="<?= $statusClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm">
                                    <?= htmlspecialchars($ticket['status']); ?>
                                </span>
                            <?php endif; ?>

                            <div class="flex items-center space-x-2 border-l border-gray-200 dark:border-gray-600 pl-3 ml-1">
                                <?php if ($ticket['status'] === 'Cancellation Requested'): ?>
                                    <!-- Approve Cancellation Button -->
                                    <button onclick="openApproveCancelModal(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-orange-500 hover:text-orange-600 transition focus:outline-none" title="Approve Cancellation Request">
                                        <i class="fa-solid fa-circle-check text-lg"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Mark as Delivered Button -->
                                    <button onclick="markDispatchDelivered(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-green-500 hover:text-green-600 transition focus:outline-none" title="Mark as Delivered">
                                        <i class="fa-solid fa-circle-check text-lg"></i>
                                    </button>
                                <?php endif; ?>
                                <!-- Print Button -->
                                <button onclick="window.open('print_ticket.php?id=<?= $ticket['id']; ?>', '_blank')" class="text-gray-400 hover:text-blue-500 transition focus:outline-none" title="Print Waybill Ticket">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <!-- Cancel / Void Button -->
                                <?php if ($ticket['status'] !== 'Cancelled' && $ticket['status'] !== 'Delivered'): ?>
                                    <button onclick="openDeleteDispatchModal(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-gray-400 hover:text-red-500 transition focus:outline-none" title="Cancel/Void Dispatch">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 mb-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-truck text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Truck:</span>
                                <?= htmlspecialchars($ticket['truck_code']); ?>
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-user text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Driver:</span>
                                <?= htmlspecialchars($ticket['driver_name']); ?>
                            </span>
                        </div>
                        <?php if (!empty($ticket['client_name'])): ?>
                            <div class="flex items-center space-x-2">
                                <i class="fa-regular fa-id-badge text-blue-500 w-5 flex justify-center"></i>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Client:</span>
                                    <?= htmlspecialchars($ticket['client_name']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['contact_number'])): ?>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-phone text-emerald-500 w-5 flex justify-center"></i>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Contact:</span>
                                    <?= htmlspecialchars($ticket['contact_number']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-cube text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Volume:</span>
                                <?= number_format($ticket['cubic_meters'] ?? 0, 2); ?> cu.m
                            </span>
                        </div>
                        <?php if (!empty($ticket['order_number'])): ?>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-clipboard-list text-indigo-500 w-5 flex justify-center"></i>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Order:</span>
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400 font-semibold"><?= htmlspecialchars($ticket['order_number']); ?></span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-clock text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300 text-xs">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Created:</span>
                                <?= date('M j, Y h:i A', strtotime($ticket['created_at'])) ?>
                            </span>
                        </div>
                        <?php if ($ticket['status'] == 'In Transit' && $ticket['transit_start_time']): ?>
                            <div class="flex items-center space-x-2 text-indigo-600 dark:text-indigo-400">
                                <i class="fa-solid fa-stopwatch w-5 flex justify-center animate-pulse"></i>
                                <span class="text-xs font-semibold">
                                    Started: <?= date('h:i A', strtotime($ticket['transit_start_time'])) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-3 relative text-sm text-gray-600 dark:text-gray-300">
                        <div class="absolute inset-y-0 left-2.5 w-px bg-gray-200 mt-2 mb-2"></div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-green-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white dark:bg-gray-800 rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">From:</span>
                                San Leonardo, Nueva Ecija
                            </div>
                        </div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-red-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white dark:bg-gray-800 rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">To:</span> <?= htmlspecialchars($ticket['destination']); ?>
                                <?php if (!empty($ticket['landmark'])): ?>
                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-start gap-1">
                                        <i class="fa-solid fa-location-dot mt-0.5 text-amber-500 text-[11px]"></i>
                                        <span><span class="font-semibold">Landmark:</span> <?= htmlspecialchars($ticket['landmark']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="noActiveDispatchesMatch" class="col-span-full py-12 text-center text-gray-400 dark:text-gray-500 hidden">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40 block mx-auto"></i>
                <p class="text-sm font-medium" id="noActiveDispatchesText">No active dispatches match your search.</p>
                <button type="button" onclick="clearDispatchSearch()" class="text-xs text-blue-500 hover:underline mt-2 inline-block">Clear search</button>
            </div>
        </div>

        <div id="dispatch-grid-requests" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
            <?php foreach ($cancellationRequests as $ticket):
                $reqSearchMeta = strtolower(implode(' ', array_filter([
                    $ticket['ticket_number'] ?? '',
                    $ticket['truck_code'] ?? '',
                    $ticket['driver_name'] ?? '',
                    $ticket['client_name'] ?? '',
                    $ticket['contact_number'] ?? '',
                    $ticket['destination'] ?? '',
                    $ticket['landmark'] ?? '',
                    $ticket['order_number'] ?? '',
                    $ticket['status'] ?? '',
                    'pending cancel cancellation request'
                ])));
            ?>
                <div class="dispatch-card border-2 border-orange-200 dark:border-orange-900 rounded-xl p-6 shadow-sm bg-orange-50/30 dark:bg-orange-900/10 hover:shadow-md transition" data-search="<?= htmlspecialchars($reqSearchMeta) ?>">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800 dark:text-gray-200">
                            <i class="fa-solid fa-triangle-exclamation text-orange-500"></i>
                            <span><?= htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="bg-orange-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm animate-pulse">pending cancel</span>
                            <div class="flex items-center space-x-2 border-l border-gray-200 dark:border-gray-600 pl-3 ml-1">
                                <button onclick="openApproveCancelModal(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-orange-500 hover:text-orange-600 transition" title="Approve Cancellation">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </button>
                                <button onclick="openSwitchTruckModal(<?= $ticket['driver_id']; ?>, '<?= addslashes($ticket['driver_name']); ?>', '<?= htmlspecialchars($ticket['truck_code']); ?>')" class="text-blue-500 hover:text-blue-600 transition focus:outline-none" title="Resolve by Switching Truck">
                                    <i class="fa-solid fa-truck-arrow-right text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4 text-sm">
                        <div class="flex items-center space-x-2"><i class="fa-solid fa-truck text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i><span class="text-gray-600 dark:text-gray-300"><span class="font-bold text-gray-800 dark:text-gray-200">Truck:</span> <?= htmlspecialchars($ticket['truck_code']); ?></span></div>
                        <div class="flex items-center space-x-2"><i class="fa-regular fa-user text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i><span class="text-gray-600 dark:text-gray-300"><span class="font-bold text-gray-800 dark:text-gray-200">Driver:</span> <?= htmlspecialchars($ticket['driver_name']); ?></span></div>
                        <?php if (!empty($ticket['client_name'])): ?>
                            <div class="flex items-center space-x-2"><i class="fa-regular fa-id-badge text-blue-500 w-5 flex justify-center"></i><span class="text-gray-600 dark:text-gray-300"><span class="font-bold text-gray-800 dark:text-gray-200">Client:</span> <?= htmlspecialchars($ticket['client_name']); ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['contact_number'])): ?>
                            <div class="flex items-center space-x-2"><i class="fa-solid fa-phone text-emerald-500 w-5 flex justify-center"></i><span class="text-gray-600 dark:text-gray-300"><span class="font-bold text-gray-800 dark:text-gray-200">Contact:</span> <?= htmlspecialchars($ticket['contact_number']); ?></span></div>
                        <?php endif; ?>
                        <div class="flex items-center space-x-2"><i class="fa-solid fa-cube text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i><span class="text-gray-600 dark:text-gray-300"><span class="font-bold text-gray-800 dark:text-gray-200">Volume:</span> <?= number_format($ticket['cubic_meters'] ?? 0, 2); ?> cu.m</span></div>
                        <?php if (!empty($ticket['destination'])): ?>
                            <div class="flex items-start space-x-2 text-xs text-gray-600 dark:text-gray-300 pt-1 border-t border-orange-100 dark:border-orange-900/30">
                                <i class="fa-solid fa-map-marker-alt text-red-500 w-5 flex justify-center mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">To:</span> <?= htmlspecialchars($ticket['destination']); ?>
                                    <?php if (!empty($ticket['landmark'])): ?>
                                        <div class="text-amber-600 dark:text-amber-400 mt-0.5"><span class="font-semibold">Landmark:</span> <?= htmlspecialchars($ticket['landmark']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-orange-600 dark:text-orange-400 italic bg-orange-100 dark:bg-orange-900/20 p-2 rounded-lg">
                        <i class="fa-solid fa-info-circle mr-1"></i> Waiting for admin to approve or re-assign truck.
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($cancellationRequests) == 0): ?>
                <div class="col-span-full py-12 text-center text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-check-circle text-4xl mb-3 text-gray-200 block"></i>
                    No pending cancellation requests.
                </div>
            <?php endif; ?>
            <div id="noRequestsDispatchesMatch" class="col-span-full py-12 text-center text-gray-400 dark:text-gray-500 hidden">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40 block mx-auto"></i>
                <p class="text-sm font-medium" id="noRequestsDispatchesText">No cancellation requests match your search.</p>
                <button type="button" onclick="clearDispatchSearch()" class="text-xs text-blue-500 hover:underline mt-2 inline-block">Clear search</button>
            </div>
        </div>

        <div id="dispatch-grid-completed" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
            <?php foreach ($completedTickets as $ticket):
                $compSearchMeta = strtolower(implode(' ', array_filter([
                    $ticket['ticket_number'] ?? '',
                    $ticket['truck_code'] ?? '',
                    $ticket['driver_name'] ?? '',
                    $ticket['client_name'] ?? '',
                    $ticket['contact_number'] ?? '',
                    $ticket['destination'] ?? '',
                    $ticket['landmark'] ?? '',
                    $ticket['order_number'] ?? '',
                    $ticket['status'] ?? '',
                    ($ticket['status'] === 'Cancelled' ? 'cancelled' : 'delivered completed')
                ])));
            ?>
                <div class="dispatch-card border border-gray-100 dark:border-gray-700 rounded-xl p-6 shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition" data-search="<?= htmlspecialchars($compSearchMeta) ?>">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800 dark:text-gray-200">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?= htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <?php if ($ticket['status'] == 'Cancelled'): ?>
                                <span class="bg-red-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm">cancelled</span>
                            <?php else: ?>
                                <span class="bg-green-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm">delivered</span>
                            <?php endif; ?>
                            <div class="border-l border-gray-200 dark:border-gray-600 pl-3 ml-1 flex items-center">
                                <button onclick="window.open('print_ticket.php?id=<?= $ticket['id']; ?>', '_blank')" class="text-gray-400 hover:text-blue-500 transition focus:outline-none" title="Print Waybill Ticket">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4 text-sm">
                        <div class="flex items-center space-x-2"><i class="fa-solid fa-truck text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Truck:</span>
                                <?= htmlspecialchars($ticket['truck_code']); ?>
                            </span>
                        </div>
                        <div class="flex items-center space-x-2"><i class="fa-regular fa-user text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Driver:</span>
                                <?= htmlspecialchars($ticket['driver_name']); ?>
                            </span>
                        </div>
                        <?php if (!empty($ticket['client_name'])): ?>
                            <div class="flex items-center space-x-2">
                                <i class="fa-regular fa-id-badge text-blue-500 w-5 flex justify-center"></i>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Client:</span>
                                    <?= htmlspecialchars($ticket['client_name']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['contact_number'])): ?>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-phone text-emerald-500 w-5 flex justify-center"></i>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Contact:</span>
                                    <?= htmlspecialchars($ticket['contact_number']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center space-x-2"><i class="fa-solid fa-cube text-gray-500 dark:text-gray-400 w-5 flex justify-center"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Volume:</span>
                                <?= number_format($ticket['cubic_meters'] ?? 0, 2); ?> cu.m
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs border-t border-gray-100 dark:border-gray-700 pt-2">
                            <div class="w-full">
                                <div class="flex justify-between text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Created:</span>
                                    <span><?= date('M j, Y h:i A', strtotime($ticket['created_at'])) ?></span>
                                </div>
                                <?php if ($ticket['transit_start_time'] && $ticket['transit_end_time']):
                                    $start = new DateTime($ticket['transit_start_time']);
                                    $end = new DateTime($ticket['transit_end_time']);
                                    $diff = $start->diff($end);
                                    $duration = '';
                                    if ($diff->h > 0) $duration .= $diff->h . 'h ';
                                    $duration .= $diff->i . 'm';
                                ?>
                                    <div class="flex justify-between text-gray-500 dark:text-gray-400 mb-1">
                                        <span>Transit Time:</span>
                                        <span><?= date('h:i A', strtotime($ticket['transit_start_time'])) ?> - <?= date('h:i A', strtotime($ticket['transit_end_time'])) ?></span>
                                    </div>
                                    <div class="flex justify-between text-indigo-600 dark:text-indigo-400 font-semibold">
                                        <span>Duration:</span>
                                        <span><?= $duration ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 relative text-sm text-gray-600 dark:text-gray-300">
                        <div class="absolute inset-y-0 left-2.5 w-px bg-gray-200 mt-2 mb-2"></div>
                        <div class="relative pl-8">
                            <i class="fa-regular fa-circle text-green-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white dark:bg-gray-800 rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">From:</span> San Leonardo, Nueva Ecija
                            </div>
                        </div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-red-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white dark:bg-gray-800 rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200">To:</span>
                                <?= htmlspecialchars($ticket['destination']); ?>
                                <?php if (!empty($ticket['landmark'])): ?>
                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-start gap-1">
                                        <i class="fa-solid fa-location-dot mt-0.5 text-amber-500 text-[11px]"></i>
                                        <span><span class="font-semibold">Landmark:</span> <?= htmlspecialchars($ticket['landmark']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="noCompletedDispatchesMatch" class="col-span-full py-12 text-center text-gray-400 dark:text-gray-500 hidden">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40 block mx-auto"></i>
                <p class="text-sm font-medium" id="noCompletedDispatchesText">No completed dispatches match your search.</p>
                <button type="button" onclick="clearDispatchSearch()" class="text-xs text-blue-500 hover:underline mt-2 inline-block">Clear search</button>
            </div>
        </div>
    </div>
</div>

<script>
    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'dispatches'): ?>
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i);
            if (key.startsWith('loading_start_') || key.startsWith('loading_complete_')) {
                localStorage.removeItem(key);
            }
        }
    <?php endif; ?>

    function filterDispatches() {
        const input = document.getElementById('dispatchSearchInput');
        const clearBtn = document.getElementById('dispatchSearchClear');
        const query = input ? input.value.toLowerCase().trim() : '';

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', query.length === 0);
        }

        const grids = [{
                id: 'dispatch-grid-active',
                noResId: 'noActiveDispatchesMatch',
                noResTextId: 'noActiveDispatchesText',
                label: 'active dispatches'
            },
            {
                id: 'dispatch-grid-requests',
                noResId: 'noRequestsDispatchesMatch',
                noResTextId: 'noRequestsDispatchesText',
                label: 'cancellation requests'
            },
            {
                id: 'dispatch-grid-completed',
                noResId: 'noCompletedDispatchesMatch',
                noResTextId: 'noCompletedDispatchesText',
                label: 'completed dispatches'
            }
        ];

        grids.forEach(g => {
            const gridEl = document.getElementById(g.id);
            if (!gridEl) return;
            const cards = gridEl.querySelectorAll('.dispatch-card');
            let matchCount = 0;

            cards.forEach(card => {
                const meta = card.getAttribute('data-search') || '';
                if (!query || meta.includes(query)) {
                    card.classList.remove('hidden');
                    matchCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResEl = document.getElementById(g.noResId);
            const noResTextEl = document.getElementById(g.noResTextId);
            if (noResEl) {
                if (matchCount === 0 && cards.length > 0) {
                    noResEl.classList.remove('hidden');
                    if (noResTextEl) noResTextEl.textContent = `No ${g.label} match "${query}".`;
                } else {
                    noResEl.classList.add('hidden');
                }
            }
        });
    }

    function clearDispatchSearch() {
        const input = document.getElementById('dispatchSearchInput');
        if (input) {
            input.value = '';
            filterDispatches();
            input.focus();
        }
    }
</script>
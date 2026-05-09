<div id="view-dispatches" class="tab-content hidden">

    <!-- Flash messages -->
    <?php if (isset($_SESSION['scan_msg'])): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-xl px-5 py-4 flex items-start space-x-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mt-0.5 flex-shrink-0"></i>
            <p class="text-sm font-medium"><?= $_SESSION['scan_msg'] ?></p>
        </div>
    <?php unset($_SESSION['scan_msg']);
    elseif (isset($_SESSION['scan_err'])): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-xl px-5 py-4 flex items-start space-x-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5 flex-shrink-0"></i>
            <p class="text-sm font-medium"><?= $_SESSION['scan_err'] ?></p>
        </div>
    <?php unset($_SESSION['scan_err']);
    endif; ?>

    <!-- RFID Scanner Panel -->
    <div class="mb-8 bg-indigo-600 rounded-xl shadow border border-indigo-700 overflow-hidden flex flex-col md:flex-row">
        <div class="p-6 md:w-1/3 flex flex-col justify-center text-white border-b md:border-b-0 md:border-r border-indigo-500">
            <div class="flex items-center space-x-3 mb-2">
                <i class="fa-solid fa-wifi text-3xl text-indigo-200"></i>
                <h2 class="font-bold text-xl">Dispatch Scanner</h2>
            </div>
            <p class="text-indigo-200 text-sm">Scan a truck's RFID card to dispatch it or confirm arrival.</p>
        </div>
        <div class="p-6 md:w-2/3 bg-white dark:bg-gray-800 flex items-center">
            <form method="POST" action="dashboard.php" class="w-full flex space-x-3">
                <input type="hidden" name="action" value="dispatch_scan_rfid">
                <div class="flex-grow">
                    <input type="text" name="rfid_tag" required autocomplete="off"
                        placeholder="Click here and scan RFID card..."
                        class="w-full border border-indigo-300 dark:border-indigo-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:text-gray-100 font-mono text-sm transition-colors">
                </div>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2 text-sm flex-shrink-0">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Confirm</span>
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2 text-xl font-bold text-gray-800 dark:text-gray-200">
                <i class="fa-regular fa-file-lines text-blue-500"></i>
                <span>Dispatch Tickets</span>
            </div>
            <button onclick="toggleModal('dispatchModal', true)" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Create Dispatch</span>
            </button>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-full inline-flex mb-6 text-sm font-medium text-gray-600 dark:text-gray-300">
            <button id="btn-tab-active" onclick="switchDispatchTab('active')" class="px-6 py-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition">
                Active (<?= count($activeTickets); ?>)
            </button>
            <button id="btn-tab-completed" onclick="switchDispatchTab('completed')" class="px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition">
                Completed (<?= count($completedTickets); ?>)
            </button>
        </div>

        <div id="dispatch-grid-active" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($activeTickets as $ticket):
                $statusClass = 'bg-blue-500';
                if ($ticket['status'] == 'Pending') $statusClass = 'bg-yellow-500';
            ?>
                <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-6 shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800 dark:text-gray-200">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?= htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="<?= $statusClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm">
                                <?= htmlspecialchars($ticket['status']); ?>
                            </span>
                            <div class="flex items-center space-x-2 border-l border-gray-200 dark:border-gray-600 pl-3 ml-1">
                                <!-- NEW: Mark as Delivered Button -->
                                <button onclick="markDispatchDelivered(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-green-500 hover:text-green-600 transition focus:outline-none" title="Mark as Delivered">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </button>
                                <!-- Print Button -->
                                <button onclick="window.open('print_ticket.php?id=<?= $ticket['id']; ?>', '_blank')" class="text-gray-400 hover:text-blue-500 transition focus:outline-none" title="Print Waybill Ticket">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <!-- Delete Button -->
                                <button onclick="openDeleteDispatchModal(<?= $ticket['id']; ?>, '<?= htmlspecialchars($ticket['ticket_number']); ?>')" class="text-gray-400 hover:text-red-500 transition focus:outline-none" title="Cancel/Void Dispatch">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
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
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="dispatch-grid-completed" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
            <?php foreach ($completedTickets as $ticket): ?>
                <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-6 shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800 dark:text-gray-200">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?= htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="bg-green-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase shadow-sm">delivered</span>
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
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
</script>
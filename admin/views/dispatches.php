<div id="view-dispatches" class="tab-content hidden">
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
                            <div class="flex space-x-2 border-l border-gray-200 dark:border-gray-600 pl-3 ml-1">
                                <button onclick="window.open('print_ticket.php?id=<?= $ticket['id']; ?>', '_blank')" class="text-gray-400 hover:text-blue-500 transition focus:outline-none" title="Print Waybill Ticket">
                                    <i class="fa-solid fa-print"></i>
                                </button>
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
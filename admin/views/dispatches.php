<div id="view-dispatches" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2 text-xl font-bold text-gray-800">
                <i class="fa-regular fa-file-lines text-blue-500"></i>
                <span>Dispatch Tickets</span>
            </div>
            <button onclick="toggleModal('dispatchModal', true)" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Create Dispatch</span>
            </button>
        </div>

        <div class="bg-gray-100 p-1 rounded-full inline-flex mb-6 text-sm font-medium text-gray-600">
            <button id="btn-tab-active" onclick="switchDispatchTab('active')" class="px-6 py-2 rounded-full bg-white shadow-sm text-gray-900 transition">
                Active (<?php echo count($activeTickets); ?>)
            </button>
            <button id="btn-tab-completed" onclick="switchDispatchTab('completed')" class="px-6 py-2 rounded-full hover:text-gray-900 transition">
                Completed (<?php echo count($completedTickets); ?>)
            </button>
        </div>

        <div id="dispatch-grid-active" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($activeTickets as $ticket):
                $statusClass = 'bg-blue-500';
                if ($ticket['status'] == 'Pending') $statusClass = 'bg-yellow-500';
            ?>
                <div class="border border-gray-100 rounded-xl p-6 shadow-sm bg-white hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                        <span class="<?php echo $statusClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase">
                            <?php echo htmlspecialchars($ticket['status']); ?>
                        </span>
                    </div>
                    <div class="space-y-3 mb-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-truck text-gray-500 w-5 flex justify-center"></i>
                            <span class="text-gray-600">
                                <span class="font-bold text-gray-800">Truck:</span>
                                <?php echo htmlspecialchars($ticket['truck_code']); ?>
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-user text-gray-500 w-5 flex justify-center"></i>
                            <span class="text-gray-600">
                                <span class="font-bold text-gray-800">Driver:</span>
                                <?php echo htmlspecialchars($ticket['driver_name']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="space-y-3 relative text-sm text-gray-600">
                        <div class="absolute inset-y-0 left-2.5 w-px bg-gray-200 mt-2 mb-2"></div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-green-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800">From:</span>
                                San Leonardo, Nueva Ecija
                            </div>
                        </div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-red-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800">To:</span> <?php echo htmlspecialchars($ticket['destination']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="dispatch-grid-completed" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
            <?php foreach ($completedTickets as $ticket): ?>
                <div class="border border-gray-100 rounded-xl p-6 shadow-sm bg-white hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 font-bold text-gray-800">
                            <i class="fa-regular fa-file-lines text-blue-500"></i>
                            <span><?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                        <span class="bg-green-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full lowercase">delivered</span>
                    </div>
                    <div class="space-y-3 mb-4 text-sm">
                        <div class="flex items-center space-x-2"><i class="fa-solid fa-truck text-gray-500 w-5 flex justify-center"></i>
                            <span class="text-gray-600">
                                <span class="font-bold text-gray-800">Truck:</span>
                                <?php echo htmlspecialchars($ticket['truck_code']); ?>
                            </span>
                        </div>
                        <div class="flex items-center space-x-2"><i class="fa-regular fa-user text-gray-500 w-5 flex justify-center"></i>
                            <span class="text-gray-600">
                                <span class="font-bold text-gray-800">Driver:</span>
                                <?php echo htmlspecialchars($ticket['driver_name']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="space-y-3 relative text-sm text-gray-600">
                        <div class="absolute inset-y-0 left-2.5 w-px bg-gray-200 mt-2 mb-2"></div>
                        <div class="relative pl-8">
                            <i class="fa-regular fa-circle text-green-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800">From:</span> San Leonardo, Nueva Ecija
                            </div>
                        </div>
                        <div class="relative pl-8"><i class="fa-regular fa-circle text-red-500 absolute left-0 top-0 mt-1 w-5 flex justify-center bg-white rounded-full"></i>
                            <div>
                                <span class="font-bold text-gray-800">To:</span>
                                <?php echo htmlspecialchars($ticket['destination']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
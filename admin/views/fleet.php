<div id="view-fleet" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow border border-gray-100 p-6 mb-6 flex justify-between items-center">
        <div class="flex items-center space-x-2 text-xl font-bold text-gray-800">
            <i class="fa-solid fa-truck-fast text-gray-700"></i>
            <span>Fleet Management</span>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-500 font-medium">Total: <?= count($fleetData); ?> trucks</div>
            <button onclick="toggleModal('addTruckModal', true)" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Truck</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($fleetData as $truck):
            $badgeClass = 'bg-gray-500';
            if ($truck['status'] == 'In Transit') $badgeClass = 'bg-green-500';
            if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500';
            if ($truck['status'] == 'Loading') $badgeClass = 'bg-blue-500';
            if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500';
        ?>
            <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col h-full relative hover:shadow-md transition">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-xl">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($truck['truck_code']); ?></h3>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($truck['driver_name'] ?? 'No Driver Assigned'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="<?= $badgeClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                            <?= htmlspecialchars($truck['status']); ?>
                        </span>

                        <button onclick="openUpdateStatusModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['status']); ?>', '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-blue-600 transition ml-2" title="Change Truck Status">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <button onclick="openDeleteTruckModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-red-600 transition ml-1" title="Remove Truck">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-gray-600 mb-5">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-wifi w-4 text-center text-blue-400"></i>
                        <span>RFID:
                            <strong class="text-gray-900"><?= htmlspecialchars($truck['rfid_tag'] ?? 'Unassigned'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-bolt w-4 text-center text-gray-400"></i>
                        <span>Speed:
                            <strong class="text-gray-900"><?= htmlspecialchars($truck['speed'] ?? 0); ?> mph</strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-location-dot w-4 text-center text-gray-400"></i>
                        <span>Destination:
                            <strong class="text-gray-900"><?= htmlspecialchars($truck['current_location'] ?? 'Garage'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-tower-broadcast w-4 text-center text-purple-400"></i>
                        <span>Coordinates:
                            <strong class="text-gray-900"><?= htmlspecialchars($truck['latitude'] ?? '0.0000'); ?>,
                                <?= htmlspecialchars($truck['longitude'] ?? '0.0000'); ?></strong>
                        </span>
                    </div>
                </div>

                <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6 mt-auto">
                    <div class="text-xs text-gray-500 mb-1">Active Dispatch</div>
                    <?php if ($truck['ticket_number']): ?>
                        <div class="font-bold text-gray-800 text-sm mb-1"><?= htmlspecialchars($truck['ticket_number']); ?></div>
                        <div class="text-xs text-gray-500 flex items-center space-x-2">
                            <span>San Leonardo, Nueva Ecija</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                            <span><?= htmlspecialchars($truck['destination']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 italic py-1">No active dispatch</div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-auto">
                    <button onclick="focusTruck(<?= $truck['latitude'] ?? 0; ?>, <?= $truck['longitude'] ?? 0; ?>)" class="border border-gray-200 bg-white rounded-lg py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-location-crosshairs text-gray-500"></i>
                        <span>Track</span>
                    </button>
                    <button class="border border-gray-200 bg-white rounded-lg py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition flex items-center justify-center space-x-2">
                        <i class="fa-regular fa-user text-gray-500"></i>
                        <span>Details</span>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
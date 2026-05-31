<div id="view-fleet" class="tab-content hidden">

    <?php if (isset($_GET['open']) && $_GET['open'] === 'addTruck'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toggleModal('addTruckModal', true);
        });
    </script>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 mb-6 flex justify-between items-center">
        <div class="flex items-center space-x-2 text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-truck-fast text-gray-700 dark:text-gray-200"></i>
            <span>Fleet Management</span>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total: <?= count($fleetData); ?> trucks</div>
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
            if ($truck['status'] == 'Maintenance') $badgeClass = 'bg-red-600';
        ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col h-full relative hover:shadow-md transition">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-xl">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg"><?= htmlspecialchars($truck['truck_code']); ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($truck['driver_name'] ?? 'No Driver Assigned'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-30">
                        <span class="<?= $badgeClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full w-30">
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

                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-wifi w-4 text-center text-blue-400"></i>
                        <span>RFID:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['rfid_tag'] ?? 'Unassigned'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-bolt w-4 text-center text-gray-400"></i>
                        <span>Speed:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['speed'] ?? 0); ?> mph</strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-location-dot w-4 text-center text-gray-400"></i>
                        <span>Destination:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['current_location'] ?? 'Garage'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-tower-broadcast w-4 text-center text-purple-400"></i>
                        <span>Coordinates:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['latitude'] ?? '0.0000'); ?>,
                                <?= htmlspecialchars($truck['longitude'] ?? '0.0000'); ?></strong>
                        </span>
                    </div>
                </div>

                <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6 mt-auto dark:bg-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Active Dispatch</div>
                    <?php if ($truck['ticket_number']): ?>
                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm mb-1"><?= htmlspecialchars($truck['ticket_number']); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-2">
                            <span>San Leonardo, Nueva Ecija</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                            <span><?= htmlspecialchars($truck['destination']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 dark:text-gray-400 italic py-1">No active dispatch</div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-3 mt-auto">
                    <button onclick="focusTruck(<?= $truck['latitude'] ?? 0; ?>, <?= $truck['longitude'] ?? 0; ?>)" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-location-crosshairs text-gray-500 dark:text-gray-400"></i>
                        <span>Track</span>
                    </button>
                    <?php if ($truck['status'] === 'Maintenance'): ?>
                    <button onclick="openMarkFixedModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg py-2 text-sm font-semibold transition flex items-center justify-center space-x-2 shadow-sm shadow-green-200 dark:shadow-none">
                        <i class="fa-solid fa-wrench"></i>
                        <span>Mark as Fixed</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
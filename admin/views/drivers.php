<div id="view-drivers" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-center mb-6">
        <div class="flex items-center space-x-2 text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-users text-gray-700 dark:text-gray-200"></i>
            <span>Driver Management</span>
        </div>
        <button onclick="toggleModal('addDriverModal', true)" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
            <i class="fa-solid fa-user-plus"></i><span>Add Driver</span>
        </button>
    </div>

    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-users text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['total_drivers'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Drivers</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-arrow-trend-up text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['on_duty'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">On Duty</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-medal text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= number_format($driverStats['avg_rating'] ?? 5.0, 1); ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Avg Rating</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($allDrivers as $driver):
            $badgeClass = 'bg-gray-500';
            if ($driver['status'] == 'Active') $badgeClass = 'bg-green-500';
            if ($driver['status'] == 'Dispatched') $badgeClass = 'bg-blue-500';

            $driverJson = htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8');
        ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 relative group">

                <div class="absolute top-6 right-6 flex items-center space-x-3 pt-10">
                    <button onclick="openDeleteDriverModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" class="text-gray-300 hover:text-red-500 transition" title="Remove Driver">
                        <i class="fa-solid fa-trash"></i>
                    </button>

                    <span class="<?= $badgeClass; ?> text-white text-xs font-semibold px-2 py-1 rounded-md">
                        <?= htmlspecialchars($driver['status']); ?>
                    </span>

                    <button onclick="openResetPasswordModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" class="text-gray-400 hover:text-orange-500 transition" title="Reset Password">
                        <i class="fa-solid fa-key"></i>
                    </button>

                    <button onclick="openSwitchTruckModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', '<?= htmlspecialchars($driver['truck_code'] ?? 'None'); ?>')" class="text-gray-400 hover:text-blue-500 transition" title="Switch Truck">
                        <i class="fa-solid fa-truck-arrow-right"></i>
                    </button>

                    <button onclick="openUpdateDriverStatusModal(<?= $driver['id']; ?>, '<?= htmlspecialchars($driver['status']); ?>', '<?= addslashes($driver['name']); ?>')" class="text-gray-400 hover:text-blue-600 transition" title="Change Driver Status">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>

                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center text-lg font-bold text-gray-400">
                        <?= getInitials($driver['name']); ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-lg"><?= htmlspecialchars($driver['name']); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300 mb-4">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-phone w-4 text-center"></i>
                        <span><?= htmlspecialchars($driver['phone'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-gray-700 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-300 mb-4 flex justify-between">
                    <span>Current Truck:</span>
                    <span class="font-bold text-blue-600"><?= htmlspecialchars($driver['truck_code'] ?? 'None'); ?></span>
                </div>

                <div class="grid grid-cols-1 bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-center mb-4">
                    <div>
                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                            <i class="fa-solid fa-star text-yellow-400 text-xs mr-1"></i>
                            <?= number_format($driver['rating'] ?? 5.0, 1); ?>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Rating</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-auto">
                    <button onclick='openViewDriverModal(<?= $driverJson; ?>)' class="border border-gray-300 dark:border-gray-600 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">View Details</button>
                    <button onclick='openContactDriverModal(<?= $driverJson; ?>)' class="border border-gray-300 dark:border-gray-600 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">Contact</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div id="view-drivers" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-2 text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>
            <span>Driver Management</span>
        </div>
        <button onclick="toggleModal('addDriverModal', true)" class="btn-primary text-sm w-full sm:w-auto">
            <i class="fa-solid fa-user-plus"></i><span>Add Driver</span>
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-users text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['total_drivers'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">Total Drivers</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['on_duty'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">On Duty</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-star text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= number_format($driverStats['avg_rating'] ?? 5.0, 1); ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">Avg Rating</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($allDrivers as $driver):
            $badgeClass = 'bg-gray-500';
            if ($driver['status'] == 'Active') $badgeClass = 'bg-emerald-500';
            if ($driver['status'] == 'Dispatched' || $driver['status'] == 'In Transit') $badgeClass = 'bg-blue-600';

            $driverJson = htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8');
        ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-all duration-200">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-3 min-w-0 pr-2">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-md shadow-blue-500/20 flex-shrink-0">
                                <?= getInitials($driver['name']); ?>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base truncate" title="<?= htmlspecialchars($driver['name']); ?>"><?= htmlspecialchars($driver['name']); ?></h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">CDL: <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <span class="<?= $badgeClass; ?> text-white text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm flex-shrink-0">
                            <?= htmlspecialchars($driver['status']); ?>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 mb-4">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-truck text-blue-500 w-4 text-center"></i>
                                <span>Assigned Truck</span>
                            </span>
                            <span class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['truck_code'] ?? 'Unassigned'); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-phone text-indigo-500 w-4 text-center"></i>
                                <span>Phone Number</span>
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['phone'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-star text-amber-400 w-4 text-center"></i>
                                <span>Performance</span>
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= number_format($driver['rating'] ?? 5.0, 1); ?> / 5.0</span>
                        </div>
                    </div>

                    <!-- Quick Action Bar -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/60 mb-4">
                        <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Quick Actions</span>
                        <div class="flex items-center space-x-1">
                            <button onclick="openPrintDriverTripsModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Print Trip Ticket">
                                <i class="fa-solid fa-print text-xs"></i>
                            </button>
                            <button onclick="openUpdateDriverStatusModal(<?= $driver['id']; ?>, '<?= htmlspecialchars($driver['status']); ?>', '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Edit Driver Status">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button onclick="openSwitchTruckModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', '<?= htmlspecialchars($driver['truck_code'] ?? 'None'); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Switch Assigned Truck">
                                <i class="fa-solid fa-truck-arrow-right text-xs"></i>
                            </button>
                            <button onclick="openResetPasswordModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Reset Password">
                                <i class="fa-solid fa-key text-xs"></i>
                            </button>
                            <button onclick="openDeleteDriverModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Delete Driver">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="grid grid-cols-2 gap-2.5 pt-2">
                    <button onclick='openViewDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition-all">View Details</button>
                    <button onclick='openContactDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-xl transition-all">Contact</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
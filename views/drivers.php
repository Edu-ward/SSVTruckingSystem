<div id="view-drivers" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex justify-between items-center mb-6">
        <div class="flex items-center space-x-2 text-xl font-bold text-gray-800">
            <i class="fa-solid fa-users text-gray-700"></i>
            <span>Driver Management</span>
        </div>
        <button onclick="toggleModal('driverModal', true)" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
            <i class="fa-solid fa-user-plus"></i><span>Add Driver</span>
        </button>
    </div>

    <div class="grid grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-users text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800"><?php echo $driverStats['total_drivers']; ?></div>
            <div class="text-xs text-gray-500 mt-1">Total Drivers</div>
        </div>
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-arrow-trend-up text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800"><?php echo $driverStats['on_duty'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 mt-1">On Duty</div>
        </div>
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-medal text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800"><?php echo number_format($driverStats['avg_rating'], 1); ?></div>
            <div class="text-xs text-gray-500 mt-1">Avg Rating</div>
        </div>
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col items-center justify-center">
            <i class="fa-regular fa-clock text-purple-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800"><?php echo number_format($driverStats['avg_hours'], 1); ?></div>
            <div class="text-xs text-gray-500 mt-1">Avg Hours/Week</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($allDrivers as $driver):
            $badgeClass = 'bg-gray-500';
            if ($driver['status'] == 'Active') $badgeClass = 'bg-green-500';
            if ($driver['status'] == 'Dispatched') $badgeClass = 'bg-blue-500';

            $progressPct = ($driver['hours_this_week'] / 60) * 100;
            $driverJson = htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8');
        ?>
            <div class="bg-white rounded-xl shadow border border-gray-100 p-6 relative group">

                <div class="absolute top-6 right-6 flex items-center space-x-3 pt-10">
                    <button onclick="openDeleteDriverModal(<?php echo $driver['id']; ?>, '<?php echo addslashes($driver['name']); ?>')" class="text-gray-300 hover:text-red-500 transition" title="Remove Driver">
                        <i class="fa-solid fa-trash"></i>
                    </button>

                    <span class="<?php echo $badgeClass; ?> text-white text-xs font-semibold px-2 py-1 rounded-md">
                        <?php echo htmlspecialchars($driver['status']); ?>
                    </span>

                    <button onclick="openUpdateDriverStatusModal(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['status']); ?>', '<?php echo addslashes($driver['name']); ?>')" class="text-gray-400 hover:text-blue-600 transition" title="Change Driver Status">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>

                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center text-lg font-bold text-gray-400">
                        <?php echo getInitials($driver['name']); ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($driver['name']); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-phone w-4 text-center"></i> <span><?php echo htmlspecialchars($driver['phone'] ?? 'N/A'); ?></span></div>
                    <div class="flex items-center space-x-2"><i class="fa-regular fa-envelope w-4 text-center"></i> <span><?php echo htmlspecialchars($driver['email'] ?? 'N/A'); ?></span></div>
                </div>

                <div class="bg-blue-50 rounded-lg p-3 text-sm text-gray-600 mb-4 flex justify-between">
                    <span>Current Truck:</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($driver['truck_code'] ?? 'None'); ?></span>
                </div>

                <div class="grid grid-cols-3 gap-2 bg-gray-50 rounded-lg p-3 text-center mb-4">
                    <div>
                        <div class="font-bold text-gray-800 text-sm"><i class="fa-solid fa-star text-yellow-400 text-xs mr-1"></i><?php echo number_format($driver['rating'], 1); ?></div>
                        <div class="text-xs text-gray-500">Rating</div>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 text-sm"><?php echo number_format($driver['total_deliveries']); ?></div>
                        <div class="text-xs text-gray-500">Deliveries</div>
                    </div>
                    <div>
                        <div class="font-bold text-green-600 text-sm"><?php echo $driver['on_time_pct']; ?>%</div>
                        <div class="text-xs text-gray-500">On-Time</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Hours This Week</span>
                        <span class="font-bold text-gray-800"><?php echo $driver['hours_this_week']; ?> / 60</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo min(100, $progressPct); ?>%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-auto">
                    <button onclick='openViewDriverModal(<?php echo $driverJson; ?>)' class="border border-gray-300 rounded-lg py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">View Details</button>
                    <button onclick='openContactDriverModal(<?php echo $driverJson; ?>)' class="border border-gray-300 rounded-lg py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Contact</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
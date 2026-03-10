<div id="view-tracking" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-100 p-4 flex flex-col">
            <div class="flex items-center space-x-2 mb-4">
                <i class="fa-solid fa-tower-broadcast text-purple-500"></i>
                <h2 class="font-semibold text-gray-800 text-lg">Live GPS Tracking</h2>
            </div>
            <div class="flex-grow border border-gray-200 rounded-lg overflow-hidden">
                <div id="map"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 p-4 flex flex-col max-h-[780px]">
            <div class="flex items-center space-x-2 mb-4 px-2"><i class="fa-solid fa-truck text-gray-700"></i>
                <h2 class="font-semibold text-gray-800 text-lg">Active Trucks</h2>
            </div>
            <div class="overflow-y-auto pr-2 space-y-4">
                <?php foreach ($trackingTrucks as $truck):
                    $badgeClass = 'bg-gray-500';
                    if ($truck['status'] == 'In Transit') $badgeClass = 'bg-blue-500';
                    if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500';
                    if ($truck['status'] == 'Loading') $badgeClass = 'bg-indigo-500';
                    if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500';
                ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-start space-x-3"><i class="fa-solid fa-truck text-gray-500 mt-1"></i>
                                <div>
                                    <div class="font-bold text-gray-800"><?php echo htmlspecialchars($truck['truck_code']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($truck['driver_name']); ?></div>
                                </div>
                            </div>
                            <span class="<?php echo $badgeClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($truck['status']); ?></span>
                        </div>
                        <div class="mt-3 space-y-1 text-sm text-gray-600 ml-7">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-location-dot w-4 text-center"></i>
                                <span><?php echo htmlspecialchars($truck['current_location']); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-chart-line w-4 text-center"></i>
                                <span>Speed: <?php echo htmlspecialchars($truck['speed']); ?> mph</span>
                            </div>
                        </div>
                        <button onclick="focusTruck(<?php echo $truck['latitude']; ?>, <?php echo $truck['longitude']; ?>)" class="mt-4 w-full bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium py-2 rounded-lg transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-location-crosshairs"></i><span>Track on Map</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
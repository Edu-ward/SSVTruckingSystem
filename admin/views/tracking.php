        <div id="view-tracking" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-4 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-tower-broadcast text-purple-500"></i>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-200 text-lg">Live GPS Tracking</h2>
                    <span class="flex items-center gap-1.5 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span> LIVE
                    </span>
                </div>
                <span id="map-last-updated" class="text-xs text-gray-400 dark:text-gray-500 italic"></span>
            </div>
            <div class="flex-grow border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden relative" style="min-height: 500px;">
                <div id="map" class="absolute inset-0 w-full h-full"></div>
                <!-- Locate Me button -->
                <button onclick="locateMe()" id="locateMeBtn"
                    title="Show my location on map"
                    class="absolute bottom-4 right-4 z-[999] bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-lg hover:shadow-xl hover:bg-blue-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-full w-11 h-11 flex items-center justify-center transition-all duration-200 group">
                    <i id="locateMeIcon" class="fa-solid fa-location-crosshairs text-blue-500 text-lg group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-4 flex flex-col max-h-[780px]">
            <div class="flex items-center space-x-2 mb-4 px-2">
                <i class="fa-solid fa-truck text-gray-700 dark:text-gray-200"></i>
                <h2 class="font-semibold text-gray-800 dark:text-gray-200 text-lg">Active Trucks</h2>
            </div>
            <div class="overflow-y-auto space-y-4">
                <?php foreach ($trackingTrucks as $truck):
                    $badgeClass = 'bg-gray-500';
                    if ($truck['status'] == 'In Transit') $badgeClass = 'bg-blue-500 dark:bg-gray-700';
                    if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500 dark:bg-gray-700';
                    if ($truck['status'] == 'Loading') $badgeClass = 'bg-indigo-500 dark:bg-gray-700';
                    if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500 dark:bg-gray-700';
                ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition bg-gray-50 dark:bg-gray-900">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-start space-x-3">
                                <i class="fa-solid fa-truck text-gray-500 dark:text-gray-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($truck['truck_code']); ?></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($truck['driver_name']); ?></div>
                                </div>
                            </div>
                            <span class="<?= $badgeClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                <?= htmlspecialchars($truck['status']); ?>
                            </span>
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-300 ml-7">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-location-dot w-4 text-center text-gray-400"></i>
                                <span><?= htmlspecialchars($truck['current_location']); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-chart-line w-4 text-center text-gray-400"></i>
                                <span>Speed: <?= htmlspecialchars($truck['speed']); ?> mph</span>
                            </div>
                        </div>

                        <?php if ($truck['status'] == 'Loading'): ?>
                            <div class="mt-4 bg-indigo-50 dark:bg-gray-700 border border-indigo-100 dark:border-gray-700 rounded-lg p-3 loading-timer-container" data-truck-id="<?= $truck['truck_code']; ?>">
                                <div class="flex justify-between items-center text-xs font-bold text-indigo-700 mb-2">
                                    <span class="timer-label"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading Cargo</span>
                                    <span class="timer-text font-mono text-sm">20:00</span>
                                </div>
                                <div class="w-full bg-indigo-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full timer-progress transition-all duration-1000" style="width: 0%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button onclick="focusTruck(<?= $truck['latitude']; ?>, <?= $truck['longitude']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="mt-4 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold py-2 rounded-lg transition flex items-center justify-center space-x-2 shadow-sm">
                            <i class="fa-solid fa-location-crosshairs text-blue-500"></i><span>Track on Map</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
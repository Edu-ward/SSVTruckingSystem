        <div id="view-tracking" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-tower-broadcast text-base"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900 dark:text-gray-100 text-lg leading-tight">Live Fleet GPS Tracking</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Real-time vehicle telemetry</p>
                    </div>
                    <span class="chip-emerald ml-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping inline-block"></span> LIVE
                    </span>
                </div>
                <span id="map-last-updated" class="text-xs text-gray-400 dark:text-gray-500 font-mono"></span>
            </div>
            <div class="flex-grow border border-gray-200/80 dark:border-gray-700/80 rounded-2xl overflow-hidden relative shadow-inner" style="min-height: 520px;">
                <div id="map" class="absolute inset-0 w-full h-full"></div>
                <!-- Recenter Fleet Map button -->
                <button onclick="recenterMap()" id="locateMeBtn"
                    title="Center map on fleet"
                    class="absolute bottom-5 right-5 z-[999] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 shadow-xl hover:shadow-2xl hover:bg-blue-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-2xl w-12 h-12 flex items-center justify-center transition-all duration-200 group active:scale-95">
                    <i id="locateMeIcon" class="fa-solid fa-crosshairs text-blue-600 dark:text-blue-400 text-xl group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 flex flex-col max-h-[780px]">
            <div class="flex items-center justify-between mb-4 px-1">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-truck text-sm"></i>
                    </div>
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-lg">Active Fleet</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <?= count($trackingTrucks); ?> Vehicles
                </span>
            </div>

            <div class="overflow-y-auto space-y-3.5 pr-1">
                <?php foreach ($trackingTrucks as $truck):
                    $chipClass = 'chip-blue';
                    if ($truck['status'] == 'In Transit') $chipClass = 'chip-emerald';
                    if ($truck['status'] == 'Idle') $chipClass = 'chip-amber';
                    if ($truck['status'] == 'Loading') $chipClass = 'chip-blue';
                    if ($truck['status'] == 'Unloading') $chipClass = 'chip-rose';
                ?>
                    <div class="border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 hover:shadow-md transition-all bg-gray-50/60 dark:bg-gray-900/60 hover:bg-white dark:hover:bg-gray-800 group">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-200/70 dark:bg-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-200 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="fa-solid fa-truck-front text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-gray-100 text-sm"><?= htmlspecialchars($truck['truck_code']); ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium"><?= htmlspecialchars($truck['driver_name']); ?></div>
                                </div>
                            </div>
                            <span class="<?= $chipClass; ?>">
                                <?= htmlspecialchars($truck['status']); ?>
                            </span>
                        </div>

                        <div class="mt-3 space-y-1.5 text-xs text-gray-600 dark:text-gray-300 font-medium pl-1">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-location-dot w-4 text-center text-rose-500"></i>
                                <span class="truncate"><?= htmlspecialchars($truck['current_location']); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-gauge-high w-4 text-center text-blue-500"></i>
                                <span>Speed: <strong class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($truck['speed']); ?> mph</strong></span>
                            </div>
                        </div>

                        <?php if ($truck['status'] == 'Loading'): ?>
                            <div class="mt-3 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl p-3 loading-timer-container" data-truck-id="<?= $truck['truck_code']; ?>">
                                <div class="flex justify-between items-center text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-1.5">
                                    <span class="timer-label flex items-center"><i class="fa-solid fa-spinner fa-spin mr-1.5 text-indigo-500"></i> Loading Cargo</span>
                                    <span class="timer-text font-mono text-xs">20:00</span>
                                </div>
                                <div class="w-full bg-indigo-200/70 dark:bg-indigo-900/50 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full timer-progress transition-all duration-1000" style="width: 0%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button onclick="focusTruck(<?= $truck['latitude']; ?>, <?= $truck['longitude']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="mt-3 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:border-blue-600 text-gray-700 dark:text-gray-200 text-xs font-bold py-2 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-sm group-hover:border-blue-500">
                            <i class="fa-solid fa-location-crosshairs text-blue-500 group-hover:text-white transition-colors"></i><span>Track on Map</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
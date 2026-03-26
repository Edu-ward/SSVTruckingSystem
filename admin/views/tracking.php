<div id="view-tracking" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-100 p-4 flex flex-col">
            <div class="flex items-center space-x-2 mb-4">
                <i class="fa-solid fa-tower-broadcast text-purple-500"></i>
                <h2 class="font-semibold text-gray-800 text-lg">Live GPS Tracking</h2>
            </div>
            <div class="flex-grow border border-gray-200 rounded-lg overflow-hidden relative" style="min-height: 500px;">
                <div id="map" class="absolute inset-0 w-full h-full"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 p-4 flex flex-col max-h-[780px]">
            <div class="flex items-center space-x-2 mb-4 px-2">
                <i class="fa-solid fa-truck text-gray-700"></i>
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
                            <div class="flex items-start space-x-3">
                                <i class="fa-solid fa-truck text-gray-500 mt-1"></i>
                                <div>
                                    <div class="font-bold text-gray-800"><?php echo htmlspecialchars($truck['truck_code']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($truck['driver_name']); ?></div>
                                </div>
                            </div>
                            <span class="<?php echo $badgeClass; ?> text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                <?php echo htmlspecialchars($truck['status']); ?>
                            </span>
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-600 ml-7">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-location-dot w-4 text-center text-gray-400"></i>
                                <span><?php echo htmlspecialchars($truck['current_location']); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-chart-line w-4 text-center text-gray-400"></i>
                                <span>Speed: <?php echo htmlspecialchars($truck['speed']); ?> mph</span>
                            </div>
                        </div>

                        <?php if ($truck['status'] == 'Loading'): ?>
                            <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-lg p-3 loading-timer-container" data-truck-id="<?php echo $truck['truck_code']; ?>">
                                <div class="flex justify-between items-center text-xs font-bold text-indigo-700 mb-2">
                                    <span class="timer-label"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading Cargo</span>
                                    <span class="timer-text font-mono text-sm">20:00</span>
                                </div>
                                <div class="w-full bg-indigo-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full timer-progress transition-all duration-1000" style="width: 0%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button onclick="focusTruck(<?php echo $truck['latitude']; ?>, <?php echo $truck['longitude']; ?>)" class="mt-4 w-full bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-semibold py-2 rounded-lg transition flex items-center justify-center space-x-2 shadow-sm">
                            <i class="fa-solid fa-location-crosshairs text-blue-500"></i><span>Track on Map</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        function updateLoadingTimers() {
            const containers = document.querySelectorAll('.loading-timer-container');
            const LOADING_DURATION_MS = 20 * 60 * 1000; // 20 minutes in milliseconds

            containers.forEach(container => {
                const truckId = container.getAttribute('data-truck-id');
                const timerText = container.querySelector('.timer-text');
                const timerProgress = container.querySelector('.timer-progress');
                const timerLabel = container.querySelector('.timer-label');

                // 1. Get or set the start time in the browser's memory (LocalStorage)
                let startTime = localStorage.getItem('loading_start_' + truckId);
                if (!startTime) {
                    startTime = Date.now();
                    localStorage.setItem('loading_start_' + truckId, startTime);
                }

                // 2. Calculate time elapsed and remaining
                const elapsed = Date.now() - parseInt(startTime);
                const remaining = Math.max(0, LOADING_DURATION_MS - elapsed);

                // 3. Update the UI
                if (remaining > 0) {
                    const mins = Math.floor(remaining / 60000);
                    const secs = Math.floor((remaining % 60000) / 1000);
                    // Format to look like 19:05
                    timerText.innerText = `${mins}:${secs.toString().padStart(2, '0')}`;

                    // Animate the progress bar width
                    const percent = (elapsed / LOADING_DURATION_MS) * 100;
                    timerProgress.style.width = percent + '%';
                } else {
                    // Time is up! 
                    timerText.innerText = 'Ready!';
                    timerLabel.innerHTML = '<i class="fa-solid fa-check text-green-600 mr-1"></i> Loading Complete';
                    timerLabel.classList.replace('text-indigo-700', 'text-green-700');
                    timerText.classList.replace('text-indigo-700', 'text-green-700');

                    timerProgress.style.width = '100%';
                    timerProgress.classList.replace('bg-indigo-600', 'bg-green-500');
                }
            });
        }

        // Run the timer every second
        setInterval(updateLoadingTimers, 1000);
        // Call it immediately so there's no 1-second delay on page load
        updateLoadingTimers();
    });
</script>
<div id="view-fleet" class="tab-content hidden">

    <?php if (isset($_GET['open']) && $_GET['open'] === 'addTruck'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                toggleModal('addTruckModal', true);
            });
        </script>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Fleet Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage registered vehicles, status, drivers, and RFID trackers</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">

            <div class="relative flex-1 sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text" id="fleetSearchInput" placeholder="Search trucks, drivers, status, RFID..." oninput="filterFleetCards()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="fleetSearchClear" onclick="clearFleetSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">Total: <?= count($fleetData); ?> trucks</div>
            <button onclick="toggleModal('addTruckModal', true)" class="btn-primary text-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Add Truck</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="fleetCardsGrid">
        <?php foreach ($fleetData as $truck):
            $badgeClass = 'bg-gray-500';
            if ($truck['status'] == 'In Transit') $badgeClass = 'bg-green-500';
            if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500';
            if ($truck['status'] == 'Loading') $badgeClass = 'bg-blue-500';
            if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500';
            if ($truck['status'] == 'Maintenance') $badgeClass = 'bg-red-600';
            if ($truck['status'] == 'Decommissioned') $badgeClass = 'bg-stone-500';

            $isInTransit = ($truck['status'] === 'In Transit');

            $rawLocation = trim($truck['current_location'] ?? '');
            if ($rawLocation === '' || strcasecmp($rawLocation, 'Garage') === 0) {
                $displayLocation = 'San Leonardo (Garage)';
            } else {
                $displayLocation = $rawLocation;
            }

            $hasDestination = $isInTransit && !empty(trim($truck['destination'] ?? ''));
            $destinationDisplay = $hasDestination ? trim($truck['destination']) : 'Unavailable';

            $searchMeta = htmlspecialchars(strtolower(($truck['truck_code'] ?? '') . ' ' . ($truck['driver_name'] ?? '') . ' ' . ($truck['status'] ?? '') . ' ' . ($truck['rfid_tag'] ?? '') . ' ' . $displayLocation . ' ' . $destinationDisplay));

            $driverNames = [];
            if (!empty($truck['driver_name'])) {
                $driverNames = array_map('trim', explode('•', $truck['driver_name']));
                $driverNames = array_filter($driverNames);
            }

            $hasActiveDispatch = !empty($truck['ticket_number']) || ($truck['status'] === 'In Transit');
        ?>
            <div class="fleet-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="<?= $searchMeta; ?>" <?= $hasActiveDispatch ? 'data-truck-id="' . $truck['id'] . '"' : ''; ?>>
                <div class="flex justify-between items-start mb-5 gap-2">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg truncate"><?= htmlspecialchars($truck['truck_code']); ?></h3>
                            <?php if (!empty($driverNames) && count($driverNames) >= 2): ?>
                                <div class="flex flex-col gap-0.5 mt-0.5">
                                    <?php foreach ($driverNames as $index => $dName): ?>
                                        <p class="text-xs <?= $index === 0 ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-500 dark:text-gray-400'; ?> truncate flex items-center gap-1" title="<?= htmlspecialchars($dName); ?>">
                                            <i class="fa-solid <?= $index === 0 ? 'fa-id-card-clip' : 'fa-user-clock'; ?> text-[10px] flex-shrink-0"></i>
                                            <span class="truncate"><?= htmlspecialchars($dName); ?></span>
                                            <?php if ($index === 0): ?>
                                                <span class="text-[9px] bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-bold px-1.5 py-0.5 rounded flex-shrink-0">Main</span>
                                            <?php else: ?>
                                                <span class="text-[9px] bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-300 font-bold px-1.5 py-0.5 rounded flex-shrink-0">Alt</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($truck['driver_name'] ?? 'No Driver Assigned'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1.5 flex-shrink-0 ml-auto">
                        <span class="<?= $badgeClass; ?> text-white text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                            <?= htmlspecialchars($truck['status']); ?>
                        </span>

                        <button type="button" data-truck="<?= htmlspecialchars(json_encode($truck), ENT_QUOTES, 'UTF-8'); ?>" onclick="openEditTruckModal(this.dataset.truck)" class="text-gray-400 hover:text-blue-600 transition p-1" title="Edit Truck Details & Status">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <button onclick="openDecommissionTruckModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-amber-600 transition p-1 <?= $truck['status'] === 'Decommissioned' ? 'opacity-40 cursor-not-allowed' : '' ?>" title="<?= $truck['status'] === 'Decommissioned' ? 'Truck Already Decommissioned' : 'Decommission Truck' ?>" <?= $truck['status'] === 'Decommissioned' ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-ban"></i>
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
                        <i class="fa-solid fa-route w-4 text-center <?= $hasDestination ? 'text-blue-500' : 'text-gray-400'; ?>"></i>
                        <span>Destination:
                            <?php if ($hasDestination): ?>
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($destinationDisplay); ?></strong>
                            <?php else: ?>
                                <strong class="text-gray-400 dark:text-gray-500 font-medium italic">Unavailable</strong>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-location-dot w-4 text-center text-rose-500"></i>
                        <span>Location:
                            <?php if ($hasActiveDispatch): ?>
                                <strong class="text-gray-900 dark:text-gray-100" data-live-location="<?= $truck['id'] ?>">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse mr-1 align-middle"></span><?= htmlspecialchars($displayLocation); ?>
                                </strong>
                            <?php else: ?>
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($displayLocation); ?></strong>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6 mt-auto dark:bg-gray-700">
                    <?php if ($truck['ticket_number'] && $hasDestination): ?>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Active Dispatch</div>
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
                    <?php if ($hasActiveDispatch): ?>
                        <button onclick="focusTruck(<?= $truck['latitude'] ?? 0; ?>, <?= $truck['longitude'] ?? 0; ?>)" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-location-crosshairs text-gray-500 dark:text-gray-400"></i>
                            <span>Track</span>
                        </button>
                    <?php else: ?>
                        <div class="border border-dashed border-gray-200 dark:border-gray-700 rounded-lg py-2 text-sm font-medium text-gray-400 dark:text-gray-600 flex items-center justify-center space-x-2 cursor-not-allowed select-none">
                            <i class="fa-solid fa-location-crosshairs"></i>
                            <span>Tracking Unavailable</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($truck['status'] === 'Maintenance'): ?>
                        <button onclick="openMarkFixedModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg py-2 text-sm font-semibold transition flex items-center justify-center space-x-2 shadow-sm shadow-green-200 dark:shadow-none">
                            <i class="fa-solid fa-wrench"></i>
                            <span>Mark as Fixed</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div id="noFleetSearchResults" class="hidden col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-truck"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No trucks found</h4>
            <p class="text-xs text-gray-400 mt-1" id="noFleetSearchText">No fleet vehicles match your search filter.</p>
            <button type="button" onclick="clearFleetSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer">
                Clear search filter
            </button>
        </div>
    </div>

    <script>
        function filterFleetCards() {
            const input = document.getElementById('fleetSearchInput');
            const clearBtn = document.getElementById('fleetSearchClear');
            const query = input ? input.value.toLowerCase().trim() : '';
            const cards = document.querySelectorAll('#fleetCardsGrid .fleet-card');
            const noResults = document.getElementById('noFleetSearchResults');
            const noResultsText = document.getElementById('noFleetSearchText');

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', query.length === 0);
            }

            let matchCount = 0;
            cards.forEach(card => {
                const meta = card.getAttribute('data-search') || '';
                if (!query || meta.includes(query)) {
                    card.classList.remove('hidden');
                    matchCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (noResults) {
                if (matchCount === 0 && cards.length > 0) {
                    noResults.classList.remove('hidden');
                    if (noResultsText) noResultsText.textContent = `No fleet vehicles match "${query}".`;
                } else {
                    noResults.classList.add('hidden');
                }
            }
        }

        function clearFleetSearch() {
            const input = document.getElementById('fleetSearchInput');
            if (input) {
                input.value = '';
                filterFleetCards();
                input.focus();
            }
        }

        // Reverse geocode cache keyed by rounded lat,lng to ~100m precision
        const _geoCache = {};
        const _geoPending = {};

        function reverseGeocode(lat, lng) {
            const key = `${Math.round(lat * 1000) / 1000},${Math.round(lng * 1000) / 1000}`;
            if (_geoCache[key]) return Promise.resolve(_geoCache[key]);
            if (_geoPending[key]) return _geoPending[key];
            _geoPending[key] = fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&zoom=16`, {
                        headers: {
                            'Accept-Language': 'en'
                        }
                    }
                )
                .then(r => r.json())
                .then(d => {
                    const a = d.address || {};
                    const parts = [
                        a.village || a.suburb || a.hamlet || a.neighbourhood || a.town || a.city_district || a.city,
                        a.city || a.county || a.state_district
                    ].filter(Boolean);
                    const loc = parts.length ? parts.join(', ') : (d.display_name || `${lat.toFixed(4)}, ${lng.toFixed(4)}`);
                    _geoCache[key] = loc;
                    delete _geoPending[key];
                    return loc;
                })
                .catch(() => {
                    delete _geoPending[key];
                    return `${parseFloat(lat).toFixed(4)}°N, ${parseFloat(lng).toFixed(4)}°E`;
                });
            return _geoPending[key];
        }

        function updateLocEl(el, locText) {
            const dot = el.querySelector('span.animate-pulse');
            el.textContent = '';
            if (dot) el.appendChild(dot);
            el.appendChild(document.createTextNode(' ' + locText));
        }

        (function startFleetLocationPolling() {
            function pollFleetLocations() {
                const fleetView = document.getElementById('view-fleet');
                if (!fleetView || fleetView.classList.contains('hidden')) return;

                fetch('get_tracking_data.php')
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success || !Array.isArray(data.trucks)) return;
                        data.trucks.forEach((truck, i) => {
                            const lat = parseFloat(truck.latitude);
                            const lng = parseFloat(truck.longitude);
                            if (!lat || !lng) return;
                            setTimeout(() => {
                                reverseGeocode(lat, lng).then(loc => {
                                    const fleetLocEl = document.querySelector(`[data-live-location="${truck.id}"]`);
                                    if (fleetLocEl) updateLocEl(fleetLocEl, loc);
                                    const sideLocEl = document.querySelector(`[data-live-loc="${truck.truck_code}"]`);
                                    if (sideLocEl) updateLocEl(sideLocEl, loc);
                                });
                            }, i * 1100);
                        });
                    })
                    .catch(() => {});
            }

            pollFleetLocations();
            setInterval(pollFleetLocations, 10000);
        })();
    </script>
</div>
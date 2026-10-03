<?php
$activeFleet = array_values(array_filter($fleetData ?? [], fn($t) => ($t['status'] ?? '') !== 'Decommissioned'));
$decommissionedFleetList = !empty($decommissionedTrucks) ? $decommissionedTrucks : array_values(array_filter($fleetData ?? [], fn($t) => ($t['status'] ?? '') === 'Decommissioned'));
?>
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
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100" id="fleetHeaderTitle">Fleet Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400" id="fleetHeaderSubtitle">Manage registered trucks, status, drivers, and RFID trackers</p>
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

            <!-- View Decommissioned / Active Toggle Button -->
            <button type="button" id="fleetToggleDecomBtn" onclick="toggleFleetView()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition shadow-sm cursor-pointer">
                <i class="fa-solid fa-box-archive" id="fleetToggleDecomIcon"></i>
                <span id="fleetToggleDecomText">Decommissioned Trucks</span>
                <span id="decomBadgeCount" class="px-2 py-0.5 text-xs rounded-full bg-amber-200/80 dark:bg-amber-800 text-amber-900 dark:text-amber-100 font-bold"><?= count($decommissionedFleetList); ?></span>
            </button>

            <button onclick="toggleModal('addTruckModal', true)" class="btn-primary text-sm" id="fleetAddTruckBtn">
                <i class="fa-solid fa-plus"></i>
                <span>Add Truck</span>
            </button>
        </div>
    </div>

    <!-- View Pills Sub-navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-2xl border border-gray-200/60 dark:border-gray-700/60">
            <button type="button" onclick="switchFleetView('active')" id="fleetTabActive" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm cursor-pointer">
                <i class="fa-solid fa-truck"></i>
                <span>Active Fleet</span>
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-bold" id="activeFleetBadge"><?= count($activeFleet); ?></span>
            </button>
            <button type="button" onclick="switchFleetView('decommissioned')" id="fleetTabDecom" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">
                <i class="fa-solid fa-ban text-amber-500"></i>
                <span>Decommissioned Trucks</span>
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 font-bold" id="decomFleetBadge"><?= count($decommissionedFleetList); ?></span>
            </button>
        </div>
    </div>

    <!-- Active Fleet Section -->
    <div id="fleetActiveSection">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="fleetCardsGrid">
            <?php foreach ($activeFleet as $truck):
                $badgeClass = 'bg-gray-500';
                if ($truck['status'] == 'In Transit') $badgeClass = 'bg-green-500';
                if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500';
                if ($truck['status'] == 'Loading') $badgeClass = 'bg-blue-500';
                if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500';
                if ($truck['status'] == 'Maintenance') $badgeClass = 'bg-red-600';

                $isInTransit = ($truck['status'] === 'In Transit');

                $rawLocation = trim($truck['current_location'] ?? '');
                if ($rawLocation === '' || strcasecmp($rawLocation, 'Garage') === 0) {
                    $displayLocation = 'San Leonardo (Garage)';
                } else {
                    $displayLocation = $rawLocation;
                }

                $hasActiveDispatch = !empty($truck['ticket_number']);
                $hasDestination = $hasActiveDispatch && !empty(trim($truck['destination'] ?? ''));
                $destinationDisplay = $hasDestination ? trim($truck['destination']) : 'Unavailable';

                $searchMeta = htmlspecialchars(strtolower(($truck['truck_code'] ?? '') . ' ' . ($truck['driver_name'] ?? '') . ' ' . ($truck['status'] ?? '') . ' ' . ($truck['rfid_tag'] ?? '') . ' ' . $displayLocation . ' ' . $destinationDisplay));

                $driverNames = [];
                if (!empty($truck['driver_name'])) {
                    $driverNames = array_map('trim', explode('•', $truck['driver_name']));
                    $driverNames = array_filter($driverNames);
                }
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
                                    <div class="flex flex-col gap-1 mt-1">
                                        <?php foreach ($driverNames as $index => $dName): ?>
                                            <p class="text-xs text-gray-700 dark:text-gray-300 font-medium truncate flex items-center gap-1.5" title="<?= htmlspecialchars($dName); ?>">
                                                <i class="fa-solid fa-user-check text-[10px] text-blue-500 flex-shrink-0"></i>
                                                <span class="truncate"><?= htmlspecialchars($dName); ?></span>
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

                            <button onclick="openDecommissionTruckModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-amber-600 transition p-1" title="Decommission Truck">
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
                            <button onclick="focusTruck(<?= $truck['latitude'] ?? 0; ?>, <?= $truck['longitude'] ?? 0; ?>)" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-center space-x-2 cursor-pointer">
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
                            <button onclick="openMarkFixedModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg py-2 text-sm font-semibold transition flex items-center justify-center space-x-2 shadow-sm shadow-green-200 dark:shadow-none cursor-pointer">
                                <i class="fa-solid fa-wrench"></i>
                                <span>Mark as Fixed</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($activeFleet)): ?>
                <div class="col-span-full py-16 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No active trucks in fleet</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">All trucks may be decommissioned or none registered yet.</p>
                    <button type="button" onclick="toggleModal('addTruckModal', true)" class="mt-4 btn-primary text-xs">
                        <i class="fa-solid fa-plus mr-1"></i> Register New Truck
                    </button>
                </div>
            <?php endif; ?>

            <div id="noFleetSearchResults" class="hidden col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-lg">
                    <i class="fa-solid fa-truck"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No trucks found</h4>
                <p class="text-xs text-gray-400 mt-1" id="noFleetSearchText">No fleet trucks match your search filter.</p>
                <button type="button" onclick="clearFleetSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer">
                    Clear search filter
                </button>
            </div>
        </div>
    </div>

    <!-- Decommissioned Trucks Section -->
    <div id="fleetDecommissionedSection" class="hidden">
        <!-- Archive Information Banner -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/20 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-4 sm:p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                        <span>Decommissioned Trucks Archive</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-200/70 dark:bg-amber-800/60 text-amber-800 dark:text-amber-200" id="decomSectionCount">
                            <?= count($decommissionedFleetList); ?> <?= count($decommissionedFleetList) === 1 ? 'truck' : 'trucks'; ?>
                        </span>
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        These trucks are retired from fleet operations and prevented from taking orders. Past trip logs and maintenance records remain permanently intact. Click <strong>Commission Again</strong> on any vehicle to restore it to the active fleet.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="loadDecommissionedTrucks(true)" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate text-amber-600 dark:text-amber-400" id="decomRefreshSpinner"></i>
                    <span>Reload</span>
                </button>
                <button type="button" onclick="switchFleetView('active')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Active Fleet</span>
                </button>
            </div>
        </div>

        <!-- Decommissioned Trucks Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="decommissionedCardsGrid">
            <?php foreach ($decommissionedFleetList as $truck):
                $decomMeta = htmlspecialchars(strtolower(($truck['truck_code'] ?? '') . ' ' . ($truck['rfid_tag'] ?? '') . ' decommissioned ' . ($truck['current_location'] ?? '')));
                $dispatchesCount = intval($truck['total_dispatches'] ?? 0);
            ?>
                <div class="decom-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="<?= $decomMeta; ?>">
                    <div class="flex justify-between items-start mb-4 gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-11 h-11 bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 rounded-xl flex items-center justify-center text-xl flex-shrink-0 border border-stone-200 dark:border-stone-700">
                                <i class="fa-solid fa-truck-pickup"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg truncate"><?= htmlspecialchars($truck['truck_code']); ?></h3>
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    <span>Decommissioned Truck</span>
                                </p>
                            </div>
                        </div>
                        <span class="bg-stone-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap shadow-sm">
                            Decommissioned
                        </span>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-wifi w-4 text-center text-amber-500"></i>
                            <span>RFID Tag:
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['rfid_tag'] ?? 'Unassigned'); ?></strong>
                                <span class="text-[10px] text-gray-400 font-medium ml-1">(Deactivated)</span>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-box-archive w-4 text-center text-blue-500"></i>
                            <span>Historical Dispatches:
                                <strong class="text-gray-900 dark:text-gray-100"><?= $dispatchesCount; ?> trip<?= $dispatchesCount === 1 ? '' : 's'; ?> completed</strong>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-location-dot w-4 text-center text-rose-500"></i>
                            <span>Last Location:
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['current_location'] ?: 'San Leonardo (Garage)'); ?></strong>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-clock-rotate-left w-4 text-center text-amber-500"></i>
                            <span>Last Dispatched:
                                <strong class="text-gray-900 dark:text-gray-100"><?= !empty($truck['last_dispatch_date']) ? date('M d, Y', strtotime($truck['last_dispatch_date'])) : 'No recorded trips'; ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="bg-amber-50/60 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3 mb-5 mt-auto text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                        <span>Vehicle is out of service. To return this truck to operations and allow dispatches, click below.</span>
                    </div>

                    <button type="button" onclick="openRecommissionTruckModal(<?= $truck['id']; ?>, '<?= htmlspecialchars(addslashes($truck['truck_code'])); ?>', '<?= htmlspecialchars(addslashes($truck['rfid_tag'] ?? '')); ?>')" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl py-2.5 px-4 text-sm font-bold transition flex items-center justify-center space-x-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Commission Again</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noDecommissionedMsg" class="<?= empty($decommissionedFleetList) ? '' : 'hidden' ?> py-16 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No Decommissioned Trucks</h4>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">All fleet trucks are currently active and in service. When a truck is decommissioned, it will be safely archived here with options to recommission it back into service anytime.</p>
        </div>

        <div id="noDecomSearchResults" class="hidden py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-ban"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No decommissioned trucks found</h4>
            <p class="text-xs text-gray-400 mt-1" id="noDecomSearchText">No decommissioned trucks match your search filter.</p>
            <button type="button" onclick="clearFleetSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/40 rounded-xl hover:bg-amber-100 dark:hover:bg-amber-900/60 transition cursor-pointer">
                Clear search filter
            </button>
        </div>
    </div>

    <script>
        let currentFleetView = 'active';

        function switchFleetView(view) {
            currentFleetView = view;
            const activeSec = document.getElementById('fleetActiveSection');
            const decomSec = document.getElementById('fleetDecommissionedSection');
            const tabActive = document.getElementById('fleetTabActive');
            const tabDecom = document.getElementById('fleetTabDecom');
            const toggleBtn = document.getElementById('fleetToggleDecomBtn');
            const toggleIcon = document.getElementById('fleetToggleDecomIcon');
            const toggleText = document.getElementById('fleetToggleDecomText');
            const legend = document.getElementById('fleetActiveStatsLegend');
            const addTruckBtn = document.getElementById('fleetAddTruckBtn');
            const decomBadge = document.getElementById('decomBadgeCount');
            const decomFleetBadge = document.getElementById('decomFleetBadge');
            const activeFleetBadge = document.getElementById('activeFleetBadge');

            if (view === 'decommissioned') {
                if (activeSec) activeSec.classList.add('hidden');
                if (decomSec) decomSec.classList.remove('hidden');

                if (tabActive) {
                    tabActive.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer';
                }
                if (tabDecom) {
                    tabDecom.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-sm cursor-pointer';
                }

                if (toggleBtn) {
                    toggleBtn.className = 'inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition shadow-sm cursor-pointer';
                }
                if (toggleIcon) {
                    toggleIcon.className = 'fa-solid fa-arrow-left';
                }
                if (toggleText) {
                    toggleText.textContent = 'Active Fleet';
                }
                if (decomBadge && activeFleetBadge) {
                    decomBadge.textContent = activeFleetBadge.textContent;
                    decomBadge.className = 'px-2 py-0.5 text-xs rounded-full bg-blue-200/80 dark:bg-blue-800 text-blue-900 dark:text-blue-100 font-bold';
                }
                if (legend) legend.classList.add('hidden');
                if (addTruckBtn) addTruckBtn.classList.add('hidden');

                loadDecommissionedTrucks();
            } else {
                if (activeSec) activeSec.classList.remove('hidden');
                if (decomSec) decomSec.classList.add('hidden');

                if (tabActive) {
                    tabActive.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm cursor-pointer';
                }
                if (tabDecom) {
                    tabDecom.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer';
                }

                if (toggleBtn) {
                    toggleBtn.className = 'inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition shadow-sm cursor-pointer';
                }
                if (toggleIcon) {
                    toggleIcon.className = 'fa-solid fa-box-archive';
                }
                if (toggleText) {
                    toggleText.textContent = 'Decommissioned Trucks';
                }
                if (decomBadge && decomFleetBadge) {
                    decomBadge.textContent = decomFleetBadge.textContent;
                    decomBadge.className = 'px-2 py-0.5 text-xs rounded-full bg-amber-200/80 dark:bg-amber-800 text-amber-900 dark:text-amber-100 font-bold';
                }
                if (legend) legend.classList.remove('hidden');
                if (addTruckBtn) addTruckBtn.classList.remove('hidden');
            }

            filterFleetCards();
        }

        function toggleFleetView() {
            if (currentFleetView === 'active') {
                switchFleetView('decommissioned');
            } else {
                switchFleetView('active');
            }
        }

        let isFetchingDecom = false;

        function loadDecommissionedTrucks(forceSpinner = false) {
            if (isFetchingDecom) return;
            isFetchingDecom = true;

            const spinner = document.getElementById('decomRefreshSpinner');
            if (spinner) spinner.classList.add('fa-spin');

            fetch('get_decommissioned_trucks.php')
                .then(res => res.json())
                .then(data => {
                    isFetchingDecom = false;
                    if (spinner) spinner.classList.remove('fa-spin');

                    if (data.success && Array.isArray(data.trucks)) {
                        renderDecommissionedCards(data.trucks);
                    }
                })
                .catch(err => {
                    isFetchingDecom = false;
                    if (spinner) spinner.classList.remove('fa-spin');
                });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderDecommissionedCards(trucks) {
            const grid = document.getElementById('decommissionedCardsGrid');
            const emptyMsg = document.getElementById('noDecommissionedMsg');
            const decomBadgeCount = document.getElementById('decomBadgeCount');
            const decomFleetBadge = document.getElementById('decomFleetBadge');
            const decomSectionCount = document.getElementById('decomSectionCount');

            const count = trucks.length;
            if (currentFleetView !== 'decommissioned' && decomBadgeCount) {
                decomBadgeCount.textContent = count;
            }
            if (decomFleetBadge) decomFleetBadge.textContent = count;
            if (decomSectionCount) decomSectionCount.textContent = `${count} ${count === 1 ? 'truck' : 'trucks'}`;

            if (!grid) return;

            if (count === 0) {
                grid.innerHTML = '';
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                return;
            }

            if (emptyMsg) emptyMsg.classList.add('hidden');

            grid.innerHTML = trucks.map(truck => {
                const truckCodeEsc = escapeHtml(truck.truck_code);
                const rfidEsc = escapeHtml(truck.rfid_tag || 'Unassigned');
                const locEsc = escapeHtml(truck.current_location || 'San Leonardo (Garage)');
                const dispatchesCount = parseInt(truck.total_dispatches || 0, 10);
                const jsTruckCode = escapeHtml(truck.truck_code).replace(/'/g, "\\'");
                const jsRfid = escapeHtml(truck.rfid_tag || '').replace(/'/g, "\\'");
                const searchMeta = `${truckCodeEsc} ${rfidEsc} decommissioned ${locEsc}`.toLowerCase();
                const lastDisp = truck.last_dispatch_date ? new Date(truck.last_dispatch_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }) : 'No recorded trips';

                return `
                    <div class="decom-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="${searchMeta}">
                        <div class="flex justify-between items-start mb-4 gap-2">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-11 h-11 bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 rounded-xl flex items-center justify-center text-xl flex-shrink-0 border border-stone-200 dark:border-stone-700">
                                    <i class="fa-solid fa-truck-pickup"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg truncate">${truckCodeEsc}</h3>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                        <span>Decommissioned Truck</span>
                                    </p>
                                </div>
                            </div>
                            <span class="bg-stone-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap shadow-sm">
                                Decommissioned
                            </span>
                        </div>

                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-wifi w-4 text-center text-amber-500"></i>
                                <span>RFID Tag:
                                    <strong class="text-gray-900 dark:text-gray-100">${rfidEsc}</strong>
                                    <span class="text-[10px] text-gray-400 font-medium ml-1">(Deactivated)</span>
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-box-archive w-4 text-center text-blue-500"></i>
                                <span>Historical Dispatches:
                                    <strong class="text-gray-900 dark:text-gray-100">${dispatchesCount} trip${dispatchesCount === 1 ? '' : 's'} completed</strong>
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-location-dot w-4 text-center text-rose-500"></i>
                                <span>Last Location:
                                    <strong class="text-gray-900 dark:text-gray-100">${locEsc}</strong>
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-clock-rotate-left w-4 text-center text-amber-500"></i>
                                <span>Last Dispatched:
                                    <strong class="text-gray-900 dark:text-gray-100">${lastDisp}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="bg-amber-50/60 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3 mb-5 mt-auto text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                            <span>Vehicle is out of service. To return this truck to operations and allow dispatches, click below.</span>
                        </div>

                        <button type="button" onclick="openRecommissionTruckModal(${truck.id}, '${jsTruckCode}', '${jsRfid}')" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl py-2.5 px-4 text-sm font-bold transition flex items-center justify-center space-x-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Commission Again</span>
                        </button>
                    </div>
                `;
            }).join('');

            filterFleetCards();
        }

        function filterFleetCards() {
            const input = document.getElementById('fleetSearchInput');
            const clearBtn = document.getElementById('fleetSearchClear');
            const query = input ? input.value.toLowerCase().trim() : '';

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', query.length === 0);
            }

            if (currentFleetView === 'active') {
                const cards = document.querySelectorAll('#fleetCardsGrid .fleet-card');
                const noResults = document.getElementById('noFleetSearchResults');
                const noResultsText = document.getElementById('noFleetSearchText');

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
                        if (noResultsText) noResultsText.textContent = `No active fleet trucks match "${query}".`;
                    } else {
                        noResults.classList.add('hidden');
                    }
                }
            } else {
                const cards = document.querySelectorAll('#decommissionedCardsGrid .decom-card');
                const noResults = document.getElementById('noDecomSearchResults');
                const noResultsText = document.getElementById('noDecomSearchText');

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
                        if (noResultsText) noResultsText.textContent = `No decommissioned trucks match "${query}".`;
                    } else {
                        noResults.classList.add('hidden');
                    }
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
                if (!fleetView || fleetView.classList.contains('hidden') || currentFleetView !== 'active') return;

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

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('view') === 'decommissioned') {
                switchFleetView('decommissioned');
            }
        });
    </script>
</div>
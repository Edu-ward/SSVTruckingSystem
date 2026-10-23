<?php
$activeDrivers     = array_values(array_filter($allDrivers ?? [], fn($d) => ($d['status'] ?? '') !== 'Resigned'));
$resignedDriverList = array_values(array_filter($allDrivers ?? [], fn($d) => ($d['status'] ?? '') === 'Resigned'));
?>
<div id="view-drivers" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Driver Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage driver profiles, licenses, truck assignments, and performance records</p>
            </div>
        </div>

        <!-- asdsadasdasd -->

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">

            <div class="relative flex-1 sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text" id="driverSearchInput" placeholder="Search drivers, CDL, trucks, phone..." oninput="filterDriverCards()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="driverSearchClear" onclick="clearDriverSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <button id="driverAddBtn" onclick="toggleModal('addDriverModal', true)" class="btn-primary text-sm w-full sm:w-auto">
                <i class="fa-solid fa-user-plus"></i><span>Add Driver</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-blue-200/70 dark:border-blue-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Drivers</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $driverStats['total_drivers'] ?? 0; ?></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Registered personnel</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-emerald-200/70 dark:border-emerald-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">On Duty</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= $driverStats['on_duty'] ?? 0; ?></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Active &amp; available</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-amber-200/70 dark:border-amber-900/40 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Avg Rating</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1"><?= number_format($driverStats['avg_rating'] ?? 5.0, 1); ?></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 inline-block">Performance score</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-yellow-500"></div>
        </div>
    </div>

    <!-- View Pills Sub-navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-2xl border border-gray-200/60 dark:border-gray-700/60">
            <button type="button" onclick="switchDriverView('active')" id="driverTabActive" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm cursor-pointer">
                <i class="fa-solid fa-users"></i>
                <span>Active Drivers</span>
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-bold" id="activeDriversBadge"><?= count($activeDrivers); ?></span>
            </button>
            <button type="button" onclick="switchDriverView('resigned')" id="driverTabResigned" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">
                <i class="fa-solid fa-user-xmark text-amber-500"></i>
                <span>Resigned Drivers</span>
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 font-bold" id="resignedDriversBadge"><?= count($resignedDriverList); ?></span>
            </button>
        </div>
    </div>

    <style>
        .driver-card,
        .driver-card * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        .driver-card::-webkit-scrollbar,
        .driver-card *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>

    <!-- Active Drivers Section -->
    <div id="driverActiveSection">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="driverCardsGrid">
            <?php foreach ($activeDrivers as $driver):
                $badgeClass = 'bg-gray-500';
                $statusTitle = 'Off Duty — awaits first dispatch of the day';
                if ($driver['status'] == 'Active') {
                    $badgeClass = 'bg-emerald-500';
                    $statusTitle = 'Active for today (dispatched today until 11:59 PM)';
                }
                if ($driver['status'] == 'Dispatched' || $driver['status'] == 'In Transit') {
                    $badgeClass = 'bg-blue-600';
                    $statusTitle = 'Currently In Transit';
                }

                $driverEditData = [
                    'id'         => $driver['id'],
                    'name'       => $driver['name'],
                    'first_name' => $driver['first_name'] ?? '',
                    'last_name'  => $driver['last_name'] ?? '',
                    'cdl_number' => $driver['cdl_number'] ?? '',
                    'phone'      => $driver['phone'] ?? '',
                    'username'   => $driver['username'] ?? '',
                    'status'     => $driver['status'] ?? 'Off Duty',
                    'truck_id'   => $driver['truck_id'] ?? ''
                ];
                $driverEditJson = htmlspecialchars(json_encode($driverEditData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                $driverJson = htmlspecialchars(json_encode($driver, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');

                $dPhotoPath = $driver['profile_photo'] ?? null;
                $dPhotoFull = $dPhotoPath ? (dirname(__DIR__, 2) . '/' . $dPhotoPath) : null;
                $dPhotoUrl  = ($dPhotoFull && file_exists($dPhotoFull))
                    ? '../' . htmlspecialchars($dPhotoPath) . '?v=' . filemtime($dPhotoFull)
                    : null;

                $hasPayable = ($driver['net_earnings'] ?? 0) > 0;
                $searchMeta = htmlspecialchars(strtolower(($driver['name'] ?? '') . ' ' . ($driver['cdl_number'] ?? '') . ' ' . ($driver['truck_code'] ?? '') . ' ' . ($driver['phone'] ?? '') . ' ' . ($driver['status'] ?? '')));
            ?>
                <div class="driver-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-all duration-200 overflow-hidden" data-search="<?= $searchMeta; ?>">
                    <div>

                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3 min-w-0 pr-2">
                                <?php if ($dPhotoUrl): ?>
                                    <img src="<?= $dPhotoUrl ?>" alt="<?= htmlspecialchars($driver['name']) ?>"
                                        onclick="expandDriverPhoto('<?= $dPhotoUrl ?>', '<?= htmlspecialchars(addslashes($driver['name'])) ?>', 'CDL: <?= htmlspecialchars(addslashes($driver['cdl_number'] ?? 'N/A')) ?>')"
                                        class="w-12 h-12 rounded-xl object-cover shadow-md flex-shrink-0 border-2 border-white dark:border-gray-700 ring-2 ring-blue-500/20 cursor-pointer hover:scale-105 hover:ring-blue-500 transition-all duration-200"
                                        title="Click to expand photo">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-md shadow-blue-500/20 flex-shrink-0">
                                        <?= getInitials($driver['name']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base truncate" title="<?= htmlspecialchars($driver['name']); ?>"><?= htmlspecialchars($driver['name']); ?></h3>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">CDL: <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1.5 flex-shrink-0">
                                <span class="<?= $badgeClass; ?> text-white text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm flex-shrink-0" title="<?= htmlspecialchars($statusTitle); ?>">
                                    <?= htmlspecialchars($driver['status']); ?>
                                </span>
                                <button type="button" onclick='openEditDriverModal(<?= $driverEditJson; ?>)' class="text-gray-400 hover:text-blue-600 transition p-1" title="Edit Driver Profile">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300 mb-3">
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                                <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-truck text-blue-500 w-4 text-center"></i>
                                    <span>Assigned Truck</span>
                                </span>
                                <span class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['truck_code'] ?? 'Unassigned'); ?></span>
                            </div>
                            <?php if (!empty($driver['co_driver_name'])): ?>
                                <div class="flex items-center justify-between p-2.5 bg-blue-50/70 dark:bg-blue-900/20 rounded-xl border border-blue-100/60 dark:border-blue-800/40">
                                    <span class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-medium">
                                        <i class="fa-solid fa-user-group text-blue-500 w-4 text-center"></i>
                                        <span>Co-Driver</span>
                                    </span>
                                    <span class="font-bold text-blue-700 dark:text-blue-300 truncate max-w-[140px]" title="<?= htmlspecialchars($driver['co_driver_name']); ?>">
                                        <?= htmlspecialchars($driver['co_driver_name']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                                <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-phone text-indigo-500 w-4 text-center"></i>
                                    <span>Phone Number</span>
                                </span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['phone'] ?? 'N/A'); ?></span>
                            </div>

                            <button type="button"
                                onclick='openDriverPerformanceModal(<?= $driverJson; ?>)'
                                title="Click to view weekly kilometers, dispatches, and performance analytics"
                                class="w-full flex items-center justify-between p-2.5 bg-amber-50/70 hover:bg-amber-100/80 dark:bg-amber-950/20 dark:hover:bg-amber-900/30 border border-amber-200/50 dark:border-amber-800/40 rounded-xl transition-all group cursor-pointer text-left">
                                <span class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 font-medium">
                                    <i class="fa-solid fa-chart-line text-amber-500 w-4 text-center group-hover:scale-110 transition-transform"></i>
                                    <span>Performance</span>
                                </span>
                                <span class="flex items-center space-x-1.5 font-bold text-amber-800 dark:text-amber-300 text-xs">
                                    <span><?= number_format($driver['rating'] ?? 0.0, 1); ?> / 5.0</span>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-amber-500/70 group-hover:translate-x-0.5 transition-transform"></i>
                                </span>
                            </button>

                            <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-gray-900 rounded-xl">
                                <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-route text-cyan-500 w-4 text-center"></i>
                                    <span>Completed Trips</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="font-bold text-gray-900 dark:text-gray-100"><?= $driver['total_deliveries'] ?? 0; ?> trips</span>
                                    <?php if (($driver['total_lifetime_km'] ?? 0) > 0): ?>
                                        <span class="text-[11px] font-semibold text-cyan-700 dark:text-cyan-300 bg-cyan-100 dark:bg-cyan-900/40 px-1.5 py-0.5 rounded"><?= number_format($driver['total_lifetime_km'], 1); ?> km</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <?php if (!empty($driver['recent_trips'])):
                                $latestTrip = $driver['recent_trips'][0];
                            ?>
                                <div class="p-2.5 bg-slate-50 dark:bg-gray-900/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="flex items-center space-x-1.5 font-medium">
                                            <i class="fa-solid fa-truck-ramp-box text-blue-500 w-4 text-center"></i>
                                            <span>Recent Delivery</span>
                                        </span>
                                        <span class="text-[10px] font-semibold text-gray-400"><?= htmlspecialchars($latestTrip['trip_date'] ?? 'Recent'); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs gap-2">
                                        <span class="font-bold text-gray-800 dark:text-gray-200 truncate"><?= htmlspecialchars($latestTrip['destination']); ?></span>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <?php if (!empty($latestTrip['duration'])): ?>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/50 px-2 py-0.5 rounded-md" title="Delivery Duration">
                                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                                    <span><?= htmlspecialchars($latestTrip['duration']); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-2.5 bg-gray-50/60 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-[11px] text-gray-400">
                                    No completed deliveries recorded yet
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-700/60 mb-3">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Quick Actions</span>
                            <div class="flex items-center space-x-1">
                                <button onclick="openPrintDriverTripsModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors"
                                    title="Print Trip Ticket">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </button>
                                <button type="button" onclick='openEditDriverModal(<?= $driverEditJson; ?>)'
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors"
                                    title="Edit Driver Profile &amp; Status">
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
                                <button onclick="openResignDriverModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-gray-700 transition-colors"
                                    title="Resign Driver">
                                    <i class="fa-solid fa-user-xmark text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 pt-2">
                        <button onclick='openViewDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition-all">View Details</button>
                        <button onclick='openContactDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-xl transition-all">Contact</button>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($activeDrivers)): ?>
                <div class="col-span-full py-16 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No active drivers</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">All drivers may be resigned or none registered yet.</p>
                    <button type="button" onclick="toggleModal('addDriverModal', true)" class="mt-4 btn-primary text-xs">
                        <i class="fa-solid fa-user-plus mr-1"></i> Register New Driver
                    </button>
                </div>
            <?php endif; ?>

            <div id="noDriverSearchResults" class="hidden col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-lg">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No drivers found</h4>
                <p class="text-xs text-gray-400 mt-1" id="noDriverSearchText">No driver profiles match your search filter.</p>
                <button type="button" onclick="clearDriverSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer">
                    Clear search filter
                </button>
            </div>
        </div>
    </div>

    <!-- Resigned Drivers Section -->
    <div id="driverResignedSection" class="hidden">
        <!-- Archive Information Banner -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/20 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-4 sm:p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                        <span>Resigned Drivers Archive</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-200/70 dark:bg-amber-800/60 text-amber-800 dark:text-amber-200" id="resignedSectionCount">
                            <?= count($resignedDriverList); ?> <?= count($resignedDriverList) === 1 ? 'driver' : 'drivers'; ?>
                        </span>
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        These drivers are no longer active. Past trip logs, payroll records, and delivery history remain permanently intact. Click <strong>Reinstate</strong> on any driver to restore them to active status.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="loadResignedDrivers(true)" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate text-amber-600 dark:text-amber-400" id="resignedRefreshSpinner"></i>
                    <span>Reload</span>
                </button>
                <button type="button" onclick="switchDriverView('active')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Active Drivers</span>
                </button>
            </div>
        </div>

        <!-- Resigned Drivers Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="resignedDriversGrid">
            <?php foreach ($resignedDriverList as $driver):
                $dPhotoPath = $driver['profile_photo'] ?? null;
                $dPhotoFull = $dPhotoPath ? (dirname(__DIR__, 2) . '/' . $dPhotoPath) : null;
                $dPhotoUrl  = ($dPhotoFull && file_exists($dPhotoFull))
                    ? '../' . htmlspecialchars($dPhotoPath) . '?v=' . filemtime($dPhotoFull)
                    : null;
                $resignedMeta = htmlspecialchars(strtolower(($driver['name'] ?? '') . ' ' . ($driver['cdl_number'] ?? '') . ' ' . ($driver['phone'] ?? '') . ' resigned'));
            ?>
                <div class="resigned-driver-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="<?= $resignedMeta; ?>">
                    <div class="flex justify-between items-start mb-4 gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <?php if ($dPhotoUrl): ?>
                                <img src="<?= $dPhotoUrl ?>" alt="<?= htmlspecialchars($driver['name']) ?>"
                                    class="w-12 h-12 rounded-xl object-cover shadow-md flex-shrink-0 border-2 border-stone-200 dark:border-stone-700 opacity-70">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-stone-200 dark:bg-stone-700 text-stone-500 dark:text-stone-400 rounded-xl flex items-center justify-center font-bold text-base shadow-sm flex-shrink-0 border border-stone-300 dark:border-stone-600">
                                    <?= getInitials($driver['name']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base truncate"><?= htmlspecialchars($driver['name']); ?></h3>
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-user-xmark text-[10px]"></i>
                                    <span>Resigned Driver</span>
                                </p>
                            </div>
                        </div>
                        <span class="bg-stone-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap shadow-sm">
                            Resigned
                        </span>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-id-card w-4 text-center text-amber-500"></i>
                            <span>CDL:
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></strong>
                                <span class="text-[10px] text-gray-400 font-medium ml-1">(Inactive)</span>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-phone w-4 text-center text-blue-400"></i>
                            <span>Phone:
                                <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['phone'] ?? 'N/A'); ?></strong>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-route w-4 text-center text-cyan-500"></i>
                            <span>Total Trips:
                                <strong class="text-gray-900 dark:text-gray-100"><?= intval($driver['total_deliveries'] ?? 0); ?> completed</strong>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-star w-4 text-center text-amber-500"></i>
                            <span>Rating:
                                <strong class="text-gray-900 dark:text-gray-100"><?= number_format($driver['rating'] ?? 0.0, 1); ?> / 5.0</strong>
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-clock-rotate-left w-4 text-center text-amber-500"></i>
                            <span>Last Dispatched:
                                <strong class="text-gray-900 dark:text-gray-100"><?= !empty($driver['last_dispatch_date']) ? date('M d, Y', strtotime($driver['last_dispatch_date'])) : 'No recorded trips'; ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="bg-amber-50/60 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3 mb-5 mt-auto text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                        <span>Driver is no longer active. To return this driver to operations and allow dispatching, click below.</span>
                    </div>

                    <button type="button" onclick="openReinstateDriverModal(<?= $driver['id']; ?>, '<?= htmlspecialchars(addslashes($driver['name'])); ?>')" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl py-2.5 px-4 text-sm font-bold transition flex items-center justify-center space-x-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reinstate Driver</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResignedDriversMsg" class="<?= empty($resignedDriverList) ? '' : 'hidden' ?> py-16 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">No Resigned Drivers</h4>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">All registered drivers are currently active. When a driver resigns, they will be archived here and can be reinstated at any time.</p>
        </div>

        <div id="noResignedSearchResults" class="hidden py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No resigned drivers found</h4>
            <p class="text-xs text-gray-400 mt-1" id="noResignedSearchText">No resigned drivers match your search filter.</p>
            <button type="button" onclick="clearDriverSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/40 rounded-xl hover:bg-amber-100 dark:hover:bg-amber-900/60 transition cursor-pointer">
                Clear search filter
            </button>
        </div>
    </div>

    <script>
        let currentDriverView = 'active';

        function switchDriverView(view) {
            currentDriverView = view;
            const activeSec   = document.getElementById('driverActiveSection');
            const resignedSec = document.getElementById('driverResignedSection');
            const tabActive   = document.getElementById('driverTabActive');
            const tabResigned = document.getElementById('driverTabResigned');
            const addBtn      = document.getElementById('driverAddBtn');

            if (view === 'resigned') {
                if (activeSec)   activeSec.classList.add('hidden');
                if (resignedSec) resignedSec.classList.remove('hidden');

                if (tabActive) {
                    tabActive.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer';
                }
                if (tabResigned) {
                    tabResigned.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-sm cursor-pointer';
                }
                if (addBtn) addBtn.classList.add('hidden');
                loadResignedDrivers();
            } else {
                if (activeSec)   activeSec.classList.remove('hidden');
                if (resignedSec) resignedSec.classList.add('hidden');

                if (tabActive) {
                    tabActive.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm cursor-pointer';
                }
                if (tabResigned) {
                    tabResigned.className = 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer';
                }
                if (addBtn) addBtn.classList.remove('hidden');
            }

            filterDriverCards();
        }

        let isFetchingResigned = false;

        function loadResignedDrivers(forceSpinner = false) {
            if (isFetchingResigned) return;
            isFetchingResigned = true;

            const spinner = document.getElementById('resignedRefreshSpinner');
            if (spinner) spinner.classList.add('fa-spin');

            fetch('get_resigned_drivers.php')
                .then(res => res.json())
                .then(data => {
                    isFetchingResigned = false;
                    if (spinner) spinner.classList.remove('fa-spin');
                    if (data.success && Array.isArray(data.drivers)) {
                        renderResignedDriverCards(data.drivers);
                    }
                })
                .catch(() => {
                    isFetchingResigned = false;
                    if (spinner) spinner.classList.remove('fa-spin');
                });
        }

        function escapeHtmlDriver(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getInitialsDriver(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ').filter(Boolean);
            if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            return parts[0][0].toUpperCase();
        }

        function renderResignedDriverCards(drivers) {
            const grid        = document.getElementById('resignedDriversGrid');
            const emptyMsg    = document.getElementById('noResignedDriversMsg');
            const badge       = document.getElementById('resignedDriversBadge');
            const sectionCount = document.getElementById('resignedSectionCount');
            const count       = drivers.length;

            if (badge) badge.textContent = count;
            if (sectionCount) sectionCount.textContent = `${count} ${count === 1 ? 'driver' : 'drivers'}`;

            if (!grid) return;

            if (count === 0) {
                grid.innerHTML = '';
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                return;
            }
            if (emptyMsg) emptyMsg.classList.add('hidden');

            grid.innerHTML = drivers.map(driver => {
                const nameEsc   = escapeHtmlDriver(driver.name);
                const cdlEsc    = escapeHtmlDriver(driver.cdl_number || 'N/A');
                const phoneEsc  = escapeHtmlDriver(driver.phone || 'N/A');
                const initials  = getInitialsDriver(driver.name);
                const jsName    = nameEsc.replace(/'/g, "\\'");
                const trips     = parseInt(driver.total_deliveries || 0, 10);
                const rating    = parseFloat(driver.rating || 0).toFixed(1);
                const searchMeta = `${nameEsc} ${cdlEsc} ${phoneEsc} resigned`.toLowerCase();
                const lastDisp  = driver.last_dispatch_date
                    ? new Date(driver.last_dispatch_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                    : 'No recorded trips';

                return `
                    <div class="resigned-driver-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="${searchMeta}">
                        <div class="flex justify-between items-start mb-4 gap-2">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-12 h-12 bg-stone-200 dark:bg-stone-700 text-stone-500 dark:text-stone-400 rounded-xl flex items-center justify-center font-bold text-base shadow-sm flex-shrink-0 border border-stone-300 dark:border-stone-600">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base truncate">${nameEsc}</h3>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-user-xmark text-[10px]"></i>
                                        <span>Resigned Driver</span>
                                    </p>
                                </div>
                            </div>
                            <span class="bg-stone-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap shadow-sm">Resigned</span>
                        </div>

                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-id-card w-4 text-center text-amber-500"></i>
                                <span>CDL: <strong class="text-gray-900 dark:text-gray-100">${cdlEsc}</strong> <span class="text-[10px] text-gray-400 font-medium ml-1">(Inactive)</span></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-phone w-4 text-center text-blue-400"></i>
                                <span>Phone: <strong class="text-gray-900 dark:text-gray-100">${phoneEsc}</strong></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-route w-4 text-center text-cyan-500"></i>
                                <span>Total Trips: <strong class="text-gray-900 dark:text-gray-100">${trips} completed</strong></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-star w-4 text-center text-amber-500"></i>
                                <span>Rating: <strong class="text-gray-900 dark:text-gray-100">${rating} / 5.0</strong></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-clock-rotate-left w-4 text-center text-amber-500"></i>
                                <span>Last Dispatched: <strong class="text-gray-900 dark:text-gray-100">${lastDisp}</strong></span>
                            </div>
                        </div>

                        <div class="bg-amber-50/60 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3 mb-5 mt-auto text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                            <span>Driver is no longer active. To return this driver to operations and allow dispatching, click below.</span>
                        </div>

                        <button type="button" onclick="openReinstateDriverModal(${driver.id}, '${jsName}')" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl py-2.5 px-4 text-sm font-bold transition flex items-center justify-center space-x-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Reinstate Driver</span>
                        </button>
                    </div>
                `;
            }).join('');

            filterDriverCards();
        }

        function filterDriverCards() {
            const input = document.getElementById('driverSearchInput');
            const clearBtn = document.getElementById('driverSearchClear');
            const query = input ? input.value.toLowerCase().trim() : '';

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', query.length === 0);
            }

            if (currentDriverView === 'active') {
                const cards = document.querySelectorAll('#driverCardsGrid .driver-card');
                const noResults = document.getElementById('noDriverSearchResults');
                const noResultsText = document.getElementById('noDriverSearchText');

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
                        if (noResultsText) noResultsText.textContent = `No driver profiles match "${query}".`;
                    } else {
                        noResults.classList.add('hidden');
                    }
                }
            } else {
                const cards = document.querySelectorAll('#resignedDriversGrid .resigned-driver-card');
                const noResults = document.getElementById('noResignedSearchResults');
                const noResultsText = document.getElementById('noResignedSearchText');

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
                        if (noResultsText) noResultsText.textContent = `No resigned drivers match "${query}".`;
                    } else {
                        noResults.classList.add('hidden');
                    }
                }
            }
        }

        function clearDriverSearch() {
            const input = document.getElementById('driverSearchInput');
            if (input) {
                input.value = '';
                filterDriverCards();
                input.focus();
            }
        }
    </script>

</div>
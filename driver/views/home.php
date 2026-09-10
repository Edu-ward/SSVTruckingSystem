<?php
$driverFullName  = trim(($driverProfile['first_name'] ?? '') . ' ' . ($driverProfile['last_name'] ?? ''));
$driverUsername  = $driverProfile['username'] ?? '';
$driverPhotoPath = $driverProfile['profile_photo'] ?? null;
$driverPhotoFull = $driverPhotoPath ? (dirname(__DIR__, 2) . '/' . $driverPhotoPath) : null;
$driverPhotoUrl  = ($driverPhotoFull && file_exists($driverPhotoFull))
    ? '../' . htmlspecialchars($driverPhotoPath) . '?v=' . filemtime($driverPhotoFull)
    : null;

$initials = 'DR';
if (!empty($driverFullName)) {
    $parts = explode(' ', $driverFullName);
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>

<!-- =========================================================
     TAB 1: DASHBOARD OVERVIEW
     ========================================================= -->
<div id="view-dashboard" class="tab-content space-y-6">

    <!-- Welcome Greeting & Quick Bar -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-2xl p-5 sm:p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <i class="fa-solid fa-truck-moving text-9xl"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/15 text-blue-100 border border-white/20">
                    <i class="fa-solid fa-id-badge mr-1 text-[10px]"></i> Driver Portal
                </span>
                <?php if ($active_dispatch): ?>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <?= htmlspecialchars($active_dispatch['status']); ?>
                    </span>
                <?php else: ?>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span> Idle at Garage
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">
                Welcome back, <?= htmlspecialchars($driverFullName ?: $driverUsername); ?>!
            </h1>
            <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-xl">
                <?php if ($active_dispatch): ?>
                    Active dispatch ticket <strong><?= htmlspecialchars($active_dispatch['ticket_number']); ?></strong> en route to <strong><?= htmlspecialchars($active_dispatch['destination']); ?></strong>.
                <?php else: ?>
                    Stationed at SSV Quarry Garage. You are ready for your next trip assignment.
                <?php endif; ?>
            </p>
        </div>

        <div class="flex items-center gap-2 relative z-10 flex-wrap">
            <button type="button" onclick="switchTab('route')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-blue-700 hover:bg-blue-50 active:scale-95 shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-map-location-dot text-blue-600"></i>
                <span>Live Route</span>
            </button>
            <button type="button" onclick="switchTab('trips')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 active:scale-95 transition flex items-center gap-1.5">
                <i class="fa-solid fa-route text-cyan-300"></i>
                <span>Trips</span>
            </button>
            <button type="button" onclick="switchTab('cash_advance')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-orange-500/80 hover:bg-orange-500 text-white border border-orange-400/40 active:scale-95 transition flex items-center gap-1.5">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>Advance</span>
            </button>
        </div>
    </div>

    <!-- Active Dispatch or Idle Status Card -->
    <div>
        <?php if ($active_dispatch): ?>
            <?php 
                $status = $active_dispatch['status'];
                $statusColor = 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
                $statusIcon = 'fa-circle-info';
                $statusDesc = 'Your dispatch is pending.';

                if ($status === 'Loading') {
                    $statusColor = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300';
                    $statusIcon = 'fa-spinner fa-spin';
                    $statusDesc = 'Your truck is currently loading gravel at the quarry site.';
                } elseif ($status === 'In Transit') {
                    $statusColor = 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
                    $statusIcon = 'fa-truck-fast animate-bounce';
                    $statusDesc = 'You are on the road. Live GPS location is broadcasting to dispatch.';
                } elseif ($status === 'Unloading') {
                    $statusColor = 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300';
                    $statusIcon = 'fa-dumpster';
                    $statusDesc = 'You have arrived at the destination. Unloading cargo.';
                } elseif ($status === 'Cancellation Requested') {
                    $statusColor = 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 animate-pulse';
                    $statusIcon = 'fa-triangle-exclamation';
                    $statusDesc = 'Trip cancellation requested. Awaiting Admin confirmation.';
                }
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-blue-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 sm:px-6 py-4 text-white flex justify-between items-center flex-wrap gap-2">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-route text-2xl opacity-90"></i>
                        <div>
                            <h3 class="font-bold text-base sm:text-lg">Active Trip Dispatch</h3>
                            <p class="text-blue-100 text-xs font-mono">Ticket: <?= htmlspecialchars($active_dispatch['ticket_number']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white text-blue-700 shadow-sm flex items-center gap-1.5 uppercase">
                            <i class="fa-solid <?= $statusIcon; ?>"></i> <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Destination</span>
                            <span class="text-sm sm:text-base font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 truncate">
                                <i class="fa-solid fa-location-dot text-red-500 flex-shrink-0"></i>
                                <span class="truncate"><?= htmlspecialchars($active_dispatch['destination']); ?></span>
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Distance</span>
                            <span class="text-sm sm:text-base font-extrabold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-route text-blue-500 flex-shrink-0"></i>
                                <?= number_format($active_dispatch['distance_km'] ?? 0, 1); ?> km
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Trip Pay</span>
                            <span class="text-sm sm:text-base font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-peso-sign text-emerald-500 flex-shrink-0"></i>
                                ₱<?= number_format($active_dispatch['pay_amount'] ?? 0, 2); ?>
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Assigned Truck</span>
                            <span class="text-sm sm:text-base font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-truck text-blue-500 flex-shrink-0"></i>
                                <?= htmlspecialchars($active_dispatch['truck_code']); ?>
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Load Volume</span>
                            <span class="text-sm sm:text-base font-extrabold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-cube text-indigo-500 flex-shrink-0"></i>
                                <?= number_format($active_dispatch['cubic_meters'] ?? 0, 2); ?> cu.m
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Dispatch Time</span>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1 mt-1 truncate">
                                <i class="fa-solid fa-clock text-blue-500 flex-shrink-0"></i>
                                <?= !empty($active_dispatch['transit_start_time']) ? date('M d, h:i A', strtotime($active_dispatch['transit_start_time'])) : (!empty($active_dispatch['created_at']) ? date('M d, h:i A', strtotime($active_dispatch['created_at'])) : 'Pending') ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl <?= $statusColor; ?> mb-5">
                        <i class="fa-solid fa-info-circle text-lg mt-0.5"></i>
                        <div>
                            <span class="font-bold text-sm block">Current State</span>
                            <p class="text-xs mt-1 leading-relaxed opacity-90"><?= $statusDesc; ?></p>
                        </div>
                    </div>

                    <!-- Dispatch Actions -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" onclick="switchTab('route')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 active:scale-95 transition">
                            <i class="fa-solid fa-location-arrow"></i> Open Full Route & GPS Map
                        </button>

                        <?php if ($status !== 'Cancellation Requested' && $status !== 'Delivered'): ?>
                            <button type="button" onclick="openCancelTripModal()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-orange-700 dark:text-orange-400 bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800 transition">
                                <i class="fa-solid fa-ban"></i> Request Cancellation (Breakdown)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4 text-center sm:text-left">
                    <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-2xl flex items-center justify-center text-2xl mx-auto sm:mx-0 shadow-inner">
                        <i class="fa-solid fa-house-chimney-user"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 flex items-center gap-2 justify-center sm:justify-start">
                            Status: Idle (At Garage)
                            <span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block animate-pulse"></span>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">You are currently active, registered, and waiting for a new trip assignment.</p>
                    </div>
                </div>
                <div class="text-center sm:text-right w-full sm:w-auto">
                    <span class="text-xs bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-4 py-2 rounded-xl font-bold uppercase inline-block border border-gray-200 dark:border-gray-700">
                        Waiting for Admin Dispatch
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 3 Delivery KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
            <div class="relative z-10">
                <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Trips This Week</p>
                <h3 class="text-3xl font-extrabold tracking-tight"><?= number_format($weekly_trips); ?> <span class="text-lg font-medium text-blue-100">trips</span></h3>
                <p class="text-blue-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                    <i class="fa-solid fa-calendar-week"></i> Current week deliveries (Mon–Sun)
                </p>
            </div>
            <i class="fa-solid fa-truck-ramp-box absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
            <div class="relative z-10">
                <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Trips This Month</p>
                <h3 class="text-3xl font-extrabold tracking-tight"><?= number_format($monthly_trips); ?> <span class="text-lg font-medium text-emerald-100">trips</span></h3>
                <p class="text-emerald-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                    <i class="fa-regular fa-calendar-check"></i> Delivered in <?= date('F Y'); ?>
                </p>
            </div>
            <i class="fa-solid fa-clipboard-check absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
        </div>

        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
            <div class="relative z-10">
                <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Total Completed Trips</p>
                <h3 class="text-3xl font-extrabold tracking-tight"><?= number_format($total_completed_trips); ?> <span class="text-lg font-medium text-indigo-100">trips</span></h3>
                <p class="text-indigo-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                    <i class="fa-solid fa-flag-checkered"></i> Lifetime completed dispatches
                </p>
            </div>
            <i class="fa-solid fa-route absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
        </div>
    </div>
</div>


<!-- =========================================================
     TAB 2: LIVE TRIP ROUTE & NAVIGATION
     ========================================================= -->
<div id="view-route" class="tab-content hidden space-y-6">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all relative z-0">
        <!-- Route Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 p-5 sm:p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-blue-300 text-2xl flex-shrink-0 shadow-inner">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-bold text-lg sm:text-xl text-white">Live Route & Turn-by-Turn Navigation</h2>
                        <?php if ($active_dispatch && ($active_dispatch['status'] ?? '') === 'In Transit'): ?>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-green-500/20 text-green-300 border border-green-400/30">
                                <i class="fa-solid fa-satellite-dish fa-fade mr-1"></i> In Transit
                            </span>
                        <?php elseif ($active_dispatch): ?>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                                <?= htmlspecialchars($active_dispatch['status']); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                Idle (At Garage)
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs sm:text-sm text-blue-200 mt-0.5">
                        <?php if ($active_dispatch): ?>
                            Road route from SSV Quarry Garage to <strong><?= htmlspecialchars($active_dispatch['destination']); ?></strong>.
                        <?php else: ?>
                            SSV Quarry Garage base station & GPS overview. Next dispatch will auto-calculate road routes here.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Nav App Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="launchGoogleMapsNav()"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-gray-800 hover:bg-gray-100 active:scale-95 shadow transition">
                    <i class="fa-brands fa-google text-blue-600"></i> Google Maps
                </button>
                <button type="button" onclick="launchWazeNav()"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-cyan-500 hover:bg-cyan-400 active:scale-95 text-white shadow transition">
                    <i class="fa-brands fa-waze"></i> Waze
                </button>
            </div>
        </div>

        <!-- Metric Bars -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 sm:p-5 bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700 text-xs sm:text-sm">
            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                <div class="text-gray-400 dark:text-gray-500 text-[11px] font-semibold uppercase flex items-center gap-1">
                    <i class="fa-solid fa-warehouse text-indigo-500"></i> Origin
                </div>
                <div class="font-bold text-gray-800 dark:text-gray-200 mt-0.5 truncate" title="Brgy. Burgos San Leonardo, Nueva Ecija">
                    San Leonardo (Quarry)
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                <div class="text-gray-400 dark:text-gray-500 text-[11px] font-semibold uppercase flex items-center gap-1">
                    <i class="fa-solid fa-location-dot text-red-500"></i> Destination
                </div>
                <div class="font-bold text-gray-800 dark:text-gray-200 mt-0.5 truncate" id="driverRouteDestDisplay">
                    <?= htmlspecialchars($active_dispatch['destination'] ?? 'San Leonardo Garage'); ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                <div class="text-gray-400 dark:text-gray-500 text-[11px] font-semibold uppercase flex items-center gap-1">
                    <i class="fa-solid fa-route text-blue-500"></i> Est. Distance
                </div>
                <div class="font-bold text-blue-600 dark:text-blue-400 mt-0.5 flex items-center gap-1">
                    <span id="routeDistanceText" class="text-base sm:text-lg">Calculating...</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                <div class="text-gray-400 dark:text-gray-500 text-[11px] font-semibold uppercase flex items-center gap-1">
                    <i class="fa-solid fa-clock text-emerald-500"></i> Est. Travel Time
                </div>
                <div class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 flex items-center gap-1">
                    <span id="routeDurationText" class="text-base sm:text-lg">Calculating...</span>
                </div>
            </div>
        </div>

        <!-- Leaflet Map Box -->
        <div class="p-3 sm:p-5 relative z-0">
            <div class="relative w-full h-[400px] sm:h-[550px] rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-inner z-0">
                <div id="driverRouteMap" class="w-full h-full relative z-0"></div>

                <!-- Floating Controls -->
                <div class="absolute bottom-4 right-4 z-10 flex flex-col gap-2">
                    <button type="button" onclick="fitDriverRouteBounds()" title="Fit full route in view"
                            class="w-11 h-11 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-xl shadow-lg hover:bg-blue-50 dark:hover:bg-gray-700 flex items-center justify-center transition active:scale-90">
                        <i class="fa-solid fa-maximize text-base text-blue-600 dark:text-blue-400"></i>
                    </button>
                    <button type="button" onclick="centerOnDriverLiveLocation()" title="Snap to my current GPS location"
                            class="w-11 h-11 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-xl shadow-lg hover:bg-blue-50 dark:hover:bg-gray-700 flex items-center justify-center transition active:scale-90">
                        <i class="fa-solid fa-crosshairs text-base text-emerald-600 dark:text-emerald-400"></i>
                    </button>
                </div>

                <!-- Status Pill -->
                <div class="absolute top-4 left-4 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 shadow-md flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="driverMapStatusText">Live GPS Route Active</span>
                </div>
            </div>
        </div>

        <?php if ($active_dispatch && ($active_dispatch['status'] ?? '') === 'In Transit'): ?>
            <div class="p-4 bg-green-50 dark:bg-green-950/20 border-t border-green-200/60 dark:border-green-900/30 flex items-center gap-3 text-xs text-green-800 dark:text-green-300">
                <span class="flex h-3 w-3 relative flex-shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <div>
                    <strong>Continuous GPS Synchronization:</strong> Your truck's position is transmitting live to dispatch and monitoring stations. Keep this device powered and connected.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>


<!-- =========================================================
     TAB 3: TRIPS (WEEKLY VIEW WITH MONDAY-SUNDAY SELECTOR)
     ========================================================= -->
<div id="view-trips" class="tab-content hidden space-y-6">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
        
        <!-- Header & Week Selector -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-5 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-route text-blue-600"></i> Trips
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Showing dispatches for selected weekly delivery cycle (Monday to Sunday)
                </p>
            </div>

            <!-- Selector Controls -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Dropdown selector -->
                <div class="relative min-w-[240px] sm:min-w-[280px]">
                    <select id="tripWeekSelector" onchange="onWeekSelectorChange(this.value)"
                            class="w-full text-xs font-semibold py-2 px-3 pr-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                        <?php foreach ($selectableWeeks as $sw): 
                            $isSelected = ($sw['from'] === $selectedFrom && $sw['to'] === $selectedTo);
                        ?>
                            <option value="<?= $sw['from'] . '|' . $sw['to']; ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($sw['label']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="ALL">Show All Past Trips</option>
                        <option value="CUSTOM">Custom Date Range...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-gray-400 text-xs">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Previous / Next Week Quick Buttons -->
                <div class="flex items-center gap-1">
                    <button type="button" onclick="shiftTripWeek(-1)" title="Previous Week (Mon–Sun)"
                            class="w-8 h-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-xs active:scale-95 transition">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" onclick="shiftTripWeek(0)" title="Current Week (This Week)"
                            class="px-2.5 h-8 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 text-xs font-bold active:scale-95 transition">
                        This Week
                    </button>
                    <button type="button" onclick="shiftTripWeek(1)" title="Next Week (Mon–Sun)"
                            class="w-8 h-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-xs active:scale-95 transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Bar (Collapsible or visible) -->
        <div id="customDateRangeBar" class="p-3.5 mb-5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-days text-blue-500"></i> Date Range:
                </span>
                <div class="flex items-center gap-1.5">
                    <input type="date" id="tripDateFrom" value="<?= htmlspecialchars($selectedFrom); ?>"
                           class="px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs">
                    <span class="text-gray-400 font-bold">to</span>
                    <input type="date" id="tripDateTo" value="<?= htmlspecialchars($selectedTo); ?>"
                           class="px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs">
                </div>
                <button type="button" onclick="applyCustomDateRange()"
                        class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition active:scale-95">
                    Apply Filter
                </button>
            </div>

            <!-- Live text filter within the selected period -->
            <div class="relative w-full sm:w-56">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="tripSearchInput" onkeyup="filterDriverTrips()"
                       placeholder="Filter destination, status..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <!-- Weekly Summary KPI Banner -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50/70 dark:bg-blue-950/20 p-4 rounded-xl border border-blue-100 dark:border-blue-900/30 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase text-blue-700 dark:text-blue-300">Trips In Selected Week</span>
                    <div class="text-2xl font-extrabold text-blue-800 dark:text-blue-200 mt-0.5">
                        <?= count($weeklyFilteredTrips); ?> <span class="text-xs font-medium text-blue-500">deliveries</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>

            <div class="bg-indigo-50/70 dark:bg-indigo-950/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/30 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase text-indigo-700 dark:text-indigo-300">Total Distance Travelled</span>
                    <div class="text-2xl font-extrabold text-indigo-800 dark:text-indigo-200 mt-0.5">
                        <?= number_format($weeklyDistanceKm, 1); ?> <span class="text-xs font-medium text-indigo-500">km</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>

            <div class="bg-emerald-50/70 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/30 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase text-emerald-700 dark:text-emerald-300">Computed Trip Earnings</span>
                    <div class="text-2xl font-extrabold text-emerald-800 dark:text-emerald-200 mt-0.5">
                        ₱<?= number_format($weeklyPayAmount, 2); ?>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-peso-sign"></i>
                </div>
            </div>
        </div>

        <!-- Mobile View (Cards) -->
        <div class="block sm:hidden space-y-3" id="driverTripsMobileList">
            <?php if (count($weeklyFilteredTrips) > 0): ?>
                <?php foreach ($weeklyFilteredTrips as $trip): ?>
                    <?php
                        $duration = 'N/A';
                        if (!empty($trip['transit_start_time']) && !empty($trip['transit_end_time'])) {
                            $start = new DateTime($trip['transit_start_time']);
                            $end = new DateTime($trip['transit_end_time']);
                            $diff = $start->diff($end);
                            $duration = '';
                            if ($diff->h > 0) $duration .= $diff->h . 'h ';
                            $duration .= $diff->i . 'm';
                        }
                        $dispTimeStr = !empty($trip['transit_start_time']) ? date('M d, Y h:i A', strtotime($trip['transit_start_time'])) : (!empty($trip['created_at']) ? date('M d, Y h:i A', strtotime($trip['created_at'])) : date('M d, Y', strtotime($trip['trip_date'])));
                        $arrTimeStr = !empty($trip['transit_end_time']) ? date('M d, Y h:i A', strtotime($trip['transit_end_time'])) : ($trip['status'] === 'Delivered' ? 'Delivered' : 'N/A');
                        $searchMeta = strtolower(($trip['destination'] ?? '') . ' ' . ($trip['status'] ?? ''));
                    ?>
                    <div class="driver-trip-card bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 shadow-sm space-y-2.5"
                         data-search="<?= htmlspecialchars($searchMeta); ?>">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400 dark:text-gray-500 font-mono">Dispatch: <?= $dispTimeStr; ?></span>
                            <span class="text-gray-400 dark:text-gray-500 font-mono">Duration: <?= $duration; ?></span>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                            Arrival: <?= $arrTimeStr; ?>
                        </div>
                        <div class="flex justify-between items-end pt-1">
                            <div>
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 block uppercase font-semibold">Destination</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm"><?= htmlspecialchars($trip['destination']); ?></span>
                            </div>
                            <div class="text-right">
                                <?php 
                                $s = isset($trip['status']) ? trim($trip['status']) : '';
                                if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-150 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        <i class="fa-solid fa-check mr-1 text-[8px]"></i> Delivered
                                    </span>
                                <?php elseif ($s === 'Cancellation Requested'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-850 dark:bg-orange-950/20 dark:text-orange-400 animate-pulse">
                                        <i class="fa-solid fa-clock mr-1 text-[8px]"></i> Pending Cancel
                                    </span>
                                <?php elseif ($s === 'Cancelled'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                        <i class="fa-solid fa-ban mr-1 text-[8px]"></i> Cancelled
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                        <i class="fa-solid fa-truck-fast mr-1 text-[8px]"></i> <?= htmlspecialchars($s); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-2 border-t border-gray-200/60 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-route text-blue-500"></i> Distance:
                            </span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                <?= number_format($trip['distance_km'] ?? 0, 1); ?> km
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-peso-sign text-emerald-500"></i> Trip Pay:
                            </span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400">
                                ₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="py-12 text-center text-gray-400">
                    <i class="fa-solid fa-road text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm font-medium">No trips recorded for this selected period.</p>
                    <p class="text-xs text-gray-400 mt-1">Try selecting another week or choosing "Show All Past Trips".</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse" id="driverTripsTable">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <th class="py-3 px-3 font-semibold">Dispatch Date & Time</th>
                        <th class="py-3 px-3 font-semibold">Arrival Date & Time</th>
                        <th class="py-3 px-3 font-semibold">Destination</th>
                        <th class="py-3 px-3 font-semibold">Distance</th>
                        <th class="py-3 px-3 font-semibold">Trip Pay</th>
                        <th class="py-3 px-3 font-semibold">Duration</th>
                        <th class="py-3 px-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 dark:text-gray-200 divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <?php if (count($weeklyFilteredTrips) > 0): ?>
                        <?php foreach ($weeklyFilteredTrips as $trip): ?>
                            <?php
                                $duration = 'N/A';
                                if (!empty($trip['transit_start_time']) && !empty($trip['transit_end_time'])) {
                                    $start = new DateTime($trip['transit_start_time']);
                                    $end = new DateTime($trip['transit_end_time']);
                                    $diff = $start->diff($end);
                                    $duration = '';
                                    if ($diff->h > 0) $duration .= $diff->h . 'h ';
                                    $duration .= $diff->i . 'm';
                                }
                                $dispTimeStr = !empty($trip['transit_start_time']) ? date('M d, Y h:i A', strtotime($trip['transit_start_time'])) : (!empty($trip['created_at']) ? date('M d, Y h:i A', strtotime($trip['created_at'])) : date('M d, Y', strtotime($trip['trip_date'])));
                                $arrTimeStr = !empty($trip['transit_end_time']) ? date('M d, Y h:i A', strtotime($trip['transit_end_time'])) : ($trip['status'] === 'Delivered' ? 'Delivered' : '—');
                                $searchMeta = strtolower(($trip['destination'] ?? '') . ' ' . ($trip['status'] ?? ''));
                            ?>
                            <tr class="driver-trip-row hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors"
                                data-search="<?= htmlspecialchars($searchMeta); ?>">
                                <td class="py-3.5 px-3 font-medium text-gray-800 dark:text-gray-200 text-xs"><?= $dispTimeStr; ?></td>
                                <td class="py-3.5 px-3 font-medium text-gray-600 dark:text-gray-400 text-xs"><?= $arrTimeStr; ?></td>
                                <td class="py-3.5 px-3 font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($trip['destination']); ?></td>
                                <td class="py-3.5 px-3 font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                    <i class="fa-solid fa-route mr-1 text-xs"></i><?= number_format($trip['distance_km'] ?? 0, 1); ?> km
                                </td>
                                <td class="py-3.5 px-3 font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                    ₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?>
                                </td>
                                <td class="py-3.5 px-3 text-gray-500 dark:text-gray-400 font-mono text-xs"><?= $duration; ?></td>
                                <td class="py-3.5 px-3">
                                    <?php 
                                    $s = isset($trip['status']) ? trim($trip['status']) : '';
                                    if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                    ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                            <i class="fa-solid fa-check mr-1 text-[10px]"></i> Delivered
                                        </span>
                                    <?php elseif ($s === 'Cancellation Requested'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-850 dark:bg-orange-950/20 dark:text-orange-400 animate-pulse">
                                            <i class="fa-solid fa-clock mr-1 text-[10px]"></i> Pending Cancel
                                        </span>
                                    <?php elseif ($s === 'Cancelled'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            <i class="fa-solid fa-ban mr-1 text-[10px]"></i> Cancelled
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                            <i class="fa-solid fa-truck-fast mr-1 text-[10px]"></i> <?= htmlspecialchars($s); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-12 px-3 text-center text-gray-400">
                                <i class="fa-solid fa-road text-4xl mb-3 text-gray-300 block"></i>
                                No trips found for this week period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<!-- =========================================================
     TAB 4: CASH ADVANCE (DEDICATED MENU)
     ========================================================= -->
<div id="view-cash_advance" class="tab-content hidden space-y-6">

    <!-- Header Banner with Request Advance Button -->
    <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-slate-900 rounded-2xl p-5 sm:p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-200 text-2xl flex-shrink-0 shadow-inner">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h2 class="font-bold text-lg sm:text-xl text-white">Cash Advance Requests & History</h2>
                <p class="text-xs sm:text-sm text-amber-100 mt-0.5">
                    Submit advance requests anytime. Approved amounts are auto-deducted upon payroll release.
                </p>
            </div>
        </div>

        <div>
            <button type="button" onclick="openCashAdvanceModal()"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-white text-orange-700 hover:bg-orange-50 active:scale-95 shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-orange-600 text-base"></i>
                <span>Request Cash Advance</span>
            </button>
        </div>
    </div>

    <!-- 3 Cash Advance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Pending Requests</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400"><?= $caPendingCount; ?></div>
            <p class="text-[11px] text-gray-400 mt-1">Awaiting admin review</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Approved (To Deduct)</span>
                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-orange-600 dark:text-orange-400">₱<?= number_format($totalCashAdvancesClaimed, 2); ?></div>
            <p class="text-[11px] text-gray-400 mt-1">Deducted on next payroll</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Settled Advances</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">₱<?= number_format($totalCashAdvancesSettled, 2); ?></div>
            <p class="text-[11px] text-gray-400 mt-1">Previously cleared claims</p>
        </div>
    </div>

    <!-- Request History List & Print Vouchers -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-orange-500"></i> My Advance History & Print Vouchers
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Click "Voucher" to print or download an approved advance slip</p>
            </div>
            <span class="text-xs bg-orange-50 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400 px-3 py-1 rounded-full font-bold">
                <?= count($driverCashAdvances); ?> Requests Total
            </span>
        </div>

        <?php if (!empty($driverCashAdvances)): ?>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                <?php foreach ($driverCashAdvances as $ca):
                    $caStatus = $ca['status'] ?? 'Pending';
                    $chipClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-300';
                    if ($caStatus === 'Approved') $chipClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300';
                    if ($caStatus === 'Rejected') $chipClass = 'bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-300';
                ?>
                <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm sm:text-base text-gray-900 dark:text-gray-100">
                                ₱<?= number_format($ca['amount'], 2); ?>
                            </div>
                            <?php if (!empty($ca['reason'])): ?>
                                <p class="text-xs text-gray-600 dark:text-gray-300 truncate mt-0.5"><?= htmlspecialchars($ca['reason']); ?></p>
                            <?php endif; ?>
                            <div class="text-[11px] text-gray-400 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <i class="fa-regular fa-clock text-[10px]"></i>
                                <span>Requested: <?= date('M d, Y h:i A', strtotime($ca['requested_at'])); ?></span>
                                <?php if (!empty($ca['resolved_at'])): ?>
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <span>Resolved: <?= date('M d, Y', strtotime($ca['resolved_at'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase <?= $chipClass; ?>">
                            <?= htmlspecialchars($caStatus); ?>
                        </span>

                        <?php if ($caStatus === 'Approved'): ?>
                            <button type="button" onclick="window.open('../admin/print_cash_advance.php?id=<?= $ca['id']; ?>', '_blank')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 transition shadow-sm"
                                    title="View / Print Cash Advance Voucher">
                                <i class="fa-solid fa-print"></i>
                                <span class="hidden sm:inline">Print Voucher</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-10 text-center text-gray-400">
                <i class="fa-solid fa-hand-holding-dollar text-4xl mb-2 opacity-30"></i>
                <p class="text-sm font-medium">No cash advances requested yet.</p>
                <button type="button" onclick="openCashAdvanceModal()" class="mt-3 text-xs text-orange-600 hover:underline font-bold inline-block">
                    Request Your First Cash Advance
                </button>
            </div>
        <?php endif; ?>
    </div>

</div>


<!-- =========================================================
     TAB 5: PAYROLL & COMPENSATION
     ========================================================= -->
<div id="view-payroll" class="tab-content hidden space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-700 via-teal-700 to-slate-900 rounded-2xl p-5 sm:p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-emerald-300 text-2xl flex-shrink-0 shadow-inner">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <h2 class="font-bold text-lg sm:text-xl text-white">Driver Payroll & Earnings</h2>
                <p class="text-xs sm:text-sm text-emerald-100 mt-0.5">
                    Official haul rates: ₱300 within San Leonardo base / +₱10 per km outside.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="switchTab('cash_advance')"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition active:scale-95 flex items-center gap-1.5">
                <i class="fa-solid fa-hand-holding-dollar text-amber-300"></i>
                <span>Cash Advances</span>
            </button>
        </div>
    </div>

    <!-- 4 Financial Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Gross Earnings -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Gross Trip Earnings</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">₱<?= number_format($driverGrossEarnings ?? 0, 2); ?></div>
            <p class="text-[11px] text-gray-400 mt-1">From delivered trips awaiting payout</p>
        </div>

        <!-- 2. Carried Balance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Remaining Balance</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">₱<?= number_format($driverRemainingBalance ?? 0, 2); ?></div>
            <p class="text-[11px] text-gray-400 mt-1">Carried forward from prior settlement</p>
        </div>

        <!-- 3. Approved Cash Advances -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Advance Deductions</span>
                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-orange-600 dark:text-orange-400">-₱<?= number_format($totalCashAdvancesClaimed ?? 0, 2); ?></div>
            <p class="text-[11px] text-gray-400 mt-1">Auto-deducted from gross</p>
        </div>

        <!-- 4. Net Payable -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-100 uppercase">Current Net Payable</span>
                <div class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center text-sm">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-white">₱<?= number_format($netPay ?? 0, 2); ?></div>
            <p class="text-[11px] text-emerald-100 mt-1">Available for upcoming disbursement</p>
        </div>
    </div>

    <!-- Section: Delivered Trips Contributing to Payroll -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i> Delivered Trips Earnings Breakdown
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Trips completed by you and their computed pay</p>
            </div>
            <span class="text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-full font-bold">
                <?= count($payrollTrips); ?> Delivered Trips
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <th class="py-3 px-4 font-semibold">Ticket #</th>
                        <th class="py-3 px-4 font-semibold">Destination</th>
                        <th class="py-3 px-4 font-semibold">Delivered Time</th>
                        <th class="py-3 px-4 font-semibold">Distance</th>
                        <th class="py-3 px-4 font-semibold">Load (cu.m)</th>
                        <th class="py-3 px-4 font-semibold">Trip Pay</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-200">
                    <?php if (!empty($payrollTrips)): ?>
                        <?php foreach ($payrollTrips as $pt): ?>
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-xs text-blue-600 dark:text-blue-400">
                                    <?= htmlspecialchars($pt['ticket_number']); ?>
                                </td>
                                <td class="py-3.5 px-4 font-semibold"><?= htmlspecialchars($pt['destination']); ?></td>
                                <td class="py-3.5 px-4 text-xs text-gray-500 dark:text-gray-400">
                                    <?= !empty($pt['transit_end_time']) ? date('M d, Y h:i A', strtotime($pt['transit_end_time'])) : (!empty($pt['created_at']) ? date('M d, Y', strtotime($pt['created_at'])) : '—'); ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                    <?= number_format($pt['distance_km'] ?? 0, 1); ?> km
                                </td>
                                <td class="py-3.5 px-4 text-xs"><?= number_format($pt['cubic_meters'] ?? 0, 2); ?></td>
                                <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                                    ₱<?= number_format($pt['pay_amount'] ?? 0, 2); ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <?php if (!empty($pt['is_payroll_paid'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="fa-solid fa-check-double mr-1 text-[10px]"></i> Claimed
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                                            <i class="fa-solid fa-coins mr-1 text-[10px]"></i> Unsettled (Payable)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-400">
                                No delivered trips recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Past Settlement Claims History -->
    <?php if (!empty($payrollSettlements)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-indigo-600"></i> Past Payroll Settlement Releases
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Historical payout disbursement records for your account</p>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
            <?php foreach ($payrollSettlements as $ps): ?>
            <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-extrabold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>Settlement #<?= htmlspecialchars($ps['settlement_ticket']); ?></span>
                            <span class="text-xs text-emerald-600 font-bold">₱<?= number_format($ps['amount_claimed'], 2); ?> Claimed</span>
                        </div>
                        <div class="text-[11px] text-gray-400 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                            <span>Settled on <?= date('M d, Y h:i A', strtotime($ps['settled_at'])); ?></span>
                            <span>&bull;</span>
                            <span><?= $ps['trips_count']; ?> Trips</span>
                            <?php if ($ps['remaining_balance'] > 0): ?>
                                <span>&bull;</span>
                                <span class="text-indigo-500">Bal. Carried: ₱<?= number_format($ps['remaining_balance'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                    Disbursed
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>


<!-- =========================================================
     TAB 6: NOTIFICATIONS (NEW FEATURE TAB)
     ========================================================= -->
<div id="view-notifications" class="tab-content hidden space-y-6">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-lg sm:text-xl text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        Driver Notifications & Alerts
                        <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-rose-500 text-white font-bold">
                                <?= $unreadNotificationCount; ?> New
                            </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Real-time alerts regarding trip dispatches, advance approvals, and payroll</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="markAllNotificationsAsRead()"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition active:scale-95 flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double text-blue-500"></i> Mark All as Read
                </button>
            </div>
        </div>

        <?php if (!empty($driverNotifications)): ?>
            <div class="divide-y divide-gray-100 dark:divide-gray-800" id="driverNotificationList">
                <?php foreach ($driverNotifications as $n): 
                    $timeAgo = '';
                    $diffSec = time() - $n['timestamp'];
                    if ($diffSec < 60) $timeAgo = 'Just now';
                    elseif ($diffSec < 3600) $timeAgo = floor($diffSec / 60) . ' mins ago';
                    elseif ($diffSec < 86400) $timeAgo = floor($diffSec / 3600) . ' hours ago';
                    else $timeAgo = date('M d, Y', $n['timestamp']);
                ?>
                <div class="driver-notif-item p-4 sm:px-6 flex items-start justify-between gap-4 hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition cursor-pointer"
                     onclick="switchTab('<?= htmlspecialchars($n['tab']); ?>')">
                    <div class="flex items-start space-x-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl <?= $n['color']; ?> flex items-center justify-center text-base flex-shrink-0 mt-0.5">
                            <i class="fa-solid <?= $n['icon']; ?>"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($n['title']); ?></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase">
                                    <?= htmlspecialchars($n['badge'] ?? 'Notice'); ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed"><?= htmlspecialchars($n['message']); ?></p>
                            <span class="text-[11px] text-gray-400 font-medium mt-1 inline-flex items-center gap-1">
                                <i class="fa-regular fa-clock text-[9px]"></i> <?= $timeAgo; ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex-shrink-0 text-gray-400 hover:text-blue-600 transition pt-1">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center text-gray-400">
                <i class="fa-solid fa-bell-slash text-4xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium">No notifications yet.</p>
                <p class="text-xs text-gray-400 mt-1">Dispatches and cash advance updates will be delivered here in real-time.</p>
            </div>
        <?php endif; ?>
    </div>

</div>


<!-- =========================================================
     TAB 7: PROFILE & ACCOUNT SETTINGS
     ========================================================= -->
<div id="view-profile" class="tab-content hidden space-y-6">

    <!-- Profile Hero Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700/80 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-slate-900 h-24 relative">
            <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg,transparent,transparent 8px,rgba(255,255,255,.1) 8px,rgba(255,255,255,.1) 16px)"></div>
        </div>
        <div class="px-5 sm:px-6 pb-6">
            <div class="flex items-end justify-between -mt-12 mb-4 flex-wrap gap-3">
                <div class="relative group">
                    <?php if ($driverPhotoUrl): ?>
                        <img src="<?= $driverPhotoUrl ?>" alt="Profile Photo"
                             id="driverProfilePhotoPreview"
                             class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-xl">
                    <?php else: ?>
                        <div id="driverProfilePhotoPreview"
                             class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-3xl border-4 border-white dark:border-gray-800 shadow-xl">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                    <?php endif; ?>
                    
                    <button type="button" onclick="document.getElementById('profilePhotoInput').click()"
                            title="Change profile photo"
                            class="absolute -bottom-1.5 -right-1.5 w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white dark:border-gray-800 transition-transform active:scale-90">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('profilePhotoInput').click()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 transition">
                        <i class="fa-solid fa-upload"></i> Upload New Photo
                    </button>
                </div>
            </div>

            <div>
                <h2 class="font-extrabold text-gray-900 dark:text-gray-100 text-xl leading-tight">
                    <?= htmlspecialchars($driverFullName ?: $driverUsername) ?>
                </h2>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium mt-1 flex-wrap">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-user-tag text-blue-500"></i>
                        <?= htmlspecialchars($driverUsername) ?>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-shield text-indigo-500"></i>
                        Verified SSV Driver
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal & Fleet Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-blue-600"></i> Driver Credentials
            </h3>
            <div class="space-y-3.5 text-xs sm:text-sm">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">CDL / Driver License</span>
                    <span class="font-bold font-mono text-gray-800 dark:text-gray-200"><?= htmlspecialchars($driverProfile['cdl_number'] ?? 'N/A'); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Contact Phone</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($driverProfile['phone'] ?? 'Not provided'); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Duty Status</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <?= htmlspecialchars($driverProfile['status'] ?? 'Active'); ?>
                    </span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Performance Rating</span>
                    <span class="font-bold text-amber-500 flex items-center gap-1">
                        <i class="fa-solid fa-star text-xs"></i>
                        <?= number_format($driverProfile['rating'] ?? 5.0, 1); ?> / 5.0
                    </span>
                </div>
            </div>
        </div>

        <!-- Assigned Truck Details -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-truck text-indigo-600"></i> Assigned Truck & Fleet
            </h3>
            <div class="space-y-3.5 text-xs sm:text-sm">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Assigned Truck Code</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400 font-mono text-base">
                        <?= htmlspecialchars($driverProfile['truck_code'] ?? 'None Assigned'); ?>
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Truck Status</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">
                        <?= htmlspecialchars($driverProfile['truck_status'] ?? 'Active'); ?>
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">Home Garage</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">San Leonardo Quarry Site</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-400 dark:text-gray-500 font-medium">GPS Tracking Mode</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i class="fa-solid fa-satellite-dish text-xs"></i> High Accuracy Continuous
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Security & Account Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-lock text-slate-600"></i> Account Security & Preferences
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button type="button" onclick="openResetPasswordModal()"
                    class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-left transition flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-gray-900 dark:text-gray-100">Reset Password</div>
                    <div class="text-[11px] text-gray-400">Request password change</div>
                </div>
            </button>

            <button type="button" onclick="toggleTheme()"
                    class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-left transition flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-gray-900 dark:text-gray-100">Switch Theme</div>
                    <div class="text-[11px] text-gray-400">Toggle light / dark mode</div>
                </div>
            </button>

            <a href="../logout.php"
               class="p-4 rounded-xl border border-red-100 dark:border-red-900/30 bg-red-50/40 dark:bg-red-950/20 hover:bg-red-50 text-left transition flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-red-600 dark:text-red-400">Sign Out</div>
                    <div class="text-[11px] text-red-400/80">End driver session</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Hidden Photo Upload Form & Cropper Modal -->
    <form id="profilePhotoForm" method="POST" action="upload_profile_photo.php" enctype="multipart/form-data" class="hidden">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
    </form>

    <div id="photoCropModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-3 sm:p-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[85vh]">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-crop-simple"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white leading-tight">Crop Profile Photo</h3>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Position & frame your avatar (1:1 square)</p>
                    </div>
                </div>
                <button type="button" onclick="closePhotoCropModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center transition active:scale-95" aria-label="Close">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="relative bg-gray-950 p-2 sm:p-3 flex items-center justify-center overflow-hidden h-[300px] sm:h-[350px]">
                <img id="photoCropImage" src="" alt="Crop image" class="max-w-full max-h-full block">
            </div>

            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0 text-xs">
                <div class="flex items-center space-x-1 sm:space-x-1.5">
                    <button type="button" onclick="cropperZoom(0.1)" title="Zoom In" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                        <span class="hidden sm:inline text-[11px]">Zoom In</span>
                    </button>
                    <button type="button" onclick="cropperZoom(-0.1)" title="Zoom Out" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                        <span class="hidden sm:inline text-[11px]">Zoom Out</span>
                    </button>
                    <button type="button" onclick="cropperRotate(-90)" title="Rotate Left" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" onclick="cropperRotate(90)" title="Rotate Right" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
                <button type="button" onclick="cropperReset()" class="px-2 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition font-medium flex items-center gap-1 text-[11px]">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                    <span>Reset</span>
                </button>
            </div>

            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-3 bg-white dark:bg-gray-900 flex-shrink-0">
                <button type="button" onclick="closePhotoCropModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button type="button" id="btnSaveCroppedPhoto" onclick="saveCroppedPhoto()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/30 transition active:scale-95 flex items-center space-x-2">
                    <span id="btnSaveCroppedText">Save & Upload</span>
                    <i id="btnSaveCroppedSpinner" class="fa-solid fa-spinner fa-spin hidden text-xs"></i>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================================
     JAVASCRIPT: WEEK SELECTOR, GPS, MAP, CROPPER & FILTER
     ========================================================= -->
<script>
    // -------------------------------------------------------------
    // Trips Week Selector & Date Filtering
    // -------------------------------------------------------------
    let currentMonday = "<?= $thisMonday; ?>";
    let currentSunday = "<?= $thisSunday; ?>";

    function onWeekSelectorChange(val) {
        if (!val) return;
        if (val === 'ALL') {
            window.location.href = 'dashboard.php?tab=trips&date_from=2020-01-01&date_to=2030-12-31';
            return;
        }
        if (val === 'CUSTOM') {
            const bar = document.getElementById('customDateRangeBar');
            if (bar) bar.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const parts = val.split('|');
        if (parts.length === 2) {
            window.location.href = `dashboard.php?tab=trips&date_from=${parts[0]}&date_to=${parts[1]}`;
        }
    }

    function shiftTripWeek(deltaWeeks) {
        const fromInput = document.getElementById('tripDateFrom');
        const toInput = document.getElementById('tripDateTo');

        let baseDate = new Date();
        if (deltaWeeks !== 0 && fromInput && fromInput.value) {
            baseDate = new Date(fromInput.value + 'T00:00:00');
            baseDate.setDate(baseDate.getDate() + (deltaWeeks * 7));
        }

        const day = baseDate.getDay(); // 0 is Sun, 1 is Mon
        const diffToMon = day === 0 ? -6 : 1 - day;
        const mon = new Date(baseDate);
        mon.setDate(baseDate.getDate() + diffToMon);

        const sun = new Date(mon);
        sun.setDate(mon.getDate() + 6);

        const formatYmd = (d) => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const da = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${da}`;
        };

        const fStr = formatYmd(mon);
        const tStr = formatYmd(sun);

        window.location.href = `dashboard.php?tab=trips&date_from=${fStr}&date_to=${tStr}`;
    }

    function applyCustomDateRange() {
        const fromVal = document.getElementById('tripDateFrom')?.value;
        const toVal = document.getElementById('tripDateTo')?.value;
        if (!fromVal || !toVal) {
            if (typeof showToast === 'function') showToast('Please select both from and to dates.', 'warning');
            return;
        }
        window.location.href = `dashboard.php?tab=trips&date_from=${fromVal}&date_to=${toVal}`;
    }

    function filterDriverTrips() {
        const input = document.getElementById('tripSearchInput');
        const term = (input ? input.value : '').toLowerCase().trim();

        document.querySelectorAll('.driver-trip-row').forEach(row => {
            const meta = row.getAttribute('data-search') || '';
            row.style.display = meta.includes(term) ? '' : 'none';
        });

        document.querySelectorAll('.driver-trip-card').forEach(card => {
            const meta = card.getAttribute('data-search') || '';
            card.style.display = meta.includes(term) ? '' : 'none';
        });
    }

    // -------------------------------------------------------------
    // Notifications Helpers
    // -------------------------------------------------------------
    function markAllNotificationsAsRead() {
        document.querySelectorAll('.driver-notif-item').forEach(el => {
            el.classList.add('opacity-70');
        });
        const badge1 = document.querySelector('#nav-notifications span.bg-rose-500');
        if (badge1) badge1.classList.add('hidden');
        const badge2 = document.querySelector('#bottom-nav-notifications span.bg-rose-500');
        if (badge2) badge2.classList.add('hidden');
        const badge3 = document.querySelector('button[title="Notifications"] span.bg-rose-500');
        if (badge3) badge3.classList.add('hidden');
        if (typeof showToast === 'function') {
            showToast('All notifications marked as read', 'info');
        }
    }

    // -------------------------------------------------------------
    // Profile Photo Cropper
    // -------------------------------------------------------------
    let cropperInstance = null;

    const photoInput = document.getElementById('profilePhotoInput');
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                if (typeof showToast === 'function') showToast('Only JPG, PNG, GIF, or WEBP images allowed.', 'error');
                else alert('Only JPG, PNG, GIF, or WEBP images allowed.');
                this.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                if (typeof showToast === 'function') showToast('Selected image must be under 10MB.', 'error');
                else alert('Selected image must be under 10MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                openPhotoCropModal(e.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    function openPhotoCropModal(imageSrc) {
        const modal = document.getElementById('photoCropModal');
        const img = document.getElementById('photoCropImage');
        if (!modal || !img) return;

        img.src = imageSrc;
        modal.classList.remove('hidden');

        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        setTimeout(() => {
            if (typeof Cropper !== 'undefined') {
                cropperInstance = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            }
        }, 100);
    }

    function closePhotoCropModal() {
        const modal = document.getElementById('photoCropModal');
        if (modal) modal.classList.add('hidden');
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        const input = document.getElementById('profilePhotoInput');
        if (input) input.value = '';
    }

    function cropperZoom(val) {
        if (cropperInstance) cropperInstance.zoom(val);
    }

    function cropperRotate(deg) {
        if (cropperInstance) cropperInstance.rotate(deg);
    }

    function cropperReset() {
        if (cropperInstance) cropperInstance.reset();
    }

    function saveCroppedPhoto() {
        const form = document.getElementById('profilePhotoForm');
        if (!cropperInstance) {
            if (form) form.submit();
            return;
        }

        const btn = document.getElementById('btnSaveCroppedPhoto');
        const btnText = document.getElementById('btnSaveCroppedText');
        const btnSpinner = document.getElementById('btnSaveCroppedSpinner');

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
        }
        if (btnText) btnText.textContent = 'Uploading...';
        if (btnSpinner) btnSpinner.classList.remove('hidden');

        const canvas = cropperInstance.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            if (form) form.submit();
            return;
        }

        canvas.toBlob(function (blob) {
            if (!blob) {
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                }
                if (btnText) btnText.textContent = 'Save & Upload';
                if (btnSpinner) btnSpinner.classList.add('hidden');
                return;
            }

            try {
                const croppedFile = new File([blob], 'profile_cropped.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(croppedFile);
                document.getElementById('profilePhotoInput').files = dt.files;
                form.submit();
            } catch (err) {
                const formData = new FormData();
                formData.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
                formData.append('profile_photo', blob, 'profile_cropped.jpg');

                fetch('upload_profile_photo.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    window.location.reload();
                }).catch(() => {
                    window.location.reload();
                });
            }
        }, 'image/jpeg', 0.92);
    }

    // -------------------------------------------------------------
    // Leaflet Road Route Map & GPS
    // -------------------------------------------------------------
    let driverMap = null;
    let driverRoutePolyline = null;
    let driverOriginMarker = null;
    let driverDestMarker = null;
    let driverGpsMarker = null;
    let driverGpsAccuracyCircle = null;
    let driverCurrentLat = 15.359042;
    let driverCurrentLng = 120.965016;

    const GARAGE_LOCATION = {
        name: "San Leonardo (SSV Quarry Garage)",
        lat: 15.359042,
        lng: 120.965016
    };

    const PRESET_DESTINATION_COORDS = {
        "San Leonardo": { lat: 15.359042, lng: 120.965016 },
        "Gapan": { lat: 15.3089, lng: 120.9464 },
        "Gapan City": { lat: 15.3089, lng: 120.9464 },
        "Cabanatuan": { lat: 15.4859, lng: 120.9673 },
        "Cabanatuan City": { lat: 15.4859, lng: 120.9673 },
        "San Isidro": { lat: 15.3114, lng: 120.9080 },
        "Santa Rosa": { lat: 15.4247, lng: 120.9388 },
        "Sta. Rosa": { lat: 15.4247, lng: 120.9388 },
        "Peñaranda": { lat: 15.3533, lng: 120.9950 },
        "General Tinio": { lat: 15.3486, lng: 121.0478 },
        "Papaya": { lat: 15.3486, lng: 121.0478 },
        "Palayan": { lat: 15.5414, lng: 121.0847 },
        "Palayan City": { lat: 15.5414, lng: 121.0847 },
        "Talavera": { lat: 15.5847, lng: 120.9197 },
        "Guimba": { lat: 15.6586, lng: 120.7678 },
        "San Jose": { lat: 15.7947, lng: 120.9956 },
        "San Jose City": { lat: 15.7947, lng: 120.9956 },
        "Muñoz": { lat: 15.7144, lng: 120.9056 },
        "Science City of Muñoz": { lat: 15.7144, lng: 120.9056 },
        "Zaragoza": { lat: 15.4503, lng: 120.7936 },
        "Jaen": { lat: 15.3375, lng: 120.9058 },
        "Aliaga": { lat: 15.5033, lng: 120.8592 },
        "Licab": { lat: 15.5564, lng: 120.7611 },
        "Quezon": { lat: 15.5683, lng: 120.8164 },
        "Santo Domingo": { lat: 15.5833, lng: 120.8833 },
        "Llanera": { lat: 15.6639, lng: 121.0189 },
        "Rizal": { lat: 15.7114, lng: 121.1256 },
        "Pantabangan": { lat: 15.8239, lng: 121.1506 },
        "Carranglan": { lat: 15.9619, lng: 121.0664 },
        "Laur": { lat: 15.4385, lng: 121.1895 },
        "Gabaldon": { lat: 15.4533, lng: 121.3283 },
        "Dingalan": { lat: 15.3944, lng: 121.3967 },
        "Baler": { lat: 15.7594, lng: 121.5622 },
        "San Miguel": { lat: 15.1450, lng: 120.9767 },
        "San Ildefonso": { lat: 15.0806, lng: 120.9417 },
        "San Rafael": { lat: 14.9983, lng: 120.9639 },
        "Baliuag": { lat: 14.9547, lng: 120.9008 },
        "Tarlac": { lat: 15.4828, lng: 120.5963 },
        "Tarlac City": { lat: 15.4828, lng: 120.5963 },
        "Arayat": { lat: 15.1506, lng: 120.7686 }
    };

    let activeDestName = "<?= addslashes($active_dispatch['destination'] ?? 'San Leonardo') ?>";
    let activeDestCoords = null;

    function initDriverMap() {
        const mapContainer = document.getElementById('driverRouteMap');
        if (!mapContainer || typeof L === 'undefined') return;

        try {
            const googleSatLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Satellite'
            });

            const googleStreetLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            });

            const esriImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri'
            });
            const esriLabels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            });
            const satelliteLayer = L.layerGroup([esriImagery, esriLabels]);

            const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                subdomains: ['a', 'b', 'c'],
                attribution: '&copy; OpenStreetMap contributors'
            });

            driverMap = L.map('driverRouteMap', {
                center: [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng],
                zoom: 12,
                layers: [googleSatLayer],
                zoomControl: true
            });

            L.control.layers({
                "🛰️ Google Satellite": googleSatLayer,
                "🌐 Google Streets": googleStreetLayer,
                "🛰️ Satellite (Hybrid)": satelliteLayer,
                "🗺️ OpenStreetMap": streetLayer
            }, null, { position: 'topright' }).addTo(driverMap);

            const originIcon = L.divIcon({
                className: 'custom-origin-icon',
                html: `<div class="w-9 h-9 rounded-2xl bg-indigo-600 border-2 border-white text-white flex items-center justify-center shadow-lg transform -translate-x-1/2 -translate-y-1/2"><i class="fa-solid fa-warehouse text-sm"></i></div>`,
                iconSize: [0, 0]
            });

            driverOriginMarker = L.marker([GARAGE_LOCATION.lat, GARAGE_LOCATION.lng], { icon: originIcon })
                .addTo(driverMap)
                .bindPopup(`
                    <div class="p-2 min-w-[180px]">
                        <div class="font-bold text-gray-900 text-sm flex items-center gap-1.5 border-b pb-1">
                            <i class="fa-solid fa-warehouse text-indigo-500"></i> Quarry Origin
                        </div>
                        <div class="text-xs text-gray-600 mt-1.5 font-medium">Brgy. Burgos San Leonardo</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">SSV Fleet Loading & Dispatch Site</div>
                    </div>
                `);

            if (activeDestName && activeDestName !== 'San Leonardo') {
                resolveAndPlotRoute(activeDestName);
            } else {
                const distEl = document.getElementById('routeDistanceText');
                const durEl = document.getElementById('routeDurationText');
                const statusEl = document.getElementById('driverMapStatusText');
                if (distEl) distEl.textContent = '0.0 km';
                if (durEl) durEl.textContent = 'At Garage';
                if (statusEl) statusEl.textContent = 'Stationed at San Leonardo Garage';
            }

            startDriverLiveLocation();

            setTimeout(() => {
                if (driverMap) driverMap.invalidateSize();
            }, 300);

        } catch (e) {
            console.error("Driver route map initialization error:", e);
        }
    }

    async function resolveAndPlotRoute(destName) {
        if (!destName || !driverMap) return;
        activeDestName = destName;

        const statusEl = document.getElementById('driverMapStatusText');
        const distEl = document.getElementById('routeDistanceText');
        const durEl = document.getElementById('routeDurationText');

        if (statusEl) statusEl.textContent = 'Calculating Route to ' + destName + '...';
        if (distEl) distEl.textContent = 'Calculating...';
        if (durEl) durEl.textContent = 'Calculating...';

        let coords = getPresetCoords(destName);
        if (!coords) {
            try {
                const query = encodeURIComponent(destName + ', Nueva Ecija, Philippines');
                const resp = await fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + query + '&limit=1');
                const results = await resp.json();
                if (results && results.length > 0) {
                    coords = { lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) };
                }
            } catch (err) {
                console.warn('Geocoding fallback failed:', err);
            }
        }

        if (!coords) {
            coords = { lat: 15.4859, lng: 120.9673 };
        }
        activeDestCoords = coords;

        if (driverDestMarker) {
            driverMap.removeLayer(driverDestMarker);
        }
        const destIcon = L.divIcon({
            className: 'custom-dest-icon',
            html: `
                <div class="relative flex items-center justify-center">
                    <span class="absolute w-9 h-9 rounded-full bg-red-500 opacity-75 animate-ping"></span>
                    <div class="w-9 h-9 rounded-2xl bg-red-600 border-2 border-white text-white flex items-center justify-center shadow-xl transform -translate-x-1/2 -translate-y-1/2">
                        <i class="fa-solid fa-location-dot text-base"></i>
                    </div>
                </div>
            `,
            iconSize: [0, 0]
        });

        driverDestMarker = L.marker([coords.lat, coords.lng], { icon: destIcon })
            .addTo(driverMap)
            .bindPopup(`
                <div class="p-2 min-w-[180px]">
                    <div class="font-bold text-gray-900 text-sm flex items-center gap-1.5 border-b pb-1">
                        <i class="fa-solid fa-location-dot text-red-500"></i> Delivery Destination
                    </div>
                    <div class="text-xs font-bold text-blue-600 mt-1.5">${destName}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">Target Dispatch Site</div>
                </div>
            `);

        const startPoint = (driverCurrentLat && driverCurrentLng) 
            ? [driverCurrentLat, driverCurrentLng] 
            : [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng];

        try {
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[1]},${startPoint[0]};${coords.lng},${coords.lat}?overview=full&geometries=geojson`;
            const r = await fetch(osrmUrl);
            const routeData = await r.json();

            if (routeData.code === 'Ok' && routeData.routes && routeData.routes.length > 0) {
                const primaryRoute = routeData.routes[0];
                const distanceKm = (primaryRoute.distance / 1000).toFixed(1);
                const durationMins = Math.round(primaryRoute.duration / 60);

                if (distEl) distEl.textContent = distanceKm + ' km';
                if (durEl) {
                    if (durationMins >= 60) {
                        const hrs = Math.floor(durationMins / 60);
                        const remMins = durationMins % 60;
                        durEl.textContent = `${hrs} hr ${remMins > 0 ? remMins + ' min' : ''}`;
                    } else {
                        durEl.textContent = durationMins + ' mins';
                    }
                }

                const coordinates = primaryRoute.geometry.coordinates.map(c => [c[1], c[0]]);
                drawRoutePolyline(coordinates);

                if (statusEl) statusEl.textContent = `Route to ${destName} Ready (${distanceKm} km)`;
                fitDriverRouteBounds();
                return;
            }
        } catch (routeErr) {
            console.warn('OSRM routing request failed, fallback applied:', routeErr);
        }

        const fallbackPath = [startPoint, [coords.lat, coords.lng]];
        drawRoutePolyline(fallbackPath);
        const distKm = calculateDirectDistanceKm(startPoint[0], startPoint[1], coords.lat, coords.lng);
        if (distEl) distEl.textContent = '~' + distKm.toFixed(1) + ' km';
        if (durEl) durEl.textContent = '~' + Math.round((distKm / 45) * 60) + ' mins';
        if (statusEl) statusEl.textContent = `Route Ready (${distKm.toFixed(1)} km)`;
        fitDriverRouteBounds();
    }

    function drawRoutePolyline(latLngs) {
        if (driverRoutePolyline && driverMap) {
            driverMap.removeLayer(driverRoutePolyline);
        }
        if (driverMap) {
            driverRoutePolyline = L.polyline(latLngs, {
                color: '#2563eb',
                weight: 6,
                opacity: 0.85,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(driverMap);
        }
    }

    function getPresetCoords(name) {
        if (!name) return null;
        const clean = name.trim().toLowerCase();
        for (const [key, val] of Object.entries(PRESET_DESTINATION_COORDS)) {
            if (clean.includes(key.toLowerCase()) || key.toLowerCase().includes(clean)) {
                return val;
            }
        }
        return null;
    }

    function startDriverLiveLocation() {
        if (!navigator.geolocation || !driverMap) return;

        const updateGpsUI = (lat, lng, accuracy) => {
            driverCurrentLat = lat;
            driverCurrentLng = lng;

            const truckIcon = L.divIcon({
                className: 'custom-truck-icon',
                html: `
                    <div class="relative flex items-center justify-center">
                        <span class="absolute w-8 h-8 rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                        <div class="w-8 h-8 rounded-2xl bg-emerald-600 border-2 border-white text-white flex items-center justify-center shadow-xl transform -translate-x-1/2 -translate-y-1/2">
                            <i class="fa-solid fa-truck text-xs"></i>
                        </div>
                    </div>
                `,
                iconSize: [0, 0]
            });

            if (!driverGpsMarker) {
                driverGpsMarker = L.marker([lat, lng], { icon: truckIcon })
                    .addTo(driverMap)
                    .bindPopup(`
                        <div class="p-2">
                            <div class="font-bold text-gray-900 text-xs flex items-center gap-1">
                                <i class="fa-solid fa-truck text-emerald-600"></i> Your Current Position
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 font-mono">${lat.toFixed(5)}, ${lng.toFixed(5)}</div>
                        </div>
                    `);
            } else {
                driverGpsMarker.setLatLng([lat, lng]);
            }

            if (accuracy && accuracy < 2000) {
                if (!driverGpsAccuracyCircle) {
                    driverGpsAccuracyCircle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#10b981',
                        fillColor: '#10b981',
                        fillOpacity: 0.1,
                        weight: 1
                    }).addTo(driverMap);
                } else {
                    driverGpsAccuracyCircle.setLatLng([lat, lng]);
                    driverGpsAccuracyCircle.setRadius(accuracy);
                }
            }
        };

        navigator.geolocation.getCurrentPosition(
            pos => updateGpsUI(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
            err => console.log('Driver initial GPS location pending:', err.message),
            { enableHighAccuracy: true, timeout: 10000 }
        );

        navigator.geolocation.watchPosition(
            pos => updateGpsUI(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
            err => console.log('Driver GPS watch pending:', err.message),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
        );
    }

    function fitDriverRouteBounds() {
        if (!driverMap) return;
        const points = [];
        if (GARAGE_LOCATION) points.push([GARAGE_LOCATION.lat, GARAGE_LOCATION.lng]);
        if (activeDestCoords) points.push([activeDestCoords.lat, activeDestCoords.lng]);
        if (driverCurrentLat && driverCurrentLng) points.push([driverCurrentLat, driverCurrentLng]);

        if (points.length > 0) {
            const bounds = L.latLngBounds(points);
            driverMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        }
    }

    function centerOnDriverLiveLocation() {
        if (!driverMap) return;
        if (driverCurrentLat && driverCurrentLng) {
            driverMap.flyTo([driverCurrentLat, driverCurrentLng], 15, { animate: true, duration: 1 });
            if (driverGpsMarker) driverGpsMarker.openPopup();
        } else {
            if (typeof showToast === 'function') showToast('GPS location is still synchronizing...', 'info');
            else alert('GPS location is still synchronizing...');
        }
    }

    function launchGoogleMapsNav() {
        const dest = activeDestName || "Cabanatuan City, Nueva Ecija";
        const url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(dest + ', Nueva Ecija')}&travelmode=driving`;
        window.open(url, '_blank');
    }

    function launchWazeNav() {
        if (activeDestCoords) {
            const url = `https://waze.com/ul?ll=${activeDestCoords.lat},${activeDestCoords.lng}&navigate=yes`;
            window.open(url, '_blank');
        } else {
            const url = `https://waze.com/ul?q=${encodeURIComponent(activeDestName)}&navigate=yes`;
            window.open(url, '_blank');
        }
    }

    function calculateDirectDistanceKm(lat1, lon1, lat2, lon2) {
        const R = 6371; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    document.addEventListener("DOMContentLoaded", function() {
        initDriverMap();
    });
</script>

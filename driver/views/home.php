        <?php
        $driverFullName  = trim(($driverProfile['first_name'] ?? '') . ' ' . ($driverProfile['last_name'] ?? ''));
        $driverUsername  = $driverProfile['username'] ?? '';
        $driverPhotoPath = $driverProfile['profile_photo'] ?? null;
        $driverPhotoFull = $driverPhotoPath ? (dirname(__DIR__, 2) . '/' . $driverPhotoPath) : null;
        $driverPhotoUrl  = ($driverPhotoFull && file_exists($driverPhotoFull))
            ? '../' . htmlspecialchars($driverPhotoPath) . '?v=' . filemtime($driverPhotoFull)
            : null;

        // Build initials for avatar fallback
        $initials = 'DR';
        if (!empty($driverFullName)) {
            $parts = explode(' ', $driverFullName);
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        }
        ?>

        <!-- ====== DRIVER PROFILE CARD ====== -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 h-20 relative">
                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg,transparent,transparent 8px,rgba(255,255,255,.1) 8px,rgba(255,255,255,.1) 16px)"></div>
            </div>
            <div class="px-5 pb-5">
                <div class="flex items-end justify-between -mt-10 mb-4">
                    <!-- Avatar / Photo -->
                    <div class="relative group">
                        <?php if ($driverPhotoUrl): ?>
                            <img src="<?= $driverPhotoUrl ?>" alt="Profile Photo"
                                 id="driverProfilePhotoPreview"
                                 class="w-20 h-20 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-xl">
                        <?php else: ?>
                            <div id="driverProfilePhotoPreview"
                                 class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-2xl border-4 border-white dark:border-gray-800 shadow-xl">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                        <?php endif; ?>
                        <!-- Camera overlay trigger -->
                        <button type="button" onclick="document.getElementById('profilePhotoInput').click()"
                                title="Change profile photo"
                                class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white dark:border-gray-800 transition-colors">
                            <i class="fa-solid fa-camera text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Upload button -->
                    <div>
                        <button type="button" onclick="document.getElementById('profilePhotoInput').click()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 transition-all">
                            <i class="fa-solid fa-upload text-[10px]"></i>
                            Upload Photo
                        </button>
                    </div>
                </div>

                <!-- Driver Name & Username -->
                <div>
                    <h2 class="font-extrabold text-gray-900 dark:text-gray-100 text-lg leading-tight">
                        <?= htmlspecialchars($driverFullName ?: $driverUsername) ?>
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium mt-0.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-id-badge text-blue-400"></i>
                        <?= htmlspecialchars($driverUsername) ?>
                    </p>
                </div>
            </div>

            <!-- Hidden upload form -->
            <form id="profilePhotoForm" method="POST" action="upload_profile_photo.php" enctype="multipart/form-data" class="hidden">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
            </form>

            <!-- Preview + Confirm bar (hidden by default) -->
            <div id="photoUploadConfirmBar" class="hidden border-t border-gray-100 dark:border-gray-700 px-5 py-3 flex items-center gap-3 bg-blue-50 dark:bg-blue-950/30">
                <img id="photoPreviewImg" src="" alt="Preview" class="w-10 h-10 rounded-xl object-cover border border-blue-200 dark:border-blue-800 shadow">
                <div class="flex-1 min-w-0">
                    <p id="photoPreviewName" class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate"></p>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Ready to upload</p>
                </div>
                <button type="button" onclick="submitPhotoUpload()" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm">
                    <i class="fa-solid fa-check mr-1"></i>Save
                </button>
                <button type="button" onclick="cancelPhotoUpload()" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-all">
                    <i class="fa-solid fa-xmark mr-1"></i>Cancel
                </button>
            </div>
        </div>

        <script>
        document.getElementById('profilePhotoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                if (typeof showToast === 'function') showToast('Only JPG, PNG, GIF, or WEBP images allowed.', 'error');
                else alert('Only JPG, PNG, GIF, or WEBP images allowed.');
                this.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                if (typeof showToast === 'function') showToast('File size must be under 2MB.', 'error');
                else alert('File size must be under 2MB.');
                this.value = '';
                return;
            }

            // Show preview bar
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('photoPreviewImg').src = e.target.result;
                document.getElementById('photoPreviewName').textContent = file.name;
                document.getElementById('photoUploadConfirmBar').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        function submitPhotoUpload() {
            document.getElementById('profilePhotoForm').submit();
        }

        function cancelPhotoUpload() {
            document.getElementById('profilePhotoInput').value = '';
            document.getElementById('photoUploadConfirmBar').classList.add('hidden');
        }
        </script>

        <!-- ACTIVE TRIP SECTION -->
        <div class="mb-8">
            <?php if ($active_dispatch): ?>
                <?php 
                    $status = $active_dispatch['status'];
                    $statusColor = 'bg-blue-150 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
                    $statusIcon = 'fa-circle-info';
                    $statusDesc = 'Your dispatch is pending.';

                    if ($status === 'Loading') {
                        $statusColor = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300';
                        $statusIcon = 'fa-spinner fa-spin';
                        $statusDesc = 'Your truck is currently loading gravel at the site.';
                    } elseif ($status === 'In Transit') {
                        $statusColor = 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
                        $statusIcon = 'fa-truck-fast animate-bounce';
                        $statusDesc = 'You are on the road. GPS location is sharing live with dispatch.';
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
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-blue-100 dark:border-gray-700 overflow-hidden transition-all duration-300 transform hover:scale-[1.01]">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-route text-2xl opacity-90"></i>
                            <div>
                                <h3 class="font-bold text-lg">Active Trip Dispatch</h3>
                                <p class="text-blue-100 text-xs font-mono">Ticket: <?= htmlspecialchars($active_dispatch['ticket_number']); ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white text-blue-700 shadow-sm flex items-center gap-1.5 uppercase">
                            <i class="fa-solid <?= $statusIcon; ?>"></i> <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Destination</span>
                                <span class="text-base sm:text-lg font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-location-dot text-red-500 flex-shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($active_dispatch['destination']); ?></span>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Distance</span>
                                <span class="text-base sm:text-lg font-extrabold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-route text-blue-500 flex-shrink-0"></i>
                                    <?= number_format($active_dispatch['distance_km'] ?? 0, 1); ?> km
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Trip Pay (₱10/km)</span>
                                <span class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-peso-sign text-emerald-500 flex-shrink-0"></i>
                                    ₱<?= number_format($active_dispatch['pay_amount'] ?? 0, 2); ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Assigned Truck</span>
                                <span class="text-base sm:text-lg font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck text-blue-500 flex-shrink-0"></i>
                                    <?= htmlspecialchars($active_dispatch['truck_code']); ?>
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Load Volume</span>
                                <span class="text-base sm:text-lg font-extrabold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-cube text-indigo-500 flex-shrink-0"></i>
                                    <?= number_format($active_dispatch['cubic_meters'] ?? 0, 2); ?> cu.m
                                </span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold block uppercase mb-1">Dispatch Time</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-clock text-blue-500 flex-shrink-0"></i>
                                    <?= !empty($active_dispatch['transit_start_time']) ? date('M d, h:i A', strtotime($active_dispatch['transit_start_time'])) : (!empty($active_dispatch['created_at']) ? date('M d, h:i A', strtotime($active_dispatch['created_at'])) : 'Pending') ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 rounded-xl <?= $statusColor; ?>">
                            <i class="fa-solid fa-info-circle text-lg mt-0.5"></i>
                            <div>
                                <span class="font-bold text-sm block">Current State</span>
                                <p class="text-xs mt-1 leading-relaxed opacity-90"><?= $statusDesc; ?></p>
                            </div>
                        </div>

                        <?php if ($status === 'In Transit'): ?>
                            <div class="mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950/20 text-green-800 dark:text-green-300 border border-green-200/50 dark:border-green-900/30 rounded-xl">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <div class="text-xs font-medium">
                                    <strong>GPS Live Tracking Active:</strong> Your location updates are synchronized with the office. Please do not close or minimize this tab.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- IDLE STATUS CARD -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-4 text-center sm:text-left">
                        <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto sm:mx-0 shadow-inner">
                            <i class="fa-solid fa-house-chimney-user"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-850 dark:text-gray-100 flex items-center gap-2 justify-center sm:justify-start">
                                Status: Idle (At Garage)
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block animate-pulse"></span>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">You are currently active and ready for a new trip assignment.</p>
                        </div>
                    </div>
                    <div class="text-center sm:text-right w-full sm:w-auto">
                        <span class="text-xs bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-3.5 py-1.5 rounded-full font-bold uppercase inline-block">
                            Waiting for Admin
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($active_dispatch && ($active_dispatch['status'] ?? '') === 'In Transit'): ?>
            <!-- ==================== DRIVER ROUTE & NAVIGATION MAP (IN TRANSIT ONLY) ==================== -->
            <div id="liveTripRouteSection" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-8 transition-all relative z-0">
                <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 p-5 sm:p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-blue-300 text-2xl flex-shrink-0 shadow-inner">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-lg sm:text-xl text-white">Live Trip Route & Navigation</h3>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-green-500/20 text-green-300 border border-green-400/30">
                                    <i class="fa-solid fa-satellite-dish fa-fade mr-1"></i> In Transit
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-blue-200 mt-0.5">
                                Turn-by-turn road route from Origin Quarry to <?= htmlspecialchars($active_dispatch['destination']); ?> with live GPS.
                            </p>
                        </div>
                    </div>

                    <!-- External GPS Navigation Launchers -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="launchGoogleMapsNav()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-gray-800 hover:bg-gray-100 active:scale-95 shadow transition">
                            <i class="fa-brands fa-google text-blue-600"></i> Google Maps
                        </button>
                        <button type="button" onclick="launchWazeNav()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-cyan-500 hover:bg-cyan-400 active:scale-95 text-white shadow transition">
                            <i class="fa-brands fa-waze"></i> Waze
                        </button>
                    </div>
                </div>

                <!-- Route Quick Stats Strip -->
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
                            <?= htmlspecialchars($active_dispatch['destination']); ?>
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

                <!-- Map View Container -->
                <div class="p-3 sm:p-5 relative z-0">
                    <div class="relative w-full h-[360px] sm:h-[480px] rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-inner z-0">
                        <div id="driverRouteMap" class="w-full h-full relative z-0"></div>

                        <!-- Floating Action Buttons -->
                        <div class="absolute bottom-4 right-4 z-10 flex flex-col gap-2">
                            <button type="button" onclick="fitDriverRouteBounds()" title="Fit full route in view"
                                class="w-10 h-10 sm:w-11 sm:h-11 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-xl shadow-lg hover:bg-blue-50 dark:hover:bg-gray-700 flex items-center justify-center transition active:scale-90">
                                <i class="fa-solid fa-maximize text-sm sm:text-base text-blue-600 dark:text-blue-400"></i>
                            </button>
                            <button type="button" onclick="centerOnDriverLiveLocation()" title="Snap to my current GPS location"
                                class="w-10 h-10 sm:w-11 sm:h-11 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-xl shadow-lg hover:bg-blue-50 dark:hover:bg-gray-700 flex items-center justify-center transition active:scale-90">
                                <i class="fa-solid fa-crosshairs text-sm sm:text-base text-emerald-600 dark:text-emerald-400"></i>
                            </button>
                        </div>

                        <!-- Live route status pill overlay -->
                        <div class="absolute top-4 left-4 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 shadow-md flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span id="driverMapStatusText">Live GPS Route Loaded</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TRIP METRICS STATS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

            <div class="bg-gradient-to-br from-blue-500 to-blue-650 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
                <div class="relative z-10">
                    <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Trips This Week</p>
                    <h3 class="text-3xl font-extrabold tracking-tight"><?= number_format($weekly_trips); ?> <span class="text-lg font-medium text-blue-100">trips</span></h3>
                    <p class="text-blue-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                        <i class="fa-solid fa-calendar-week"></i> Current week deliveries (Mon–Sun)
                    </p>
                </div>
                <i class="fa-solid fa-truck-ramp-box absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
            </div>

            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
                <div class="relative z-10">
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider mb-1 opacity-90">Trips This Month</p>
                    <h3 class="text-3xl font-extrabold tracking-tight"><?= number_format($monthly_trips); ?> <span class="text-lg font-medium text-emerald-100">trips</span></h3>
                    <p class="text-emerald-100 text-xs mt-3 flex items-center gap-1 opacity-75">
                        <i class="fa-regular fa-calendar-check"></i> Total delivered for <?= date('F Y'); ?>
                    </p>
                </div>
                <i class="fa-solid fa-clipboard-check absolute -right-6 -bottom-6 text-9xl text-white opacity-15 transform -rotate-12 pointer-events-none"></i>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-md transition transform hover:-translate-y-0.5">
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

        <!-- ==================== PAYROLL & CASH ADVANCE SECTION ==================== -->
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-wallet text-2xl opacity-90"></i>
                    <div>
                        <h3 class="font-bold text-lg">My Payroll Summary</h3>
                        <p class="text-emerald-100 text-xs">Earnings based on distance (₱10/km) — Cash advances auto-deducted</p>
                    </div>
                </div>
                <button onclick="openCashAdvanceModal()"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white text-orange-600 hover:bg-orange-50 shadow transition active:scale-95">
                    <i class="fa-solid fa-hand-holding-dollar"></i> Request Cash Advance
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 dark:divide-gray-700">
                <div class="p-5 text-center bg-indigo-50/40 dark:bg-indigo-950/20">
                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold uppercase mb-1">Remaining Balance</div>
                    <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">₱<?= number_format($driverRemainingBalance ?? 0, 2); ?></div>
                    <div class="text-[11px] text-indigo-500/80 mt-0.5">Carried from prior claim</div>
                </div>
                <div class="p-5 text-center">
                    <div class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase mb-1">Cash Advances</div>
                    <div class="text-2xl font-extrabold text-orange-600 dark:text-orange-400">-₱<?= number_format($totalCashAdvancesClaimed ?? 0, 2); ?></div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Total approved advances</div>
                </div>
                <div class="p-5 text-center bg-blue-50/40 dark:bg-blue-950/20">
                    <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase mb-1">Net Payable</div>
                    <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">₱<?= number_format($netPay ?? 0, 2); ?></div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Available for payout</div>
                </div>
            </div>

            <?php if (!empty($driverCashAdvances)): ?>
            <div class="border-t border-gray-100 dark:border-gray-700">
                <div class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900">
                    Cash Advance History (Last 10)
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <?php foreach ($driverCashAdvances as $ca):
                        $caStatusColor = $ca['status'] === 'Approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                         ($ca['status'] === 'Rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                                         'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400');
                    ?>
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">₱<?= number_format($ca['amount'], 2); ?></span>
                            <?php if (!empty($ca['reason'])): ?>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">&mdash; <?= htmlspecialchars($ca['reason']); ?></span>
                            <?php endif; ?>
                            <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5"><i class="fa-regular fa-clock mr-1"></i><?= date('M d, Y', strtotime($ca['requested_at'])); ?></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full <?= $caStatusColor; ?>"><?= $ca['status']; ?></span>
                            <?php if ($ca['status'] === 'Approved'): ?>
                            <button onclick="window.open('../admin/print_cash_advance.php?id=<?= $ca['id']; ?>', '_blank')" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition flex items-center gap-1 border border-blue-200 dark:border-blue-800"
                                    title="View / Print Voucher">
                                <i class="fa-solid fa-print"></i>
                                <span class="hidden sm:inline">Ticket</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_dispatch && floatval($active_dispatch['pay_amount'] ?? 0) > 0): ?>
            <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-3 flex items-center justify-between gap-3 bg-blue-50 dark:bg-blue-950/20">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-truck text-blue-500"></i>
                    <span class="text-xs text-gray-600 dark:text-gray-300">
                        <strong>Current Trip Pay:</strong>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">₱<?= number_format($active_dispatch['pay_amount'], 2); ?></span>
                        &bull; Distance: <strong><?= number_format($active_dispatch['distance_km'] ?? 0, 1); ?> km</strong>
                        (payable upon delivery)
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- TRIP HISTORY SECTION -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-850 dark:text-gray-200">Past Trip History</h2>
                <span class="text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full font-bold uppercase">
                    <?= count($trips); ?> Trips
                </span>
            </div>

            <!-- Mobile View: Stacked Cards list (shown on small screens) -->
            <div class="block sm:hidden space-y-3">
                <?php if (count($trips) > 0): ?>
                    <?php foreach ($trips as $trip): ?>
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
                        ?>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-150 dark:border-gray-800 shadow-sm space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">Dispatch: <?= $dispTimeStr; ?></span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">Duration: <?= $duration; ?></span>
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                Arrival: <?= $arrTimeStr; ?>
                            </div>
                            <div class="flex justify-between items-end pt-1">
                                <div>
                                    <span class="text-xs text-gray-400 dark:text-gray-550 block">Destination</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 text-sm"><?= htmlspecialchars($trip['destination']); ?></span>
                                </div>
                                <div class="text-right">
                                    <?php 
                                    $s = isset($trip['status']) ? trim($trip['status']) : '';
                                    if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                    ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-150 text-green-800 dark:bg-green-900/20 dark:text-green-455">
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
                            <div class="flex justify-between items-center text-xs pb-0.5">
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
                    <div class="py-8 text-center text-gray-400">
                        <i class="fa-solid fa-road text-4xl mb-2 opacity-30"></i>
                        <p class="text-sm">No trips recorded yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Desktop View: Grid/Table (shown on md/lg screens) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 px-2 font-medium">Dispatch Date & Time</th>
                            <th class="pb-3 px-2 font-medium">Arrival Date & Time</th>
                            <th class="pb-3 px-2 font-medium">Destination</th>
                            <th class="pb-3 px-2 font-medium">Distance</th>
                            <th class="pb-3 px-2 font-medium">Trip Pay</th>
                            <th class="pb-3 px-2 font-medium">Duration</th>
                            <th class="pb-3 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-200">
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
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
                                ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition-colors">
                                    <td class="py-4 px-2 text-sm font-medium text-gray-800 dark:text-gray-200"><?= $dispTimeStr; ?></td>
                                    <td class="py-4 px-2 text-sm font-medium text-gray-600 dark:text-gray-400"><?= $arrTimeStr; ?></td>
                                    <td class="py-4 px-2 font-medium"><?= htmlspecialchars($trip['destination']); ?></td>
                                    <td class="py-4 px-2 text-sm font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                        <i class="fa-solid fa-route mr-1 text-xs"></i><?= number_format($trip['distance_km'] ?? 0, 1); ?> km
                                    </td>
                                    <td class="py-4 px-2 text-sm font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        ₱<?= number_format($trip['pay_amount'] ?? 0, 2); ?>
                                    </td>
                                    <td class="py-4 px-2 text-gray-500 dark:text-gray-400 font-mono text-sm"><?= $duration; ?></td>
                                    <td class="py-4 px-2">
                                        <?php 
                                        $s = isset($trip['status']) ? trim($trip['status']) : '';
                                        if (empty($s) || strtolower($s) === 'delivered' || strtolower($s) === 'completed'): 
                                        ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-455">
                                                <i class="fa-solid fa-check mr-1"></i> Delivered
                                            </span>
                                        <?php elseif ($s === 'Cancellation Requested'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-850 dark:bg-orange-950/20 dark:text-orange-400 animate-pulse">
                                                <i class="fa-solid fa-clock mr-1"></i> Pending Cancel
                                            </span>
                                        <?php elseif ($s === 'Cancelled'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                                <i class="fa-solid fa-ban mr-1"></i> Cancelled
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                                <i class="fa-solid fa-truck-fast mr-1"></i> <?= htmlspecialchars($s); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-8 px-2 text-center text-gray-550 dark:text-gray-400">
                                    <i class="fa-solid fa-road text-4xl mb-3 text-gray-300 block"></i>
                                    No trips recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<script>
    // ==================== DRIVER ROUTE & MAP NAVIGATION ENGINE ====================
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

    // Fast coordinate lookup for standard provincial destinations
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

    let activeDestName = "<?= addslashes($active_dispatch['destination'] ?? ($_dest_rows[0]['name'] ?? 'Cabanatuan City')) ?>";
    let activeDestCoords = null;

    function initDriverMap() {
        const mapContainer = document.getElementById('driverRouteMap');
        if (!mapContainer || typeof L === 'undefined') return;

        try {
            // Layer providers
            const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                subdomains: ['a', 'b', 'c'],
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

            const esriImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, USGS, AeroGRID, IGN, and GIS User Community'
            });
            const esriLabels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            });
            const satelliteLayer = L.layerGroup([esriImagery, esriLabels]);

            const voyagerLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 20,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            });

            const darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 20,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            });

            const googleStreetLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            });

            const googleSatLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Satellite'
            });

            driverMap = L.map('driverRouteMap', {
                center: [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng],
                zoom: 12,
                layers: [streetLayer],
                zoomControl: true
            });

            L.control.layers({
                "🗺️ OpenStreetMap": streetLayer,
                "🛰️ Satellite (Hybrid)": satelliteLayer,
                "🚗 Navigation (Voyager)": voyagerLayer,
                "🌙 Dark Mode": darkLayer,
                "🌐 Google Streets": googleStreetLayer,
                "🛰️ Google Satellite": googleSatLayer
            }, null, { position: 'topright' }).addTo(driverMap);

            // Add Origin marker (Quarry)
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
                        <div class="text-[11px] text-gray-400 mt-0.5">Fleet Loading & Dispatch Site</div>
                    </div>
                `);

            // Initialize Driver GPS tracking and route plotting
            resolveAndPlotRoute(activeDestName);
            startDriverLiveLocation();

            setTimeout(() => {
                driverMap.invalidateSize();
            }, 300);

        } catch (e) {
            console.error("Driver route map initialization failed:", e);
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

        // 1. Get Destination Coordinates (Cache or Nominatim)
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
            // Default fallback offset near Central Luzon
            coords = { lat: 15.4859, lng: 120.9673 };
        }
        activeDestCoords = coords;

        // 2. Render Destination Marker
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
                        <i class="fa-solid fa-location-dot text-red-500"></i> Dispatch Destination
                    </div>
                    <div class="text-xs font-bold text-blue-600 mt-1.5">${destName}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">Target Delivery Site</div>
                </div>
            `);

        // 3. Start Point: Driver current position or Garage
        const startPoint = (driverCurrentLat && driverCurrentLng) 
            ? [driverCurrentLat, driverCurrentLng] 
            : [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng];

        // 4. Query OSRM Driving Route
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

                // Draw OSRM polyline
                const coordinates = primaryRoute.geometry.coordinates.map(c => [c[1], c[0]]);
                drawRoutePolyline(coordinates);

                if (statusEl) statusEl.textContent = `Route to ${destName} Ready (${distanceKm} km)`;
                fitDriverRouteBounds();
                return;
            }
        } catch (routeErr) {
            console.warn('OSRM routing request failed, using straight-line fallback:', routeErr);
        }

        // Fallback straight-line polyline if OSRM is unreachable
        const fallbackPath = [startPoint, [coords.lat, coords.lng]];
        drawRoutePolyline(fallbackPath);
        const distKm = calculateDirectDistanceKm(startPoint[0], startPoint[1], coords.lat, coords.lng);
        if (distEl) distEl.textContent = '~' + distKm.toFixed(1) + ' km';
        if (durEl) durEl.textContent = '~' + Math.round((distKm / 45) * 60) + ' mins';
        if (statusEl) statusEl.textContent = `Route Ready (${distKm.toFixed(1)} km)`;
        fitDriverRouteBounds();
    }

    function drawRoutePolyline(latLngs) {
        if (driverRoutePolyline) {
            driverMap.removeLayer(driverRoutePolyline);
        }
        driverRoutePolyline = L.polyline(latLngs, {
            color: '#2563eb',
            weight: 6,
            opacity: 0.85,
            lineJoin: 'round',
            lineCap: 'round'
        }).addTo(driverMap);
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
            showToast('GPS location is still synchronizing...', 'info');
        }
    }

    function switchDriverDestination(newDest) {
        if (!newDest) return;
        resolveAndPlotRoute(newDest);
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
        const R = 6371; // Earth radius in km
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

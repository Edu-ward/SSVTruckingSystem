<?php if (in_array($_SESSION['role'] ?? '', ['Admin', 'Driver'])): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'Admin'): ?>
    <!-- ================= ADMIN SCRIPTS ================= -->
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'dashboard';
        let map = null;

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Reset all sidebar nav items
            document.querySelectorAll('.sidebar-nav-item').forEach(el => el.classList.remove('active'));
            const viewEl = document.getElementById('view-' + tabName);
            if (viewEl) viewEl.classList.remove('hidden');
            const navBtn = document.getElementById('nav-' + tabName);
            if (navBtn) navBtn.classList.add('active');
            if (tabName === 'tracking') {
                setTimeout(() => {
                    if (!map) {
                        initMap();
                    } else {
                        map.invalidateSize();
                    }
                }, 250);
            }
            if (tabName === 'settings') {
                setTimeout(() => {
                    if (window.initSettingsSimulatorMap) {
                        window.initSettingsSimulatorMap();
                    } else if (window.settingsSimulatorMap) {
                        window.settingsSimulatorMap.invalidateSize();
                    }
                }, 250);
            }
            if (tabName === 'dispatches') {
                setTimeout(() => {
                    const input = document.getElementById('dispatchScannerRfidInput');
                    if (input) input.focus();
                }, 200);
            }
            window.history.pushState({}, '', '?tab=' + tabName);
            // Close mobile sidebar if open
            const sidebar = document.getElementById('admin-sidebar');
            if (sidebar && !sidebar.classList.contains('sidebar-closed') && window.innerWidth < 1024) {
                toggleSidebar();
            }
        }
        switchTab(activeTab);

        // ── Password Reset Badge Polling (Admin) ──
        function refreshPwdResetBadge() {
            fetch('get_pwd_reset_count.php')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('pwdResetBadge');
                    const navBtn = document.getElementById('nav-pwd_requests');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        } else if (navBtn) {
                            // Create badge if not rendered (was 0 on page load)
                            const span = document.createElement('span');
                            span.id = 'pwdResetBadge';
                            span.className = 'ml-auto min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-red-500 text-white flex items-center justify-center';
                            span.textContent = data.count;
                            navBtn.appendChild(span);
                        }
                    } else {
                        if (badge) badge.classList.add('hidden');
                    }
                })
                .catch(() => {});
        }
        // Poll every 15 seconds
        setInterval(refreshPwdResetBadge, 15000);

        // ── Cash Advance Badge Polling (Admin) ──
        function refreshCashAdvanceBadge() {
            fetch('get_cash_advance_count.php')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('cashAdvanceBadge');
                    const navBtn = document.getElementById('nav-cash_advances');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        } else if (navBtn) {
                            const span = document.createElement('span');
                            span.id = 'cashAdvanceBadge';
                            span.className = 'ml-auto min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center';
                            span.textContent = data.count;
                            navBtn.appendChild(span);
                        }
                    } else {
                        if (badge) badge.classList.add('hidden');
                    }
                })
                .catch(() => {});
        }
        setInterval(refreshCashAdvanceBadge, 15000);

        // ── Activity Logs Filter ──
        function filterActivityLogs() {
            const search = (document.getElementById('activityLogSearch')?.value || '').toLowerCase();
            const role = document.getElementById('activityLogRoleFilter')?.value || '';
            const rows = document.querySelectorAll('.activity-log-row');
            let visible = 0;
            rows.forEach(row => {
                const rowRole = row.dataset.role || '';
                const rowSearch = row.dataset.search || '';
                const matchRole = !role || rowRole === role;
                const matchSearch = !search || rowSearch.includes(search);
                if (matchRole && matchSearch) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });
            const countEl = document.getElementById('activityLogVisibleCount');
            if (countEl) countEl.textContent = visible + ' of ' + rows.length + ' entries visible';
        }

        const trackingData = <?= json_encode($trackingTrucks ?? []); ?>;
        let streetLayer = null;
        let googleStreetLayer = null;
        let satelliteLayer = null;
        let voyagerLayer = null;
        let darkLayer = null;

        function initMap() {
            const mapDiv = document.getElementById('map');
            if (!mapDiv) return;
            try {
                // Free, high-reliability tile providers (NO API Key required)
                // 1. Standard OpenStreetMap
                streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c'],
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                });

                // 2. High-Resolution Esri World Imagery (Satellite) + Boundaries/Labels (Hybrid Satellite)
                const esriImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, USGS, AeroGRID, IGN, and GIS User Community'
                });
                const esriLabels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19
                });
                satelliteLayer = L.layerGroup([esriImagery, esriLabels]);

                // 3. CartoDB Voyager (Modern detailed road navigation)
                voyagerLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
                });

                // 4. CartoDB Dark Matter (Dark mode map)
                darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
                });

                // 5. Google Streets (with resilient subdomains)
                googleStreetLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                });

                // 6. Google Satellite
                const googleSatLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps Satellite'
                });

                map = L.map('map', {
                    center: [15.359042, 120.965016],
                    zoom: 14,
                    layers: [streetLayer], // Default to OpenStreetMap for guaranteed 100% reliable initial load
                    zoomControl: true
                });

                // Interactive Layer Control Switcher (top right)
                const baseMaps = {
                    "🗺️ OpenStreetMap": streetLayer,
                    "🛰️ Satellite (Hybrid)": satelliteLayer,
                    "🚗 Navigation (Voyager)": voyagerLayer,
                    "🌙 Dark Mode": darkLayer,
                    "🌐 Google Streets": googleStreetLayer,
                    "🛰️ Google Satellite": googleSatLayer
                };
                L.control.layers(baseMaps, null, {
                    position: 'topright'
                }).addTo(map);

                // Add Central Garage Marker
                const garageIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white shadow-xl border-2 border-white ring-4 ring-indigo-500/30 hover:scale-110 transition-transform cursor-pointer" title="SSV Garage (Quarry) — San Leonardo, Nueva Ecija"><i class="fa-solid fa-warehouse text-sm"></i></div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });
                L.marker([15.359042, 120.965016], {
                        icon: garageIcon
                    })
                    .addTo(map)
                    .bindPopup(`
                        <div class="p-3 min-w-[200px]">
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm pb-1 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-warehouse text-indigo-500"></i> SSV Quarry Site
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mt-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-rose-500"></i>
                                <span>San Leonardo, Nueva Ecija</span>
                            </div>
                        </div>
                    `);

                // Initial render from PHP data
                renderMapMarkers(trackingData);

                setTimeout(() => {
                    map.invalidateSize();
                }, 300);

                // Start live polling every 5 seconds
                setInterval(refreshMap, 5000);
            } catch (error) {
                console.error("Map initialization failed:", error);
            }
        }

        let truckMarkers = {};

        function renderMapMarkers(trucks) {
            const activeCodes = trucks.map(t => t.truck_code);
            Object.keys(truckMarkers).forEach(code => {
                if (!activeCodes.includes(code)) {
                    map.removeLayer(truckMarkers[code]);
                    delete truckMarkers[code];
                }
            });

            trucks.forEach(truck => {
                if (!truck.latitude || !truck.longitude) return;

                let markerClass = 'bg-gray-500';
                let pulseEffect = '';
                if (truck.status === 'In Transit') {
                    markerClass = 'bg-emerald-500';
                    pulseEffect = '<span class="absolute -inset-1 rounded-full bg-emerald-400 opacity-75 animate-ping"></span>';
                }
                if (truck.status === 'Idle') markerClass = 'bg-amber-500';
                if (truck.status === 'Loading') markerClass = 'bg-blue-500';
                if (truck.status === 'Unloading') markerClass = 'bg-orange-500';

                const customIcon = L.divIcon({
                    className: 'custom-div-icon relative',
                    html: `${pulseEffect}<div class="marker-pin ${markerClass} relative z-10 shadow-lg border-2 border-white"><i class="fa-solid fa-truck text-xs"></i></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const statusBadgeBg = truck.status === 'In Transit' ? 'bg-emerald-600' : (truck.status === 'Idle' ? 'bg-amber-600' : 'bg-blue-600');
                let etaHtml = '';
                if (truck.estimated_arrival_time) {
                    try {
                        let dateStr = truck.estimated_arrival_time;
                        if (!dateStr.includes('+') && !dateStr.endsWith('Z')) {
                            dateStr = dateStr.replace(' ', 'T') + '+08:00';
                        }
                        const etaDate = new Date(dateStr);
                        if (!isNaN(etaDate.getTime())) {
                            const now = new Date();
                            const diffMins = Math.round((etaDate.getTime() - now.getTime()) / 60000);
                            const timeStr = etaDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                            let relStr = '';
                            let badgeBg = 'background: rgba(16, 185, 129, 0.1); color: #059669; border-color: rgba(16, 185, 129, 0.3);';
                            if (diffMins > 0) {
                                if (diffMins >= 60) {
                                    const h = Math.floor(diffMins / 60);
                                    const m = diffMins % 60;
                                    relStr = m > 0 ? `in ${h}h ${m}m` : `in ${h}h`;
                                } else {
                                    relStr = `in ${diffMins} min${diffMins > 1 ? 's' : ''}`;
                                }
                            } else if (diffMins >= -15) {
                                relStr = 'Arriving at site';
                                badgeBg = 'background: rgba(59, 130, 246, 0.1); color: #2563eb; border-color: rgba(59, 130, 246, 0.3);';
                            } else {
                                const past = Math.abs(diffMins);
                                if (past >= 60) {
                                    const h = Math.floor(past / 60);
                                    const m = past % 60;
                                    relStr = m > 0 ? `${h}h ${m}m past ETA` : `${h}h past ETA`;
                                } else {
                                    relStr = `${past}m past ETA`;
                                }
                                badgeBg = 'background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.3);';
                            }
                            etaHtml = `
                                <div style="margin-top: 6px; padding: 4px 8px; border-radius: 8px; border: 1px solid; display: flex; align-items: center; justify-content: space-between; font-size: 11px; ${badgeBg}" title="Estimated return to site (round trip + 20m unloading allowance)">
                                    <span style="font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa-regular fa-clock"></i> Return ETA:
                                    </span>
                                    <span style="font-weight: 700; font-family: monospace;">${timeStr} <span style="font-weight: 500; font-size: 10px; opacity: 0.85;">(${relStr})</span></span>
                                </div>
                            `;
                        }
                    } catch (e) {}
                }

                const isDark = document.documentElement.classList.contains('dark');
                const titleColor = isDark ? '#f9fafb' : '#111827';
                const subColor = isDark ? '#d1d5db' : '#4b5563';
                const mutedColor = isDark ? '#9ca3af' : '#6b7280';
                const borderColor = isDark ? '#374151' : '#e5e7eb';

                const popupContent = `
                    <div class="osm-popup-card" style="padding: 8px 10px; min-width: 200px; max-width: 270px; font-family: inherit;">
                        <div style="font-weight: 700; font-size: 13px; color: ${titleColor}; display: flex; align-items: center; justify-content: space-between; padding-bottom: 5px; border-bottom: 1px solid ${borderColor};">
                            <span>${truck.truck_code}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full text-white font-semibold ${statusBadgeBg}">${truck.status}</span>
                        </div>
                        <div style="font-size: 12px; color: ${subColor}; margin-top: 5px; font-weight: 500;">
                            <i class="fa-regular fa-user mr-1.5 text-blue-500"></i>${truck.driver_name || 'Unassigned'}
                        </div>
                        <div style="font-size: 11.5px; color: ${mutedColor}; margin-top: 3px;">
                            <i class="fa-solid fa-location-dot mr-1.5 text-rose-500"></i>${truck.current_location || 'San Leonardo'}
                        </div>
                        ${truck.destination ? '<div style="font-size: 12px; font-weight: 600; color: #6366f1; margin-top: 4px;"><i class="fa-solid fa-flag-checkered mr-1.5"></i>To: ' + truck.destination + '</div>' : ''}
                        ${etaHtml}
                        ${truck.speed ? '<div style="font-size: 11px; color: ' + mutedColor + '; margin-top: 4px;"><i class="fa-solid fa-gauge-high mr-1.5 text-blue-400"></i>Speed: ' + truck.speed + ' mph</div>' : ''}
                    </div>
                `;
                const latLng = [parseFloat(truck.latitude), parseFloat(truck.longitude)];

                if (truckMarkers[truck.truck_code]) {
                    // Smoothly update existing marker position
                    truckMarkers[truck.truck_code].setLatLng(latLng);
                    truckMarkers[truck.truck_code].setPopupContent(popupContent);
                } else {
                    // Create new marker
                    truckMarkers[truck.truck_code] = L.marker(latLng, {
                            icon: customIcon
                        })
                        .addTo(map)
                        .bindPopup(popupContent);
                }
            });
        }

        function refreshMap() {
            if (!map) return;
            fetch('get_tracking_data.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderMapMarkers(data.trucks);
                        // Update the last-refreshed badge if it exists
                        const badge = document.getElementById('map-last-updated');
                        if (badge) {
                            const now = new Date();
                            badge.textContent = 'Last updated: ' + now.toLocaleTimeString();
                        }
                    }
                })
                .catch(err => console.warn('Map refresh error:', err));
        }

        function focusTruck(lat, lng, truckCode) {
            switchTab('tracking');
            if (lat != 0 && lng != 0) {
                setTimeout(() => {
                    if (map) {
                        map.flyTo([lat, lng], 15, {
                            animate: true,
                            duration: 1.2
                        });
                        // Open the marker's popup to highlight the exact truck
                        if (truckCode && truckMarkers && truckMarkers[truckCode]) {
                            truckMarkers[truckCode].openPopup();
                        }
                    }
                }, 300);
            } else {
                if (typeof showToast === 'function') {
                    showToast("Location data not available for this truck yet.", "warning");
                } else {
                    alert("Location data not available for this truck yet.");
                }
            }
        }

        // ===== RECENTER FLEET MAP =====
        function recenterMap() {
            if (!map) {
                switchTab('tracking');
                setTimeout(recenterMap, 300);
                return;
            }

            map.invalidateSize();

            const bounds = L.latLngBounds();
            bounds.extend([15.359042, 120.965016]); // Central Garage (Quarry)

            let validTruckCount = 0;
            if (typeof trackingData !== 'undefined' && Array.isArray(trackingData)) {
                trackingData.forEach(t => {
                    const lat = parseFloat(t.latitude);
                    const lng = parseFloat(t.longitude);
                    if (lat && lng && lat !== 0) {
                        bounds.extend([lat, lng]);
                        validTruckCount++;
                    }
                });
            }

            if (validTruckCount > 0) {
                map.flyToBounds(bounds, {
                    padding: [60, 60],
                    maxZoom: 15,
                    duration: 1.2
                });
            } else {
                map.flyTo([15.359042, 120.965016], 14, {
                    duration: 1.2
                });
            }

            if (typeof showToast === 'function') {
                showToast("Map centered on fleet & garage", "info", 2000);
            }
        }


        function switchDispatchTab(tab) {
            document.getElementById('dispatch-grid-active').classList.add('hidden');
            document.getElementById('dispatch-grid-requests').classList.add('hidden');
            document.getElementById('dispatch-grid-completed').classList.add('hidden');

            document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition";
            document.getElementById('btn-tab-requests').className = "px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition relative";
            document.getElementById('btn-tab-completed').className = "px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition";

            if (tab === 'active') {
                document.getElementById('dispatch-grid-active').classList.remove('hidden');
                document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition";
            } else if (tab === 'requests') {
                document.getElementById('dispatch-grid-requests').classList.remove('hidden');
                document.getElementById('btn-tab-requests').className = "px-6 py-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition relative";
            } else {
                document.getElementById('dispatch-grid-completed').classList.remove('hidden');
                document.getElementById('btn-tab-completed').className = "px-6 py-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition";
            }

            if (typeof filterDispatches === 'function') {
                filterDispatches();
            }
        }

        function toggleModal(modalID, show) {
            const modal = document.getElementById(modalID);
            if (show) {
                modal.classList.remove('hidden');
                if (modalID === 'dispatchModal') setTimeout(() => document.getElementById('rfidInput').focus(), 100);
                if (modalID === 'addTruckModal') setTimeout(() => document.getElementById('newTruckRfidInput').focus(), 100);
            } else {
                modal.classList.add('hidden');
            }
        }

        function getInitialsJS(name) {
            return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        }

        let currentViewingDriver = null;

        function openViewDriverModal(driver) {
            currentViewingDriver = driver;

            // Handle profile photo vs initials avatar
            const photoEl    = document.getElementById('vd-photo');
            const initialsEl = document.getElementById('vd-initials');
            if (driver.profile_photo) {
                // Build URL relative to admin dashboard (one level up reaches CAPSTONE root)
                const photoUrl = '../' + driver.profile_photo + '?v=' + Date.now();
                if (photoEl) {
                    photoEl.src = photoUrl;
                    photoEl.alt = driver.name;
                    photoEl.classList.remove('hidden');
                }
                if (initialsEl) initialsEl.classList.add('hidden');
            } else {
                if (photoEl) photoEl.classList.add('hidden');
                if (initialsEl) {
                    initialsEl.classList.remove('hidden');
                    initialsEl.innerText = getInitialsJS(driver.name);
                }
            }

            document.getElementById('vd-name').innerText = driver.name;
            document.getElementById('vd-cdl').innerText = driver.cdl_number || 'N/A';
            document.getElementById('vd-status').innerText = driver.status;
            document.getElementById('vd-phone').innerText = driver.phone || 'N/A';
            document.getElementById('vd-truck').innerText = driver.truck_code || 'None assigned';

            document.getElementById('vd-deliveries').innerText = driver.total_deliveries ? driver.total_deliveries : 0;
            document.getElementById('vd-ontime').innerText = (driver.on_time_pct ? parseFloat(driver.on_time_pct).toFixed(1) : '100.0') + '%';

            const tripsContainer = document.getElementById('vd-recent-trips');
            const viewAllBtnContainer = document.getElementById('vd-view-all-trips-btn-container');
            const viewAllBtnText = document.getElementById('vd-view-all-btn-text');

            if (tripsContainer) {
                tripsContainer.innerHTML = '';
                const allTrips = driver.recent_trips || [];
                // Limit to two (2) deliveries in the driver card details modal
                const displayTrips = allTrips.slice(0, 2);

                if (displayTrips.length > 0) {
                    displayTrips.forEach(trip => {
                        let statusBadge = (trip.status === 'Delivered' || !trip.status) ?
                            `<span class="ml-2 text-green-600 font-semibold bg-green-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-[10px] uppercase"><i class="fa-solid fa-check mr-1"></i>Delivered</span>` :
                            `<span class="ml-2 text-blue-500 font-semibold bg-blue-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-[10px] uppercase"><i class="fa-solid fa-truck-fast mr-1"></i>${trip.status}</span>`;

                        const distNum = parseFloat(trip.distance_km || 0);
                        const distanceDisplay = distNum > 0 ? `${distNum.toFixed(1)} km` : 'Distance N/A';
                        const payNum = parseFloat(trip.pay_amount || 0);
                        const payDisplay = payNum > 0 ? `₱${payNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '₱0.00';

                        const durationBadge = trip.duration ?
                            `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-gray-800 px-2 py-0.5 rounded-md" title="Delivery Duration"><i class="fa-regular fa-clock text-[10px]"></i><span>${trip.duration}</span></span>` : '';

                        tripsContainer.innerHTML += `
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/60 p-3 rounded-xl border-l-4 border-blue-500 shadow-sm mb-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="font-bold text-gray-800 dark:text-gray-200 text-sm flex items-center flex-wrap gap-1">
                                    <span class="truncate">${trip.destination}</span>
                                    ${statusBadge}
                                </div>
                                <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span><i class="fa-regular fa-calendar mr-1"></i>${trip.trip_date || 'Recent'}</span>
                                    <span class="inline-flex items-center text-blue-600 dark:text-blue-400 font-semibold">
                                        <i class="fa-solid fa-route mr-1"></i>${distanceDisplay}
                                    </span>
                                    ${durationBadge}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 pl-2">
                                <div class="text-[10px] text-gray-400 dark:text-gray-400 font-bold uppercase tracking-wider">Trip Pay</div>
                                <div class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">${payDisplay}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500">₱10 / km</div>
                            </div>
                        </div>`;
                    });

                    if (viewAllBtnContainer) {
                        viewAllBtnContainer.classList.remove('hidden');
                        if (viewAllBtnText) {
                            viewAllBtnText.textContent = `View All Deliveries (${allTrips.length})`;
                        }
                    }
                } else {
                    tripsContainer.innerHTML = '<div class="text-xs text-gray-500 dark:text-gray-400 italic mt-2">No trips recorded for this driver.</div>';
                    if (viewAllBtnContainer) viewAllBtnContainer.classList.add('hidden');
                }
            }

            // Populate payroll overview in view driver modal
            const netPay = parseFloat(driver.net_earnings || 0);
            const grossPay = parseFloat(driver.gross_earnings || 0);
            const caAmount = parseFloat(driver.approved_cash_advances || 0);
            const remBal = parseFloat(driver.remaining_balance || 0);

            const netEl = document.getElementById('vd-payroll-net');
            const breakdownEl = document.getElementById('vd-payroll-breakdown');
            const settleBtn = document.getElementById('vd-settle-btn');

            if (netEl) netEl.innerText = `₱${netPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            if (breakdownEl) breakdownEl.innerText = `Remaining Bal: ₱${remBal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} • CA: -₱${caAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            if (settleBtn) {
                if (netPay > 0) {
                    settleBtn.disabled = false;
                    settleBtn.className = 'px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm flex items-center gap-1.5 flex-shrink-0 cursor-pointer';
                    settleBtn.innerHTML = '<i class="fa-solid fa-money-bill-transfer"></i><span>Settle</span>';
                } else {
                    settleBtn.disabled = true;
                    settleBtn.className = 'px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 transition flex items-center gap-1.5 flex-shrink-0 cursor-not-allowed';
                    settleBtn.innerHTML = '<i class="fa-solid fa-check"></i><span>Settled</span>';
                }
            }

            toggleModal('viewDriverModal', true);
        }

        let currentPerformanceDriverId = null;

        function openDriverPerformanceModal(driver) {
            if (!driver) return;
            currentPerformanceDriverId = driver.id;

            // Header info
            const nameEl = document.getElementById('dp-driver-name');
            const cdlEl = document.getElementById('dp-driver-cdl');
            const truckEl = document.getElementById('dp-driver-truck');
            const ratingNumEl = document.getElementById('dp-rating-num');
            const photoEl = document.getElementById('dp-driver-photo');
            const initialsEl = document.getElementById('dp-driver-initials');

            if (nameEl) nameEl.innerText = driver.name || 'Driver';
            if (cdlEl) cdlEl.innerHTML = `<i class="fa-solid fa-id-card mr-1"></i>CDL: ${driver.cdl_number || 'N/A'}`;
            if (truckEl) truckEl.innerHTML = `<i class="fa-solid fa-truck mr-1"></i>Truck: ${driver.truck_code || 'Unassigned'}`;
            if (ratingNumEl) ratingNumEl.innerText = (parseFloat(driver.rating !== undefined ? driver.rating : (stats.rating || 5.0))).toFixed(1);

            // Avatar
            if (driver.profile_photo) {
                if (photoEl) {
                    photoEl.src = '../' + driver.profile_photo + '?v=' + Date.now();
                    photoEl.classList.remove('hidden');
                }
                if (initialsEl) initialsEl.classList.add('hidden');
            } else {
                if (photoEl) photoEl.classList.add('hidden');
                if (initialsEl) {
                    initialsEl.classList.remove('hidden');
                    initialsEl.innerText = getInitialsJS(driver.name);
                }
            }

            // Stats
            const stats = driver.performance_stats || {};
            const thisWeekKm = parseFloat(stats.this_week_km || 0);
            const thisWeekTrips = parseInt(stats.this_week_dispatches || 0);
            const avgKm = parseFloat(stats.avg_km_per_week || 0);
            const avgTrips = parseFloat(stats.avg_dispatches_per_week || 0);
            const ontime = parseFloat(stats.on_time_pct !== undefined ? stats.on_time_pct : (driver.on_time_pct !== undefined ? driver.on_time_pct : 100));
            const lifetimeKm = parseFloat(stats.total_lifetime_km || 0);
            const lifetimeTrips = parseInt(stats.total_lifetime_dispatches || 0);
            const activeWeeks = parseInt(stats.active_weeks_count || 0);

            const thisWeekKmEl = document.getElementById('dp-this-week-km');
            const thisWeekTripsEl = document.getElementById('dp-this-week-trips');
            const avgKmEl = document.getElementById('dp-avg-km');
            const avgTripsEl = document.getElementById('dp-avg-trips');
            const ontimeEl = document.getElementById('dp-ontime');
            const lifetimeKmEl = document.getElementById('dp-lifetime-km');
            const lifetimeTripsEl = document.getElementById('dp-lifetime-trips');
            const activeWeeksEl = document.getElementById('dp-active-weeks');
            const weeksCountEl = document.getElementById('dp-weeks-count');

            if (thisWeekKmEl) thisWeekKmEl.innerText = `${thisWeekKm.toFixed(1)} km`;
            if (thisWeekTripsEl) thisWeekTripsEl.innerText = `${thisWeekTrips} ${thisWeekTrips === 1 ? 'Trip' : 'Trips'}`;
            if (avgKmEl) avgKmEl.innerText = `${avgKm.toFixed(1)} km`;
            if (avgTripsEl) avgTripsEl.innerText = `${avgTrips.toFixed(1)} / wk`;
            if (ontimeEl) ontimeEl.innerText = `${ontime.toFixed(1)}%`;
            if (lifetimeKmEl) lifetimeKmEl.innerText = `${lifetimeKm.toFixed(1)} km`;
            if (lifetimeTripsEl) lifetimeTripsEl.innerText = `${lifetimeTrips} ${lifetimeTrips === 1 ? 'Trip' : 'Trips'}`;
            if (activeWeeksEl) activeWeeksEl.innerText = `${activeWeeks} ${activeWeeks === 1 ? 'Week' : 'Weeks'}`;

            // Weekly history table
            const tableBody = document.getElementById('dp-weekly-table-body');
            const emptyEl = document.getElementById('dp-weekly-empty');
            const history = stats.weekly_history || [];

            if (weeksCountEl) {
                weeksCountEl.innerText = `${history.length} active ${history.length === 1 ? 'week' : 'weeks'} recorded`;
            }

            if (tableBody) {
                tableBody.innerHTML = '';
                if (history.length > 0) {
                    if (emptyEl) emptyEl.classList.add('hidden');
                    history.forEach(w => {
                        const kmVal = parseFloat(w.total_km || 0).toFixed(1);
                        const payVal = parseFloat(w.total_pay || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        const tripsCount = parseInt(w.dispatches || 0);

                        tableBody.innerHTML += `
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar-check text-amber-500"></i>
                                        <span>${w.week_label}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <i class="fa-solid fa-truck-check text-[10px]"></i>
                                        ${tripsCount} ${tripsCount === 1 ? 'Trip' : 'Trips'}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-200">
                                    ${kmVal} km
                                </td>
                                <td class="px-4 py-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400">
                                    ₱${payVal}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    if (emptyEl) emptyEl.classList.remove('hidden');
                }
            }

            toggleModal('driverPerformanceModal', true);
        }

        function printCurrentDriverTrips() {
            if (currentPerformanceDriverId) {
                window.open(`print_driver_trips.php?driver_id=${currentPerformanceDriverId}`, '_blank');
            }
        }

        let currentSettleTotalPayable = 0;

        function triggerSettleFromModal() {
            if (!currentViewingDriver) return;
            const netPay = parseFloat(currentViewingDriver.net_earnings || 0);
            if (netPay <= 0) return;
            toggleModal('viewDriverModal', false);
            openSettlePayrollModal(
                currentViewingDriver.id,
                currentViewingDriver.name,
                currentViewingDriver.gross_earnings || 0,
                currentViewingDriver.approved_cash_advances || 0,
                currentViewingDriver.net_earnings || 0,
                currentViewingDriver.remaining_balance || 0
            );
        }

        function openSettlePayrollModal(driverId, driverName, gross, advances, net, prevBalance) {
            const netNum = parseFloat(net || 0);
            if (netNum <= 0) {
                return;
            }

            const idEl = document.getElementById('sp-driver-id');
            const nameEl = document.getElementById('sp-driver-name');
            if (idEl) idEl.value = driverId;
            if (nameEl) nameEl.textContent = driverName;

            const grossNum = parseFloat(gross || 0);
            const advNum = parseFloat(advances || 0);
            const prevBalNum = parseFloat(prevBalance || 0);

            currentSettleTotalPayable = netNum;

            const grossEl = document.getElementById('sp-gross-amount');
            const advEl = document.getElementById('sp-advances-amount');
            const netPayEl = document.getElementById('sp-net-pay');
            const prevBalRow = document.getElementById('sp-previous-balance-row');
            const prevBalEl = document.getElementById('sp-previous-balance');
            const claimedInput = document.getElementById('sp-claimed-input');

            if (grossEl) grossEl.textContent = `₱${grossNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            if (advEl) advEl.textContent = `-₱${advNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            if (netPayEl) netPayEl.textContent = `₱${netNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            if (prevBalRow && prevBalEl) {
                if (prevBalNum > 0) {
                    prevBalRow.classList.remove('hidden');
                    prevBalEl.textContent = `+₱${prevBalNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                } else {
                    prevBalRow.classList.add('hidden');
                }
            }

            if (claimedInput) {
                claimedInput.value = netNum.toFixed(2);
                claimedInput.max = netNum.toFixed(2);
            }
            recalculateSettleRemaining();

            toggleModal('settlePayrollModal', true);
        }

        function recalculateSettleRemaining() {
            const claimedInput = document.getElementById('sp-claimed-input');
            const remEl = document.getElementById('sp-remaining-balance');
            if (!claimedInput || !remEl) return;

            let claimedVal = parseFloat(claimedInput.value) || 0;
            if (claimedVal < 0) claimedVal = 0;
            if (claimedVal > currentSettleTotalPayable) {
                claimedVal = currentSettleTotalPayable;
                claimedInput.value = currentSettleTotalPayable.toFixed(2);
            }

            const remaining = Math.max(0, currentSettleTotalPayable - claimedVal);
            remEl.textContent = `₱${remaining.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        function setSettleClaimFull() {
            const claimedInput = document.getElementById('sp-claimed-input');
            if (claimedInput) {
                claimedInput.value = currentSettleTotalPayable.toFixed(2);
                recalculateSettleRemaining();
            }
        }

        function setSettleClaimPercent(pct) {
            const claimedInput = document.getElementById('sp-claimed-input');
            if (claimedInput) {
                const val = (currentSettleTotalPayable * pct);
                claimedInput.value = val.toFixed(2);
                recalculateSettleRemaining();
            }
        }

        function openAdjustBalanceModal(driverId, driverName, currentBalance) {
            const idEl = document.getElementById('ab-driver-id');
            const nameEl = document.getElementById('ab-driver-name');
            const curBalEl = document.getElementById('ab-current-balance');

            if (idEl) idEl.value = driverId;
            if (nameEl) nameEl.textContent = driverName;
            const balNum = parseFloat(currentBalance || 0);
            if (curBalEl) curBalEl.textContent = `₱${balNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            toggleModal('adjustDriverBalanceModal', true);
        }

        function openAllDriverDeliveriesModal() {
            if (!currentViewingDriver) return;
            const driver = currentViewingDriver;

            const nameEl = document.getElementById('ad-driver-name');
            if (nameEl) nameEl.textContent = driver.name;

            const allTrips = driver.all_trips || driver.recent_trips || [];

            // Populate unique months in the month filter dropdown
            const monthSelect = document.getElementById('ad-month-select');
            if (monthSelect) {
                const monthSet = new Set();
                allTrips.forEach(t => {
                    const dStr = t.trip_date || (t.transit_end_time ? t.transit_end_time.substring(0, 10) : (t.transit_start_time ? t.transit_start_time.substring(0, 10) : ''));
                    if (dStr && dStr.length >= 7) {
                        monthSet.add(dStr.substring(0, 7));
                    }
                });

                const sortedMonths = Array.from(monthSet).sort().reverse();
                let optionsHtml = '<option value="all">All Deliveries (All Time)</option>';
                sortedMonths.forEach(ym => {
                    const [y, m] = ym.split('-');
                    const dateObj = new Date(parseInt(y), parseInt(m) - 1, 1);
                    const label = dateObj.toLocaleString('default', { month: 'long', year: 'numeric' });
                    optionsHtml += `<option value="${ym}">${label}</option>`;
                });
                monthSelect.innerHTML = optionsHtml;
                monthSelect.value = 'all';
            }

            filterAllDriverDeliveriesByMonth('all');
            toggleModal('allDriverDeliveriesModal', true);
        }

        function filterAllDriverDeliveriesByMonth(selectedMonth) {
            if (!currentViewingDriver) return;
            const allTrips = currentViewingDriver.all_trips || currentViewingDriver.recent_trips || [];

            const filteredTrips = (selectedMonth === 'all')
                ? allTrips
                : allTrips.filter(t => {
                    const dStr = t.trip_date || (t.transit_end_time ? t.transit_end_time.substring(0, 10) : (t.transit_start_time ? t.transit_start_time.substring(0, 10) : ''));
                    return dStr && dStr.startsWith(selectedMonth);
                });

            let totalKm = 0;
            let totalPay = 0;
            filteredTrips.forEach(t => {
                totalKm += parseFloat(t.distance_km || 0);
                totalPay += parseFloat(t.pay_amount || 0);
            });

            const countBadge = document.getElementById('ad-trip-count-badge');
            if (countBadge) countBadge.textContent = `${filteredTrips.length} Trips`;

            const sumTripsEl = document.getElementById('ad-sum-trips');
            if (sumTripsEl) sumTripsEl.textContent = filteredTrips.length;

            const sumDistEl = document.getElementById('ad-sum-distance');
            if (sumDistEl) sumDistEl.textContent = `${totalKm.toFixed(1)} km`;

            const sumPayEl = document.getElementById('ad-sum-pay');
            if (sumPayEl) sumPayEl.textContent = `₱${totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            const monthCountPill = document.getElementById('ad-month-count-pill');
            if (monthCountPill) {
                monthCountPill.textContent = `${filteredTrips.length} ${filteredTrips.length === 1 ? 'trip' : 'trips'}`;
            }

            const listEl = document.getElementById('ad-all-trips-list');
            if (listEl) {
                listEl.innerHTML = '';
                if (filteredTrips.length > 0) {
                    filteredTrips.forEach((trip, idx) => {
                        let statusBadge = (trip.status === 'Delivered' || !trip.status) ?
                            `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"><i class="fa-solid fa-check mr-1 text-[8px]"></i>Delivered</span>` :
                            `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"><i class="fa-solid fa-truck-fast mr-1 text-[8px]"></i>${trip.status}</span>`;

                        const distNum = parseFloat(trip.distance_km || 0);
                        const distanceDisplay = distNum > 0 ? `${distNum.toFixed(1)} km` : 'Distance N/A';
                        const payNum = parseFloat(trip.pay_amount || 0);
                        const payDisplay = payNum > 0 ? `₱${payNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '₱0.00';

                        const durationBadge = trip.duration ?
                            `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-gray-800 px-2 py-0.5 rounded-md" title="Delivery Duration"><i class="fa-regular fa-clock text-[10px]"></i><span>${trip.duration}</span></span>` : '';

                        listEl.innerHTML += `
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/60 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-100/70 dark:hover:bg-gray-700/90 hover:border-blue-300 dark:hover:border-blue-500/50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs text-gray-400 font-bold">#${idx + 1}</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">${trip.destination}</span>
                                        ${statusBadge}
                                    </div>
                                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                                        <span><i class="fa-regular fa-calendar mr-1"></i>${trip.trip_date || 'Recent'}</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                                            <i class="fa-solid fa-route mr-1"></i>${distanceDisplay}
                                        </span>
                                        ${durationBadge}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Trip Pay</div>
                                    <div class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">${payDisplay}</div>
                                    <div class="text-[10px] text-gray-400">₱10 / km</div>
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    listEl.innerHTML = '<div class="text-center py-10 text-xs text-gray-400 dark:text-gray-500 italic"><i class="fa-solid fa-inbox text-2xl mb-1 text-gray-300 dark:text-gray-600 block"></i>No deliveries found for this month.</div>';
                }
            }
        }

        function openPrintDriverTripsModal(driverId, driverName) {
            const dr = currentViewingDriver || { id: driverId, name: driverName || 'Driver' };
            const modal = document.getElementById('printDriverTripsModal');
            if (!modal) return;

            const idInput = document.getElementById('pdt_driver_id');
            const nameEl = document.getElementById('pdt_driver_name_display');
            if (idInput) idInput.value = dr.id;
            if (nameEl) nameEl.textContent = dr.name + (dr.cdl_number ? ' (CDL: ' + dr.cdl_number + ')' : '');

            const periodSelect = document.getElementById('pdt_period');
            if (periodSelect) periodSelect.value = 'monthly';
            updatePdtDateInputs('monthly');

            toggleModal('printDriverTripsModal', true);
        }

        function updatePdtDateInputs(period) {
            const customDiv = document.getElementById('pdt_custom_date_range');
            const startInput = document.getElementById('pdt_start_date');
            const endInput = document.getElementById('pdt_end_date');
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];

            if (period === 'today') {
                if (startInput) startInput.value = todayStr;
                if (endInput) endInput.value = todayStr;
                if (customDiv) customDiv.classList.add('hidden');
            } else if (period === 'weekly') {
                const firstDayOfWeek = new Date(today);
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                firstDayOfWeek.setDate(diff);
                if (startInput) startInput.value = firstDayOfWeek.toISOString().split('T')[0];
                if (endInput) endInput.value = todayStr;
                if (customDiv) customDiv.classList.add('hidden');
            } else if (period === 'monthly') {
                const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                if (startInput) startInput.value = firstDayOfMonth.toISOString().split('T')[0];
                if (endInput) endInput.value = todayStr;
                if (customDiv) customDiv.classList.add('hidden');
            } else if (period === 'all') {
                if (startInput) startInput.value = '';
                if (endInput) endInput.value = '';
                if (customDiv) customDiv.classList.add('hidden');
            } else if (period === 'custom') {
                if (customDiv) customDiv.classList.remove('hidden');
            }
        }

        function submitPrintDriverTrips() {
            const driverId = document.getElementById('pdt_driver_id').value;
            const period = document.getElementById('pdt_period').value;
            const startDate = document.getElementById('pdt_start_date').value;
            const endDate = document.getElementById('pdt_end_date').value;
            const status = document.getElementById('pdt_status').value;

            let url = `print_driver_trips.php?driver_id=${encodeURIComponent(driverId)}&period=${encodeURIComponent(period)}&status=${encodeURIComponent(status)}`;
            if (period === 'custom' || (startDate && endDate)) {
                url += `&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
            }

            window.open(url, '_blank');
            toggleModal('printDriverTripsModal', false);
        }

        function openContactDriverModal(driver) {
            document.getElementById('cd-title').innerText = 'Contact ' + driver.name.split(' ')[0];
            document.getElementById('cd-phone-text').innerText = 'Call ' + (driver.phone || 'N/A');
            document.getElementById('cd-phone-link').href = driver.phone ? 'tel:' + driver.phone : '#';
            toggleModal('contactDriverModal', true);
        }

        function openResignDriverModal(id, name) {
            const nameEl = document.getElementById('dd-name');
            if (nameEl) nameEl.innerText = name;
            const idEl = document.getElementById('delete_driver_id');
            if (idEl) idEl.value = id;
            toggleModal('resignDriverModal', true);
        }
        function openDeleteDriverModal(id, name) {
            openResignDriverModal(id, name);
        }

        function openResetPasswordModal(id, name) {
            document.getElementById('rp-name').innerText = name;
            document.getElementById('reset_password_driver_id').value = id;
            const pwdInput = document.getElementById('new_driver_password');
            if (pwdInput) {
                pwdInput.value = '';
                pwdInput.dispatchEvent(new Event('input'));
            }
            toggleModal('resetPasswordModal', true);
        }

        function openDecommissionTruckModal(truckId, truckCode) {
            const codeEl = document.getElementById('dt-truck-code');
            if (codeEl) codeEl.innerText = truckCode;
            const idEl = document.getElementById('delete_truck_id');
            if (idEl) idEl.value = truckId;
            toggleModal('decommissionTruckModal', true);
        }
        function openDeleteTruckModal(truckId, truckCode) {
            openDecommissionTruckModal(truckId, truckCode);
        }

        function openMarkFixedModal(truckId, truckCode) {
            document.getElementById('mf-truck-code').innerText = truckCode;
            document.getElementById('mf_truck_id').value = truckId;
            toggleModal('markFixedModal', true);
        }

        function openUpdateStatusModal(truckId, currentStatus, truckCode) {
            document.getElementById('us-truck-code').innerText = truckCode;
            document.getElementById('update_status_truck_id').value = truckId;
            document.getElementById('update_status_select').value = currentStatus;
            toggleModal('updateStatusModal', true);
        }

        function openUpdateDriverStatusModal(driverId, currentStatus, driverName) {
            document.getElementById('uds-driver-name').innerText = driverName;
            document.getElementById('update_status_driver_id').value = driverId;
            document.getElementById('update_driver_status_select').value = currentStatus;
            toggleModal('updateDriverStatusModal', true);
        }

        function openSwitchTruckModal(driverId, driverName, truckCode) {
            document.getElementById('st-driver-name').innerText = driverName;
            document.getElementById('st-truck-code').innerText = truckCode || 'None';
            document.getElementById('switch_truck_driver_id').value = driverId;

            // Auto-detect redirect tab
            const activeTabContent = document.querySelector('.tab-content:not(.hidden)');
            if (activeTabContent) {
                const tabId = activeTabContent.id.replace('view-', '');
                document.getElementById('switch_truck_redirect_tab').value = tabId;
            }

            toggleModal('switchTruckModal', true);
        }

        function openApproveCancelModal(dispatchId, ticketNumber) {
            document.getElementById('ac-ticket-number').innerText = ticketNumber;
            document.getElementById('approve_cancel_dispatch_id').value = dispatchId;
            toggleModal('approveCancelModal', true);
        }

        function openDeleteDispatchModal(dispatchId, ticketNumber) {
            document.getElementById('dd-ticket-number').innerText = ticketNumber;
            document.getElementById('delete_dispatch_id').value = dispatchId;
            toggleModal('deleteDispatchModal', true);
        }

        function markDispatchDelivered(dispatchId, ticketNumber) {
            document.getElementById('cd-ticket-number').innerText = ticketNumber;
            document.getElementById('complete_dispatch_id').value = dispatchId;
            toggleModal('completeDispatchModal', true);
        }

        function openAssignCheckerModal(orderId, orderNumber) {
            document.getElementById('ac-order-number').innerText = orderNumber;
            document.getElementById('ac_order_id').value = orderId;
            toggleModal('assignCheckerModal', true);
        }

        function openCancelOrderModal(orderId, orderNumber) {
            document.getElementById('co-order-number').innerText = orderNumber;
            document.getElementById('co_order_id').value = orderId;
            toggleModal('cancelOrderModal', true);
        }

        function openResignCheckerModal(checkerId, checkerName) {
            const nameEl = document.getElementById('dc-checker-name');
            if (nameEl) nameEl.innerText = checkerName;
            const idEl = document.getElementById('delete_checker_id');
            if (idEl) idEl.value = checkerId;
            toggleModal('resignCheckerModal', true);
        }
        function openDeleteCheckerModal(checkerId, checkerName) {
            openResignCheckerModal(checkerId, checkerName);
        }

        try {
            const weeklyData = <?= json_encode($weeklyData ?? []); ?>;
            const fleetStatusData = <?= json_encode($fleetStatusData ?? []); ?>;
            const efficiencyData = <?= json_encode($efficiencyData ?? []); ?>;
            if (document.getElementById('weeklyChart') && weeklyData.length > 0) {
                new Chart(document.getElementById('weeklyChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: weeklyData.map(row => row.day_name),
                        datasets: [{
                            label: 'Total Dispatches',
                            data: weeklyData.map(row => row.total_dispatches),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }, {
                            label: 'Completed',
                            data: weeklyData.map(row => row.completed),
                            backgroundColor: '#22c55e',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
                const fleetCounts = fleetStatusData.map(row => row.count);
                const fleetTotal = fleetCounts.reduce((a, b) => a + parseInt(b), 0);
                const pieLabels = fleetStatusData.map(row => `${row.status}: ${fleetTotal > 0 ? Math.round((row.count / fleetTotal) * 100) : 0}%`);
                new Chart(document.getElementById('fleetChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: fleetCounts,
                            backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e', '#f97316'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    padding: 20
                                }
                            }
                        }
                    }
                });
                new Chart(document.getElementById('efficiencyChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: efficiencyData.map(row => row.month_name),
                        datasets: [{
                            label: 'Efficiency %',
                            data: efficiencyData.map(row => row.efficiency_pct),
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.15)',
                            pointBorderColor: '#22c55e',
                            pointBackgroundColor: '#ffffff',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
            }

            const financeReports = <?= json_encode($financeReports ?? []); ?>;
            if (document.getElementById('deliveredTripsReportChart') && financeReports.length > 0) {
                new Chart(document.getElementById('deliveredTripsReportChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: financeReports.map(row => row.month_name),
                        datasets: [{
                            label: 'Delivered Trips',
                            data: financeReports.map(row => row.deliveries),
                            backgroundColor: '#f97316',
                            borderColor: '#ea580c',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById('driverPayrollReportChart') && financeReports.length > 0) {
                new Chart(document.getElementById('driverPayrollReportChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: financeReports.map(row => row.month_name),
                        datasets: [{
                            label: 'Driver Payroll (₱)',
                            data: financeReports.map(row => row.payroll),
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.15)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#22c55e'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                },
                                grid: {
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
            }
        } catch (err) {
            console.error("Dashboard Charts Error:", err);
        }



        // ===== REAL-TIME PLATE NUMBER DUPLICATE CHECK =====
        document.addEventListener("DOMContentLoaded", function() {
            const plateInput = document.getElementById('newTruckPlateInput');
            const plateFeedback = document.getElementById('plateCheckFeedback');
            const addTruckSubmitBtn = document.querySelector('#addTruckModal button[type="submit"]');
            if (!plateInput || !plateFeedback) return;

            let plateCheckTimer = null;

            plateInput.addEventListener('input', function() {
                const val = this.value.trim();
                clearTimeout(plateCheckTimer);

                // Reset state
                plateFeedback.innerHTML = '';
                plateInput.classList.remove('border-red-500', 'border-green-500');
                plateInput.classList.add('border-gray-300');
                if (addTruckSubmitBtn) addTruckSubmitBtn.disabled = false;

                if (val.length === 0) return;

                plateFeedback.innerHTML = '<span class="text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Checking...</span>';

                plateCheckTimer = setTimeout(() => {
                    fetch('check_plate.php?plate=' + encodeURIComponent(val))
                        .then(r => r.json())
                        .then(data => {
                            if (data.exists) {
                                plateFeedback.innerHTML = '<span class="text-red-500 font-semibold"><i class="fa-solid fa-circle-xmark mr-1"></i>Plate number already registered.</span>';
                                plateInput.classList.remove('border-gray-300', 'border-green-500');
                                plateInput.classList.add('border-red-500', 'focus:ring-red-500');
                                if (addTruckSubmitBtn) addTruckSubmitBtn.disabled = true;
                            } else {
                                plateFeedback.innerHTML = '<span class="text-green-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>Available.</span>';
                                plateInput.classList.remove('border-gray-300', 'border-red-500');
                                plateInput.classList.add('border-green-500');
                                if (addTruckSubmitBtn) addTruckSubmitBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            plateFeedback.innerHTML = '';
                        });
                }, 400);
            });
        });

        // ===== REAL-TIME PASSWORD COMPLEXITY CHECK FOR ALL PASSWORD RESETS =====
        document.addEventListener("DOMContentLoaded", function() {
            const pwdInputs = document.querySelectorAll('#new_driver_password, #newPasswordInput, .pw-complexity-input');
            pwdInputs.forEach(pwdInput => {
                const container = pwdInput.closest('div');
                if (!container) return;
                const reqLength = container.querySelector('#req-length, .req-length') || container.parentElement.querySelector('#req-length, .req-length');
                const reqUpper = container.querySelector('#req-uppercase, .req-uppercase') || container.parentElement.querySelector('#req-uppercase, .req-uppercase');
                const reqLower = container.querySelector('#req-lowercase, .req-lowercase') || container.parentElement.querySelector('#req-lowercase, .req-lowercase');
                const reqNumber = container.querySelector('#req-number, .req-number') || container.parentElement.querySelector('#req-number, .req-number');
                const modalOrForm = pwdInput.closest('form, div.bg-white, div.bg-gray-800');
                const submitBtn = modalOrForm ? modalOrForm.querySelector('button[type="submit"], #resetPasswordSubmitBtn, #btnVerifyOtp') : null;

                function updateRequirement(el, isValid) {
                    if (!el) return;
                    const icon = el.querySelector('i');
                    if (isValid) {
                        el.className = 'flex items-center text-green-600 dark:text-green-400 font-semibold';
                        if (icon) {
                            icon.className = 'fa-solid fa-check mr-2';
                        }
                    } else {
                        el.className = 'flex items-center text-gray-500 dark:text-gray-400';
                        if (icon) {
                            icon.className = 'fa-solid fa-circle text-[6px] mr-2';
                        }
                    }
                }

                pwdInput.addEventListener('input', function() {
                    const val = this.value;
                    const hasLength = val.length >= 8;
                    const hasUpper = /[A-Z]/.test(val);
                    const hasLower = /[a-z]/.test(val);
                    const hasNumber = /[0-9]/.test(val);

                    updateRequirement(reqLength, hasLength);
                    updateRequirement(reqUpper, hasUpper);
                    updateRequirement(reqLower, hasLower);
                    updateRequirement(reqNumber, hasNumber);

                    if (submitBtn) {
                        submitBtn.disabled = !(hasLength && hasUpper && hasLower && hasNumber);
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const rfidInput = document.getElementById('rfidInput');
            const truckPlate = document.getElementById('truckPlate');
            const hiddenTruckId = document.getElementById('hiddenTruckId');
            const rfidFeedback = document.getElementById('rfidFeedback');
            const dispatchForm = document.getElementById('dispatchForm');
            if (dispatchForm && rfidInput) {
                dispatchForm.addEventListener('submit', function(e) {
                    if (document.activeElement === rfidInput && rfidInput.value !== '') {
                        e.preventDefault();
                        return false;
                    }
                });
                rfidInput.addEventListener('change', function() {
                    const rfidValue = this.value.trim();
                    if (rfidValue.length > 0) {
                        rfidFeedback.innerHTML = '<span class="text-blue-500"><i class="fa-solid fa-spinner fa-spin"></i> Finding truck...</span>';
                        fetch('get_truck_by_rfid.php?rfid=' + encodeURIComponent(rfidValue)).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (data.status === 'Maintenance') {
                                        rfidFeedback.innerHTML = '<span class="text-red-500 font-bold"><i class="fa-solid fa-screwdriver-wrench"></i> This truck is in Maintenance!</span>';
                                        truckPlate.value = '';
                                        hiddenTruckId.value = '';
                                        rfidInput.value = '';
                                        return;
                                    }

                                    if (data.status !== 'Idle') {
                                        rfidFeedback.innerHTML = `<span class="text-orange-500 font-bold"><i class="fa-solid fa-circle-exclamation"></i> Truck is currently ${data.status}!</span>`;
                                        truckPlate.value = '';
                                        hiddenTruckId.value = '';
                                        rfidInput.value = '';
                                        return;
                                    }

                                    // Block dispatch if truck has no assigned driver
                                    if (!data.driver_id) {
                                        rfidFeedback.innerHTML = '<span class="text-red-500 font-bold"><i class="fa-solid fa-user-slash"></i> No driver assigned to this truck! Assign a driver before dispatching.</span>';
                                        truckPlate.value = '';
                                        hiddenTruckId.value = '';
                                        rfidInput.value = '';
                                        rfidInput.focus();
                                        return;
                                    }

                                    truckPlate.value = data.truck_code;
                                    hiddenTruckId.value = data.truck_id;

                                    const hiddenDriverId = document.getElementById('hiddenDriverId');
                                    const assignedDriverName = document.getElementById('assignedDriverName');
                                    if (hiddenDriverId && assignedDriverName) {
                                        hiddenDriverId.value = data.driver_id || '';
                                        assignedDriverName.value = data.driver_name || 'No Driver Assigned';
                                    }

                                    rfidFeedback.innerHTML = '<span class="text-green-500"><i class="fa-solid fa-check"></i> Truck matched!</span>';
                                } else {
                                    truckPlate.value = '';
                                    hiddenTruckId.value = '';
                                    rfidFeedback.innerHTML = '<span class="text-red-500"><i class="fa-solid fa-triangle-exclamation"></i> Unregistered RFID tag!</span>';
                                    rfidInput.value = '';
                                    rfidInput.focus();
                                }
                            })
                            .catch(error => {
                                rfidFeedback.innerHTML = '<span class="text-red-500">Database connection error.</span>';
                            });
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            function updateLoadingTimers() {
                const containers = document.querySelectorAll('.loading-timer-container');
                const LOADING_DURATION_MS = 20 * 60 * 1000;
                containers.forEach(container => {
                    const truckCode = container.getAttribute('data-truck-id');
                    const timerText = container.querySelector('.timer-text');
                    const timerProgress = container.querySelector('.timer-progress');
                    const timerLabel = container.querySelector('.timer-label');
                    let startTime = localStorage.getItem('loading_start_' + truckCode);
                    if (!startTime) {
                        startTime = Date.now();
                        localStorage.setItem('loading_start_' + truckCode, startTime);
                    }
                    const elapsed = Date.now() - parseInt(startTime);
                    const remaining = Math.max(0, LOADING_DURATION_MS - elapsed);
                    if (remaining > 0) {
                        const mins = Math.floor(remaining / 60000);
                        const secs = Math.floor((remaining % 60000) / 1000);
                        timerText.innerText = `${mins}:${secs.toString().padStart(2, '0')}`;
                        timerProgress.style.width = (elapsed / LOADING_DURATION_MS) * 100 + '%';
                    } else {
                        timerText.innerText = 'Ready!';
                        timerLabel.innerHTML = '<i class="fa-solid fa-check text-green-600 mr-1"></i> Loading Complete';
                        timerLabel.classList.replace('text-indigo-700', 'text-green-700');
                        timerText.classList.replace('text-indigo-700', 'text-green-700');
                        timerProgress.style.width = '100%';
                        timerProgress.classList.replace('bg-indigo-600', 'bg-green-500');
                        if (!localStorage.getItem('loading_complete_' + truckCode)) {
                            localStorage.setItem('loading_complete_' + truckCode, 'true');
                            fetch('update_transit_status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'truck_code=' + encodeURIComponent(truckCode)
                            }).then(response => response.json()).then(data => {
                                if (data.success) {
                                    window.location.reload();
                                }
                            }).catch(error => console.error("Error updating status:", error));
                        }
                    }
                });
            }
            if (document.querySelector('.loading-timer-container')) {
                setInterval(updateLoadingTimers, 1000);
                updateLoadingTimers();
            }
        });

        try {
            const financeData = <?= json_encode($financeReports ?? []); ?>;
            const deliveryData = [];
            if (financeData.length > 0 && document.getElementById('revenueReportChart')) {
                new Chart(document.getElementById('revenueReportChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: financeData.map(d => d.month_name),
                        datasets: [{
                            label: 'Driver Salaries Paid (₱)',
                            data: financeData.map(d => d.payroll),
                            backgroundColor: 'rgba(59, 130, 246, 0.15)',
                            borderColor: '#3b82f6',
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Delivered Trips Count',
                            data: financeData.map(d => d.deliveries),
                            backgroundColor: 'rgba(249, 115, 22, 0.15)',
                            borderColor: '#f97316',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                grid: {
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
                if (document.getElementById('deliveryReportChart')) {
                    new Chart(document.getElementById('deliveryReportChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: deliveryData.map(d => d.week_name),
                            datasets: [{
                                label: 'On Time',
                                data: deliveryData.map(d => d.on_time),
                                backgroundColor: '#22c55e'
                            }, {
                                label: 'Delayed',
                                data: deliveryData.map(d => d.delayed),
                                backgroundColor: '#ef4444'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        borderDash: [4, 4]
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8
                                    }
                                }
                            }
                        }
                    });
                }
            }
        } catch (err) {
            console.error("Reports Charts Error:", err);
        }
    </script>

<?php elseif ($_SESSION['role'] === 'Driver' || $_SESSION['role'] === 'Checker'): ?>
    <!-- ================= DRIVER / CHECKER SCRIPTS ================= -->
    <script>
        <?php if ($_SESSION['role'] === 'Driver' && !empty($active_dispatch)): ?>
                // ===== LIVE GPS TRACKING (Driver — Any Dispatched Status) =====
                (function() {
                    const PUSH_INTERVAL_MS = 10000; // push every 10 seconds
                    let lastPushTime = 0;
                    let watchId = null;
                    let simIntervalId = null;
                    let gpsBadge = null;
                    const isTransit = <?= ($active_dispatch['status'] === 'In Transit') ? 'true' : 'false'; ?>;
                    const activeDest = <?= json_encode($active_dispatch['destination'] ?? ''); ?>;

                    function createGpsBadge(statusMsg, colorHex = '#22c55e') {
                        let badge = document.getElementById('gps-status-badge');
                        if (!badge) {
                            badge = document.createElement('div');
                            badge.id = 'gps-status-badge';
                            badge.style.cssText = 'position:fixed;bottom:1.2rem;left:1.2rem;z-index:9999;display:flex;align-items:center;gap:0.5rem;background:rgba(0,0,0,0.85);color:#fff;font-size:0.78rem;font-weight:600;padding:0.5rem 1rem;border-radius:999px;backdrop-filter:blur(4px);box-shadow:0 4px 12px rgba(0,0,0,0.15);pointer-events:none;transition:all 0.3s;';
                            const style = document.createElement('style');
                            style.textContent = '@keyframes gps-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.4;transform:scale(1.35)}}';
                            document.head.appendChild(style);
                            document.body.appendChild(badge);
                        }
                        badge.innerHTML = `<span style="width:8.5px;height:8.5px;border-radius:50%;background:${colorHex};display:inline-block;animation:gps-pulse 1.4s ease-in-out infinite;"></span> ${statusMsg}`;
                        gpsBadge = badge;
                    }

                    async function pushLocation(lat, lng, speed) {
                        if (!isTransit) return; // Only push updates when actively In Transit
                        const now = Date.now();
                        if (now - lastPushTime < PUSH_INTERVAL_MS) return;
                        lastPushTime = now;

                        let locationName = '';
                        if (typeof NominatimService !== 'undefined') {
                            try {
                                const geoRes = await NominatimService.reverseGeocode(lat, lng);
                                if (geoRes && geoRes.formatted) {
                                    locationName = geoRes.formatted;
                                }
                            } catch (e) {
                                console.warn('Reverse geocode error:', e);
                            }
                        }

                        const fd = new FormData();
                        fd.append('latitude', lat.toFixed(7));
                        fd.append('longitude', lng.toFixed(7));
                        fd.append('speed', (speed || 0).toFixed(2));
                        if (locationName) {
                            fd.append('location_name', locationName);
                        }

                        fetch('update_location.php', {
                                method: 'POST',
                                body: fd
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (gpsBadge) {
                                    if (data.tracking) {
                                        gpsBadge.style.background = 'rgba(22,101,52,0.92)';
                                    } else {
                                        gpsBadge.style.background = 'rgba(0,0,0,0.85)';
                                    }
                                }
                            })
                            .catch(() => {});
                    }

                    function startSimulatedGps() {
                        if (simIntervalId !== null) return;

                        showToast("ℹ️ Simulated GPS active (Local Testing Fallback). Fetching IP location...", "info", 5000);

                        let startLat = 15.3621;
                        let startLng = 120.9632;

                        fetch('https://ipapi.co/json/')
                            .then(r => r.json())
                            .then(ipData => {
                                if (ipData.latitude && ipData.longitude) {
                                    startLat = ipData.latitude;
                                    startLng = ipData.longitude;
                                    showToast("✅ Simulation aligned with your local network location.", "success", 4000);
                                }
                                runSimulation();
                            })
                            .catch(() => {
                                runSimulation();
                            });

                        function runSimulation() {
                            const destCoords = {
                                'San Leonardo': {
                                    lat: 15.3621,
                                    lng: 120.9632
                                },
                                'Tarlac': {
                                    lat: 15.4828,
                                    lng: 120.5963
                                },
                                'Laur': {
                                    lat: 15.4385,
                                    lng: 121.1895
                                },
                                'Gabaldon': {
                                    lat: 15.4533,
                                    lng: 121.3283
                                }
                            };

                            const target = destCoords[activeDest] || {
                                lat: startLat,
                                lng: startLng
                            };

                            if (isTransit) {
                                createGpsBadge('GPS Simulated — Sharing Location', '#0284c7');
                            } else {
                                createGpsBadge('GPS Simulated — Ready for Trip', '#3b82f6');
                                return;
                            }

                            function stepSimulation() {
                                let progress = parseFloat(localStorage.getItem('sim_progress_' + activeDest)) || 0;

                                const curLat = startLat + (target.lat - startLat) * progress;
                                const curLng = startLng + (target.lng - startLng) * progress;
                                const curSpeed = progress < 1 ? 55 + Math.random() * 5 : 0;

                                pushLocation(curLat, curLng, curSpeed);

                                if (progress < 1) {
                                    progress += 0.05; // 5% per 10 seconds (~3.3 mins total)
                                    if (progress > 1) progress = 1;
                                    localStorage.setItem('sim_progress_' + activeDest, progress);
                                } else {
                                    createGpsBadge('GPS Simulated — Arrived', '#22c55e');
                                }
                            }

                            stepSimulation();
                            simIntervalId = setInterval(stepSimulation, PUSH_INTERVAL_MS);
                        }
                    }

                    function startGPS() {
                        // HTTPS / Security Context Check
                        if ((window.isSecureContext === false || window.location.protocol !== 'https:') && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                            showToast("⚠️ Notice: Mobile browsers require HTTPS for real GPS hardware. Connect via https:// or enable SSL in InfinityFree for live GPS (simulated transit active).", "warning", 10000);
                        }

                        if (!navigator.geolocation) {
                            showToast("❌ Geolocation not supported. Activating simulated fallback...", "warning", 8000);
                            startSimulatedGps();
                            return;
                        }

                        createGpsBadge('Requesting GPS Permission...', '#f59e0b');

                        navigator.geolocation.getCurrentPosition(
                            function(pos) {
                                if (isTransit) {
                                    createGpsBadge('GPS Active — Sharing Location', '#22c55e');
                                    pushLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.speed ? pos.coords.speed * 3.6 : 0);
                                } else {
                                    createGpsBadge('GPS Authorized — Ready for Trip', '#3b82f6');
                                }
                            },
                            function(err) {
                                let msg = "GPS Status: ";
                                let badgeColor = '#ef4444';
                                if (err.code === err.PERMISSION_DENIED) {
                                    msg = "Location Access Denied. Activating simulated fallback...";
                                    showToast("⚠️ " + msg, "warning", 10000);
                                } else {
                                    msg = "GPS Error: " + err.message + ". Activating simulated fallback...";
                                    showToast("⚠️ " + msg, "warning", 10000);
                                }
                                startSimulatedGps();
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000
                            }
                        );

                        // Continuous tracking watch loop
                        watchId = navigator.geolocation.watchPosition(
                            function(pos) {
                                if (isTransit) {
                                    pushLocation(
                                        pos.coords.latitude,
                                        pos.coords.longitude,
                                        pos.coords.speed ? pos.coords.speed * 3.6 : 0
                                    );
                                }
                            },
                            function(err) {
                                console.warn('GPS watch error:', err.message);
                                // Fall back to simulation if watch fails
                                startSimulatedGps();
                            }, {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 5000
                            }
                        );
                    }

                    // Start as soon as DOM is ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', startGPS);
                    } else {
                        startGPS();
                    }

                    // Clean up watch on page unload
                    window.addEventListener('beforeunload', function() {
                        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                        if (simIntervalId !== null) clearInterval(simIntervalId);
                    });
                })();
        <?php endif; ?>



        // ── Password Reset Modal Functions ──────────────────────────
        let prPollingInterval = null;
        const prRole = '<?= strtolower($_SESSION['role'] ?? 'driver') ?>';
        const prBasePath = prRole === 'checker' ? '../checker/' : '../driver/';

        function openResetPasswordModal() {
            checkCurrentResetStatus(function(status) {
                const overlay = document.getElementById('pwdResetOverlay');
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                if (status === 'Approved') {
                    showPrStep('prStepSetPwd');
                } else if (status === 'Pending') {
                    showPrStep('prStepWaiting');
                    startPrPolling();
                } else {
                    showPrStep('prStepRequest');
                }
            });
        }

        function closePwdResetModal() {
            const overlay = document.getElementById('pwdResetOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            stopPrPolling();
            document.querySelectorAll('#pwdResetOverlay > div').forEach(d => {
                d.classList.add('hidden');
                d.classList.remove('scale-100', 'opacity-100');
                d.classList.add('scale-95', 'opacity-0');
            });
        }

        function showPrStep(stepId) {
            document.querySelectorAll('#pwdResetOverlay > div').forEach(d => {
                d.classList.add('hidden');
                d.classList.remove('scale-100', 'opacity-100');
                d.classList.add('scale-95', 'opacity-0');
            });
            const step = document.getElementById(stepId);
            if (!step) return;
            step.classList.remove('hidden');
            setTimeout(() => {
                step.classList.remove('scale-95', 'opacity-0');
                step.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function checkCurrentResetStatus(cb) {
            fetch('../includes/check_reset_status.php')
                .then(r => r.json())
                .then(data => cb(data.status || 'none'))
                .catch(() => cb('none'));
        }

        function submitResetRequest() {
            const btn = document.getElementById('btnSendRequest');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }
            fetch(prBasePath + 'request_pwd_reset.php', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.already_approved) {
                            showPrStep('prStepSetPwd');
                        } else {
                            showPrStep('prStepWaiting');
                            startPrPolling();
                            showToast(data.message || 'Request sent!', 'info');
                        }
                    } else {
                        showToast(data.message || 'Failed to send request.', 'error');
                        if (btn) { btn.disabled = false; btn.innerHTML = 'Send Request'; }
                    }
                }).catch(() => {
                    showToast('Network error. Please try again.', 'error');
                    if (btn) { btn.disabled = false; btn.innerHTML = 'Send Request'; }
                });
        }

        function startPrPolling() {
            stopPrPolling();
            prPollingInterval = setInterval(() => {
                checkCurrentResetStatus(function(status) {
                    if (status === 'Approved') {
                        stopPrPolling();
                        // If modal is open, jump to set-password step
                        const overlay = document.getElementById('pwdResetOverlay');
                        if (overlay && !overlay.classList.contains('hidden')) {
                            showPrStep('prStepSetPwd');
                        }
                        // Regardless, show a toast notification
                        showToast('✅ Your password reset was approved! You can now set your new password.', 'success', 8000);
                    } else if (status === 'none') {
                        // Request was rejected
                        stopPrPolling();
                        const overlay = document.getElementById('pwdResetOverlay');
                        if (overlay && !overlay.classList.contains('hidden')) {
                            showPrStep('prStepRequest');
                        }
                        showToast('Your password reset request was rejected by the Admin.', 'warning', 6000);
                    }
                });
            }, 5000); // poll every 5 seconds
        }

        function stopPrPolling() {
            if (prPollingInterval) { clearInterval(prPollingInterval); prPollingInterval = null; }
        }

        function submitNewPassword() {
            const pwd = document.getElementById('prNewPassword');
            const msgEl = document.getElementById('prSetPwdMsg');
            const btn = document.getElementById('btnSetPwd');

            if (!pwd.value) { showMsg(msgEl, 'Please enter a new password.', 'error'); return; }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            const fd = new FormData();
            fd.append('new_password', pwd.value);

            fetch(prBasePath + 'set_new_password.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Password updated successfully!', 'success');
                        closePwdResetModal();
                        if (pwd) pwd.value = '';
                    } else {
                        showMsg(msgEl, data.message || 'Failed to update password.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = 'Update Password';
                    }
                }).catch(() => {
                    showMsg(msgEl, 'Network error. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Update Password';
                });
        }

        function showMsg(el, msg, type) {
            if (!el) return;
            el.textContent = msg;
            el.className = 'mb-3 text-sm rounded-lg px-3 py-2 ' + (type === 'error'
                ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'
                : 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400');
            el.classList.remove('hidden');
        }

        // Legacy alias for any remaining openChangePasswordModal() calls
        function openChangePasswordModal() { openResetPasswordModal(); }
        function closeOtpModal() { closePwdResetModal(); }

        // Theme icon initialisation
        document.addEventListener("DOMContentLoaded", function() {
            const icon = document.getElementById('themeIcon');
            if (icon && document.documentElement.classList.contains('dark')) {
                icon.classList.replace('fa-moon', 'fa-sun');
            }

            // If user already had a pending/approved request, start polling silently
            checkCurrentResetStatus(function(status) {
                if (status === 'Pending') {
                    startPrPolling();
                } else if (status === 'Approved') {
                    showToast('✅ Your password reset was approved! Click "Reset Password" to set your new password.', 'success', 8000);
                }
            });
        });
    </script>

<?php endif; ?>
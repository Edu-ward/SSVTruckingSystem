<?php if ($_SESSION['role'] === 'Admin'): ?>
    <!-- ================= ADMIN SCRIPTS ================= -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        const trackingData = <?= json_encode($trackingTrucks ?? []); ?>;
        let streetLayer = null;
        let darkLayer = null;
        let satelliteLayer = null;
        let osmLayer = null;

        function initMap() {
            const mapDiv = document.getElementById('map');
            if (!mapDiv) return;
            try {
                // High-resolution tile providers over HTTPS
                streetLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                });

                darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                });

                satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and GIS User Community'
                });

                osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                });

                const isDark = document.documentElement.classList.contains('dark');
                const activeBaseLayer = isDark ? darkLayer : streetLayer;

                map = L.map('map', {
                    center: [15.3621, 120.9632],
                    zoom: 13,
                    layers: [activeBaseLayer],
                    zoomControl: true
                });

                // Interactive Layer Control Switcher (top right)
                const baseMaps = {
                    "🗺️ High-Def Street": streetLayer,
                    "🛰️ Satellite View": satelliteLayer,
                    "🌙 Dark Vector": darkLayer,
                    "📍 OpenStreetMap": osmLayer
                };
                L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);

                // Add Central Garage Marker
                const garageIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white shadow-xl border-2 border-white ring-4 ring-indigo-500/30" title="Central Garage"><i class="fa-solid fa-warehouse text-sm"></i></div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });
                L.marker([15.3621, 120.9632], { icon: garageIcon })
                    .addTo(map)
                    .bindPopup('<div class="p-1"><b class="text-sm font-bold text-gray-900">SSV Central Garage</b><br><span class="text-xs text-gray-500"><i class="fa-solid fa-location-dot text-red-500 mr-1"></i>San Leonardo, Nueva Ecija</span></div>');

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
                const popupContent = `
                    <div class="p-1 min-w-[170px]">
                        <div class="font-bold text-gray-900 text-sm flex items-center justify-between pb-1 border-b border-gray-100">
                            <span>${truck.truck_code}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full text-white font-semibold ${statusBadgeBg}">${truck.status}</span>
                        </div>
                        <div class="text-xs text-gray-600 mt-1.5 font-medium"><i class="fa-regular fa-user mr-1.5 text-blue-500"></i>${truck.driver_name || 'Unassigned'}</div>
                        <div class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-location-dot mr-1.5 text-red-500"></i>${truck.current_location || 'San Leonardo'}</div>
                        ${truck.destination ? '<div class="text-xs text-indigo-600 font-semibold mt-1"><i class="fa-solid fa-arrow-right mr-1.5"></i>To: ' + truck.destination + '</div>' : ''}
                        ${truck.speed ? '<div class="text-[11px] text-gray-400 mt-1"><i class="fa-solid fa-gauge mr-1.5"></i>Speed: ' + truck.speed + ' mph</div>' : ''}
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
            if (!map) { switchTab('tracking'); setTimeout(recenterMap, 300); return; }

            map.invalidateSize();

            const bounds = L.latLngBounds();
            bounds.extend([15.3621, 120.9632]); // Central Garage

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
                map.flyToBounds(bounds, { padding: [60, 60], maxZoom: 15, duration: 1.2 });
            } else {
                map.flyTo([15.3621, 120.9632], 13, { duration: 1.2 });
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

        function openViewDriverModal(driver) {
            document.getElementById('vd-initials').innerText = getInitialsJS(driver.name);
            document.getElementById('vd-name').innerText = driver.name;
            document.getElementById('vd-cdl').innerText = driver.cdl_number || 'N/A';
            document.getElementById('vd-status').innerText = driver.status;
            document.getElementById('vd-phone').innerText = driver.phone || 'N/A';
            document.getElementById('vd-truck').innerText = driver.truck_code || 'None assigned';

            document.getElementById('vd-deliveries').innerText = driver.total_deliveries ? driver.total_deliveries : 0;
            document.getElementById('vd-ontime').innerText = (driver.on_time_pct ? parseFloat(driver.on_time_pct).toFixed(1) : '100.0') + '%';

            let balance = parseFloat(driver.available_balance || 0).toFixed(2);
            document.getElementById('vd-owed-balance').innerText = balance;

            const settleBtn = document.getElementById('vd-settle-btn');
            const printBtn = document.getElementById('vd-print-btn');
            if (parseFloat(balance) > 0) {
                settleBtn.classList.remove('hidden');
                settleBtn.onclick = function() {
                    toggleModal('viewDriverModal', false);
                    openSettlePayrollModal(driver.id, driver.name, balance);
                };
                printBtn.classList.remove('hidden');
                printBtn.href = 'print_payroll.php?driver_id=' + driver.id;
            } else {
                settleBtn.classList.add('hidden');
                printBtn.classList.add('hidden');
            }

            const tripsContainer = document.getElementById('vd-recent-trips');
            if (tripsContainer) {
                tripsContainer.innerHTML = '';
                if (driver.recent_trips && driver.recent_trips.length > 0) {
                    driver.recent_trips.forEach(trip => {
                        const payAmt = parseFloat(trip.display_pay || 0).toFixed(2);
                        let statusBadge = trip.payment_status === 'Paid' ?
                            `<span class="ml-2 text-green-600 font-semibold bg-green-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-[10px] uppercase"><i class="fa-solid fa-check mr-1"></i>Paid</span>` :
                            `<span class="ml-2 text-orange-500 font-semibold bg-orange-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-[10px] uppercase"><i class="fa-solid fa-clock-rotate-left mr-1"></i>Pending</span>`;

                        tripsContainer.innerHTML += `
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border-l-4 ${trip.payment_status === 'Paid' ? 'border-green-500' : 'border-orange-500'} shadow-sm mb-2">
                            <div><div class="font-bold text-gray-800 dark:text-gray-200">${trip.destination} ${statusBadge}</div><div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><i class="fa-regular fa-calendar mr-1"></i> ${trip.trip_date}</div></div>
                            <div class="font-bold ${trip.payment_status === 'Paid' ? 'text-green-600 dark:text-green-400' : 'text-orange-500 dark:text-orange-400'} text-base">₱${payAmt}</div>
                        </div>`;
                    });
                } else {
                    tripsContainer.innerHTML = '<div class="text-xs text-gray-500 dark:text-gray-400 italic mt-2">No trips recorded for this driver.</div>';
                }
            }
            toggleModal('viewDriverModal', true);
        }

        function openContactDriverModal(driver) {
            document.getElementById('cd-title').innerText = 'Contact ' + driver.name.split(' ')[0];
            document.getElementById('cd-phone-text').innerText = 'Call ' + (driver.phone || 'N/A');
            document.getElementById('cd-phone-link').href = driver.phone ? 'tel:' + driver.phone : '#';
            toggleModal('contactDriverModal', true);
        }

        function openDeleteDriverModal(id, name) {
            document.getElementById('dd-name').innerText = name;
            document.getElementById('delete_driver_id').value = id;
            toggleModal('deleteDriverModal', true);
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

        function openDeleteCheckerModal(id, name) {
            document.getElementById('dc-name').innerText = name;
            document.getElementById('delete_checker_id').value = id;
            toggleModal('deleteCheckerModal', true);
        }

        function openDeleteTruckModal(truckId, truckCode) {
            document.getElementById('dt-truck-code').innerText = truckCode;
            document.getElementById('delete_truck_id').value = truckId;
            toggleModal('deleteTruckModal', true);
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

        function openSettlePayrollModal(driverId, driverName, balance) {
            document.getElementById('sp-driver-name').innerText = driverName;
            document.getElementById('sp-balance').innerText = '₱' + balance;
            document.getElementById('settle_driver_id').value = driverId;
            toggleModal('settlePayrollModal', true);
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

        function openDeleteCheckerModal(checkerId, checkerName) {
            document.getElementById('dc-checker-name').innerText = checkerName;
            document.getElementById('delete_checker_id').value = checkerId;
            toggleModal('deleteCheckerModal', true);
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
                                ticks: { precision: 0 },
                                grid: { borderDash: [4, 4] }
                            },
                            x: { grid: { display: false } }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
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
                                    callback: function(value) { return '₱' + value.toLocaleString(); }
                                },
                                grid: { borderDash: [4, 4] }
                            },
                            x: { grid: { display: false } }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                        }
                    }
                });
            }
        } catch (err) {
            console.error("Dashboard Charts Error:", err);
        }



        // ===== REAL-TIME PLATE NUMBER DUPLICATE CHECK =====
        document.addEventListener("DOMContentLoaded", function() {
            const plateInput    = document.getElementById('newTruckPlateInput');
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
                        .catch(() => { plateFeedback.innerHTML = ''; });
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
                const reqUpper  = container.querySelector('#req-uppercase, .req-uppercase') || container.parentElement.querySelector('#req-uppercase, .req-uppercase');
                const reqLower  = container.querySelector('#req-lowercase, .req-lowercase') || container.parentElement.querySelector('#req-lowercase, .req-lowercase');
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
                    const hasUpper  = /[A-Z]/.test(val);
                    const hasLower  = /[a-z]/.test(val);
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

                    function pushLocation(lat, lng, speed) {
                        if (!isTransit) return; // Only push updates when actively In Transit
                        const now = Date.now();
                        if (now - lastPushTime < PUSH_INTERVAL_MS) return;
                        lastPushTime = now;

                        const fd = new FormData();
                        fd.append('latitude', lat.toFixed(7));
                        fd.append('longitude', lng.toFixed(7));
                        fd.append('speed', (speed || 0).toFixed(2));

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
                                'San Leonardo': { lat: 15.3621, lng: 120.9632 },
                                'Tarlac':       { lat: 15.4828, lng: 120.5963 },
                                'Laur':         { lat: 15.4385, lng: 121.1895 },
                                'Gabaldon':     { lat: 15.4533, lng: 121.3283 }
                            };
                            
                            const target = destCoords[activeDest] || { lat: startLat, lng: startLng };
                            
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
                        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                            showToast("⚠️ GPS Warning: Geolocation requires HTTPS on mobile devices. Activating simulated fallback...", "warning", 10000);
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



        function openChangePasswordModal() {
            document.getElementById('otpModalOverlay').classList.remove('hidden');
            document.getElementById('otpModalOverlay').classList.add('flex');
            showStep('stepRequest');
        }

        function closeOtpModal() {
            document.getElementById('otpModalOverlay').classList.add('hidden');
            document.getElementById('otpModalOverlay').classList.remove('flex');
            document.getElementById('stepRequest').classList.remove('scale-100', 'opacity-100');
            document.getElementById('stepVerify').classList.remove('scale-100', 'opacity-100');
        }

        function showStep(stepId) {
            document.getElementById('stepRequest').classList.add('hidden');
            document.getElementById('stepVerify').classList.add('hidden');
            const step = document.getElementById(stepId);
            step.classList.remove('hidden');
            setTimeout(() => {
                step.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function requestOtp() {
            const btn = document.getElementById('btnRequestOtp');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;
            fetch('otp_handler.php', {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("SIMULATED SMS: Your SSV Trucking OTP code is <strong>" + data.simulated_otp + "</strong>", "info", 8000);
                        document.getElementById('otpPhoneText').innerText = "***-***-" + data.phone_last_4;
                        btn.innerHTML = 'Send OTP';
                        btn.disabled = false;
                        showStep('stepVerify');
                    } else {
                        showToast(data.message, "error");
                        btn.innerHTML = 'Send OTP';
                        btn.disabled = false;
                    }
                }).catch(e => {
                    showToast("Network Error", "error");
                    btn.innerHTML = 'Send OTP';
                    btn.disabled = false;
                });
        }

        function verifyAndChangePwd() {
            const otp = document.getElementById('otpInput').value;
            const pwd = document.getElementById('newPasswordInput').value;
            if (!otp || !pwd) {
                showToast("Please fill all fields", "warning");
                return;
            }

            const btn = document.getElementById('btnVerifyOtp');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const fn = new FormData();
            fn.append('otp', otp);
            fn.append('new_password', pwd);

            fetch('change_pwd.php', {
                    method: 'POST',
                    body: fn
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, "success");
                        closeOtpModal();
                        document.getElementById('otpInput').value = '';
                        document.getElementById('newPasswordInput').value = '';
                    } else {
                        showToast(data.message, "error");
                    }
                    btn.innerHTML = 'Update';
                    btn.disabled = false;
                }).catch(e => {
                    showToast("Network Error", "error");
                    btn.innerHTML = 'Update';
                    btn.disabled = false;
                });
        }
        // Theme icon initialisation
        document.addEventListener("DOMContentLoaded", function() {
            const icon = document.getElementById('themeIcon');
            if (icon && document.documentElement.classList.contains('dark')) {
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        });
    </script>

<?php endif; ?>
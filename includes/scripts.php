<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // ==========================================
    // 1. CORE TAB LOGIC (Moved to top so UI always loads)
    // ==========================================
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'dashboard';
    let map = null;

    function switchTab(tabName) {
        // Hide all views
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.nav-btn').forEach(el => el.className = "nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition");

        // Show selected view
        const viewEl = document.getElementById('view-' + tabName);
        if (viewEl) viewEl.classList.remove('hidden');

        // Highlight button
        const navBtn = document.getElementById('nav-' + tabName);
        if (navBtn) navBtn.className = "nav-btn flex items-center space-x-1 text-white bg-blue-700 px-3 py-1.5 rounded transition";

        // Map rendering logic (Delayed to ensure DOM is fully visible)
        if (tabName === 'tracking') {
            setTimeout(() => {
                if (!map) {
                    initMap();
                } else {
                    map.invalidateSize();
                }
            }, 250);
        }

        window.history.pushState({}, '', '?tab=' + tabName);
    }

    // BOOT UI IMMEDIATELY
    switchTab(activeTab);


    // ==========================================
    // 2. MAP LOGIC
    // ==========================================
    const trackingData = <?php echo json_encode($trackingTrucks ?? []); ?>;

    function initMap() {
        const mapDiv = document.getElementById('map');
        if (!mapDiv) return;

        try {
            map = L.map('map').setView([15.3621, 120.9632], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            trackingData.forEach(truck => {
                if (truck.latitude && truck.longitude) {
                    let markerClass = 'bg-gray-500';
                    if (truck.status === 'In Transit') markerClass = 'bg-transit';
                    if (truck.status === 'Idle') markerClass = 'bg-yellow-500'; // Corrected CSS color class
                    if (truck.status === 'Loading') markerClass = 'bg-blue-500';
                    if (truck.status === 'Unloading') markerClass = 'bg-orange-500';

                    const customIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class="marker-pin ${markerClass}"><i class="fa-solid fa-truck fa-xs"></i></div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15],
                        popupAnchor: [0, -15]
                    });

                    L.marker([truck.latitude, truck.longitude], {
                            icon: customIcon
                        })
                        .addTo(map)
                        .bindPopup(`<b>${truck.truck_code}</b><br><span class="text-xs">${truck.driver_name}</span><br><span class="text-xs">Status: ${truck.status}</span><br><span class="text-xs">Speed: ${truck.speed} mph</span>`);
                }
            });

            // Force redraw immediately after building to prevent grey tiles
            setTimeout(() => {
                map.invalidateSize();
            }, 300);

        } catch (error) {
            console.error("Map initialization failed:", error);
        }
    }

    function focusTruck(lat, lng) {
        switchTab('tracking');
        if (lat != 0 && lng != 0) {
            setTimeout(() => {
                if (map) map.setView([lat, lng], 15, {
                    animate: true,
                    duration: 1
                });
            }, 300);
        } else {
            alert("Location data not available for this truck yet.");
        }
    }


    // ==========================================
    // 3. MODALS & SUB-TABS LOGIC
    // ==========================================
    function switchDispatchTab(tab) {
        document.getElementById('dispatch-grid-active').classList.add('hidden');
        document.getElementById('dispatch-grid-completed').classList.add('hidden');
        document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full hover:text-gray-900 transition";
        document.getElementById('btn-tab-completed').className = "px-6 py-2 rounded-full hover:text-gray-900 transition";

        if (tab === 'active') {
            document.getElementById('dispatch-grid-active').classList.remove('hidden');
            document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full bg-white shadow-sm text-gray-900 transition";
        } else {
            document.getElementById('dispatch-grid-completed').classList.remove('hidden');
            document.getElementById('btn-tab-completed').className = "px-6 py-2 rounded-full bg-white shadow-sm text-gray-900 transition";
        }
    }

    // --- 3. Modal Logic ---
    function toggleModal(modalID, show) {
        const modal = document.getElementById(modalID);
        if (show) {
            modal.classList.remove('hidden');
            // Auto-focus RFID inputs for card readers
            if (modalID === 'dispatchModal') setTimeout(() => document.getElementById('rfidInput').focus(), 100);
            if (modalID === 'addTruckModal') setTimeout(() => document.getElementById('newTruckRfidInput').focus(), 100);
        } else {
            modal.classList.add('hidden');
        }
    }

    function updateTruckRFID() {
        const select = document.getElementById('truckSelect');
        const rfidField = document.getElementById('rfidInput');
        const selectedOption = select.options[select.selectedIndex];
        rfidField.value = selectedOption.value ? (selectedOption.getAttribute('data-rfid') || 'No RFID assigned') : '';
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
        document.getElementById('vd-email').innerText = driver.email || 'N/A';
        document.getElementById('vd-truck').innerText = driver.truck_code || 'None assigned';
        document.getElementById('vd-deliveries').innerText = driver.total_deliveries;
        document.getElementById('vd-ontime').innerText = driver.on_time_pct + '%';
        toggleModal('viewDriverModal', true);
    }

    function openContactDriverModal(driver) {
        document.getElementById('cd-title').innerText = 'Contact ' + driver.name.split(' ')[0];
        document.getElementById('cd-phone-text').innerText = 'Call ' + (driver.phone || 'N/A');
        document.getElementById('cd-phone-link').href = driver.phone ? 'tel:' + driver.phone : '#';
        document.getElementById('cd-email-text').innerText = 'Email ' + (driver.email || 'N/A');
        document.getElementById('cd-email-link').href = driver.email ? 'mailto:' + driver.email : '#';
        toggleModal('contactDriverModal', true);
    }

    function openDeleteDriverModal(id, name) {
        document.getElementById('dd-name').innerText = name;
        document.getElementById('delete_driver_id').value = id;
        toggleModal('deleteDriverModal', true);
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


    // ==========================================
    // 4. CHART.JS INITIALIZATION (Safely wrapped)
    // ==========================================
    try {
        const weeklyData = <?php echo json_encode($weeklyData ?? []); ?>;
        const fleetStatusData = <?php echo json_encode($fleetStatusData ?? []); ?>;
        const efficiencyData = <?php echo json_encode($efficiencyData ?? []); ?>;

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
            // Protect against NaN if database is empty
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
                        borderColor: '#3b82f6',
                        backgroundColor: '#eff6ff',
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
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
    } catch (err) {
        console.error("Dashboard Charts Error:", err);
    }

    try {
        const financeData = <?php echo json_encode($financeReports ?? []); ?>;
        const deliveryData = <?php echo json_encode($deliveryPerformance ?? []); ?>;
        const fuelData = <?php echo json_encode($fuelConsumption ?? []); ?>;

        if (financeData.length > 0 && document.getElementById('revenueReportChart')) {
            new Chart(document.getElementById('revenueReportChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: financeData.map(d => d.month_name),
                    datasets: [{
                        label: 'Revenue',
                        data: financeData.map(d => d.revenue),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: '#3b82f6',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Expenses',
                        data: financeData.map(d => d.expenses),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: '#ef4444',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Profit',
                        data: financeData.map(d => d.profit),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: '#22c55e',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            stacked: true,
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
            new Chart(document.getElementById('fuelReportChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: fuelData.map(d => d.day_name),
                    datasets: [{
                        label: 'Fuel (Gallons)',
                        data: fuelData.map(d => d.gallons),
                        borderColor: '#f59e0b',
                        backgroundColor: '#fef3c7',
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f59e0b',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 800,
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
        console.error("Reports Charts Error:", err);
    }


    // ==========================================
    // 5. AJAX DELETION UI
    // ==========================================
    if (document.getElementById('deleteDriverForm')) {
        document.getElementById('deleteDriverForm').addEventListener('submit', function(e) {
            e.preventDefault();
            toggleModal('deleteDriverModal', false);

            const overlay = document.getElementById('loadingOverlay');
            const loadingState = document.getElementById('loadingState');
            const successState = document.getElementById('successState');

            overlay.classList.remove('hidden');
            loadingState.classList.remove('hidden');
            successState.classList.add('hidden');

            fetch('dashboard.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(response => {
                    setTimeout(() => {
                        loadingState.classList.add('hidden');
                        successState.classList.remove('hidden');
                        setTimeout(() => window.location.href = 'dashboard.php?tab=drivers', 1500);
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Database error.');
                    overlay.classList.add('hidden');
                });
        });
    }
</script>
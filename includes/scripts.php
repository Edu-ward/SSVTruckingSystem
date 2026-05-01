<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'dashboard';
    let map = null;

    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.nav-btn').forEach(el => el.className = "nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition");

        const viewEl = document.getElementById('view-' + tabName);
        if (viewEl) viewEl.classList.remove('hidden');

        const navBtn = document.getElementById('nav-' + tabName);
        if (navBtn) navBtn.className = "nav-btn flex items-center space-x-1 text-white bg-blue-700 px-3 py-1.5 rounded transition";

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

    switchTab(activeTab);
    const trackingData = <?= json_encode($trackingTrucks ?? []); ?>;

    function initMap() {
        const mapDiv = document.getElementById('map');
        if (!mapDiv) return;

        try {
            map = L.map('map').setView([15.3621, 120.9632], 12);
            L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(map);

            trackingData.forEach(truck => {
                if (truck.latitude && truck.longitude) {
                    let markerClass = 'bg-gray-500';
                    if (truck.status === 'In Transit') markerClass = 'bg-transit';
                    if (truck.status === 'Idle') markerClass = 'bg-yellow-500';
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


    function switchDispatchTab(tab) {
        document.getElementById('dispatch-grid-active').classList.add('hidden');
        document.getElementById('dispatch-grid-completed').classList.add('hidden');
        document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition";
        document.getElementById('btn-tab-completed').className = "px-6 py-2 rounded-full hover:text-gray-900 dark:text-gray-100 transition";

        if (tab === 'active') {
            document.getElementById('dispatch-grid-active').classList.remove('hidden');
            document.getElementById('btn-tab-active').className = "px-6 py-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-gray-100 transition";
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
        document.getElementById('vd-truck').innerText = driver.truck_code || 'None assigned';
        document.getElementById('vd-deliveries').innerText = driver.total_deliveries;
        document.getElementById('vd-ontime').innerText = driver.on_time_pct + '%';
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

    function openDeleteTruckModal(truckId, truckCode) {
        document.getElementById('dt-truck-code').innerText = truckCode;
        document.getElementById('delete_truck_id').value = truckId;
        toggleModal('deleteTruckModal', true);
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

                        // --- UPDATED COLORS ---
                        borderColor: '#22c55e', // Tailwind green-500 line
                        backgroundColor: 'rgba(34, 197, 94, 0.15)', // 15% transparent green fill
                        pointBorderColor: '#22c55e', // Green ring around the dots
                        pointBackgroundColor: '#ffffff', // White center for the dots
                        // ----------------------

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
    } catch (err) {
        console.error("Dashboard Charts Error:", err);
    }

    try {
        const financeData = <?= json_encode($financeReports ?? []); ?>;
        const deliveryData = <?= json_encode($deliveryPerformance ?? []); ?>;
        const fuelData = <?= json_encode($fuelConsumption ?? []); ?>;

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
        }
    } catch (err) {
        console.error("Reports Charts Error:", err);
    }

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
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text)
                        });
                    }
                    setTimeout(() => {
                        loadingState.classList.add('hidden');
                        successState.classList.remove('hidden');
                        setTimeout(() => window.location.href = 'dashboard.php?tab=drivers', 1500);
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Could not remove driver: ' + error.message);
                    overlay.classList.add('hidden');
                });
        });
    }

    document.addEventListener("DOMContentLoaded", function() {

        // Destination Pay Calculation Logic
        const destSelect = document.getElementById('destinationSelect');
        const payOutput = document.getElementById('payOutput');

        if (destSelect && payOutput) {
            const rates = {
                'San Leonardo': 150,
                'Tarlac': 800,
                'Laur': 900,
                'Gabaldon': 1000
            };

            destSelect.addEventListener('change', function() {
                const dest = this.value;
                if (rates[dest]) {
                    payOutput.value = '₱' + rates[dest].toFixed(2);
                } else {
                    payOutput.value = '₱0.00';
                }
            });
        }

        // RFID Scanner Logic
        const rfidInput = document.getElementById('rfidInput');
        const truckPlate = document.getElementById('truckPlate');
        const hiddenTruckId = document.getElementById('hiddenTruckId');
        const rfidFeedback = document.getElementById('rfidFeedback');
        const dispatchForm = document.getElementById('dispatchForm');

        if (dispatchForm && rfidInput) {
            // Prevent Enter key from submitting the form while scanning
            dispatchForm.addEventListener('submit', function(e) {
                if (document.activeElement === rfidInput && rfidInput.value !== '') {
                    e.preventDefault();
                    return false;
                }
            });

            // Trigger AJAX call when scanner finishes reading (it simulates a fast typing + Enter or blur)
            rfidInput.addEventListener('change', function() {
                const rfidValue = this.value.trim();

                if (rfidValue.length > 0) {
                    rfidFeedback.innerHTML = '<span class="text-blue-500"><i class="fa-solid fa-spinner fa-spin"></i> Finding truck...</span>';

                    fetch('get_truck_by_rfid.php?rfid=' + encodeURIComponent(rfidValue))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                truckPlate.value = data.truck_code;
                                hiddenTruckId.value = data.truck_id;
                                rfidFeedback.innerHTML = '<span class="text-green-500"><i class="fa-solid fa-check"></i> Truck matched!</span>';
                                // Move cursor to select driver
                                document.querySelector('select[name="driver_id"]').focus();
                            } else {
                                truckPlate.value = '';
                                hiddenTruckId.value = '';
                                rfidFeedback.innerHTML = '<span class="text-red-500"><i class="fa-solid fa-triangle-exclamation"></i> Unregistered RFID tag!</span>';
                                rfidInput.value = ''; // Clear for retry
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
                    const percent = (elapsed / LOADING_DURATION_MS) * 100;
                    timerProgress.style.width = percent + '%';
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
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'truck_code=' + encodeURIComponent(truckCode)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    console.error("Backend Error: ", data.error);
                                }
                            })
                            .catch(error => console.error("Error updating status:", error));
                    }
                }
            });
        }

        if (document.querySelector('.loading-timer-container')) {
            setInterval(updateLoadingTimers, 1000);
            updateLoadingTimers();
        }
    });

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
</script>
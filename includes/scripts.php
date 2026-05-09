<?php if ($_SESSION['role'] === 'Admin'): ?>
    <!-- ================= ADMIN SCRIPTS ================= -->
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
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
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
                            iconAnchor: [15, 15]
                        });
                        L.marker([truck.latitude, truck.longitude], {
                            icon: customIcon
                        }).addTo(map).bindPopup(`<b>${truck.truck_code}</b><br><span class="text-xs">${truck.driver_name}</span><br><span class="text-xs">Status: ${truck.status}</span>`);
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
        } catch (err) {
            console.error("Dashboard Charts Error:", err);
        }



        document.addEventListener("DOMContentLoaded", function() {
            const destSelect = document.getElementById('destinationSelect');
            const gravelTypeSelect = document.getElementById('gravelType');
            const payOutput = document.getElementById('payOutput');
            if (destSelect && payOutput) {
                const destRates = {
                    'San Leonardo': 150,
                    'Tarlac': 800,
                    'Laur': 900,
                    'Gabaldon': 1000
                };
                const gravelPrices = <?= json_encode($gravelPrices ?? ["S1_regular" => 1500, "S1_crushed" => 1600, "3_4_regular" => 1400, "3_4_crushed" => 1500, "G1_regular" => 1700, "G1_crushed" => 1800, "38_regular" => 1300, "38_crushed" => 1400, "base_course" => 1200, "river_mix" => 1100, "garden_soil" => 1000]); ?>;

                function calculateTotalPay() {
                    const dest = destSelect.value;
                    const destPrice = destRates[dest] ? destRates[dest] : 0;
                    let gravelPrice = 0;
                    if (gravelTypeSelect && gravelTypeSelect.value) {
                        const type = gravelTypeSelect.value;
                        gravelPrice = gravelPrices[type] !== undefined ? gravelPrices[type] : 0;
                    }
                    if (dest || (gravelTypeSelect && gravelTypeSelect.value)) {
                        payOutput.value = '₱' + (destPrice + gravelPrice).toFixed(2);
                    } else {
                        payOutput.value = '₱0.00';
                    }
                }
                destSelect.addEventListener('change', calculateTotalPay);
                if (gravelTypeSelect) {
                    gravelTypeSelect.addEventListener('change', calculateTotalPay);
                }
            }

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
            const deliveryData = <?= json_encode($deliveryPerformance ?? []); ?>;
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
    </script>

<?php elseif ($_SESSION['role'] === 'Driver' || $_SESSION['role'] === 'Checker'): ?>
    <!-- ================= DRIVER SCRIPTS ================= -->
    <script>
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
                        alert("SIMULATED SMS: Your SSV Trucking OTP code is " + data.simulated_otp);
                        document.getElementById('otpPhoneText').innerText = "***-***-" + data.phone_last_4;
                        btn.innerHTML = 'Send OTP';
                        btn.disabled = false;
                        showStep('stepVerify');
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = 'Send OTP';
                        btn.disabled = false;
                    }
                }).catch(e => {
                    alert('Network Error');
                    btn.innerHTML = 'Send OTP';
                    btn.disabled = false;
                });
        }

        function verifyAndChangePwd() {
            const otp = document.getElementById('otpInput').value;
            const pwd = document.getElementById('newPasswordInput').value;
            if (!otp || !pwd) {
                alert("Please fill all fields");
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
                        alert(data.message);
                        closeOtpModal();
                        document.getElementById('otpInput').value = '';
                        document.getElementById('newPasswordInput').value = '';
                    } else {
                        alert('Error: ' + data.message);
                    }
                    btn.innerHTML = 'Update';
                    btn.disabled = false;
                }).catch(e => {
                    alert("Network Error");
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
<?php
// $gravelTypes and $destinations are loaded from the DB in admin/dashboard.php
?>

<div id="addTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative max-h-[90vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-shrink-0">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Add New Truck</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Register a new truck and link its RFID tag.</p>
            </div>
            <button onclick="toggleModal('addTruckModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            <input type="hidden" name="action" value="add_truck">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Plate Number <span class="text-red-500">*</span></label>
                <input type="text" name="truck_code" id="newTruckPlateInput" required placeholder="e.g. ABC 1234" autocomplete="off"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors">
                <p id="plateCheckFeedback" class="text-xs mt-1.5 min-h-[1rem]"></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">RFID Tag <span class="text-red-500">*</span></label>
                <input type="text" name="rfid_tag" id="newTruckRfidInput" required placeholder="Scan or type RFID tag..." autocomplete="off" class="w-full border border-blue-300 dark:border-blue-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50 dark:bg-blue-900 dark:text-gray-100 transition-colors">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click the field and scan the RFID card, or type the tag manually.</p>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addTruckModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Add Truck</button>
            </div>
        </form>
    </div>
</div>

<div id="addDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden relative max-h-[90vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-shrink-0">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">Add New Driver</h3>
            <button onclick="toggleModal('addDriverModal', false)" class="text-gray-500 dark:text-gray-400 hover:text-red-500 transition">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form action="dashboard.php" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            <input type="hidden" name="action" value="add_driver">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 mb-2">
                <h4 class="font-bold text-blue-700 dark:text-blue-400 text-sm mb-3 flex items-center">
                    <i class="fa-solid fa-truck-fast mr-2"></i> Truck Assignment
                </h4>
                <div class="flex space-x-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Scan Truck RFID <span class="text-red-500">*</span></label>
                        <input type="text" name="truck_rfid" id="driverTruckRfidInput" required placeholder="Scan tag..." autocomplete="off" class="w-full border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 transition-colors text-sm">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Code</label>
                        <input type="text" id="driverTruckCodeDisplay" readonly placeholder="---" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-2 py-2 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed text-center font-bold text-sm">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="Juan Dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Licence Number</label>
                    <input type="text" name="cdl_number" required placeholder="N01-XX-XXXXXX" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Phone</label>
                    <input type="text" name="phone" required placeholder="0912-345-6789" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Username</label>
                    <input type="text" name="username" required placeholder="juan.dela.cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Password</label>
                <div class="flex">
                    <input type="text" id="driverPasswordInput" name="password" required readonly placeholder="Click generate" class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm">
                    <button type="button" onclick="generateDriverPassword()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg transition font-medium">Generate</button>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="toggleModal('addDriverModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Save Driver</button>
            </div>
        </form>
    </div>
</div>

<script>
    function generateDriverPassword() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let pwd = "";
        pwd += "ABCDEFGHIJKLMNOPQRSTUVWXYZ" [Math.floor(Math.random() * 26)];
        pwd += "abcdefghijklmnopqrstuvwxyz" [Math.floor(Math.random() * 26)];
        pwd += "0123456789" [Math.floor(Math.random() * 10)];
        for (let i = 0; i < 7; i++) {
            pwd += chars[Math.floor(Math.random() * chars.length)];
        }
        pwd = pwd.split('').sort(function() {
            return 0.5 - Math.random()
        }).join('');

        document.getElementById('driverPasswordInput').value = pwd;
    }

    const driverTruckRfidInput = document.getElementById('driverTruckRfidInput');
    const driverTruckCodeDisplay = document.getElementById('driverTruckCodeDisplay');

    if (driverTruckRfidInput) {
        let rfidTimeout;
        driverTruckRfidInput.addEventListener('input', function() {
            clearTimeout(rfidTimeout);
            const rfid = this.value.trim();
            if (rfid.length < 3) {
                driverTruckCodeDisplay.value = '';
                return;
            }

            rfidTimeout = setTimeout(() => {
                fetch(`get_truck_by_rfid.php?rfid=${rfid}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            driverTruckCodeDisplay.value = data.truck_code;
                            driverTruckCodeDisplay.classList.remove('text-red-500');
                            driverTruckCodeDisplay.classList.add('text-green-600');
                        } else {
                            driverTruckCodeDisplay.value = 'Invalid';
                            driverTruckCodeDisplay.classList.remove('text-green-600');
                            driverTruckCodeDisplay.classList.add('text-red-500');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }, 300); // 300ms debounce
        });
    }
</script>

<div id="dispatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden relative max-h-[90vh] flex flex-col">
        <button onclick="toggleModal('dispatchModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200 z-10">
            <i class="fa-solid fa-xmark fa-lg"></i>
        </button>
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Create New Dispatch Ticket</h3>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Scan the truck's RFID to auto-fill details, then select destination and gravel type to calculate pay.</p>
        </div>
        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 overflow-y-auto space-y-4" id="dispatchForm">
            <input type="hidden" name="action" value="create_dispatch">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="truck_id" id="hiddenTruckId" required>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Scan Truck RFID Tag <span class="text-red-500">*</span></label>
                <input type="text" id="rfidInput" name="rfid_tag" placeholder="Click here and scan RFID card..." required autofocus autocomplete="off" class="w-full border border-blue-300 dark:border-blue-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50 dark:bg-blue-900 dark:text-gray-100 transition-colors text-sm">
                <p id="rfidFeedback" class="text-xs mt-1 text-gray-500 dark:text-gray-400">Waiting for scan...</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Truck Plate Number</label>
                    <input type="text" id="truckPlate" readonly placeholder="Auto-filled after scan" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Assigned Driver</label>
                    <input type="hidden" name="driver_id" id="hiddenDriverId" required>
                    <input type="text" id="assignedDriverName" readonly placeholder="Auto-filled after scan" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Fulfill Existing Order <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="order_id" id="dispatchOrderSelect" onchange="autoFillOrderDetails(this)" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                    <option value="">— General Dispatch (No Order Link) —</option>
                    <?php
                    $activeOrdersForDispatch = array_filter($allOrders ?? [], fn($o) => in_array($o['status'], ['Pending', 'In Progress']));
                    foreach ($activeOrdersForDispatch as $_ao):
                        $req = floatval($_ao['cubic_meters_required'] > 0 ? $_ao['cubic_meters_required'] : $_ao['trucks_required']);
                        $done = floatval($_ao['cubic_meters_fulfilled'] > 0 ? $_ao['cubic_meters_fulfilled'] : $_ao['trucks_fulfilled']);
                        $rem = max(0, $req - $done);
                    ?>
                        <option value="<?= $_ao['id'] ?>"
                            data-destination="<?= htmlspecialchars($_ao['destination']) ?>"
                            data-gravel="<?= htmlspecialchars($_ao['gravel_type']) ?>"
                            data-customer="<?= htmlspecialchars($_ao['client_name']) ?>"
                            data-remaining="<?= $rem ?>">
                            <?= htmlspecialchars($_ao['order_number']) ?> · <?= htmlspecialchars($_ao['client_name']) ?> — <?= htmlspecialchars($_ao['destination']) ?> (<?= number_format($rem, 2) ?> cu.m remaining)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p id="dispatchCustomerInfo" class="text-xs text-blue-600 dark:text-blue-400 mt-1 hidden"><i class="fa-solid fa-user mr-1"></i><span id="dispatchCustomerName"></span></p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Origin</label>
                    <input type="text" name="origin" value="Brgy. Burgos San Leonardo, Nueva Ecija" required class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Destination</label>
                        <button type="button" onclick="openNominatimSearch('dispatch')" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-map-location-dot"></i> Search OSM Map
                        </button>
                    </div>
                    <select name="destination" id="destinationSelect" onchange="updateDispatchPayPreview()" required class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                        <option value="">Select Destination</option>
                        <?php foreach ($destinations as $_dest): ?>
                            <option value="<?= htmlspecialchars($_dest['name']); ?>" data-distance="<?= floatval($_dest['distance_km']); ?>" data-pay="<?= floatval($_dest['distance_km']) > 0 ? round(floatval($_dest['distance_km']) * 10, 2) : floatval($_dest['driver_rate']); ?>"><?= htmlspecialchars($_dest['name']); ?><?php if (floatval($_dest['distance_km']) > 0): ?> (<?= number_format($_dest['distance_km'], 1); ?> km)<?php endif; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p id="dispatchPayPreview" class="text-xs text-green-600 dark:text-green-400 mt-1 hidden">
                        <i class="fa-solid fa-peso-sign mr-1"></i>Driver Pay: <strong id="dispatchPayAmount"></strong> (based on distance)
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Gravel Type <span class="text-red-500">*</span></label>
                    <select id="gravelType" name="gravel_type" required class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                        <option value="">Select gravel type</option>
                        <?php foreach ($gravelTypes as $value => $label): ?>
                            <option value="<?= $value; ?>"><?= htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Cubic Meter (cu.m) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.1" name="cubic_meters" required placeholder="e.g. 10.00" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="toggleModal('dispatchModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-black transition">Create Dispatch</button>
            </div>
        </form>
    </div>
</div>
<script>
    function autoFillOrderDetails(selectElem) {
        const opt = selectElem.options[selectElem.selectedIndex];
        const customerInfo = document.getElementById('dispatchCustomerInfo');
        const customerNameEl = document.getElementById('dispatchCustomerName');

        if (!opt || !opt.value) {
            if (customerInfo) customerInfo.classList.add('hidden');
            return;
        }

        const dest = opt.dataset.destination;
        const gravel = opt.dataset.gravel;
        const customer = opt.dataset.customer;

        const destSelect = document.getElementById('destinationSelect');
        if (destSelect && dest) {
            destSelect.value = dest;
            updateDispatchPayPreview();
        }

        const gravelSelect = document.getElementById('gravelType');
        if (gravelSelect && gravel) gravelSelect.value = gravel;

        // Show customer name
        if (customerInfo && customerNameEl && customer) {
            customerNameEl.textContent = 'Customer: ' + customer;
            customerInfo.classList.remove('hidden');
        } else if (customerInfo) {
            customerInfo.classList.add('hidden');
        }
    }

    function updateDispatchPayPreview() {
        const destSelect = document.getElementById('destinationSelect');
        const payPreview = document.getElementById('dispatchPayPreview');
        const payAmountEl = document.getElementById('dispatchPayAmount');
        if (!destSelect || !payPreview || !payAmountEl) return;

        const opt = destSelect.options[destSelect.selectedIndex];
        if (!opt || !opt.value) {
            payPreview.classList.add('hidden');
            return;
        }

        const distKm = parseFloat(opt.dataset.distance || 0);
        const pay = parseFloat(opt.dataset.pay || 0);

        if (pay > 0) {
            if (distKm > 0) {
                payAmountEl.textContent = '₱' + pay.toFixed(2) + ' (' + distKm.toFixed(1) + ' km × ₱10/km)';
            } else {
                payAmountEl.textContent = '₱' + pay.toFixed(2) + ' (fixed rate)';
            }
            payPreview.classList.remove('hidden');
        } else {
            payPreview.classList.add('hidden');
        }
    }
</script>

<style>
    #viewDriverModal,
    #viewDriverModal * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    #viewDriverModal::-webkit-scrollbar,
    #viewDriverModal *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>

<div id="viewDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative max-h-[90vh] flex flex-col">
        <button onclick="toggleModal('viewDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200 z-10"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="bg-blue-600 p-5 sm:p-6 text-center flex-shrink-0">
            <!-- Photo avatar (shown when driver has photo) -->
            <img id="vd-photo" src="" alt=""
                 class="w-20 h-20 rounded-full object-cover border-4 border-white/80 shadow-xl mx-auto mb-2 sm:mb-3 hidden">
            <!-- Initials avatar (fallback) -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold text-blue-600 mx-auto mb-2 sm:mb-3 shadow-lg" id="vd-initials">--</div>
            <h3 class="text-lg sm:text-xl font-bold text-white" id="vd-name">Driver Name</h3>
            <p class="text-blue-100 text-xs sm:text-sm mt-0.5" id="vd-cdl">Licence #</p>
        </div>
        <div class="p-4 sm:p-6 space-y-4 overflow-y-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                    <div class="font-bold text-gray-800 dark:text-gray-200 text-sm sm:text-base" id="vd-status">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Current Truck</div>
                    <div class="font-bold text-blue-600 text-sm sm:text-base" id="vd-truck">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Deliveries</div>
                    <div class="font-bold text-gray-800 dark:text-gray-200 text-sm sm:text-base" id="vd-deliveries">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400">On-Time Rate</div>
                    <div class="font-bold text-green-600 text-sm sm:text-base" id="vd-ontime">--</div>
                </div>
            </div>

            <!-- Payroll Summary & Settle Action -->
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between gap-3" id="vd-payroll-bar">
                <div>
                    <div class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-400 tracking-wider">Unclaimed Payroll</div>
                    <div class="text-base sm:text-lg font-extrabold text-emerald-700 dark:text-emerald-300" id="vd-payroll-net">₱0.00</div>
                    <div class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5" id="vd-payroll-breakdown">Gross: ₱0.00 • CA: ₱0.00</div>
                </div>
                <button type="button" id="vd-settle-btn" onclick="triggerSettleFromModal()" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm flex items-center gap-1.5 flex-shrink-0">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>Settle</span>
                </button>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2 text-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-phone text-gray-400 w-5 text-center"></i>
                    <span id="vd-phone" class="font-medium text-gray-700 dark:text-gray-200 text-xs sm:text-sm">--</span>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200">Recent Deliveries</h4>
                    <button type="button" onclick="openPrintDriverTripsModal()" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 text-xs font-bold flex items-center gap-1">
                        <i class="fa-solid fa-print"></i> Print Ticket
                    </button>
                </div>
                <div id="vd-recent-trips" class="space-y-2"></div>
                <div id="vd-view-all-trips-btn-container" class="mt-2.5 hidden">
                    <button type="button" onclick="openAllDriverDeliveriesModal()" class="w-full py-2.5 px-3 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 border border-blue-200/70 dark:border-blue-800 shadow-sm">
                        <i class="fa-solid fa-list-ul"></i>
                        <span id="vd-view-all-btn-text">View All Deliveries</span>
                        <i class="fa-solid fa-chevron-right text-[10px] ml-0.5"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="p-3 sm:p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center justify-between gap-2 flex-shrink-0">
            <button type="button" onclick="openPrintDriverTripsModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Print Trips Ticket</span>
            </button>
            <button onclick="toggleModal('viewDriverModal', false)" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Close</button>
        </div>
    </div>
</div>

<!-- ====== SETTLE DRIVER PAYROLL CONFIRMATION MODAL ====== -->
<div id="settlePayrollModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden p-3 sm:p-4 backdrop-blur-xs">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative border border-gray-100 dark:border-gray-700">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold">Settle Driver Payroll</h3>
                    <p class="text-xs text-emerald-100 font-medium">Disburse full or partial earnings & carry balance</p>
                </div>
            </div>
            <button onclick="toggleModal('settlePayrollModal', false)" class="text-white/80 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 space-y-3.5" id="settlePayrollForm">
            <input type="hidden" name="action" value="settle_driver_payroll">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="sp-driver-id" value="">

            <div class="bg-gray-50 dark:bg-gray-900/60 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700/60 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Driver:</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100 text-sm" id="sp-driver-name">--</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Unclaimed Gross Earnings:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400" id="sp-gross-amount">₱0.00</span>
                </div>
                <div class="flex justify-between items-center text-xs hidden" id="sp-previous-balance-row">
                    <span class="text-indigo-600 dark:text-indigo-400 font-medium">Prior Carried Balance:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400" id="sp-previous-balance">+₱0.00</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Cash Advances to Deduct:</span>
                    <span class="font-bold text-orange-600 dark:text-orange-400" id="sp-advances-amount">-₱0.00</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Total Net Payable:</span>
                    <span class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400" id="sp-net-pay">₱0.00</span>
                </div>
            </div>

            <!-- Partial Claim / Disbursement Input -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Amount to Disburse / Claim (₱):
                    </label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setSettleClaimFull()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 transition">
                            100% Full
                        </button>
                        <button type="button" onclick="setSettleClaimPercent(0.5)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 transition">
                            50%
                        </button>
                    </div>
                </div>
                <input type="number" step="0.01" min="0" name="claimed_amount" id="sp-claimed-input" 
                       oninput="recalculateSettleRemaining()"
                       class="w-full text-base font-extrabold text-emerald-700 dark:text-emerald-300 rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-emerald-500 focus:border-emerald-500 p-2.5">
            </div>

            <!-- Remaining Balance Display -->
            <div class="flex items-center justify-between p-2.5 bg-indigo-50/70 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/40 text-xs">
                <span class="text-indigo-700 dark:text-indigo-400 font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Remaining Balance Carried Forward:</span>
                </span>
                <span class="font-extrabold text-indigo-700 dark:text-indigo-300 text-sm" id="sp-remaining-balance">₱0.00</span>
            </div>

            <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200/70 dark:border-amber-900/40 text-[11px] text-amber-800 dark:text-amber-300 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                <div class="leading-relaxed">
                    Settling resets active gross earnings. Any unclaimed portion is saved as the driver's remaining balance for next payroll. The ticket will itemize all settled trips.
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Settlement Notes (Optional):</label>
                <textarea name="notes" rows="1" placeholder="Add notes regarding this settlement..." class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-emerald-500 focus:border-emerald-500 p-2"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-1">
                <button type="button" onclick="toggleModal('settlePayrollModal', false)" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Confirm & Print Ticket</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ====== ADJUST DRIVER REMAINING BALANCE MODAL ====== -->
<div id="adjustDriverBalanceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden p-3 sm:p-4 backdrop-blur-xs">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative border border-gray-100 dark:border-gray-700">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold">Adjust Remaining Balance</h3>
                    <p class="text-xs text-indigo-100 font-medium">Add to or set driver's carried payroll balance</p>
                </div>
            </div>
            <button onclick="toggleModal('adjustDriverBalanceModal', false)" class="text-white/80 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 space-y-4" id="adjustBalanceForm">
            <input type="hidden" name="action" value="adjust_driver_balance">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="ab-driver-id" value="">

            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-100 dark:border-gray-700/60 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Driver:</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100 text-sm" id="ab-driver-name">--</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Current Remaining Balance:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm" id="ab-current-balance">₱0.00</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Adjustment Action:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <input type="radio" name="adjustment_type" value="add" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">+ Add to Balance</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <input type="radio" name="adjustment_type" value="set" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">= Set Exact Total</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount (₱):</label>
                <input type="number" step="0.01" min="0" name="amount" required placeholder="0.00" class="w-full text-sm font-bold rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason / Notes:</label>
                <textarea name="notes" rows="2" placeholder="e.g. Unclaimed partial balance, bonus, prior balance adjustment..." class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 p-2.5"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('adjustDriverBalanceModal', false)" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Balance</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ====== ALL DRIVER DELIVERIES MODAL ====== -->
<div id="allDriverDeliveriesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 bg-blue-600 rounded-xl flex items-center justify-center text-white text-lg font-bold shadow-md">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-bold text-white" id="ad-driver-name">Driver Deliveries</h3>
                        <span id="ad-trip-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">0</span>
                    </div>
                    <p class="text-slate-400 text-xs mt-0.5">Complete trip records, distances, and driver earnings</p>
                </div>
            </div>
            <button onclick="toggleModal('allDriverDeliveriesModal', false)" class="text-gray-400 hover:text-white transition p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Summary KPI Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 sm:p-5 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-xs flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                <span class="text-gray-400 font-semibold block uppercase text-[10px]">Total Trips</span>
                <span id="ad-sum-trips" class="font-extrabold text-gray-900 dark:text-gray-100 text-base mt-0.5 block">0</span>
            </div>
            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                <span class="text-blue-500 font-semibold block uppercase text-[10px]">Total Distance</span>
                <span id="ad-sum-distance" class="font-extrabold text-blue-600 dark:text-blue-400 text-base mt-0.5 block">0.0 km</span>
            </div>
            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800/40 shadow-sm text-center">
                <span class="text-emerald-600 font-semibold block uppercase text-[10px]">Gross Trip Earnings</span>
                <span id="ad-sum-pay" class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base mt-0.5 block">₱0.00</span>
            </div>
        </div>

        <!-- Scrollable Deliveries List -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-2.5 flex-1 max-h-[50vh]" id="ad-all-trips-list">
            <!-- Populated via JavaScript -->
        </div>

        <!-- Footer Actions -->
        <div class="p-3.5 sm:p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center justify-between gap-3 flex-shrink-0">
            <button type="button" onclick="openPrintDriverTripsModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Print Trip Ticket</span>
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleModal('allDriverDeliveriesModal', false); toggleModal('viewDriverModal', true);" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Back</span>
                </button>
                <button type="button" onclick="toggleModal('allDriverDeliveriesModal', false)" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PRINT DRIVER TRIPS COVERAGE MODAL ==================== -->
<div id="printDriverTripsModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative max-h-[90vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-lg">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-white">Print Driver Trips Ticket</h3>
                    <p class="text-xs text-blue-100 mt-0.5" id="pdt_driver_name_display">Driver Trip Log Summary</p>
                </div>
            </div>
            <button onclick="toggleModal('printDriverTripsModal', false)" class="text-white/80 hover:text-white transition">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            <input type="hidden" id="pdt_driver_id">

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Coverage Period</label>
                <select id="pdt_period" onchange="updatePdtDateInputs(this.value)" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="today">Today</option>
                    <option value="weekly">This Week (Mon–Sun)</option>
                    <option value="monthly" selected>This Month (<?= date('F Y'); ?>)</option>
                    <option value="all">All Time History</option>
                    <option value="custom">Custom Date Range...</option>
                </select>
            </div>

            <div id="pdt_custom_date_range" class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Start Date</label>
                    <input type="date" id="pdt_start_date" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">End Date</label>
                    <input type="date" id="pdt_end_date" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Trip Status Filter</label>
                <select id="pdt_status" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Dispatches (Delivered, In Transit, Cancelled)</option>
                    <option value="delivered" selected>Delivered Only</option>
                </select>
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end space-x-3 flex-shrink-0">
            <button type="button" onclick="toggleModal('printDriverTripsModal', false)" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
            <button type="button" onclick="submitPrintDriverTrips()" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-print"></i> Generate & Print Ticket
            </button>
        </div>
    </div>
</div>

<div id="contactDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('contactDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 sm:mb-4"><i class="fa-regular fa-comments"></i></div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1" id="cd-title">Contact Driver</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-5 sm:mb-6">Choose how you want to reach out to this driver.</p>
        <div class="space-y-3">
            <a href="#" id="cd-phone-link" class="w-full flex items-center justify-center space-x-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-100 border border-green-200 dark:border-green-800 py-3 rounded-xl font-semibold transition text-sm">
                <i class="fa-solid fa-phone"></i><span id="cd-phone-text">Call Number</span>
            </a>
        </div>
    </div>
</div>

<div id="updateStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('updateStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 sm:mb-4"><i class="fa-solid fa-rotate-right"></i></div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Update Truck Status</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-5 sm:mb-6">Manually override the current status for <strong id="us-truck-code" class="text-gray-800 dark:text-gray-200"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="update_truck_status">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="truck_id" id="update_status_truck_id" value="">
            <div class="mb-5 sm:mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Status</label>
                <select name="new_status" id="update_status_select" required class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200 text-sm">
                    <option value="Idle">Idle</option>
                    <option value="Loading">Loading</option>
                    <option value="In Transit">In Transit (On Trip)</option>
                    <option value="Unloading">Unloading</option>
                    <option value="Maintenance">Maintenance (Broken)</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="updateDriverStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('updateDriverStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 sm:mb-4"><i class="fa-solid fa-id-card"></i></div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Update Driver Status</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-5 sm:mb-6">Manually override the current status for <strong id="uds-driver-name" class="text-gray-800 dark:text-gray-200"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="update_driver_status">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="update_status_driver_id" value="">
            <div class="mb-5 sm:mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Status</label>
                <select name="new_status" id="update_driver_status_select" required class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200 text-sm">
                    <option value="Active">Active</option>
                    <option value="Off Duty">Off Duty</option>
                    <option value="Dispatched">Dispatched</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateDriverStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="switchTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('switchTruckModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 sm:mb-4"><i class="fa-solid fa-truck-arrow-right"></i></div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Switch Truck</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-5 sm:mb-6">Assign a new Idle truck to <strong id="st-driver-name" class="text-gray-800 dark:text-gray-200"></strong>. Current truck: <strong id="st-truck-code"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="switch_truck">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="switch_truck_driver_id" value="">
            <input type="hidden" name="redirect_tab" id="switch_truck_redirect_tab" value="drivers">
            <div class="mb-5 sm:mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Truck</label>
                <select name="new_truck_id" required class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200 text-sm">
                    <option value="">— Select Idle Truck —</option>
                    <?php foreach ($availableTrucks ?? [] as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['truck_code']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Active dispatches will automatically transfer to the new truck.</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('switchTruckModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Switch Truck</button>
            </div>
        </form>
    </div>
</div>

<!-- Approve Cancellation Modal -->
<div id="approveCancelModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-orange-50 dark:bg-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-orange-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Approve Cancellation</h3>
                    <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">Ticket: <span id="ac-ticket-number"></span></p>
                </div>
            </div>
            <button onclick="toggleModal('approveCancelModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="approve_cancel">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="dispatch_id" id="approve_cancel_dispatch_id">
            <div class="p-8 text-center">
                <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to approve this cancellation request? This will mark the truck for <span class="font-bold text-orange-600">Maintenance</span> and the dispatch as <span class="font-bold text-red-600">Cancelled</span>.</p>
                <div class="flex flex-col space-y-3">
                    <button type="submit" class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-200 dark:shadow-none transition transform hover:-translate-y-0.5">
                        Yes, Approve Cancellation
                    </button>
                    <button type="button" onclick="toggleModal('approveCancelModal', false)" class="w-full py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        No, Keep it Active
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Remove Driver</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to remove <strong id="dd-name" class="text-gray-800 dark:text-gray-200"></strong> from the system? This action cannot be undone.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="delete_driver">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="delete_driver_id" value="">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('deleteDriverModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Remove</button>
            </div>
        </form>
    </div>
</div>

<div id="resetPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('resetPasswordModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-key"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Reset Password</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Enter a new password for <strong id="rp-name" class="text-gray-800 dark:text-gray-200"></strong>.</p>
        <form method="POST" action="dashboard.php" class="text-left space-y-4">
            <input type="hidden" name="action" value="reset_driver_password">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="reset_password_driver_id" value="">

            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">New Password <span class="text-red-500">*</span></label>
                <input type="password" name="new_password" id="new_driver_password" required placeholder="At least 8 characters..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors">

                <div class="mt-3 space-y-1.5 text-xs text-left" id="pw-requirements">
                    <div id="req-length" class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least 8 characters
                    </div>
                    <div id="req-uppercase" class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one uppercase letter (A-Z)
                    </div>
                    <div id="req-lowercase" class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one lowercase letter (a-z)
                    </div>
                    <div id="req-number" class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one number (0-9)
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="toggleModal('resetPasswordModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" id="resetPasswordSubmitBtn" disabled class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed">Update Password</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-3xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Remove Truck</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to remove <strong id="dt-truck-code" class="text-gray-800 dark:text-gray-200"></strong>? This action cannot be undone.</p>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="delete_truck">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="truck_id" id="delete_truck_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="toggleModal('deleteTruckModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition dark:bg-black">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ==================== MARK AS FIXED MODAL ==================== -->
<div id="markFixedModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden relative transform transition-all">
        <div class="bg-green-600 p-6 text-center">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                <i class="fa-solid fa-screwdriver-wrench text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-white">Mark Truck as Fixed?</h3>
            <p class="text-green-100 text-sm mt-1">This will set the truck status back to <strong>Idle</strong>.</p>
        </div>
        <div class="p-6">
            <p class="text-gray-600 dark:text-gray-300 text-sm text-center mb-6">
                Confirm that <strong id="mf-truck-code" class="text-gray-900 dark:text-gray-100"></strong> has been repaired and is ready to operate.
            </p>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="update_truck_status">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="new_status" value="Idle">
                <input type="hidden" name="truck_id" id="mf_truck_id">
                <div class="flex space-x-3">
                    <button type="button" onclick="toggleModal('markFixedModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition shadow-md shadow-green-200 dark:shadow-none">
                        <i class="fa-solid fa-check mr-1"></i> Yes, Mark Fixed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteDispatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-ban text-3xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Void / Cancel Dispatch</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to void ticket <strong id="dd-ticket-number" class="text-gray-800 dark:text-gray-200"></strong>? This will release the truck and driver and reverse any payroll added for this trip.</p>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="delete_dispatch">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="dispatch_id" id="delete_dispatch_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="toggleModal('deleteDispatchModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Keep Dispatch</button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Void It</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="completeDispatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-check-double text-3xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Mark as Delivered</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to finalize ticket <strong id="cd-ticket-number" class="text-gray-800 dark:text-gray-200"></strong>? This will move it to the completed log and free up the truck and driver.</p>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="complete_dispatch">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="dispatch_id" id="complete_dispatch_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="toggleModal('completeDispatchModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">Yes, Mark Delivered</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== ADD ORDER MODAL ==================== -->
<div id="addOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden relative max-h-[90vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-shrink-0">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Place New Order</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Create a gravel delivery order for a client.</p>
            </div>
            <button onclick="toggleModal('addOrderModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 overflow-y-auto space-y-4">
            <input type="hidden" name="action" value="add_order">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Client Name <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" required placeholder="e.g. Juan dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Destination <span class="text-red-500">*</span></label>
                        <button type="button" onclick="openNominatimSearch('order')" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-map-location-dot"></i> Search OSM Map
                        </button>
                    </div>
                    <select name="destination" required class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Select destination</option>
                        <?php foreach ($destinations as $_dest): ?>
                            <option value="<?= htmlspecialchars($_dest['name']); ?>"><?= htmlspecialchars($_dest['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Gravel Type <span class="text-red-500">*</span></label>
                    <select name="gravel_type" required class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Select type</option>
                        <?php foreach ($gravelTypes as $val => $lbl): ?>
                            <option value="<?= $val ?>"><?= htmlspecialchars($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Cubic Meter (cu.m) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.1" name="cubic_meters_required" required placeholder="e.g. 50.00" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Assign Checker <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="checker_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">— Assign later —</option>
                    <?php foreach ($allCheckers ?? [] as $chk): ?>
                        <option value="<?= $chk['id'] ?>"><?= htmlspecialchars($chk['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="2" placeholder="Special instructions, remarks..." class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none text-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addOrderModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-black transition">Place Order</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== ADD CHECKER MODAL ==================== -->
<div id="addCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative max-h-[90vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-shrink-0">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Add Checker Account</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Create a login for a new field checker.</p>
            </div>
            <button onclick="toggleModal('addCheckerModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-5 sm:p-6 overflow-y-auto space-y-4">
            <input type="hidden" name="action" value="add_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" required placeholder="Juan" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" required placeholder="Dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="text" name="phone" required placeholder="09123456789" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="checker_username" required placeholder="e.g. checker_juan" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Password <span class="text-red-500">*</span></label>
                <div class="flex">
                    <input type="text" id="checkerPasswordInput" name="checker_password" required readonly placeholder="Click Generate" class="p-2 w-full border border-gray-300 dark:border-gray-600 rounded-l-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm cursor-not-allowed">
                    <button type="button" onclick="generateCheckerPassword()" class="px-4 py-2 bg-blue-100 dark:bg-blue-900 border border-l-0 border-blue-300 dark:border-blue-700 rounded-r-xl hover:bg-blue-200 text-blue-700 dark:text-blue-300 transition font-medium text-sm">Generate</button>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addCheckerModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Create Checker</button>
            </div>
        </form>
    </div>
</div>
<script>
    function generateCheckerPassword() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
        let pwd = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" [Math.floor(Math.random() * 26)];
        pwd += "abcdefghijklmnopqrstuvwxyz" [Math.floor(Math.random() * 26)];
        pwd += "0123456789" [Math.floor(Math.random() * 10)];
        for (let i = 0; i < 7; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
        pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');
        document.getElementById('checkerPasswordInput').value = pwd;
    }
</script>

<div id="assignCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('assignCheckerModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 text-center mb-1">Assign Checker</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 text-center mb-5">Order: <strong id="ac-order-number" class="text-gray-800 dark:text-gray-200"></strong></p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="assign_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="order_id" id="ac_order_id">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select Checker</label>
                <select name="checker_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">— Select —</option>
                    <?php foreach ($allCheckers ?? [] as $chk): ?>
                        <option value="<?= $chk['id'] ?>">
                            <?= htmlspecialchars($chk['full_name'] ?: $chk['username']) ?>
                            (<?= htmlspecialchars($chk['username']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('assignCheckerModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Assign</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('deleteCheckerModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-red-50 dark:bg-red-900/30 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 sm:mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Remove Checker</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to remove <strong id="dc-checker-name" class="text-gray-800 dark:text-gray-200"></strong> from the system? Their account will be permanently deleted.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="delete_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="checker_id" id="delete_checker_id">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('deleteCheckerModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Remove</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== CANCEL ORDER MODAL ==================== -->
<div id="cancelOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative p-5 sm:p-6 text-center max-h-[90vh] overflow-y-auto">
        <button onclick="toggleModal('cancelOrderModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 bg-red-50 dark:bg-red-900/30 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-ban"></i></div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Cancel Order</h3>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to cancel order <strong id="co-order-number" class="text-gray-800 dark:text-gray-200"></strong>?</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="cancel_order">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="order_id" id="co_order_id">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('cancelOrderModal', false)" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Keep Order</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== NOMINATIM OSM SEARCH MODAL ==================== -->
<div id="nominatimSearchModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900 bg-opacity-60 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative flex flex-col max-h-[90vh]">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-blue-600 text-white flex-shrink-0">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-map-location-dot text-base sm:text-lg"></i>
                <h3 class="text-sm sm:text-base font-bold">Location Search (OSM Nominatim)</h3>
            </div>
            <button onclick="closeNominatimSearchModal()" class="text-white hover:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <div class="p-3 sm:p-4 space-y-3 bg-gray-50 dark:bg-gray-900 flex-shrink-0">
            <div class="flex space-x-2">
                <div class="relative flex-1">
                    <input type="text" id="osmSearchInput" onkeypress="if(event.key==='Enter'){event.preventDefault();executeOsmSearch();}" placeholder="Search city, barangay, or landmark..." 
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-gray-400 text-xs"></i>
                </div>
                <button type="button" onclick="executeOsmSearch()" class="px-4 sm:px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs sm:text-sm transition flex items-center gap-1.5 flex-shrink-0">
                    <i class="fa-solid fa-search"></i> <span class="hidden sm:inline">Search</span>
                </button>
            </div>
            <div id="osmSearchResults" class="space-y-1.5 max-h-40 overflow-y-auto hidden bg-white dark:bg-gray-800 rounded-xl p-2 border border-gray-200 dark:border-gray-700 text-xs shadow-inner"></div>
        </div>
        <div class="flex-grow p-3 sm:p-4 min-h-[260px] sm:min-h-[300px] relative">
            <div id="osmMiniMap" class="w-full h-full rounded-xl border border-gray-200 dark:border-gray-700 min-h-[240px] sm:min-h-[280px]"></div>
        </div>
        <div class="p-3 sm:p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row sm:justify-between items-stretch sm:items-center gap-2 flex-shrink-0">
            <div id="selectedOsmLocationText" class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate max-w-full sm:max-w-[65%]">
                No location selected. Click search or tap on map.
            </div>
            <div class="flex space-x-2 justify-end">
                <button type="button" onclick="closeNominatimSearchModal()" class="px-4 py-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Cancel</button>
                <button type="button" id="useOsmLocationBtn" disabled onclick="applySelectedOsmLocation()" class="px-5 py-2 text-xs sm:text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl disabled:opacity-50 transition shadow-sm">
                    <i class="fa-solid fa-check mr-1"></i> Use Location
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let osmMiniMap = null;
    let osmMarker = null;
    let targetInputContext = null;
    let activeParentModalId = null;
    let currentSelectedLocation = null;

    function openNominatimSearch(context) {
        targetInputContext = context;
        activeParentModalId = null;
        if (context === 'dispatch') activeParentModalId = 'dispatchModal';
        if (context === 'order') activeParentModalId = 'addOrderModal';

        if (activeParentModalId) {
            toggleModal(activeParentModalId, false);
        }

        toggleModal('nominatimSearchModal', true);
        
        setTimeout(() => {
            if (!osmMiniMap) {
                osmMiniMap = L.map('osmMiniMap').setView([15.359042, 120.965016], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(osmMiniMap);

                osmMiniMap.on('click', async function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    if (osmMarker) osmMiniMap.removeLayer(osmMarker);
                    osmMarker = L.marker([lat, lng]).addTo(osmMiniMap);

                    document.getElementById('selectedOsmLocationText').innerText = 'Fetching address from OSM Nominatim...';

                    if (typeof NominatimService !== 'undefined') {
                        const geoRes = await NominatimService.reverseGeocode(lat, lng);
                        if (geoRes && geoRes.formatted) {
                            currentSelectedLocation = geoRes.formatted;
                            document.getElementById('selectedOsmLocationText').innerText = `📍 ${geoRes.formatted}`;
                            document.getElementById('useOsmLocationBtn').disabled = false;
                            return;
                        }
                    }
                    currentSelectedLocation = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    document.getElementById('selectedOsmLocationText').innerText = `📍 Coords: ${currentSelectedLocation}`;
                    document.getElementById('useOsmLocationBtn').disabled = false;
                });
            } else {
                osmMiniMap.invalidateSize();
            }
        }, 200);
    }

    function closeNominatimSearchModal() {
        toggleModal('nominatimSearchModal', false);
        if (activeParentModalId) {
            toggleModal(activeParentModalId, true);
            activeParentModalId = null;
        }
    }

    async function executeOsmSearch() {
        const q = document.getElementById('osmSearchInput').value;
        const container = document.getElementById('osmSearchResults');
        if (!q || q.trim().length < 2) return;

        container.classList.remove('hidden');
        container.innerHTML = '<div class="p-2 text-gray-500 italic flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i> Searching OpenStreetMap Nominatim...</div>';

        if (typeof NominatimService !== 'undefined') {
            const results = await NominatimService.searchAddress(q);
            if (results.length === 0) {
                container.innerHTML = '<div class="p-2 text-red-500 font-medium">No results found. Try a different place name.</div>';
                return;
            }

            container.innerHTML = '';
            results.forEach(res => {
                const item = document.createElement('div');
                item.className = 'p-2 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer rounded transition flex items-center justify-between border-b border-gray-100 dark:border-gray-700 last:border-0';
                item.innerHTML = `
                    <div class="truncate mr-2">
                        <div class="font-bold text-gray-800 dark:text-gray-200">${res.shortName}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-sm">${res.name}</div>
                    </div>
                    <button type="button" class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg font-semibold shrink-0">Select</button>
                `;
                item.onclick = function() {
                    currentSelectedLocation = res.shortName;
                    document.getElementById('selectedOsmLocationText').innerText = `📍 ${res.shortName}`;
                    document.getElementById('useOsmLocationBtn').disabled = false;

                    if (osmMiniMap) {
                        osmMiniMap.setView([res.lat, res.lng], 14);
                        if (osmMarker) osmMiniMap.removeLayer(osmMarker);
                        osmMarker = L.marker([res.lat, res.lng]).addTo(osmMiniMap).bindPopup(res.shortName).openPopup();
                    }
                };
                container.appendChild(item);
            });
        }
    }

    function applySelectedOsmLocation() {
        if (!currentSelectedLocation) return;
        
        let destSelect = null;
        if (targetInputContext === 'dispatch') {
            destSelect = document.getElementById('destinationSelect');
        } else if (targetInputContext === 'order') {
            destSelect = document.querySelector('#addOrderModal select[name="destination"]');
        }
        
        if (destSelect) {
            let found = false;
            for (let i = 0; i < destSelect.options.length; i++) {
                if (destSelect.options[i].value.toLowerCase() === currentSelectedLocation.toLowerCase()) {
                    destSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                const opt = new Option(currentSelectedLocation, currentSelectedLocation, true, true);
                destSelect.add(opt);
            }
        }
        closeNominatimSearchModal();
    }
</script>

<!-- ============================================================ -->
<!-- DRIVER PERFORMANCE & WEEKLY ANALYTICS MODAL                   -->
<!-- ============================================================ -->
<div id="driverPerformanceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative max-h-[92vh] flex flex-col border border-gray-100 dark:border-gray-700">
        <!-- Close Button -->
        <button type="button" onclick="toggleModal('driverPerformanceModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 z-10 w-8 h-8 rounded-full bg-white/20 dark:bg-gray-700/50 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark fa-lg"></i>
        </button>

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 p-5 sm:p-6 text-white flex items-center gap-4 flex-shrink-0">
            <!-- Driver Avatar -->
            <img id="dp-driver-photo" src="" alt="Driver Photo" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/80 shadow-md hidden flex-shrink-0">
            <div id="dp-driver-initials" class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white font-extrabold text-2xl shadow-inner flex-shrink-0">
                DR
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-xl font-bold text-white truncate" id="dp-driver-name">Driver Name</h3>
                    <span id="dp-driver-rating-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm border border-white/30 flex items-center gap-1">
                        <i class="fa-solid fa-star text-amber-200 text-xs"></i>
                        <span id="dp-rating-num">5.0</span>
                    </span>
                </div>
                <div class="flex items-center gap-3 text-amber-100 text-xs mt-1 flex-wrap">
                    <span id="dp-driver-cdl"><i class="fa-solid fa-id-card mr-1"></i>CDL: N/A</span>
                    <span>&bull;</span>
                    <span id="dp-driver-truck"><i class="fa-solid fa-truck mr-1"></i>Truck: Unassigned</span>
                </div>
            </div>
        </div>

        <!-- Modal Body (Scrollable with hidden scrollbars) -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5" style="scrollbar-width: none; -ms-overflow-style: none;">
            
            <!-- Section Title -->
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Weekly Performance Overview</h4>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Live analytics aggregated by delivery week</p>
                </div>
                <span class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-1 rounded-full border border-amber-200/50 dark:border-amber-800/40">
                    <i class="fa-solid fa-gauge-high mr-1"></i>Rate: ₱10 / km
                </span>
            </div>

            <!-- Top 4-KPI Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- This Week's KM -->
                <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl p-3.5 text-center">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 mb-1">This Week (KM)</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-amber-700 dark:text-amber-300" id="dp-this-week-km">0.0 km</div>
                    <div class="text-[10px] text-amber-600/80 dark:text-amber-400/70 mt-0.5">Current cycle distance</div>
                </div>

                <!-- This Week's Dispatches -->
                <div class="bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/40 rounded-2xl p-3.5 text-center">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-blue-800 dark:text-blue-400 mb-1">This Week Trips</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-blue-700 dark:text-blue-300" id="dp-this-week-trips">0 Delivered</div>
                    <div class="text-[10px] text-blue-600/80 dark:text-blue-400/70 mt-0.5">Delivered this week</div>
                </div>

                <!-- Average KM per Week -->
                <div class="bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-900/40 rounded-2xl p-3.5 text-center">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400 mb-1">Avg KM / Week</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-emerald-700 dark:text-emerald-300" id="dp-avg-km">0.0 km</div>
                    <div class="text-[10px] text-emerald-600/80 dark:text-emerald-400/70 mt-0.5">Weekly average</div>
                </div>

                <!-- Average Dispatches per Week -->
                <div class="bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl p-3.5 text-center">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-800 dark:text-indigo-400 mb-1">Avg Trips / Week</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-indigo-700 dark:text-indigo-300" id="dp-avg-trips">0.0</div>
                    <div class="text-[10px] text-indigo-600/80 dark:text-indigo-400/70 mt-0.5">Dispatches frequency</div>
                </div>
            </div>

            <!-- Secondary Metrics Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3 bg-gray-50 dark:bg-gray-900/70 rounded-2xl border border-gray-100 dark:border-gray-800 text-xs">
                <div>
                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-bold">On-Time Delivery</span>
                    <span class="font-extrabold text-green-600 dark:text-green-400 text-sm" id="dp-ontime">100.0%</span>
                </div>
                <div>
                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-bold">Total Lifetime KM</span>
                    <span class="font-extrabold text-gray-800 dark:text-gray-200 text-sm" id="dp-lifetime-km">0.0 km</span>
                </div>
                <div>
                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-bold">Lifetime Deliveries</span>
                    <span class="font-extrabold text-gray-800 dark:text-gray-200 text-sm" id="dp-lifetime-trips">0 Trips</span>
                </div>
                <div>
                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-bold">Active Service Weeks</span>
                    <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-sm" id="dp-active-weeks">0 Weeks</span>
                </div>
            </div>

            <!-- Weekly History Breakdown Table -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-week text-amber-500"></i>
                        <span>Weekly Dispatches & Distance History</span>
                    </h5>
                    <span class="text-[11px] text-gray-400" id="dp-weeks-count">0 weeks recorded</span>
                </div>

                <div class="border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                    <div class="max-h-60 overflow-y-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 font-bold uppercase text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2.5">Week & Period</th>
                                    <th class="px-4 py-2.5 text-center">Completed Dispatches</th>
                                    <th class="px-4 py-2.5 text-right">Total Distance</th>
                                    <th class="px-4 py-2.5 text-right">Trip Pay Earned</th>
                                </tr>
                            </thead>
                            <tbody id="dp-weekly-table-body" class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-800">
                                <!-- Populated dynamically by openDriverPerformanceModal -->
                            </tbody>
                        </table>
                    </div>
                    <div id="dp-weekly-empty" class="p-6 text-center text-gray-400 dark:text-gray-500 text-xs hidden">
                        <i class="fa-solid fa-inbox text-2xl mb-1 text-gray-300 dark:text-gray-600 block"></i>
                        No completed deliveries recorded yet for this driver.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="border-t border-gray-100 dark:border-gray-700 p-4 sm:p-5 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between gap-3 flex-shrink-0">
            <button type="button" id="dp-print-trips-btn" onclick="printCurrentDriverTrips()" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print text-blue-500"></i>
                <span>Print Trips Report</span>
            </button>
            <button type="button" onclick="toggleModal('driverPerformanceModal', false)" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gray-900 hover:bg-black transition shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>
</div>
<?php
// $gravelTypes and $destinations are loaded from the DB in admin/dashboard.php
?>

<div id="addTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Add New Truck</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Register a new truck and link its RFID tag.</p>
            </div>
            <button onclick="toggleModal('addTruckModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add_truck">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Plate Number <span class="text-red-500">*</span></label>
                <input type="text" name="truck_code" id="newTruckPlateInput" required placeholder="e.g. ABC 1234" autocomplete="off"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors">
                <p id="plateCheckFeedback" class="text-xs mt-1.5 min-h-[1rem]"></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">RFID Tag <span class="text-red-500">*</span></label>
                <input type="text" name="rfid_tag" id="newTruckRfidInput" required placeholder="Scan or type RFID tag..." autocomplete="off" class="w-full border border-blue-300 dark:border-blue-700 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50 dark:bg-blue-900 dark:text-gray-100 transition-colors">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click the field and scan the RFID card, or type the tag manually.</p>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addTruckModal', false)" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-gray-900 hover:bg-black transition">Add Truck</button>
            </div>
        </form>
    </div>
</div>

<div id="addDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl overflow-hidden relative">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Add New Driver</h3>
            <button onclick="toggleModal('addDriverModal', false)" class="text-gray-500 dark:text-gray-400 hover:text-red-500 transition">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form action="dashboard.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add_driver">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 mb-2">
                <h4 class="font-bold text-blue-700 dark:text-blue-400 text-sm mb-3 flex items-center">
                    <i class="fa-solid fa-truck-fast mr-2"></i> Truck Assignment
                </h4>
                <div class="flex space-x-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Scan Truck RFID <span class="text-red-500">*</span></label>
                        <input type="text" name="truck_rfid" id="driverTruckRfidInput" required placeholder="Scan tag..." autocomplete="off" class="w-full border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 transition-colors">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Code</label>
                        <input type="text" id="driverTruckCodeDisplay" readonly placeholder="---" class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-2 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed text-center font-bold">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="Juan Dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Licence Number</label>
                    <input type="text" name="cdl_number" required placeholder="N01-XX-XXXXXX" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Phone</label>
                    <input type="text" name="phone" required placeholder="0912-345-6789" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Username</label>
                    <input type="text" name="username" required placeholder="juan.dela.cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
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

    // Auto-fill Truck Code when RFID is scanned in Add Driver Modal
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

<div id="dispatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl overflow-hidden relative">
        <button onclick="toggleModal('dispatchModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200">
            <i class="fa-solid fa-xmark fa-lg"></i>
        </button>
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Create New Dispatch Ticket</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan the truck's RFID to auto-fill details, then select destination and gravel type to calculate pay.</p>
        </div>
        <form method="POST" action="dashboard.php" class="p-6" id="dispatchForm">
            <input type="hidden" name="action" value="create_dispatch">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="truck_id" id="hiddenTruckId" required>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Scan Truck RFID Tag <span class="text-red-500">*</span></label>
                <input type="text" id="rfidInput" name="rfid_tag" placeholder="Click here and scan RFID card..." required autofocus autocomplete="off" class="w-full border border-blue-300 dark:border-blue-700 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50 dark:bg-blue-900 dark:text-gray-100 transition-colors">
                <p id="rfidFeedback" class="text-xs mt-1 text-gray-500 dark:text-gray-400">Waiting for scan...</p>
            </div>
            <div class="grid grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Truck Plate Number</label>
                    <input type="text" id="truckPlate" readonly placeholder="Auto-filled after scan" class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Assigned Driver</label>
                    <input type="hidden" name="driver_id" id="hiddenDriverId" required>
                    <input type="text" id="assignedDriverName" readonly placeholder="Auto-filled after scan" class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 focus:outline-none cursor-not-allowed">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Origin</label>
                    <input type="text" name="origin" value="Brgy. Burgos San Leonardo, Nueva Ecija" required class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Destination</label>
                    <select name="destination" id="destinationSelect" required class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Select Destination</option>
                        <?php foreach ($destinations as $_dest): ?>
                            <option value="<?= htmlspecialchars($_dest['name']); ?>"><?= htmlspecialchars($_dest['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Gravel Type <span class="text-red-500">*</span></label>
                <select id="gravelType" name="gravel_type" required class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Select gravel type</option>
                    <?php foreach ($gravelTypes as $value => $label): ?>
                        <option value="<?= $value; ?>"><?= htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="toggleModal('dispatchModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-black hover:bg-gray-800 transition">Create Dispatch</button>
            </div>
        </form>
    </div>
</div>

<div id="viewDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <button onclick="toggleModal('viewDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="bg-blue-600 p-6 text-center">
            <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600 mx-auto mb-3 shadow-lg" id="vd-initials">--</div>
            <h3 class="text-xl font-bold text-white" id="vd-name">Driver Name</h3>
            <p class="text-blue-100 text-sm mt-1" id="vd-cdl">Licence #</p>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                    <div class="font-bold text-gray-800 dark:text-gray-200" id="vd-status">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Current Truck</div>
                    <div class="font-bold text-blue-600" id="vd-truck">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Deliveries</div>
                    <div class="font-bold text-gray-800 dark:text-gray-200" id="vd-deliveries">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">On-Time Rate</div>
                    <div class="font-bold text-green-600" id="vd-ontime">--</div>
                </div>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-2 text-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-phone text-gray-400 w-5 text-center"></i>
                    <span id="vd-phone" class="font-medium text-gray-700 dark:text-gray-200">--</span>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">Recent Deliveries</h4>
                    <div class="text-sm font-bold text-green-600 dark:text-green-400">
                        Total Owed: ₱<span id="vd-owed-balance">0.00</span>
                    </div>
                </div>
                <style>
                    .scrollbar-hide::-webkit-scrollbar {
                        display: none;
                    }

                    .scrollbar-hide {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                    }
                </style>
                <div id="vd-recent-trips" class="space-y-2 max-h-40 overflow-y-auto scrollbar-hide"></div>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-between">
            <div class="flex space-x-2">
                <button id="vd-settle-btn" class="px-6 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition shadow-sm hidden">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i> Settle Payroll
                </button>
                <a id="vd-print-btn" href="#" target="_blank" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition shadow-sm hidden">
                    <i class="fa-solid fa-print mr-1"></i> Print Ticket
                </a>
            </div>
            <button onclick="toggleModal('viewDriverModal', false)" class="px-6 py-2 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition ml-auto">Close</button>
        </div>
    </div>
</div>

<div id="contactDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('contactDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-regular fa-comments"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1" id="cd-title">Contact Driver</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Choose how you want to reach out to this driver.</p>
        <div class="space-y-3">
            <a href="#" id="cd-phone-link" class="w-full flex items-center justify-center space-x-2 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 py-3 rounded-lg font-semibold transition">
                <i class="fa-solid fa-phone"></i><span id="cd-phone-text">Call Number</span>
            </a>
        </div>
    </div>
</div>

<div id="updateStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('updateStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-rotate-right"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Update Truck Status</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Manually override the current status for <strong id="us-truck-code" class="text-gray-800 dark:text-gray-200"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="update_truck_status">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="truck_id" id="update_status_truck_id" value="">
            <div class="mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Status</label>
                <select name="new_status" id="update_status_select" required class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200">
                    <option value="Idle">Idle</option>
                    <option value="Loading">Loading</option>
                    <option value="In Transit">In Transit (On Trip)</option>
                    <option value="Unloading">Unloading</option>
                    <option value="Maintenance">Maintenance (Broken)</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="updateDriverStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('updateDriverStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-id-card"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Update Driver Status</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Manually override the current status for <strong id="uds-driver-name" class="text-gray-800 dark:text-gray-200"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="update_driver_status">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="update_status_driver_id" value="">
            <div class="mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Status</label>
                <select name="new_status" id="update_driver_status_select" required class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200">
                    <option value="Active">Active</option>
                    <option value="Off Duty">Off Duty</option>
                    <option value="Dispatched">Dispatched</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateDriverStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="switchTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('switchTruckModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-truck-arrow-right"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Switch Truck</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Assign a new Idle truck to <strong id="st-driver-name" class="text-gray-800 dark:text-gray-200"></strong>. Current truck: <strong id="st-truck-code"></strong>.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="switch_truck">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="switch_truck_driver_id" value="">
            <input type="hidden" name="redirect_tab" id="switch_truck_redirect_tab" value="drivers">
            <div class="mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select New Truck</label>
                <select name="new_truck_id" required class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-900 font-medium text-gray-700 dark:text-gray-200">
                    <option value="">— Select Idle Truck —</option>
                    <?php foreach ($availableTrucks ?? [] as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['truck_code']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Active dispatches will automatically transfer to the new truck.</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('switchTruckModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Switch Truck</button>
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

<div id="settlePayrollModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <div class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Settle Payroll</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to mark <strong id="sp-driver-name" class="text-gray-800 dark:text-gray-200"></strong>'s balance of <strong id="sp-balance" class="text-green-600 dark:text-green-400"></strong> as paid?</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="settle_payroll">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="driver_id" id="settle_driver_id">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('settlePayrollModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition dark:bg-black">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== ADD ORDER MODAL ==================== -->
<div id="addOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg overflow-hidden relative">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Place New Order</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a gravel delivery order for a client.</p>
            </div>
            <button onclick="toggleModal('addOrderModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add_order">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Client Name <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" required placeholder="e.g. Juan dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Destination <span class="text-red-500">*</span></label>
                    <select name="destination" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select destination</option>
                        <?php foreach ($destinations as $_dest): ?>
                            <option value="<?= htmlspecialchars($_dest['name']); ?>"><?= htmlspecialchars($_dest['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Gravel Type <span class="text-red-500">*</span></label>
                    <select name="gravel_type" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select type</option>
                        <?php foreach ($gravelTypes as $val => $lbl): ?>
                            <option value="<?= $val ?>"><?= htmlspecialchars($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Number of Trucks <span class="text-red-500">*</span></label>
                    <input type="number" name="trucks_required" required min="1" max="100" placeholder="e.g. 5" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Assign Checker <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="checker_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">— Assign later —</option>
                    <?php foreach ($allCheckers ?? [] as $chk): ?>
                        <option value="<?= $chk['id'] ?>"><?= htmlspecialchars($chk['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="2" placeholder="Special instructions, remarks..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addOrderModal', false)" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-gray-900 hover:bg-black transition">Place Order</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== ADD CHECKER MODAL ==================== -->
<div id="addCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Add Checker Account</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a login for a new field checker.</p>
            </div>
            <button onclick="toggleModal('addCheckerModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <form method="POST" action="dashboard.php" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" required placeholder="Juan" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" required placeholder="Dela Cruz" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="text" name="phone" required placeholder="09123456789" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="checker_username" required placeholder="e.g. checker_juan" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Password <span class="text-red-500">*</span></label>
                <div class="flex">
                    <input type="text" id="checkerPasswordInput" name="checker_password" required readonly placeholder="Click Generate" class="p-2 w-full border border-gray-300 dark:border-gray-600 rounded-l-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm cursor-not-allowed">
                    <button type="button" onclick="generateCheckerPassword()" class="px-4 py-2 bg-blue-100 dark:bg-blue-900 border border-l-0 border-blue-300 dark:border-blue-700 rounded-r-md hover:bg-blue-200 text-blue-700 dark:text-blue-300 transition font-medium">Generate</button>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleModal('addCheckerModal', false)" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Create Checker</button>
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

<div id="assignCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6">
        <button onclick="toggleModal('assignCheckerModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 text-center mb-1">Assign Checker</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">Order: <strong id="ac-order-number" class="text-gray-800 dark:text-gray-200"></strong></p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="assign_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="order_id" id="ac_order_id">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Select Checker</label>
                <select name="checker_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
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
                <button type="button" onclick="toggleModal('assignCheckerModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Assign</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Remove Checker</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to remove <strong id="dc-checker-name" class="text-gray-800 dark:text-gray-200"></strong> from the system? Their account will be permanently deleted.</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="delete_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="checker_id" id="delete_checker_id">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('deleteCheckerModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Keep Account</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Remove</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== CANCEL ORDER MODAL ==================== -->
<div id="cancelOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <div class="w-14 h-14 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-ban"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Cancel Order</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to cancel order <strong id="co-order-number" class="text-gray-800 dark:text-gray-200"></strong>?</p>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="cancel_order">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="order_id" id="co_order_id">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('cancelOrderModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-black hover:bg-gray-200 transition">Keep Order</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== DELETE CHECKER MODAL ==================== -->
<div id="deleteCheckerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('deleteCheckerModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-user-minus"></i></div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Delete Checker?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to remove <strong id="dc-name" class="text-gray-800 dark:text-gray-200"></strong>? This action cannot be undone.</p>
        <form method="POST" action="dashboard.php" class="flex justify-center space-x-3">
            <input type="hidden" name="action" value="delete_checker">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="checker_id" id="delete_checker_id" required>
            <button type="button" onclick="toggleModal('deleteCheckerModal', false)" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition w-full">Cancel</button>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition w-full">Delete</button>
        </form>
    </div>
</div>
</div>
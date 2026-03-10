<div id="dispatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden relative">
        <button onclick="toggleModal('dispatchModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Create New Dispatch Ticket</h3>
            <p class="text-sm text-gray-500 mt-1">Enter the details for a new dispatch. All fields are required.</p>
        </div>
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="create_dispatch">
            <div class="grid grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Assign Driver</label>
                    <select name="driver_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                        <option value="">Select a driver</option>
                        <?php foreach ($allDrivers as $driver): ?>
                            <option value="<?php echo $driver['id']; ?>">
                                <?php echo htmlspecialchars($driver['name']); ?> (<?php echo $driver['status']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Driver Name</label>
                    <input type="text" id="driverName" readonly placeholder="Select a truck first" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-100 text-gray-600 focus:outline-none cursor-not-allowed">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Truck RFID Tag</label>
                <input type="text" id="rfidInput" name="rfid_tag" placeholder="Auto-filled from selected truck" readonly required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-100 text-gray-600 focus:outline-none cursor-not-allowed">
            </div>
            <div class="grid grid-cols-2 gap-6 mb-4">
                <div><label class="block text-sm font-semibold text-gray-800 mb-2">Origin</label><input type="text" name="origin" value="San Leonardo, Nueva Ecija" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"></div>
                <div><label class="block text-sm font-semibold text-gray-800 mb-2">Destination</label><input type="text" name="destination" placeholder="e.g. Cabanatuan City" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"></div>
            </div>
            <div class="mb-8 w-1/2 pr-3">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Weight (lbs)</label>
                <input type="number" name="weight" placeholder="10000" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('dispatchModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-black hover:bg-gray-800 transition">Create Dispatch</button>
            </div>
        </form>
    </div>
</div>

<div id="driverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden relative">
        <button onclick="toggleModal('driverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Add New Driver</h3>
            <p class="text-sm text-gray-500 mt-1">Register a new driver to the system.</p>
        </div>
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="add_driver">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Full Name</label>
                <input type="text" name="name" placeholder="e.g. John Doe" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 mb-2">CDL Number</label>
                <input type="text" name="cdl_number" placeholder="e.g. CDL-123456" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
            </div>
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Phone Number</label>
                    <input type="text" name="phone" placeholder="09XX-XXX-XXXX" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Email Address</label>
                    <input type="email" name="email" placeholder="driver@email.com" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('driverModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-black hover:bg-gray-800 transition">Save Driver</button>
            </div>
        </form>
    </div>
</div>

<div id="viewDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <button onclick="toggleModal('viewDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="bg-blue-600 p-6 text-center">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-2xl font-bold text-blue-600 mx-auto mb-3 shadow-lg" id="vd-initials">--</div>
            <h3 class="text-xl font-bold text-white" id="vd-name">Driver Name</h3>
            <p class="text-blue-100 text-sm mt-1" id="vd-cdl">CDL-XXXXX</p>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-xs text-gray-500">Status</div>
                    <div class="font-bold text-gray-800" id="vd-status">--</div>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-xs text-gray-500">Current Truck</div>
                    <div class="font-bold text-blue-600" id="vd-truck">--</div>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-xs text-gray-500">Total Deliveries</div>
                    <div class="font-bold text-gray-800" id="vd-deliveries">--</div>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-xs text-gray-500">On-Time Rate</div>
                    <div class="font-bold text-green-600" id="vd-ontime">--</div>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                <div class="flex items-center space-x-3"><i class="fa-solid fa-phone text-gray-400 w-5 text-center"></i> <span id="vd-phone" class="font-medium text-gray-700">--</span></div>
                <div class="flex items-center space-x-3"><i class="fa-regular fa-envelope text-gray-400 w-5 text-center"></i> <span id="vd-email" class="font-medium text-gray-700">--</span></div>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50 text-right">
            <button onclick="toggleModal('viewDriverModal', false)" class="px-6 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition">Close</button>
        </div>
    </div>
</div>

<div id="contactDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('contactDriverModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-regular fa-comments"></i></div>
        <h3 class="text-xl font-bold text-gray-900 mb-1" id="cd-title">Contact Driver</h3>
        <p class="text-sm text-gray-500 mb-6">Choose how you want to reach out to this driver.</p>
        <div class="space-y-3">
            <a href="#" id="cd-phone-link" class="w-full flex items-center justify-center space-x-2 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 py-3 rounded-lg font-semibold transition"><i class="fa-solid fa-phone"></i><span id="cd-phone-text">Call Number</span></a>
            <a href="#" id="cd-email-link" class="w-full flex items-center justify-center space-x-2 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 py-3 rounded-lg font-semibold transition"><i class="fa-regular fa-envelope"></i><span id="cd-email-text">Send Email</span></a>
        </div>
    </div>
</div>

<div id="updateStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('updateStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-rotate-right"></i></div>
        <h3 class="text-xl font-bold text-gray-900 mb-1">Update Truck Status</h3>
        <p class="text-sm text-gray-500 mb-6">Manually override the current status for <strong id="us-truck-code" class="text-gray-800"></strong>.</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_truck_status">
            <input type="hidden" name="truck_id" id="update_status_truck_id" value="">
            <div class="mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Select New Status</label>
                <select name="new_status" id="update_status_select" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 font-medium text-gray-700">
                    <option value="Idle">Idle</option>
                    <option value="Loading">Loading</option>
                    <option value="In Transit">In Transit (On Trip)</option>
                    <option value="Unloading">Unloading</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteDriverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Remove Driver</h3>
        <p class="text-sm text-gray-500 mb-6">Are you sure you want to remove <strong id="dd-name" class="text-gray-800"></strong> from the system? This action cannot be undone.</p>
        <form id="deleteDriverForm" method="POST" action="">
            <input type="hidden" name="action" value="delete_driver">
            <input type="hidden" name="driver_id" id="delete_driver_id" value="">
            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('deleteDriverModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Yes, Remove</button>
            </div>
        </form>
    </div>
</div>

<div id="updateDriverStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden relative p-6 text-center">
        <button onclick="toggleModal('updateDriverStatusModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>

        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-user-pen"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-1">Update Driver Status</h3>
        <p class="text-sm text-gray-500 mb-6">Manually change the current status for <strong id="uds-driver-name" class="text-gray-800"></strong>.</p>

        <form method="POST" action="">
            <input type="hidden" name="action" value="update_driver_status">
            <input type="hidden" name="driver_id" id="update_status_driver_id" value="">

            <div class="mb-6 text-left">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Select New Status</label>
                <select name="new_status" id="update_driver_status_select" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 font-medium text-gray-700">
                    <option value="Active">Active</option>
                    <option value="Dispatched">Dispatched</option>
                    <option value="Off Duty">Off Duty</option>
                </select>
            </div>

            <div class="flex space-x-3">
                <button type="button" onclick="toggleModal('updateDriverStatusModal', false)" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div id="addTruckModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <button onclick="toggleModal('addTruckModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark fa-lg"></i></button>
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Add New Truck</h3>
            <p class="text-sm text-gray-500 mt-1">Register a new truck to the fleet and assign an RFID tag.</p>
        </div>
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="add_truck">

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Truck Code / Plate Number</label>
                <input type="text" name="truck_code" placeholder="e.g. TRK-006 or ABC-1234" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-800 mb-2"><i class="fa-solid fa-wifi mr-1 text-blue-500"></i> Scan RFID Tag</label>
                <input type="text" id="newTruckRfidInput" name="rfid_tag" placeholder="Scan or enter RFID-XXXXXX" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50/50">
                <p class="text-xs text-gray-500 mt-2"><i class="fa-solid fa-circle-info"></i> Click here and tap the card on the reader to auto-fill.</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('addTruckModal', false)" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-black hover:bg-gray-800 transition">Save Truck</button>
            </div>
        </form>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-80 hidden flex-col transition-opacity duration-300">
    <div id="loadingState" class="flex flex-col items-center">
        <svg class="animate-spin h-14 w-14 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <h3 class="text-xl font-bold text-white tracking-wide">Deleting the driver...</h3>
    </div>
    <div id="successState" class="flex flex-col items-center hidden">
        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-3xl mb-4 shadow-[0_0_15px_rgba(34,197,94,0.5)] animate-bounce"><i class="fa-solid fa-check"></i></div>
        <h3 class="text-xl font-bold text-white tracking-wide">Completed!</h3>
    </div>
</div>
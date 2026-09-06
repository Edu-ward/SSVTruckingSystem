<?php /* $allDestinations is loaded from admin/dashboard.php */ ?>
<div id="view-settings" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-2 text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-map-location-dot text-blue-600 dark:text-blue-400"></i>
            <span>Destination Rate Settings</span>
        </div>
        <button onclick="toggleModal('addDestinationModal', true)" class="btn-primary text-sm w-full sm:w-auto">
            <i class="fa-solid fa-plus"></i><span>Add Destination</span>
        </button>
    </div>

    <?php if (!empty($_SESSION['dest_success'])): ?>
        <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-xl text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($_SESSION['dest_success']); unset($_SESSION['dest_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['dest_error'])): ?>
        <div class="mb-4 px-4 py-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl text-sm text-red-800 dark:text-red-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($_SESSION['dest_error']); unset($_SESSION['dest_error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-circle-info mr-1 text-blue-500"></i>
                Set the <strong>one-way</strong> distance (garage to site) for each destination. Driver pay = <strong class="text-blue-600 dark:text-blue-400">distance x P10/km</strong>. If no distance is set, the flat <em>Driver Rate</em> is used instead.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/60 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="py-3 px-5 text-left">Destination</th>
                        <th class="py-3 px-4 text-center">Distance (Round Trip)</th>
                        <th class="py-3 px-4 text-center">Trip Pay (₱10/km)</th>
                        <th class="py-3 px-4 text-center">Flat Rate (fallback)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($allDestinations as $dest):
                        $km    = floatval($dest['distance_km']);
                        $rate  = floatval($dest['driver_rate']);
                        $pay   = $km > 0 ? round($km * 10, 2) : $rate;
                        $active = intval($dest['is_active']);
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <td class="py-3.5 px-5 font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($dest['name']); ?></td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($km > 0): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    <i class="fa-solid fa-arrows-left-right text-[10px]"></i><?= number_format($km, 1); ?> km (round trip)
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-red-400 font-semibold">Not set</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($km > 0): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">₱<?= number_format($pay, 2); ?></span>
                            <?php else: ?><span class="text-xs text-gray-400 italic">-</span><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($rate > 0): ?>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">₱<?= number_format($rate, 2); ?></span>
                            <?php else: ?><span class="text-xs text-gray-400 italic">-</span><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($active): ?>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-5 text-right space-x-2">
                            <button onclick="openEditDestinationModal(<?= htmlspecialchars(json_encode(['id'=>$dest['id'],'name'=>$dest['name'],'distance_km'=>$dest['distance_km'],'driver_rate'=>$dest['driver_rate'],'is_active'=>$dest['is_active']])); ?>)"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <i class="fa-solid fa-pen-to-square mr-1"></i>Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allDestinations)): ?>
                        <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400 italic">No destinations configured yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD DESTINATION MODAL -->
<div id="addDestinationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Add Destination</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Define round-trip distance (back & forth) for driver pay.</p>
            </div>
            <button onclick="toggleModal('addDestinationModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        </div>
        <form method="POST" action="dashboard.php?tab=settings" class="p-5 sm:p-6 space-y-4">
            <input type="hidden" name="action" value="add_destination">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Destination Name <span class="text-red-500">*</span></label>
                <input type="text" name="dest_name" required placeholder="e.g. Laur, Nueva Ecija" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Round Trip Distance (km) <span class="text-blue-500 font-normal text-xs">- back & forth</span></label>
                <input type="number" step="0.1" min="0" name="dest_distance_km" placeholder="e.g. 90.0" id="addDestDistKm" oninput="calcAddPay()" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                <p id="addDistPayPreview" class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 hidden">Driver Pay: <strong id="addDistPayAmt"></strong></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Flat Driver Rate (₱) <span class="text-gray-400 font-normal text-xs">- fallback when distance is 0</span></label>
                <input type="number" step="0.01" min="0" name="dest_driver_rate" placeholder="e.g. 900.00" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
            </div>
            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="toggleModal('addDestinationModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Add Destination</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT DESTINATION MODAL -->
<div id="editDestinationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Edit Destination</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Update round-trip distance (back & forth) for driver pay.</p>
            </div>
            <button onclick="toggleModal('editDestinationModal', false)" class="text-gray-400 hover:text-gray-700 dark:text-gray-200"><i class="fa-solid fa-xmark fa-lg"></i></button>
        </div>
        <form method="POST" action="dashboard.php?tab=settings" class="p-5 sm:p-6 space-y-4">
            <input type="hidden" name="action" value="edit_destination">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="dest_id" id="editDestId">
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Destination Name <span class="text-red-500">*</span></label>
                <input type="text" id="editDestName" name="dest_name" required class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Round Trip Distance (km) <span class="text-blue-500 font-normal text-xs">- back & forth</span></label>
                <input type="number" step="0.1" min="0" name="dest_distance_km" id="editDestDistKm" oninput="calcEditPay()" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Driver Pay: <strong id="editDistPayAmt">-</strong></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Flat Driver Rate (₱) <span class="text-gray-400 font-normal text-xs">- fallback when distance is 0</span></label>
                <input type="number" step="0.01" min="0" name="dest_driver_rate" id="editDestDriverRate" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Status</label>
                <select name="dest_is_active" id="editDestIsActive" class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 dark:text-gray-100 text-sm">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="toggleModal('editDestinationModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditDestinationModal(dest) {
    document.getElementById('editDestId').value         = dest.id;
    document.getElementById('editDestName').value       = dest.name;
    document.getElementById('editDestDistKm').value     = parseFloat(dest.distance_km) > 0 ? parseFloat(dest.distance_km) : '';
    document.getElementById('editDestDriverRate').value = parseFloat(dest.driver_rate) > 0 ? parseFloat(dest.driver_rate) : '';
    document.getElementById('editDestIsActive').value   = dest.is_active;
    calcEditPay();
    toggleModal('editDestinationModal', true);
}
function calcAddPay() {
    var km = parseFloat(document.getElementById('addDestDistKm').value || 0);
    var preview = document.getElementById('addDistPayPreview');
    var amtEl = document.getElementById('addDistPayAmt');
    if (km > 0) {
        amtEl.textContent = 'P' + (km * 10).toFixed(2) + ' (' + km.toFixed(1) + ' km x P10/km)';
        preview.classList.remove('hidden');
    } else { preview.classList.add('hidden'); }
}
function calcEditPay() {
    var km = parseFloat(document.getElementById('editDestDistKm').value || 0);
    var amtEl = document.getElementById('editDistPayAmt');
    amtEl.textContent = km > 0 ? 'P' + (km * 10).toFixed(2) + ' (' + km.toFixed(1) + ' km x P10/km)' : '- (flat rate fallback)';
}
</script>

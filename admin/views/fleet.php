<div id="view-fleet" class="tab-content hidden">

    <?php if (isset($_GET['open']) && $_GET['open'] === 'addTruck'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toggleModal('addTruckModal', true);
        });
    </script>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-center space-x-2 text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-200">
            <i class="fa-solid fa-truck-fast text-blue-600 dark:text-blue-400"></i>
            <span>Fleet Management</span>
        </div>
        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
            <!-- Fleet Live Search Bar -->
            <div class="relative flex-1 sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="fleetSearchInput" placeholder="Search trucks, drivers, status, RFID..." oninput="filterFleetCards()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="fleetSearchClear" onclick="clearFleetSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">Total: <?= count($fleetData); ?> trucks</div>
            <button onclick="toggleModal('addTruckModal', true)" class="btn-primary text-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Add Truck</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="fleetCardsGrid">
        <?php foreach ($fleetData as $truck):
            $badgeClass = 'bg-gray-500';
            if ($truck['status'] == 'In Transit') $badgeClass = 'bg-green-500';
            if ($truck['status'] == 'Idle') $badgeClass = 'bg-yellow-500';
            if ($truck['status'] == 'Loading') $badgeClass = 'bg-blue-500';
            if ($truck['status'] == 'Unloading') $badgeClass = 'bg-orange-500';
            if ($truck['status'] == 'Maintenance') $badgeClass = 'bg-red-600';
            if ($truck['status'] == 'Decommissioned') $badgeClass = 'bg-stone-500';

            $searchMeta = htmlspecialchars(strtolower(($truck['truck_code'] ?? '') . ' ' . ($truck['driver_name'] ?? '') . ' ' . ($truck['status'] ?? '') . ' ' . ($truck['rfid_tag'] ?? '') . ' ' . ($truck['current_location'] ?? '') . ' ' . ($truck['destination'] ?? '')));
        ?>
            <div class="fleet-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 sm:p-6 flex flex-col h-full relative hover:shadow-md transition" data-search="<?= $searchMeta; ?>">
                <div class="flex justify-between items-start mb-5 gap-2">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg truncate"><?= htmlspecialchars($truck['truck_code']); ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($truck['driver_name'] ?? 'No Driver Assigned'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1.5 flex-shrink-0 ml-auto">
                        <span class="<?= $badgeClass; ?> text-white text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                            <?= htmlspecialchars($truck['status']); ?>
                        </span>

                        <button onclick="openUpdateStatusModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['status']); ?>', '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-blue-600 transition p-1" title="Change Truck Status">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <button onclick="openDecommissionTruckModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="text-gray-400 hover:text-amber-600 transition p-1 <?= $truck['status'] === 'Decommissioned' ? 'opacity-40 cursor-not-allowed' : '' ?>" title="<?= $truck['status'] === 'Decommissioned' ? 'Truck Already Decommissioned' : 'Decommission Truck' ?>" <?= $truck['status'] === 'Decommissioned' ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-5">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-wifi w-4 text-center text-blue-400"></i>
                        <span>RFID:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['rfid_tag'] ?? 'Unassigned'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-bolt w-4 text-center text-gray-400"></i>
                        <span>Speed:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['speed'] ?? 0); ?> mph</strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-location-dot w-4 text-center text-gray-400"></i>
                        <span>Destination:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['current_location'] ?? 'Garage'); ?></strong>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-tower-broadcast w-4 text-center text-purple-400"></i>
                        <span>Coordinates:
                            <strong class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($truck['latitude'] ?? '0.0000'); ?>,
                                <?= htmlspecialchars($truck['longitude'] ?? '0.0000'); ?></strong>
                        </span>
                    </div>
                </div>

                <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6 mt-auto dark:bg-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Active Dispatch</div>
                    <?php if ($truck['ticket_number']): ?>
                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm mb-1"><?= htmlspecialchars($truck['ticket_number']); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-2">
                            <span>San Leonardo, Nueva Ecija</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                            <span><?= htmlspecialchars($truck['destination']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 dark:text-gray-400 italic py-1">No active dispatch</div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-3 mt-auto">
                    <button onclick="focusTruck(<?= $truck['latitude'] ?? 0; ?>, <?= $truck['longitude'] ?? 0; ?>)" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-location-crosshairs text-gray-500 dark:text-gray-400"></i>
                        <span>Track</span>
                    </button>
                    <?php if ($truck['status'] === 'Maintenance'): ?>
                    <button onclick="openMarkFixedModal(<?= $truck['id']; ?>, '<?= htmlspecialchars($truck['truck_code']); ?>')" class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg py-2 text-sm font-semibold transition flex items-center justify-center space-x-2 shadow-sm shadow-green-200 dark:shadow-none">
                        <i class="fa-solid fa-wrench"></i>
                        <span>Mark as Fixed</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div id="noFleetSearchResults" class="hidden col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-truck"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No trucks found</h4>
            <p class="text-xs text-gray-400 mt-1" id="noFleetSearchText">No fleet vehicles match your search filter.</p>
            <button type="button" onclick="clearFleetSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer">
                Clear search filter
            </button>
        </div>
    </div>

<script>
function filterFleetCards() {
    const input = document.getElementById('fleetSearchInput');
    const clearBtn = document.getElementById('fleetSearchClear');
    const query = input ? input.value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('#fleetCardsGrid .fleet-card');
    const noResults = document.getElementById('noFleetSearchResults');
    const noResultsText = document.getElementById('noFleetSearchText');

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
    }

    let matchCount = 0;
    cards.forEach(card => {
        const meta = card.getAttribute('data-search') || '';
        if (!query || meta.includes(query)) {
            card.classList.remove('hidden');
            matchCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    if (noResults) {
        if (matchCount === 0 && cards.length > 0) {
            noResults.classList.remove('hidden');
            if (noResultsText) noResultsText.textContent = `No fleet vehicles match "${query}".`;
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function clearFleetSearch() {
    const input = document.getElementById('fleetSearchInput');
    if (input) {
        input.value = '';
        filterFleetCards();
        input.focus();
    }
}
</script>
</div>
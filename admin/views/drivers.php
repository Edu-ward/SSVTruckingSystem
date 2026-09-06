<div id="view-drivers" class="tab-content hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">Driver Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage drivers, lifetime deliveries, payroll, and assignments</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Driver Live Search Bar -->
            <div class="relative flex-1 sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="driverSearchInput" placeholder="Search drivers, CDL, trucks, phone..." oninput="filterDriverCards()" class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <button type="button" id="driverSearchClear" onclick="clearDriverSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <button onclick="toggleModal('addDriverModal', true)" class="btn-primary text-sm w-full sm:w-auto">
                <i class="fa-solid fa-user-plus"></i><span>Add Driver</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-users text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['total_drivers'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">Total Drivers</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $driverStats['on_duty'] ?? 0; ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">On Duty</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center justify-center">
            <i class="fa-solid fa-star text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= number_format($driverStats['avg_rating'] ?? 5.0, 1); ?></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold tracking-wider">Avg Rating</div>
        </div>
    </div>

<style>
    .driver-card,
    .driver-card * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .driver-card::-webkit-scrollbar,
    .driver-card *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="driverCardsGrid">
        <?php foreach ($allDrivers as $driver):
            $badgeClass = 'bg-gray-500';
            if ($driver['status'] == 'Active') $badgeClass = 'bg-emerald-500';
            if ($driver['status'] == 'Dispatched' || $driver['status'] == 'In Transit') $badgeClass = 'bg-blue-600';
            if ($driver['status'] == 'Resigned') $badgeClass = 'bg-amber-600';

            $driverJson = htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8');

            // Resolve profile photo URL
            // admin/views/ is 2 levels deep → dirname(__DIR__,2) = CAPSTONE root
            // But the browser serves from admin/, so only one ../ is needed
            $dPhotoPath = $driver['profile_photo'] ?? null;
            $dPhotoFull = $dPhotoPath ? (dirname(__DIR__, 2) . '/' . $dPhotoPath) : null;
            $dPhotoUrl  = ($dPhotoFull && file_exists($dPhotoFull))
                ? '../' . htmlspecialchars($dPhotoPath) . '?v=' . filemtime($dPhotoFull)
                : null;
            
            $hasPayable = ($driver['net_earnings'] ?? 0) > 0;
            $searchMeta = htmlspecialchars(strtolower(($driver['name'] ?? '') . ' ' . ($driver['cdl_number'] ?? '') . ' ' . ($driver['truck_code'] ?? '') . ' ' . ($driver['phone'] ?? '') . ' ' . ($driver['status'] ?? '')));
        ?>
            <div class="driver-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-all duration-200 overflow-hidden" data-search="<?= $searchMeta; ?>">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-3 min-w-0 pr-2">
                            <?php if ($dPhotoUrl): ?>
                                <img src="<?= $dPhotoUrl ?>" alt="<?= htmlspecialchars($driver['name']) ?>"
                                     class="w-12 h-12 rounded-xl object-cover shadow-md flex-shrink-0 border-2 border-white dark:border-gray-700 ring-2 ring-blue-500/20">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-md shadow-blue-500/20 flex-shrink-0">
                                    <?= getInitials($driver['name']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base truncate" title="<?= htmlspecialchars($driver['name']); ?>"><?= htmlspecialchars($driver['name']); ?></h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">CDL: <?= htmlspecialchars($driver['cdl_number'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <span class="<?= $badgeClass; ?> text-white text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm flex-shrink-0">
                            <?= htmlspecialchars($driver['status']); ?>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300 mb-3">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-truck text-blue-500 w-4 text-center"></i>
                                <span>Assigned Truck</span>
                            </span>
                            <span class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['truck_code'] ?? 'Unassigned'); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-phone text-indigo-500 w-4 text-center"></i>
                                <span>Phone Number</span>
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($driver['phone'] ?? 'N/A'); ?></span>
                        </div>
                        <!-- Performance Button (Click to view full weekly analytics & dispatch stats) -->
                        <button type="button" 
                                onclick='openDriverPerformanceModal(<?= $driverJson; ?>)'
                                title="Click to view weekly kilometers, dispatches, and performance analytics"
                                class="w-full flex items-center justify-between p-2.5 bg-amber-50/70 hover:bg-amber-100/80 dark:bg-amber-950/20 dark:hover:bg-amber-900/30 border border-amber-200/50 dark:border-amber-800/40 rounded-xl transition-all group cursor-pointer text-left">
                            <span class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 font-medium">
                                <i class="fa-solid fa-chart-line text-amber-500 w-4 text-center group-hover:scale-110 transition-transform"></i>
                                <span>Performance</span>
                            </span>
                            <span class="flex items-center space-x-1.5 font-bold text-amber-800 dark:text-amber-300 text-xs">
                                <span><?= number_format($driver['rating'] ?? 5.0, 1); ?> / 5.0</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-amber-500/70 group-hover:translate-x-0.5 transition-transform"></i>
                            </span>
                        </button>
                        <!-- Remaining Balance (Uniformly shown on all driver cards) -->
                        <div class="flex items-center justify-between p-2.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                            <span class="flex items-center space-x-2 text-indigo-600 dark:text-indigo-400 font-medium">
                                <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>
                                <span>Remaining Balance</span>
                            </span>
                            <span class="font-bold text-indigo-700 dark:text-indigo-400">₱<?= number_format($driver['remaining_balance'] ?? 0, 2); ?></span>
                        </div>
                        <?php if (($driver['approved_cash_advances'] ?? 0) > 0): ?>
                        <div class="flex items-center justify-between p-2.5 bg-orange-50 dark:bg-orange-900/20 rounded-xl">
                            <span class="flex items-center space-x-2 text-orange-600 dark:text-orange-400">
                                <i class="fa-solid fa-hand-holding-dollar w-4 text-center"></i>
                                <span>Cash Advances</span>
                            </span>
                            <span class="font-bold text-orange-700 dark:text-orange-400">-₱<?= number_format($driver['approved_cash_advances'] ?? 0, 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <!-- Net Payable (Uniformly shown on all driver cards) -->
                        <div class="flex items-center justify-between p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <span class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-semibold">
                                <i class="fa-solid fa-wallet text-blue-500 w-4 text-center"></i>
                                <span>Net Payable</span>
                            </span>
                            <span class="font-extrabold text-blue-700 dark:text-blue-400">₱<?= number_format($driver['net_earnings'] ?? 0, 2); ?></span>
                        </div>

                        <!-- Deliveries Count & Distance -->
                        <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-gray-900 rounded-xl">
                            <span class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-route text-cyan-500 w-4 text-center"></i>
                                <span>Completed Trips</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="font-bold text-gray-900 dark:text-gray-100"><?= $driver['total_deliveries'] ?? 0; ?> trips</span>
                                <?php if (($driver['total_lifetime_km'] ?? 0) > 0): ?>
                                    <span class="text-[11px] font-semibold text-cyan-700 dark:text-cyan-300 bg-cyan-100 dark:bg-cyan-900/40 px-1.5 py-0.5 rounded"><?= number_format($driver['total_lifetime_km'], 1); ?> km</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- Recent Delivery with Duration -->
                        <?php if (!empty($driver['recent_trips'])): 
                            $latestTrip = $driver['recent_trips'][0];
                        ?>
                        <div class="p-2.5 bg-slate-50 dark:bg-gray-900/80 rounded-xl border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-1">
                                <span class="flex items-center space-x-1.5 font-medium">
                                    <i class="fa-solid fa-truck-ramp-box text-blue-500 w-4 text-center"></i>
                                    <span>Recent Delivery</span>
                                </span>
                                <span class="text-[10px] font-semibold text-gray-400"><?= htmlspecialchars($latestTrip['trip_date'] ?? 'Recent'); ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-bold text-gray-800 dark:text-gray-200 truncate"><?= htmlspecialchars($latestTrip['destination']); ?></span>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <?php if (!empty($latestTrip['duration'])): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/50 px-2 py-0.5 rounded-md" title="Delivery Duration">
                                            <i class="fa-regular fa-clock text-[10px]"></i>
                                            <span><?= htmlspecialchars($latestTrip['duration']); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="p-2.5 bg-gray-50/60 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-[11px] text-gray-400">
                            No completed deliveries recorded yet
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Settle Payroll Primary Action Button -->
                    <div class="mb-3">
                        <?php if ($hasPayable): ?>
                            <button type="button" 
                                    onclick="openSettlePayrollModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['gross_earnings'] ?? 0; ?>, <?= $driver['approved_cash_advances'] ?? 0; ?>, <?= $driver['net_earnings'] ?? 0; ?>, <?= $driver['remaining_balance'] ?? 0; ?>)" 
                                    class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-between">
                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-money-bill-transfer"></i> Settle Payroll</span>
                                <span class="px-1.5 py-0.5 rounded bg-emerald-700/70 text-[11px] font-extrabold">₱<?= number_format($driver['net_earnings'] ?? 0, 2); ?></span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled 
                                    class="w-full py-2 px-3 bg-gray-100 dark:bg-gray-700/50 text-gray-400 dark:text-gray-500 text-xs font-semibold rounded-xl cursor-default flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                <span>Payroll Settled (₱0.00)</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Action Bar -->
                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-700/60 mb-3">
                        <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Quick Actions</span>
                        <div class="flex items-center space-x-1">
                            <?php if ($hasPayable): ?>
                                <button onclick="openSettlePayrollModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['gross_earnings'] ?? 0; ?>, <?= $driver['approved_cash_advances'] ?? 0; ?>, <?= $driver['net_earnings'] ?? 0; ?>, <?= $driver['remaining_balance'] ?? 0; ?>)" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors cursor-pointer" 
                                        title="Settle Driver Payroll">
                                    <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button disabled
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50" 
                                        title="Payroll Already Settled">
                                    <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                </button>
                            <?php endif; ?>
                            <button onclick="openAdjustBalanceModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', <?= $driver['remaining_balance'] ?? 0; ?>)" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Adjust Remaining Balance">
                                <i class="fa-solid fa-coins text-xs"></i>
                            </button>
                            <button onclick="openPrintDriverTripsModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Print Trip Ticket">
                                <i class="fa-solid fa-print text-xs"></i>
                            </button>
                            <button onclick="openUpdateDriverStatusModal(<?= $driver['id']; ?>, '<?= htmlspecialchars($driver['status']); ?>', '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Edit Driver Status">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button onclick="openSwitchTruckModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>', '<?= htmlspecialchars($driver['truck_code'] ?? 'None'); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Switch Assigned Truck">
                                <i class="fa-solid fa-truck-arrow-right text-xs"></i>
                            </button>
                            <button onclick="openResetPasswordModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors" 
                                    title="Reset Password">
                                <i class="fa-solid fa-key text-xs"></i>
                            </button>
                            <button onclick="openResignDriverModal(<?= $driver['id']; ?>, '<?= addslashes($driver['name']); ?>')" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors <?= $driver['status'] === 'Resigned' ? 'opacity-40 cursor-not-allowed' : '' ?>" 
                                    title="<?= $driver['status'] === 'Resigned' ? 'Driver Already Resigned' : 'Resign Driver' ?>"
                                    <?= $driver['status'] === 'Resigned' ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-user-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="grid grid-cols-2 gap-2.5 pt-2">
                    <button onclick='openViewDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition-all">View Details</button>
                    <button onclick='openContactDriverModal(<?= $driverJson; ?>)' class="w-full py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-xl transition-all">Contact</button>
                </div>
            </div>
        <?php endforeach; ?>
        <div id="noDriverSearchResults" class="hidden col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No drivers found</h4>
            <p class="text-xs text-gray-400 mt-1" id="noDriverSearchText">No driver profiles match your search filter.</p>
            <button type="button" onclick="clearDriverSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer">
                Clear search filter
            </button>
        </div>
    </div>

    <!-- Cash Advances Quick Banner in Drivers Tab -->
    <div class="mt-8 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-transparent dark:from-amber-950/30 dark:to-transparent border border-amber-200/80 dark:border-amber-800/40 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Driver Cash Advance Management</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    <?php if (($pendingCashAdvanceCount ?? 0) > 0): ?>
                        <span class="text-amber-600 dark:text-amber-400 font-semibold"><?= $pendingCashAdvanceCount ?> pending request<?= $pendingCashAdvanceCount > 1 ? 's' : '' ?> awaiting approval.</span>
                    <?php else: ?>
                        All driver cash advance requests, approvals, and tickets are managed in the dedicated Cash Advances section.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <button onclick="switchTab('cash_advances')" class="px-4 py-2 rounded-xl text-xs font-bold text-amber-800 dark:text-amber-200 bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 dark:hover:bg-amber-800/60 border border-amber-200 dark:border-amber-700 transition flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
            <span>Open Cash Advances Section</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </button>
    </div>

<script>
function filterDriverCards() {
    const input = document.getElementById('driverSearchInput');
    const clearBtn = document.getElementById('driverSearchClear');
    const query = input ? input.value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('#driverCardsGrid .driver-card');
    const noResults = document.getElementById('noDriverSearchResults');
    const noResultsText = document.getElementById('noDriverSearchText');

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
            if (noResultsText) noResultsText.textContent = `No driver profiles match "${query}".`;
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function clearDriverSearch() {
    const input = document.getElementById('driverSearchInput');
    if (input) {
        input.value = '';
        filterDriverCards();
        input.focus();
    }
}
</script>

</div>
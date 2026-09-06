<?php /* Trip Rate & Distance Settings View */ ?>
<div id="view-settings" class="tab-content hidden space-y-6">
    <!-- TOP HEADER -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <i class="fa-solid fa-map-location-dot fa-lg"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight">Trip Rate & Distance Settings</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Configure municipal trip rates and simulate real-time Nominatim road distance calculations.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                <i class="fa-solid fa-warehouse text-blue-500"></i>
                Quarry Garage: San Leonardo (15.3590, 120.9650)
            </span>
        </div>
    </div>

    <?php if (!empty($_SESSION['dest_success'])): ?>
        <div class="px-4 py-3 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-xl text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($_SESSION['dest_success']);
            unset($_SESSION['dest_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['dest_error'])): ?>
        <div class="px-4 py-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl text-sm text-red-800 dark:text-red-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($_SESSION['dest_error']);
            unset($_SESSION['dest_error']); ?>
        </div>
    <?php endif; ?>

    <!-- GENERAL FLAT & DISTANCE RATES CONFIG CARD -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-blue-600 dark:text-blue-400"></i>
                    General Trip Pay Rates
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Set the flat rate for all trips within the San Leonardo radius and the per-kilometer rate for distance beyond the municipal boundary.
                </p>
            </div>
        </div>
        <form method="POST" action="dashboard.php?tab=settings" class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="action" value="update_trip_rates">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    San Leonardo Flat Rate (₱)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-bold">₱</span>
                    <input type="number" step="0.01" min="0" name="base_trip_rate" id="settingsBaseTripRate" value="<?= htmlspecialchars(number_format($BASE_TRIP_RATE, 2, '.', '')); ?>" required class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold">
                </div>
                <span class="text-[11px] text-gray-400">Applied to all trips within San Leonardo municipality</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Rate Per KM Outside Boundary (₱/km)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-bold">₱</span>
                    <input type="number" step="0.01" min="0" name="rate_per_km" id="settingsRatePerKm" value="<?= htmlspecialchars(number_format($RATE_PER_KM, 2, '.', '')); ?>" required class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold">
                </div>
                <span class="text-[11px] text-gray-400">Added per km beyond garage-boundary distance</span>
            </div>
            <div>
                <button type="submit" class="btn-primary text-sm w-full py-2.5 mb-6">
                    <i class="fa-solid fa-floppy-disk"></i><span>Save General Rates</span>
                </button>
            </div>
        </form>
    </div>

    <!-- INTERACTIVE NOMINATIM LIVE DISTANCE & DRIVER PAY SIMULATOR -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-route text-indigo-600 dark:text-indigo-400"></i>
                    Live Route Distance & Driver Pay Simulator
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Powered by Nominatim & Leaflet map routing. Test any location or click directly on the map to calculate exact road distance and driver pay.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-mouse-pointer text-blue-500"></i> Click map to pinpoint any route
                </span>
            </div>
        </div>

        <!-- Search Bar & Quick Destination Chips -->
        <div class="mt-4 space-y-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="simSearchInput" placeholder="Search any city, municipality, barangay, or landmark (e.g. Gapan, Peñaranda, Cabanatuan)..."
                        class="w-full pl-10 pr-10 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                        onkeydown="if (event.key === 'Enter') { event.preventDefault(); runSimulatorSearch(); }">
                    <button type="button" id="simClearBtn" onclick="document.getElementById('simSearchInput').value = ''; this.classList.add('hidden');" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hidden">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <button type="button" id="simCalcBtn" onclick="runSimulatorSearch()" class="btn-primary text-sm px-6 py-2.5 shrink-0 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-calculator"></i><span>Calculate Pay</span>
                </button>
            </div>

            <!-- Quick Preset Chips -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-gray-400 font-medium mr-1">Quick test:</span>
                <button type="button" onclick="testPresetDest('Gapan City, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Gapan (22 km RT)</button>
                <button type="button" onclick="testPresetDest('Peñaranda, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Peñaranda (East 6km boundary)</button>
                <button type="button" onclick="testPresetDest('Cabanatuan City, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Cabanatuan (44 km RT)</button>
                <button type="button" onclick="testPresetDest('Brgy. Burgos, San Leonardo, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/80 hover:bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-semibold transition">Burgos (Inside San Leonardo)</button>
                <button type="button" onclick="testPresetDest('Mallorca, San Leonardo, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/80 hover:bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-semibold transition">Mallorca (Inside San Leonardo)</button>
                <button type="button" onclick="testPresetDest('Santa Rosa, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Santa Rosa</button>
                <button type="button" onclick="testPresetDest('Laur, Nueva Ecija')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Laur</button>
                <button type="button" onclick="testPresetDest('Tarlac City')" class="sim-chip px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">Tarlac City</button>
            </div>
        </div>

        <!-- Simulation Grid: Map + Results Panel -->
        <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Map Container -->
            <div class="lg:col-span-7 flex flex-col">
                <div id="settingsSimulatorMap" class="w-full h-80 sm:h-[400px] rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-inner relative z-0"></div>
                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-400 dark:text-gray-500 px-1">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-600 inline-block shadow-xs"></span> SSV Quarry Garage (Origin)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block border border-dashed border-emerald-600 shadow-xs"></span> San Leonardo Boundary Radius</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block shadow-xs"></span> Destination Target</span>
                </div>
            </div>

            <!-- Calculation Output Cards -->
            <div class="lg:col-span-5 flex flex-col justify-between space-y-4">
                <div class="bg-gray-50/90 dark:bg-gray-700/40 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/70 space-y-4">
                    <!-- Destination Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Target Destination</span>
                            <h4 id="simResultDest" class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 truncate mt-0.5" title="Gapan City, Nueva Ecija">Gapan City, Nueva Ecija</h4>
                            <p id="simResultCoords" class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">Lat: 15.30820, Lng: 120.94720</p>
                        </div>
                        <span id="simResultBadge" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 shrink-0">
                            Outside San Leonardo
                        </span>
                    </div>

                    <!-- Metric Tiles -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-xs">
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Total Round-Trip</span>
                            <p id="simResultDistance" class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-0.5">22 km</p>
                            <span class="text-[10px] text-gray-400">Back & forth route</span>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-xs">
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Boundary Deducted</span>
                            <p id="simResultBoundary" class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">-12 km</p>
                            <span class="text-[10px] text-gray-400">Garage to boundary</span>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-xs">
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Chargeable Outside</span>
                            <p id="simResultOutside" class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">10 km</p>
                            <span class="text-[10px] text-gray-400">Distance billed</span>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-xs">
                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Rate Multiplier</span>
                            <p id="simResultRate" class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-0.5">₱<?= number_format($RATE_PER_KM, 2); ?> / km</p>
                            <span class="text-[10px] text-gray-400">Per outside kilometer</span>
                        </div>
                    </div>

                    <!-- Highlighted Total Pay Card -->
                    <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 border border-emerald-200/80 dark:border-emerald-700/60 shadow-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Driver Trip Pay</span>
                            <span id="simResultPay" class="text-2xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-300">₱400.00</span>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                            <span class="text-gray-600 dark:text-gray-300">Breakdown:</span>
                            <span id="simResultFormula" class="font-semibold text-gray-800 dark:text-gray-200">₱300.00 base + 10 km outside × ₱10.00/km</span>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-blue-50/70 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-xl text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info mt-0.5 text-blue-500 shrink-0"></i>
                    <p class="leading-relaxed">
                        Every dispatch automatically calculates driver trip pay using this exact formula and Nominatim road coordinates. You do not need to pre-enter destinations or maintain static distance tables.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- CALCULATION RULES & BOUNDARY DEDUCTION REFERENCE CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Within San Leonardo -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm mb-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Zone 1: San Leonardo Radius</span>
                </div>
                <h4 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                    Flat ₱<?= number_format($BASE_TRIP_RATE, 2); ?>
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    Any delivery destination located within the municipality of San Leonardo is paid at the standard flat rate. No distance deduction or kilometer surcharge is applied.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 dark:text-gray-400">
                Covers all 15 barangays including Burgos, Bonifacio, Mallorca, Mambangnan, Nieves, San Anton, San Bartolome, San Francisco, San Roque, Sta. Cruz, Tabuating, Tagumpay, etc.
            </div>
        </div>

        <!-- Card 2: South, North & West Corridors -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold text-sm mb-2">
                    <i class="fa-solid fa-arrows-split-up-and-left"></i>
                    <span>Zone 2: South, North & West</span>
                </div>
                <h4 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                    -12 km Boundary Deduction
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    For routes heading towards Gapan, Santa Rosa, Cabanatuan, Jaen, and San Isidro, 12 km round-trip (6 km one-way) is deducted as the distance from the garage to the San Leonardo boundary.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 font-mono text-[11px] text-blue-700 dark:text-blue-300">
                Pay = ₱<?= number_format($BASE_TRIP_RATE, 2); ?> + (Total RT km - 12 km) × ₱<?= number_format($RATE_PER_KM, 2); ?>
            </div>
        </div>

        <!-- Card 3: East Border Corridor -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-sm mb-2">
                    <i class="fa-solid fa-compass"></i>
                    <span>Zone 3: East Border</span>
                </div>
                <h4 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                    -6 km Boundary Deduction
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    For deliveries heading East towards Peñaranda and General Tinio (Papaya), the municipal boundary is closer (~3 km one-way). Exactly 6 km round-trip is deducted from the total trip distance.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 font-mono text-[11px] text-indigo-700 dark:text-indigo-300">
                Pay = ₱<?= number_format($BASE_TRIP_RATE, 2); ?> + (Total RT km - 6 km) × ₱<?= number_format($RATE_PER_KM, 2); ?>
            </div>
        </div>
    </div>
</div>

<script>
    window.BASE_TRIP_RATE = <?= floatval($BASE_TRIP_RATE ?? 300.00); ?>;
    window.RATE_PER_KM = <?= floatval($RATE_PER_KM ?? 10.00); ?>;

    let settingsSimMap = null;
    let simGarageMarker = null;
    let simDestMarker = null;
    let simRouteLine = null;
    let simBoundaryCircle = null;

    const GARAGE_LOCATION = {
        name: "San Leonardo (SSV Quarry Garage)",
        lat: 15.359042,
        lng: 120.965016
    };

    // Known coordinates dictionary for instantaneous preset response
    const PRESET_COORDS = {
        'gapan': {
            name: 'Gapan City, Nueva Ecija',
            lat: 15.3082,
            lng: 120.9472
        },
        'peñaranda': {
            name: 'Peñaranda, Nueva Ecija',
            lat: 15.3566,
            lng: 121.0163
        },
        'penaranda': {
            name: 'Peñaranda, Nueva Ecija',
            lat: 15.3566,
            lng: 121.0163
        },
        'cabanatuan': {
            name: 'Cabanatuan City, Nueva Ecija',
            lat: 15.4858,
            lng: 120.9673
        },
        'burgos': {
            name: 'Brgy. Burgos, San Leonardo, Nueva Ecija',
            lat: 15.3590,
            lng: 120.9650
        },
        'mallorca': {
            name: 'Brgy. Mallorca, San Leonardo, Nueva Ecija',
            lat: 15.3685,
            lng: 120.9602
        },
        'santa rosa': {
            name: 'Santa Rosa, Nueva Ecija',
            lat: 15.4244,
            lng: 120.9392
        },
        'laur': {
            name: 'Laur, Nueva Ecija',
            lat: 15.5894,
            lng: 121.1895
        },
        'tarlac': {
            name: 'Tarlac City, Tarlac',
            lat: 15.4802,
            lng: 120.5979
        }
    };

    function isSanLeonardoDest(name) {
        if (!name || typeof name !== 'string') return false;
        const lower = name.toLowerCase();
        if (lower.includes('san leonardo')) return true;
        const slBarangays = [
            'bonifacio', 'burgos', 'castillejos', 'diversion', 'magpapalayoc',
            'mallorca', 'mambangnan', 'nieves', 'san anton', 'san bartolome',
            'san francisco', 'san roque', 'santa cruz', 'sta. cruz', 'tabuating', 'tagumpay'
        ];
        return slBarangays.some(b => new RegExp('\\b' + b + '\\b', 'i').test(lower));
    }

    function getSanLeonardoBoundaryDistance(name, lat = null, lng = null) {
        if (typeof NominatimService !== 'undefined' && NominatimService.getSanLeonardoBoundaryDistance) {
            return NominatimService.getSanLeonardoBoundaryDistance(name, lat, lng);
        }
        if (name) {
            const lower = name.toLowerCase();
            if (lower.includes('peñaranda') || lower.includes('penaranda') || lower.includes('general tinio') || lower.includes('gen. tinio') || lower.includes('papaya')) {
                return 6.0;
            }
        }
        return 12.0;
    }

    function initSettingsSimulatorMap() {
        const mapContainer = document.getElementById('settingsSimulatorMap');
        if (!mapContainer || typeof L === 'undefined') return;

        if (settingsSimMap) {
            settingsSimMap.invalidateSize();
            return;
        }

        settingsSimMap = L.map('settingsSimulatorMap', {
            center: [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng],
            zoom: 12
        });
        window.settingsSimulatorMap = settingsSimMap;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(settingsSimMap);

        // Garage Marker Icon
        const garageIcon = L.divIcon({
            className: 'sim-garage-icon',
            html: `<div style="background:#2563eb;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.3);border:2px solid #fff;"><i class="fa-solid fa-warehouse" style="font-size:14px;"></i></div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        simGarageMarker = L.marker([GARAGE_LOCATION.lat, GARAGE_LOCATION.lng], {
                icon: garageIcon
            })
            .addTo(settingsSimMap)
            .bindPopup(`<b>${GARAGE_LOCATION.name}</b><br><span style="font-size:11px;color:#666;">Trip Origin & Quarry Depot</span>`);

        // Municipal boundary radius indicator (approx 6km radius = 12km RT diameter)
        simBoundaryCircle = L.circle([GARAGE_LOCATION.lat, GARAGE_LOCATION.lng], {
            radius: 6000,
            color: '#10b981',
            weight: 1.5,
            dashArray: '5, 8',
            fillColor: '#10b981',
            fillOpacity: 0.06
        }).addTo(settingsSimMap);

        // Map click handler to test any coordinate
        settingsSimMap.on('click', async function(e) {
            await simulateLocation(e.latlng.lat, e.latlng.lng, 'Selected Map Location');
        });

        // Default simulation: Gapan City (22 km RT)
        testPresetDest('Gapan City, Nueva Ecija');
    }

    async function simulateLocation(lat, lng, label = '') {
        if (!settingsSimMap) initSettingsSimulatorMap();

        // Reverse geocode if label is generic
        let destName = label;
        if (!destName || destName === 'Selected Map Location') {
            try {
                if (typeof NominatimService !== 'undefined' && NominatimService.reverseGeocode) {
                    const geo = await NominatimService.reverseGeocode(lat, lng);
                    if (geo && geo.formatted) destName = geo.formatted;
                }
            } catch (e) {}
            if (!destName || destName === 'Selected Map Location') {
                destName = `Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            }
        }

        // Update search bar input to reflect current simulated place
        const searchInput = document.getElementById('simSearchInput');
        if (searchInput && destName && !destName.startsWith('Location (')) {
            searchInput.value = destName;
            document.getElementById('simClearBtn')?.classList.remove('hidden');
        }

        // 1. Calculate dynamic distance using NominatimService / Haversine road curvature (round-trip x2)
        let distInfo = null;
        if (typeof NominatimService !== 'undefined' && NominatimService.calculateMapDistance) {
            distInfo = NominatimService.calculateMapDistance(lat, lng, GARAGE_LOCATION.lat, GARAGE_LOCATION.lng, destName);
        }

        let roundedKm = 0;
        if (distInfo && distInfo.km != null && !isNaN(distInfo.km)) {
            roundedKm = Math.round(distInfo.km);
        } else {
            const direct = calculateDirectDistanceKm(GARAGE_LOCATION.lat, GARAGE_LOCATION.lng, lat, lng);
            roundedKm = Math.round(direct * 1.25 * 2);
        }
        if (roundedKm < 2) roundedKm = 2;

        // 2. Calculate driver pay using boundary deduction
        let payInfo = null;
        if (typeof NominatimService !== 'undefined' && NominatimService.calculateTripPay) {
            payInfo = NominatimService.calculateTripPay(roundedKm, destName, lat, lng);
        }

        if (!payInfo || isNaN(payInfo.pay)) {
            const isSanLeo = isSanLeonardoDest(destName);
            const boundaryKm = getSanLeonardoBoundaryDistance(destName, lat, lng);
            const outsideKm = isSanLeo ? 0 : Math.max(0, roundedKm - boundaryKm);
            const base = (typeof window !== 'undefined' && window.BASE_TRIP_RATE) ? Number(window.BASE_TRIP_RATE) : 300.00;
            const perKm = (typeof window !== 'undefined' && window.RATE_PER_KM) ? Number(window.RATE_PER_KM) : 10.00;
            const pay = isSanLeo ? base : (base + (outsideKm * perKm));
            payInfo = {
                pay: pay,
                outsideKm: outsideKm,
                boundaryKm: boundaryKm,
                isWithin: isSanLeo,
                breakdown: isSanLeo ?
                    `Within San Leonardo (Flat Rate: ₱${base.toFixed(2)})` : `₱${base.toFixed(2)} base + ${outsideKm} km outside × ₱${perKm.toFixed(2)}/km`
            };
        }

        const payAmountNum = Number(payInfo.pay) || 300.00;
        const outsideKmNum = Number(payInfo.outsideKm) || 0;
        const boundaryKmNum = Number(payInfo.boundaryKm) || (payInfo.isWithin ? 0 : 12);

        // 3. Update UI Output Elements
        document.getElementById('simResultDest').textContent = destName;
        document.getElementById('simResultDest').title = destName;
        document.getElementById('simResultCoords').textContent = `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;
        document.getElementById('simResultDistance').textContent = `${roundedKm} km`;
        document.getElementById('simResultBoundary').textContent = payInfo.isWithin ? '0 km (Inside)' : `-${boundaryKmNum} km`;
        document.getElementById('simResultOutside').textContent = payInfo.isWithin ? '0 km' : `${outsideKmNum} km`;
        document.getElementById('simResultPay').textContent = `₱${payAmountNum.toFixed(2)}`;
        document.getElementById('simResultFormula').textContent = payInfo.breakdown;

        const badgeEl = document.getElementById('simResultBadge');
        if (payInfo.isWithin) {
            badgeEl.textContent = 'Within San Leonardo';
            badgeEl.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 shrink-0';
        } else {
            badgeEl.textContent = 'Outside San Leonardo';
            badgeEl.className = 'px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 shrink-0';
        }

        // 4. Update Map Elements
        if (simDestMarker) settingsSimMap.removeLayer(simDestMarker);
        if (simRouteLine) settingsSimMap.removeLayer(simRouteLine);

        const destIcon = L.divIcon({
            className: 'sim-dest-icon',
            html: `<div style="background:#ef4444;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.3);border:2px solid #fff;"><i class="fa-solid fa-location-dot" style="font-size:15px;"></i></div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        simDestMarker = L.marker([lat, lng], {
                icon: destIcon
            })
            .addTo(settingsSimMap)
            .bindPopup(`<b>${destName}</b><br><span style="font-size:12px;color:#2563eb;font-weight:600;">${roundedKm} km round trip &bull; ₱${payAmountNum.toFixed(2)} pay</span>`)
            .openPopup();

        simRouteLine = L.polyline([
            [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng],
            [lat, lng]
        ], {
            color: '#4f46e5',
            weight: 3.5,
            dashArray: '6, 8'
        }).addTo(settingsSimMap);

        // Fit bounds to display both points
        const bounds = L.latLngBounds([
            [GARAGE_LOCATION.lat, GARAGE_LOCATION.lng],
            [lat, lng]
        ]);
        settingsSimMap.fitBounds(bounds, {
            padding: [40, 40],
            maxZoom: 13
        });
    }

    function calculateDirectDistanceKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    async function runSimulatorSearch() {
        const input = document.getElementById('simSearchInput');
        const query = input?.value?.trim();
        if (!query) return;

        document.getElementById('simClearBtn')?.classList.remove('hidden');
        const calcBtn = document.getElementById('simCalcBtn');
        if (calcBtn) {
            calcBtn.disabled = true;
            calcBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Calculating...</span>';
        }

        try {
            const lower = query.toLowerCase();

            // Check preset dictionary for instant coordinates
            for (const [key, item] of Object.entries(PRESET_COORDS)) {
                if (lower.includes(key)) {
                    await simulateLocation(item.lat, item.lng, item.name);
                    return;
                }
            }

            // Try NominatimService search
            if (typeof NominatimService !== 'undefined' && NominatimService.searchAddress) {
                const results = await NominatimService.searchAddress(query);
                if (results && results.length > 0) {
                    const top = results[0];
                    await simulateLocation(top.lat, top.lng, top.name || top.shortName || query);
                    return;
                }
            }

            // Fallback search fetch
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query + ', Nueva Ecija, Philippines')}&limit=1`);
            const data = await res.json();
            if (data && data.length > 0) {
                const top = data[0];
                await simulateLocation(parseFloat(top.lat), parseFloat(top.lon), top.display_name || query);
            } else {
                alert("Location not found on map. Please try a different address or click directly on the map.");
            }
        } catch (e) {
            console.error("Simulator search error:", e);
        } finally {
            if (calcBtn) {
                calcBtn.disabled = false;
                calcBtn.innerHTML = '<i class="fa-solid fa-calculator"></i><span>Calculate Pay</span>';
            }
        }
    }

    async function testPresetDest(query) {
        const input = document.getElementById('simSearchInput');
        if (input) {
            input.value = query;
            document.getElementById('simClearBtn')?.classList.remove('hidden');
        }
        await runSimulatorSearch();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const settingsView = document.getElementById('view-settings');
        if (settingsView && !settingsView.classList.contains('hidden')) {
            setTimeout(initSettingsSimulatorMap, 200);
        }
    });
</script>
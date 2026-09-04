<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic Title -->
    <title><?= ($_SESSION['role'] === 'Admin') ? 'SSV Trucking - Admin System' : (($_SESSION['role'] === 'Checker') ? 'Checker Panel - SSV Trucking' : 'Driver Panel - SSV Trucking') ?></title>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <?php if (in_array($_SESSION['role'] ?? '', ['Admin', 'Driver'])): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <?php endif; ?>
    <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <script src="../includes/nominatim.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style type="text/tailwindcss">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        @layer components {
            .btn-primary {
                @apply bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-200 inline-flex items-center justify-center space-x-2 active:scale-[0.98];
            }
            .btn-secondary {
                @apply bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 inline-flex items-center justify-center space-x-2 active:scale-[0.98];
            }
            .btn-danger {
                @apply bg-rose-600 hover:bg-rose-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-200 inline-flex items-center justify-center space-x-2 active:scale-[0.98];
            }
            .btn-success {
                @apply bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-200 inline-flex items-center justify-center space-x-2 active:scale-[0.98];
            }
            .input-field {
                @apply w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors text-sm;
            }
            .glass-card {
                @apply bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border border-white/20 dark:border-gray-800/60 shadow-xl;
            }
            .chip-emerald {
                @apply bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold px-2.5 py-1 rounded-full text-xs inline-flex items-center gap-1.5;
            }
            .chip-amber {
                @apply bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-semibold px-2.5 py-1 rounded-full text-xs inline-flex items-center gap-1.5;
            }
            .chip-blue {
                @apply bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 font-semibold px-2.5 py-1 rounded-full text-xs inline-flex items-center gap-1.5;
            }
            .chip-rose {
                @apply bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-semibold px-2.5 py-1 rounded-full text-xs inline-flex items-center gap-1.5;
            }
        }

        /* ── Pulse Radar Ring Animation ── */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.4); opacity: 0.3; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        .pulse-ring-active {
            animation: pulse-ring 2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }

        /* ── Skeleton Shimmer Loading ── */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .skeleton-shimmer {
            background: linear-gradient(90deg, rgba(229,231,235,0.4) 25%, rgba(243,244,246,0.8) 50%, rgba(229,231,235,0.4) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        .dark .skeleton-shimmer {
            background: linear-gradient(90deg, rgba(31,41,55,0.4) 25%, rgba(55,65,81,0.8) 50%, rgba(31,41,55,0.4) 75%);
            background-size: 200% 100%;
        }

        /* ── Custom Leaflet Popups ── */
        .leaflet-popup-content-wrapper {
            @apply rounded-2xl p-0 overflow-hidden shadow-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-gray-800 dark:text-gray-200 !important;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            line-height: 1.5 !important;
        }
        .leaflet-popup-tip {
            @apply bg-white dark:bg-gray-900 !important;
        }

        <?php if ($_SESSION['role'] === 'Admin'): ?>#map {
            height: 700px;
            width: 100%;
            border-radius: 1rem;
            z-index: 10;
        }

        .custom-div-icon {
            background: none;
            border: none;
        }

        .marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 2px solid white;
        }

        .bg-transit {
            background-color: #22c55e;
        }

        .bg-idle {
            background-color: #eab308;
        }

        .bg-loading {
            background-color: #3b82f6;
        }

        .bg-unloading {
            background-color: #f97316;
        }

        <?php endif; ?>

        /* ── Hide Scrollbars Globally for Sidebars / Navs ── */
        .no-scrollbar::-webkit-scrollbar,
        aside::-webkit-scrollbar,
        nav::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .no-scrollbar,
        aside,
        nav {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* ── Sidebar Styles ── */
        .sidebar-nav-item {
            @apply flex items-center space-x-3 px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 cursor-pointer;
        }
        .sidebar-nav-item.active {
            @apply bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold;
        }
        .sidebar-nav-item:not(.active) {
            @apply text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200;
        }
        .sidebar-nav-item .nav-icon {
            @apply w-5 text-center text-sm;
        }
        .sidebar-nav-item.active .nav-icon {
            @apply text-white;
        }

        /* ── Bottom Nav (Checker/Driver mobile) ── */
        .bottom-nav-item {
            @apply flex flex-col items-center justify-center py-1.5 px-2 text-gray-400 dark:text-gray-500 transition-all duration-200 text-xs font-medium relative;
        }
        .bottom-nav-item.active {
            @apply text-blue-600 dark:text-blue-400 scale-105 font-semibold;
        }
        .bottom-nav-item:not(.active):hover {
            @apply text-gray-600 dark:text-gray-300;
        }

        /* ── Sidebar backdrop ── */
        .sidebar-backdrop {
            transition: opacity 0.3s ease;
        }

        /* ── Sidebar slide ── */
        .sidebar-panel {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @media (max-width: 1023px) {
            .sidebar-panel.sidebar-closed {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200">

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col space-y-3 pointer-events-none"></div>

    <?php if ($_SESSION['role'] === 'Admin'): ?>
    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  ADMIN SIDEBAR                                          ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebar-overlay" class="sidebar-backdrop fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="admin-sidebar" class="sidebar-panel sidebar-closed lg:translate-x-0 fixed top-0 left-0 h-screen w-72 z-50 lg:z-30 flex flex-col bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 shadow-xl lg:shadow-none no-scrollbar">

        <!-- Logo & Brand -->
        <div class="flex items-center space-x-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
            </div>
            <div class="min-w-0">
                <h1 class="text-sm font-bold text-gray-900 dark:text-white truncate">SSV Trucking</h1>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">Admin System</p>
            </div>
            <!-- Mobile close button -->
            <button onclick="toggleSidebar()" class="lg:hidden ml-auto w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- System Status -->
        <div class="px-4 py-2 flex-shrink-0">
            <div class="flex items-center space-x-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg px-3 py-1.5 border border-emerald-100 dark:border-emerald-800/30">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse flex-shrink-0"></span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">System Online</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-1 space-y-0.5 overflow-y-auto no-scrollbar">
            <p class="px-3 text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest my-1">Main Menu</p>
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="sidebar-nav-item active w-full">
                <i class="fa-solid fa-border-all nav-icon"></i>
                <span>Dashboard</span>
            </button>
            <button onclick="switchTab('tracking')" id="nav-tracking" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-map-location-dot nav-icon"></i>
                <span>Live Tracking</span>
            </button>
            <button onclick="switchTab('dispatches')" id="nav-dispatches" class="sidebar-nav-item w-full">
                <i class="fa-regular fa-file-lines nav-icon"></i>
                <span>Dispatches</span>
            </button>

            <p class="px-3 pt-2 text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest my-1">Management</p>
            <button onclick="switchTab('fleet')" id="nav-fleet" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-truck-fast nav-icon"></i>
                <span>Fleet</span>
            </button>
            <button onclick="switchTab('drivers')" id="nav-drivers" class="sidebar-nav-item w-full">
                <i class="fa-regular fa-user nav-icon"></i>
                <span>Drivers</span>
            </button>
            <button onclick="switchTab('orders')" id="nav-orders" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-clipboard-list nav-icon"></i>
                <span>Orders</span>
            </button>
            <button onclick="switchTab('reports')" id="nav-reports" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-chart-column nav-icon"></i>
                <span>Reports</span>
            </button>
            <button onclick="switchTab('activity_logs')" id="nav-activity_logs" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                <span>Activity Logs</span>
            </button>
            <button onclick="switchTab('pwd_requests')" id="nav-pwd_requests" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-key nav-icon"></i>
                <span class="flex-1 text-left">Password Requests</span>
                <?php if (($pendingPwdResetCount ?? 0) > 0): ?>
                    <span id="pwdResetBadge" class="ml-auto min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-red-500 text-white flex items-center justify-center"><?= $pendingPwdResetCount ?></span>
                <?php endif; ?>
            </button>
        </nav>

        <!-- Sidebar Footer -->
        <div class="border-t border-gray-100 dark:border-gray-800 px-3 py-3 space-y-1 flex-shrink-0">
            <!-- Dark Mode Toggle -->
            <button id="themeToggle" onclick="toggleTheme(event)" class="sidebar-nav-item w-full">
                <i id="themeIcon" class="fa-solid fa-moon nav-icon"></i>
                <span id="themeLabel">Dark Mode</span>
            </button>
            <!-- Logout -->
            <a href="../logout.php" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 w-full">
                <i class="fa-solid fa-right-from-bracket nav-icon"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Top Bar (Admin) -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white/95 dark:bg-gray-950/95 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <button onclick="toggleSidebar()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all">
                <i class="fa-solid fa-bars text-base" id="hamburger-icon"></i>
            </button>
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                    <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
                </div>
                <span class="font-bold text-sm text-gray-800 dark:text-gray-200">SSV Trucking</span>
            </div>
        </div>
        <button onclick="toggleTheme(event)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-all">
            <i class="themeIconMobile fa-solid fa-moon text-sm"></i>
        </button>
    </div>

    <!-- Admin Main Content Wrapper -->
    <div id="main-content" class="lg:ml-72 min-h-screen pt-16 lg:pt-0 transition-all duration-300">

    <?php elseif ($_SESSION['role'] === 'Checker'): ?>
    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  CHECKER LAYOUT                                         ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex fixed top-0 left-0 h-screen w-64 z-30 flex-col bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800">
        <!-- Logo -->
        <div class="flex items-center space-x-3 px-5 py-5 border-b border-gray-100 dark:border-gray-800">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
            </div>
            <div>
                <h1 class="text-sm font-bold text-gray-900 dark:text-white">SSV Trucking</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Checker Panel</p>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="px-4 py-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-sm flex-shrink-0">
                    <?= strtoupper(substr($checker_profile['first_name'] ?? 'C', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($checker_full_name ?? $_SESSION['username'] ?? 'Checker') ?></p>
                    <p class="text-xs text-gray-400">Field Checker</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="dashboard.php" class="sidebar-nav-item active w-full">
                <i class="fa-solid fa-clipboard-check nav-icon"></i>
                <span>Dashboard</span>
            </a>
            <button onclick="openResetPasswordModal()" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-key nav-icon"></i>
                <span>Reset Password</span>
            </button>
        </nav>

        <!-- Footer -->
        <div class="border-t border-gray-100 dark:border-gray-800 px-3 py-4 space-y-1">
            <button id="themeToggle" onclick="toggleTheme(event)" class="sidebar-nav-item w-full">
                <i id="themeIcon" class="fa-solid fa-moon nav-icon"></i>
                <span id="themeLabel">Dark Mode</span>
            </button>
            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 w-full">
                <i class="fa-solid fa-right-from-bracket w-5 text-center text-base"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Top Bar (Checker) -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white/95 dark:bg-gray-950/95 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-2.5">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
            </div>
            <div>
                <span class="font-bold text-sm text-gray-800 dark:text-gray-200">SSV Trucking</span>
                <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 px-1.5 py-0.5 rounded-full font-bold uppercase ml-1.5">Checker</span>
            </div>
        </div>
        <button onclick="toggleTheme(event)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-all">
            <i class="themeIconMobile fa-solid fa-moon text-sm"></i>
        </button>
    </div>

    <!-- Mobile Bottom Nav (Checker) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 dark:bg-gray-950/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800 flex items-center justify-around px-2 py-1.5 safe-bottom">
        <a href="dashboard.php" class="bottom-nav-item active">
            <i class="fa-solid fa-clipboard-check text-lg mb-0.5"></i>
            <span>Dashboard</span>
        </a>
        <a href="#scanForm" class="bottom-nav-item" onclick="document.getElementById('scanForm')?.scrollIntoView({behavior:'smooth',block:'center'})">
            <i class="fa-solid fa-truck-ramp-box text-lg mb-0.5"></i>
            <span>Log Delivery</span>
        </a>
        <button onclick="openResetPasswordModal()" class="bottom-nav-item">
            <i class="fa-solid fa-key text-lg mb-0.5"></i>
            <span>Reset Password</span>
        </button>
        <a href="../logout.php" class="bottom-nav-item text-red-400 dark:text-red-500">
            <i class="fa-solid fa-right-from-bracket text-lg mb-0.5"></i>
            <span>Logout</span>
        </a>
    </div>

    <!-- Checker Main Content Wrapper -->
    <div id="main-content" class="lg:ml-64 min-h-screen pt-16 lg:pt-0 pb-20 lg:pb-0 transition-all duration-300">

    <?php elseif ($_SESSION['role'] === 'Driver'): ?>
    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  DRIVER LAYOUT                                          ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex fixed top-0 left-0 h-screen w-64 z-30 flex-col bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800">
        <!-- Logo -->
        <div class="flex items-center space-x-3 px-5 py-5 border-b border-gray-100 dark:border-gray-800">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
            </div>
            <div>
                <h1 class="text-sm font-bold text-gray-900 dark:text-white">SSV Trucking</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Driver Panel</p>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="px-4 py-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center space-x-3">
                <?php 
                $hdrDriverPhoto = $driverProfile['profile_photo'] ?? $_SESSION['profile_photo'] ?? null;
                $hdrPhotoFull = $hdrDriverPhoto ? (dirname(__DIR__) . '/' . $hdrDriverPhoto) : null;
                $hdrPhotoUrl = ($hdrPhotoFull && file_exists($hdrPhotoFull)) ? ('../' . htmlspecialchars($hdrDriverPhoto) . '?v=' . filemtime($hdrPhotoFull)) : null;
                ?>
                <?php if ($hdrPhotoUrl): ?>
                    <img src="<?= $hdrPhotoUrl ?>" alt="Profile Photo" class="w-10 h-10 rounded-xl object-cover shadow-sm flex-shrink-0 border border-blue-200 dark:border-blue-700">
                <?php else: ?>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-sm flex-shrink-0">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'D', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'Driver') ?></p>
                    <p class="text-xs text-gray-400">Truck Driver</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="dashboard.php" class="sidebar-nav-item active w-full">
                <i class="fa-solid fa-house nav-icon"></i>
                <span>Dashboard</span>
            </a>
            <?php if ($has_pending_cancellation ?? false): ?>
                <button class="sidebar-nav-item w-full opacity-50 cursor-not-allowed" disabled>
                    <i class="fa-solid fa-spinner fa-spin nav-icon"></i>
                    <span>Cancellation Pending</span>
                </button>
            <?php else: ?>
                <button onclick="openCancelTripModal()" class="sidebar-nav-item w-full !text-orange-600 dark:!text-orange-400 hover:!bg-orange-50 dark:hover:!bg-orange-900/20">
                    <i class="fa-solid fa-ban nav-icon"></i>
                    <span>Request Cancellation</span>
                </button>
            <?php endif; ?>
            <button onclick="openResetPasswordModal()" class="sidebar-nav-item w-full">
                <i class="fa-solid fa-key nav-icon"></i>
                <span>Reset Password</span>
            </button>
        </nav>

        <!-- Footer -->
        <div class="border-t border-gray-100 dark:border-gray-800 px-3 py-4 space-y-1">
            <button id="themeToggle" onclick="toggleTheme(event)" class="sidebar-nav-item w-full">
                <i id="themeIcon" class="fa-solid fa-moon nav-icon"></i>
                <span id="themeLabel">Dark Mode</span>
            </button>
            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 w-full">
                <i class="fa-solid fa-right-from-bracket w-5 text-center text-base"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Top Bar (Driver) -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white/95 dark:bg-gray-950/95 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-2.5">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
            </div>
            <div>
                <span class="font-bold text-sm text-gray-800 dark:text-gray-200"><?= htmlspecialchars($_SESSION['username'] ?? 'Driver') ?></span>
                <span class="text-[10px] bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 px-1.5 py-0.5 rounded-full font-bold uppercase ml-1.5">Driver</span>
            </div>
        </div>
        <button onclick="toggleTheme(event)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-all">
            <i class="themeIconMobile fa-solid fa-moon text-sm"></i>
        </button>
    </div>

    <!-- Mobile Bottom Nav (Driver) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 dark:bg-gray-950/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800 flex items-center justify-around px-2 py-1.5">
        <a href="dashboard.php" class="bottom-nav-item active">
            <i class="fa-solid fa-house text-lg mb-0.5"></i>
            <span>Home</span>
        </a>
        <?php if ($has_pending_cancellation ?? false): ?>
            <button class="bottom-nav-item opacity-50 cursor-not-allowed" disabled>
                <i class="fa-solid fa-spinner fa-spin text-lg mb-0.5"></i>
                <span>Pending...</span>
            </button>
        <?php else: ?>
            <button onclick="openCancelTripModal()" class="bottom-nav-item text-orange-500">
                <i class="fa-solid fa-ban text-lg mb-0.5"></i>
                <span>Cancel Trip</span>
            </button>
        <?php endif; ?>
        <button onclick="openResetPasswordModal()" class="bottom-nav-item">
            <i class="fa-solid fa-key text-lg mb-0.5"></i>
            <span>Reset Password</span>
        </button>
        <a href="../logout.php" class="bottom-nav-item text-red-400 dark:text-red-500">
            <i class="fa-solid fa-right-from-bracket text-lg mb-0.5"></i>
            <span>Logout</span>
        </a>
    </div>

    <!-- Driver Main Content Wrapper -->
    <div id="main-content" class="lg:ml-64 min-h-screen pt-16 lg:pt-0 pb-20 lg:pb-0 transition-all duration-300">

    <?php endif; ?>

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  SHARED SCRIPTS (Theme Toggle + Sidebar)                ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <script>
        // ── Theme Toggle ──
        document.addEventListener("DOMContentLoaded", function() {
            const icons = document.querySelectorAll('#themeIcon, .themeIconMobile');
            if (document.documentElement.classList.contains('dark')) {
                icons.forEach(i => i.classList.replace('fa-moon', 'fa-sun'));
                const label = document.getElementById('themeLabel');
                if (label) label.textContent = 'Light Mode';
            }
        });

        function toggleTheme(event) {
            const htmlTag = document.documentElement;
            const icons = document.querySelectorAll('#themeIcon, .themeIconMobile');

            if (event) {
                const x = event.clientX || window.innerWidth / 2;
                const y = event.clientY || 50;
                const circle = document.createElement('div');
                circle.className = 'fixed rounded-full pointer-events-none z-[9999] transition-all duration-[700ms] ease-out';
                circle.style.left = x + 'px';
                circle.style.top = y + 'px';
                circle.style.width = '0px';
                circle.style.height = '0px';
                circle.style.transform = 'translate(-50%, -50%)';
                const isGoingDark = !htmlTag.classList.contains('dark');
                circle.style.backgroundColor = isGoingDark ? 'rgba(56, 189, 248, 0.15)' : 'rgba(250, 204, 21, 0.15)';
                circle.style.boxShadow = isGoingDark ? '0 0 40px 20px rgba(56, 189, 248, 0.1)' : '0 0 40px 20px rgba(250, 204, 21, 0.1)';
                circle.style.backdropFilter = 'contrast(1.1)';
                document.body.appendChild(circle);
                requestAnimationFrame(() => {
                    const radius = Math.max(window.innerWidth, window.innerHeight) * 2.5;
                    circle.style.width = radius + 'px';
                    circle.style.height = radius + 'px';
                    circle.style.opacity = '0';
                });
                setTimeout(() => circle.remove(), 700);
            }

            const isNowDark = htmlTag.classList.toggle('dark');
            icons.forEach(icon => {
                if (isNowDark) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            });
            const label = document.getElementById('themeLabel');
            if (label) label.textContent = isNowDark ? 'Light Mode' : 'Dark Mode';

            localStorage.setItem('theme', isNowDark ? "dark" : "light");
            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }

        // ── Admin Sidebar Toggle (Mobile) ──
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar) return;

            if (sidebar.classList.contains('sidebar-closed')) {
                sidebar.classList.remove('sidebar-closed');
                if (overlay) overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('sidebar-closed');
                if (overlay) overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // ── Close sidebar on nav click (mobile) ──
        function switchTabAndCloseMobile(tab) {
            switchTab(tab);
            const sidebar = document.getElementById('admin-sidebar');
            if (sidebar && window.innerWidth < 1024) {
                toggleSidebar();
            }
        }
    </script>

    <!-- ===== GLOBAL TOAST NOTIFICATION SYSTEM ===== -->
    <style>
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column-reverse;
            gap: 0.6rem;
            pointer-events: none;
            width: 22rem;
            max-width: calc(100vw - 3rem);
        }
        .toast-item {
            pointer-events: all;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.08);
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
            border-left: 5px solid transparent;
            animation: toast-slide-in 0.4s cubic-bezier(.21,1.02,.73,1) forwards;
            position: relative;
            overflow: hidden;
        }
        .toast-item::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            animation: toast-progress linear forwards;
            opacity: 0.6;
        }
        .toast-item.toast-success { background:#f0fdf4; color:#14532d; border-color:#22c55e; }
        .toast-item.toast-success::after { background:#22c55e; }
        .toast-item.toast-error   { background:#fef2f2; color:#7f1d1d; border-color:#ef4444; }
        .toast-item.toast-error::after   { background:#ef4444; }
        .toast-item.toast-info    { background:#eff6ff; color:#1e3a8a; border-color:#3b82f6; }
        .toast-item.toast-info::after    { background:#3b82f6; }
        .toast-item.toast-warning { background:#fffbeb; color:#78350f; border-color:#f59e0b; }
        .toast-item.toast-warning::after { background:#f59e0b; }
        .dark .toast-item.toast-success { background:#052e16; color:#bbf7d0; }
        .dark .toast-item.toast-error   { background:#450a0a; color:#fecaca; }
        .dark .toast-item.toast-info    { background:#0f172a; color:#bfdbfe; }
        .dark .toast-item.toast-warning { background:#1c1008; color:#fde68a; }
        .toast-icon {
            flex-shrink: 0;
            font-size: 1.1rem;
            margin-top: 2px;
        }
        .toast-msg  { flex: 1; }
        .toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.45;
            font-size: 0.9rem;
            padding: 0;
            line-height: 1;
            color: inherit;
            margin-top: 3px;
            transition: opacity 0.15s;
        }
        .toast-close:hover { opacity: 1; }
        @keyframes toast-slide-in {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toast-slide-out {
            from { opacity: 1; transform: translateY(0) scale(1);   max-height: 120px; margin-bottom: 0; }
            to   { opacity: 0; transform: translateY(12px) scale(0.95); max-height: 0; padding: 0; }
        }
        @keyframes toast-progress {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .toast-item.removing {
            animation: toast-slide-out 0.35s ease forwards;
            pointer-events: none;
        }
    </style>
    <div id="toast-container"></div>
    <script>
        function showToast(message, type = 'info', duration = 4500) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const icons = {
                success: 'fa-circle-check',
                error:   'fa-triangle-exclamation',
                warning: 'fa-circle-exclamation',
                info:    'fa-circle-info'
            };

            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type}`;
            toast.style.setProperty('--dur', duration + 'ms');
            toast.innerHTML = `
                <i class="fa-solid ${icons[type] || icons.info} toast-icon"></i>
                <span class="toast-msg">${message}</span>
                <button class="toast-close" onclick="removeToast(this.parentElement)" title="Dismiss">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            toast.querySelector('.toast-item::after');
            toast.style.cssText += `--dur:${duration}ms`;
            // Apply progress bar duration via inline style on ::after pseudo-element via a workaround
            const style = document.createElement('style');
            const id = 'toast-' + Date.now() + Math.random().toString(36).slice(2);
            toast.id = id;
            style.textContent = `#${id}::after { animation-duration: ${duration}ms; }`;
            document.head.appendChild(style);

            container.appendChild(toast);

            const timer = setTimeout(() => removeToast(toast), duration);
            toast._timer = timer;
            toast._style = style;
        }

        function removeToast(el) {
            if (!el || el.classList.contains('removing')) return;
            clearTimeout(el._timer);
            el.classList.add('removing');
            el.addEventListener('animationend', () => {
                el.remove();
                if (el._style) el._style.remove();
            }, { once: true });
        }

        // Fire PHP session flash messages as toasts
        document.addEventListener('DOMContentLoaded', function () {
            <?php
            $toasts = [];
            if (!empty($_SESSION['scan_msg']))  { $toasts[] = ['msg' => $_SESSION['scan_msg'],  'type' => 'success']; unset($_SESSION['scan_msg']); }
            if (!empty($_SESSION['scan_err']))  { $toasts[] = ['msg' => $_SESSION['scan_err'],  'type' => 'error'];   unset($_SESSION['scan_err']); }
            if (!empty($_SESSION['fleet_err'])) { $toasts[] = ['msg' => $_SESSION['fleet_err'], 'type' => 'error'];   unset($_SESSION['fleet_err']); }
            if (!empty($_SESSION['success']))   { $toasts[] = ['msg' => $_SESSION['success'],   'type' => 'success']; unset($_SESSION['success']); }
            if (!empty($_SESSION['error']))     { $toasts[] = ['msg' => $_SESSION['error'],     'type' => 'error'];   unset($_SESSION['error']); }
            if (!empty($_SESSION['flash_info'])){ $toasts[] = ['msg' => $_SESSION['flash_info'],'type' => 'info'];    unset($_SESSION['flash_info']); }
            foreach ($toasts as $i => $t) {
                $msg  = addslashes(strip_tags($t['msg'], '<strong><em><b>'));
                $type = htmlspecialchars($t['type']);
                $delay = $i * 200;
                echo "            setTimeout(() => showToast(`{$msg}`, '{$type}'), {$delay});\n";
            }
            ?>
        });
    </script>
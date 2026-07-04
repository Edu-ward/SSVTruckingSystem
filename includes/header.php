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
                extend: {}
            }
        }
    </script>

    <?php if ($_SESSION['role'] === 'Admin'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        <?php if ($_SESSION['role'] === 'Admin'): ?>#map {
            height: 700px;
            width: 100%;
            border-radius: 0.5rem;
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
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200">
    <nav class="bg-blue-600 dark:bg-gray-800 text-white px-6 py-4 flex items-center justify-between shadow-md">
        <div class="flex items-center space-x-2 text-xl font-bold">
            <div class="flex-shrink-0">
                <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-8 block dark:hidden">
                <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-8 hidden dark:block">
            </div>
            <span>SSV Trucking</span>
        </div>

        <!-- Desktop Navigation (hidden on mobile, shown on lg screens) -->
        <div class="hidden lg:flex space-x-2 text-sm font-medium text-blue-100 items-center">

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <!-- ADMIN NAVIGATION -->
                <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn flex items-center space-x-1 text-white bg-blue-700 px-3 py-1.5 rounded transition"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></button>
                <button onclick="switchTab('tracking')" id="nav-tracking" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-map-location-dot"></i><span>Live Tracking</span></button>
                <button onclick="switchTab('dispatches')" id="nav-dispatches" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-file-lines"></i><span>Dispatches</span></button>
                <button onclick="switchTab('fleet')" id="nav-fleet" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-truck-fast"></i><span>Fleet</span></button>
                <button onclick="switchTab('drivers')" id="nav-drivers" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-user"></i><span>Drivers</span></button>
                <button onclick="switchTab('orders')" id="nav-orders" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-clipboard-list"></i><span>Orders</span></button>
                <button onclick="switchTab('reports')" id="nav-reports" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-chart-column"></i><span>Reports</span></button>

            <?php elseif ($_SESSION['role'] === 'Driver'): ?>
                <!-- DRIVER NAVIGATION -->
                <span class="text-blue-100 mr-4">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Driver'); ?></span>
                <?php if ($has_pending_cancellation ?? false): ?>
                    <button class="flex items-center px-3 py-2 text-gray-500 bg-gray-200 dark:bg-gray-700 rounded-md cursor-not-allowed transition shadow-sm mr-2" disabled>
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Cancellation Pending...
                    </button>
                <?php else: ?>
                    <button onclick="openCancelTripModal()" class="flex items-center px-3 py-2 text-white bg-orange-600 dark:bg-orange-700 hover:bg-orange-705 rounded-md transition shadow-sm mr-2">
                        <i class="fa-solid fa-ban mr-2"></i> Request Cancellation
                    </button>
                <?php endif; ?>
                <button onclick="openChangePasswordModal()" class="flex items-center px-3 py-2 text-white bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 dark:hover:bg-gray-655 rounded-md transition shadow-sm">
                    <i class="fa-solid fa-key mr-2"></i> Change Password
                </button>

            <?php elseif ($_SESSION['role'] === 'Checker'): ?>
                <!-- CHECKER NAVIGATION -->
                <span class="text-blue-100 mr-4">Checker: <strong><?= htmlspecialchars($checker_full_name ?? $_SESSION['username'] ?? 'Checker'); ?></strong></span>
                <button onclick="openChangePasswordModal()" class="flex items-center px-3 py-2 text-white bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 dark:hover:bg-gray-655 rounded-md transition shadow-sm">
                    <i class="fa-solid fa-key mr-2"></i> Change Password
                </button>
            <?php endif; ?>

            <button id="themeToggle" onclick="toggleTheme(event)" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-700 hover:bg-blue-800 text-white transition-colors focus:outline-none shadow-sm ml-2 relative overflow-hidden">
                <i id="themeIcon" class="fa-solid fa-moon text-lg"></i>
            </button>

            <div class="w-px h-5 bg-blue-400 mx-2 opacity-50"></div>

            <a href="../logout.php" class="flex items-center space-x-1 px-3 py-1.5 rounded transition text-red-200 hover:text-white hover:bg-red-500">
                <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
            </a>
        </div>

        <!-- Mobile Navigation Controls (hidden on desktop, shown on mobile) -->
        <div class="flex items-center lg:hidden space-x-3">
            <button onclick="toggleTheme(event)" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-750 dark:bg-gray-700 text-white hover:bg-blue-800 focus:outline-none shadow-sm relative overflow-hidden">
                <i class="themeIconMobile fa-solid fa-moon text-base"></i>
            </button>
            <button onclick="toggleMobileMenu()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-750 dark:bg-gray-700 text-white hover:bg-blue-800 focus:outline-none">
                <i class="fa-solid fa-bars text-lg" id="hamburger-icon"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Navigation Dropdown Menu -->
    <div id="mobile-menu" class="hidden lg:hidden bg-blue-600 dark:bg-gray-800 border-t border-blue-500 dark:border-gray-700 shadow-lg px-4 py-4 space-y-3 transition-all duration-300">
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <div class="grid grid-cols-2 gap-2">
                <button onclick="switchTabAndCloseMobile('dashboard')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></button>
                <button onclick="switchTabAndCloseMobile('tracking')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-solid fa-map-location-dot"></i><span>Tracking</span></button>
                <button onclick="switchTabAndCloseMobile('dispatches')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-regular fa-file-lines"></i><span>Dispatches</span></button>
                <button onclick="switchTabAndCloseMobile('fleet')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-solid fa-truck-fast"></i><span>Fleet</span></button>
                <button onclick="switchTabAndCloseMobile('drivers')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-regular fa-user"></i><span>Drivers</span></button>
                <button onclick="switchTabAndCloseMobile('orders')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold"><i class="fa-solid fa-clipboard-list"></i><span>Orders</span></button>
                <button onclick="switchTabAndCloseMobile('reports')" class="flex items-center justify-center space-x-2 bg-blue-700 hover:bg-blue-850 text-white p-3 rounded-xl transition text-xs font-semibold col-span-2"><i class="fa-solid fa-chart-column"></i><span>Reports</span></button>
            </div>
        <?php elseif ($_SESSION['role'] === 'Driver'): ?>
            <div class="px-2 py-1 text-blue-100 text-sm border-b border-blue-500/30 pb-2 flex items-center justify-between">
                <span>Welcome, <strong class="text-white"><?= htmlspecialchars($_SESSION['username'] ?? 'Driver'); ?></strong></span>
                <span class="text-[10px] bg-blue-700 text-blue-100 px-2 py-0.5 rounded-full font-bold uppercase">Driver</span>
            </div>
            <div class="flex flex-col space-y-2">
                <?php if ($has_pending_cancellation ?? false): ?>
                    <button class="w-full flex items-center justify-center py-3 text-gray-400 bg-gray-200 dark:bg-gray-700 rounded-xl cursor-not-allowed text-sm font-semibold" disabled>
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Cancellation Pending...
                    </button>
                <?php else: ?>
                    <button onclick="openCancelTripModal(); toggleMobileMenu();" class="w-full flex items-center justify-center py-3 text-white bg-orange-655 dark:bg-orange-700 hover:bg-orange-700 rounded-xl transition text-sm font-semibold">
                        <i class="fa-solid fa-ban mr-2"></i> Request Cancellation
                    </button>
                <?php endif; ?>
                <button onclick="openChangePasswordModal(); toggleMobileMenu();" class="w-full flex items-center justify-center py-3 text-white bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 rounded-xl transition text-sm font-semibold">
                    <i class="fa-solid fa-key mr-2"></i> Change Password
                </button>
            </div>
        <?php elseif ($_SESSION['role'] === 'Checker'): ?>
            <div class="px-2 py-1 text-blue-100 text-sm border-b border-blue-500/30 pb-2 flex items-center justify-between">
                <span>Checker: <strong class="text-white"><?= htmlspecialchars($checker_full_name ?? $_SESSION['username'] ?? 'Checker'); ?></strong></span>
                <span class="text-[10px] bg-blue-700 text-blue-100 px-2 py-0.5 rounded-full font-bold uppercase">Checker</span>
            </div>
            <button onclick="openChangePasswordModal(); toggleMobileMenu();" class="w-full flex items-center justify-center py-3 text-white bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 rounded-xl transition text-sm font-semibold">
                <i class="fa-solid fa-key mr-2"></i> Change Password
            </button>
        <?php endif; ?>

        <div class="border-t border-blue-500/30 pt-3">
            <a href="../logout.php" class="w-full flex items-center justify-center space-x-2 py-3 bg-red-600 hover:bg-red-750 text-white rounded-xl font-bold transition text-sm shadow-md">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('hamburger-icon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
        function switchTabAndCloseMobile(tab) {
            switchTab(tab);
            toggleMobileMenu();
        }
        document.addEventListener("DOMContentLoaded", function() {
            const icons = document.querySelectorAll('#themeIcon, .themeIconMobile');
            if (document.documentElement.classList.contains('dark')) {
                icons.forEach(i => i.classList.replace('fa-moon', 'fa-sun'));
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
            localStorage.setItem('theme', isNowDark ? "dark" : "light");
            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
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
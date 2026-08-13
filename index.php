<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Fetch live stats for the branding panel ──
try {
    $statFleet      = (int) $pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
    $statDeliveries = (int) $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered'")->fetchColumn();

    // On-time rate: percentage of completed dispatches that were on time
    $totalCompleted = (int) $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered'")->fetchColumn();
    $onTimeCount    = (int) $pdo->query("SELECT COUNT(*) FROM dispatches WHERE status = 'Delivered' AND is_on_time = 1")->fetchColumn();
    $statOnTime     = $totalCompleted > 0 ? round(($onTimeCount / $totalCompleted) * 100) : 100;
} catch (Exception $e) {
    $statFleet      = 0;
    $statDeliveries = 0;
    $statOnTime     = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSV Trucking System — Sign In</title>
    <meta name="description" content="Sign in to the SSV Trucking System portal. Manage fleet operations, track deliveries, and monitor logistics.">

    <!-- Prevent FOUC: apply dark class before paint -->
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
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 2s infinite',
                        'float-slow': 'float 8s ease-in-out 1s infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'slide-up-delayed': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards',
                        'slide-up-delayed-2': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards',
                        'fade-in': 'fadeIn 0.8s ease forwards',
                        'truck-drive': 'truckDrive 12s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            },
                        },
                        slideUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(30px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            },
                        },
                        truckDrive: {
                            '0%': {
                                transform: 'translateX(-100%)'
                            },
                            '100%': {
                                transform: 'translateX(calc(100vw + 100%))'
                            },
                        },
                    },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Custom Scrollbar ── */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 9999px;
        }

        /* ── Glassmorphism Panel ── */
        .glass-panel {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ── Gradient border effect ── */
        .gradient-border {
            position: relative;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 2px;
            background: linear-gradient(135deg, #3b82f6, #6366f1, #8b5cf6);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        /* ── Input focus glow ── */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15), 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), 0 1px 2px 0 rgba(0, 0, 0, 0.2);
        }

        /* ── Animated background mesh ── */
        .mesh-gradient {
            background:
                radial-gradient(ellipse at 20% 50%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(99, 102, 241, 0.25) 0%, transparent 50%),
                radial-gradient(ellipse at 40% 80%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        /* ── Road animation ── */
        .road-line {
            animation: roadScroll 1.5s linear infinite;
        }

        @keyframes roadScroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        /* ── Particle dots ── */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: particleFloat 8s ease-in-out infinite;
        }

        @keyframes particleFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.4;
            }

            25% {
                transform: translate(10px, -30px) scale(1.1);
                opacity: 0.7;
            }

            50% {
                transform: translate(-15px, -10px) scale(0.9);
                opacity: 0.5;
            }

            75% {
                transform: translate(20px, 15px) scale(1.05);
                opacity: 0.6;
            }
        }

        /* ── Stagger children ── */
        .stagger-children>* {
            opacity: 0;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .stagger-children>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stagger-children>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stagger-children>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stagger-children>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        .stagger-children>*:nth-child(5) {
            animation-delay: 0.5s;
        }

        .stagger-children>*:nth-child(6) {
            animation-delay: 0.6s;
        }

        .stagger-children>*:nth-child(7) {
            animation-delay: 0.7s;
        }

        @keyframes slideUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Button ripple ── */
        .btn-ripple {
            position: relative;
            overflow: hidden;
        }

        .btn-ripple::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--x, 50%) var(--y, 50%), rgba(255, 255, 255, 0.2) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-ripple:hover::after {
            opacity: 1;
        }

        /* ── Password toggle ── */
        .password-wrapper .toggle-password {
            opacity: 0;
            transition: opacity 0.2s;
        }

        .password-wrapper:focus-within .toggle-password,
        .password-wrapper:hover .toggle-password {
            opacity: 1;
        }

        /* ── Stats counter ── */
        .stat-card {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased transition-colors duration-300">

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  SPLIT-SCREEN CONTAINER                                  ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- ════════════════════════════════════════════════════════ -->
        <!-- LEFT PANEL — Branding & Visuals                        -->
        <!-- ════════════════════════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-[55%] xl:w-[58%] relative mesh-gradient overflow-hidden flex-col justify-between p-10 xl:p-14">

            <!-- Floating particles -->
            <div class="particle w-3 h-3" style="top:15%; left:20%; animation-delay:0s;"></div>
            <div class="particle w-2 h-2" style="top:35%; left:70%; animation-delay:2s;"></div>
            <div class="particle w-4 h-4" style="top:60%; left:40%; animation-delay:4s;"></div>
            <div class="particle w-2 h-2" style="top:80%; left:80%; animation-delay:1s;"></div>
            <div class="particle w-3 h-3" style="top:25%; left:55%; animation-delay:3s;"></div>
            <div class="particle w-2 h-2" style="top:70%; left:15%; animation-delay:5s;"></div>

            <!-- Top bar: Logo + Badge -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="src/ssvLogoLight.png" alt="SSV Logo" class="h-9">
                    <div>
                        <h2 class="text-white font-bold text-lg tracking-tight">SSV Trucking</h2>
                        <p class="text-blue-200/60 text-xs font-medium">Management System</p>
                    </div>
                </div>
                <div class="glass-panel rounded-full px-3.5 py-1.5 flex items-center space-x-2">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-emerald-300 text-xs font-medium">System Online</span>
                </div>
            </div>

            <!-- Center: Hero Content -->
            <div class="relative z-10 flex-1 flex flex-col justify-center max-w-lg mx-auto w-full -mt-8">
                <!-- Animated Truck Illustration -->
                <div class="mb-10 animate-fade-in">
                    <div class="relative">
                        <!-- Decorative circles -->
                        <div class="absolute -top-6 -left-6 w-28 h-28 bg-blue-500/10 rounded-full blur-2xl animate-pulse-slow"></div>
                        <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-indigo-500/10 rounded-full blur-xl animate-pulse-slow" style="animation-delay: 1.5s;"></div>

                        <!-- Central icon with glass background -->
                        <div class="relative glass-panel rounded-3xl p-8 xl:p-10 text-center animate-float">
                            <div class="w-20 h-20 mx-auto mb-5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25 rotate-3">
                                <i class="fa-solid fa-truck-fast text-3xl text-white -rotate-3"></i>
                            </div>
                            <h1 class="text-white text-2xl xl:text-3xl font-extrabold tracking-tight leading-tight mb-3">
                                Fleet Operations<br>
                                <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">Made Effortless</span>
                            </h1>
                            <p class="text-slate-300/70 text-sm leading-relaxed max-w-xs mx-auto">
                                Monitor your fleet in real-time, manage deliveries, and optimize routes — all from one powerful dashboard.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-3 gap-3 animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="stat-card glass-panel rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-bold text-white mb-0.5" id="statFleet">0</div>
                        <div class="text-blue-200/50 text-xs font-medium uppercase tracking-wider">Vehicles</div>
                    </div>
                    <div class="stat-card glass-panel rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-bold text-white mb-0.5" id="statDeliveries">0</div>
                        <div class="text-blue-200/50 text-xs font-medium uppercase tracking-wider">Deliveries</div>
                    </div>
                    <div class="stat-card glass-panel rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-bold text-white mb-0.5" id="statUptime">0<span class="text-lg">%</span></div>
                        <div class="text-blue-200/50 text-xs font-medium uppercase tracking-wider">Uptime</div>
                    </div>
                </div>
            </div>

            <!-- Bottom: Road animation + footer -->
            <div class="relative z-10">
                <!-- Animated road -->
                <div class="relative h-1.5 bg-slate-700/40 rounded-full overflow-hidden mb-6">
                    <div class="road-line absolute inset-y-0 flex items-center" style="width: 200%;">
                        <div class="flex space-x-6" style="width: 50%;">
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                        </div>
                        <div class="flex space-x-6" style="width: 50%;">
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                            <span class="block w-8 h-0.5 bg-yellow-400/50 rounded-full"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-slate-400/50 text-xs">
                    <span>&copy; <?= date('Y') ?> SSV Trucking. All rights reserved.</span>
                    <span class="flex items-center space-x-1">
                        <i class="fa-solid fa-shield-halved text-emerald-400/50"></i>
                        <span>Secured Portal</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════ -->
        <!-- RIGHT PANEL — Login Form                                -->
        <!-- ════════════════════════════════════════════════════════ -->
        <div class="flex-1 flex items-center justify-center px-6 sm:px-10 lg:px-16 py-10 bg-white dark:bg-gray-950 relative">

            <!-- Theme Toggle -->
            <button id="themeToggle" onclick="toggleTheme(event)"
                class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 group"
                aria-label="Toggle dark mode">
                <i id="themeIcon" class="fa-solid fa-moon text-sm group-hover:scale-110 transition-transform duration-200"></i>
            </button>

            <!-- Mobile branding (shown on small screens instead of left panel) -->
            <div class="lg:hidden absolute top-6 left-6 flex items-center space-x-2.5">
                <div class="flex-shrink-0">
                    <img src="src/ssvLogo.png" alt="SSV Logo" class="h-7 block dark:hidden">
                    <img src="src/ssvLogoLight.png" alt="SSV Logo" class="h-7 hidden dark:block">
                </div>
                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">SSV Trucking</span>
            </div>

            <!-- Form Container -->
            <div class="w-full max-w-sm stagger-children">

                <!-- Welcome Header -->
                <div class="mb-8">
                    <div class="inline-flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold px-3 py-1.5 rounded-full mb-4 border border-blue-100 dark:border-blue-800/30">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Secure Authentication</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Welcome back
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm leading-relaxed">
                        Sign in to your account to access the management portal
                    </p>
                </div>

                <!-- Error Alert -->
                <?php if (isset($_GET['error']) && htmlspecialchars($_GET['error']) == 'invalid'): ?>
                    <div id="errorAlert" class="flex items-start space-x-3 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 p-4 rounded-xl mb-6 text-sm border border-red-100 dark:border-red-900/40 animate-slide-up">
                        <div class="w-5 h-5 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-exclamation text-xs"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Authentication Failed</p>
                            <p class="text-red-500/80 dark:text-red-400/70 text-xs mt-0.5">Invalid username or password. Please try again.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="auth.php" method="POST" class="space-y-5" id="loginForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                            Username
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-400 dark:text-gray-500 text-sm group-focus-within:text-blue-500 transition-colors duration-200"></i>
                            </div>
                            <input type="text" id="username" name="username" required
                                class="input-glow w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl pl-11 pr-4 py-3.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:border-blue-500 dark:focus:border-blue-500 focus:bg-white dark:focus:bg-gray-900 transition-all duration-200"
                                placeholder="Enter your username">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                            Password
                        </label>
                        <div class="relative group password-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-lock text-gray-400 dark:text-gray-500 text-sm group-focus-within:text-blue-500 transition-colors duration-200"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="input-glow w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl pl-11 pr-12 py-3.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:border-blue-500 dark:focus:border-blue-500 focus:bg-white dark:focus:bg-gray-900 transition-all duration-200"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" id="togglePasswordBtn"
                                class="toggle-password absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200"
                                aria-label="Toggle password visibility">
                                <i id="passwordIcon" class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-1">
                        <button type="submit" id="loginBtn"
                            class="btn-ripple w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3.5 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-950 active:scale-[0.98] flex items-center justify-center space-x-2 text-sm"
                            onmousemove="updateRipple(event, this)">
                            <span id="btnText">Sign In to Portal</span>
                            <i id="btnIcon" class="fa-solid fa-arrow-right text-xs transition-transform duration-200 group-hover:translate-x-0.5"></i>
                            <div id="btnSpinner" class="hidden">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white dark:bg-gray-950 px-3 text-xs text-gray-400 dark:text-gray-600 font-medium">Portal Access</span>
                    </div>
                </div>

                <!-- Role Badges -->
                <div class="flex items-center justify-center space-x-3">
                    <div class="flex items-center space-x-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-user-shield text-blue-500/70 text-[10px]"></i>
                        <span>Admin</span>
                    </div>
                    <div class="flex items-center space-x-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-truck text-indigo-500/70 text-[10px]"></i>
                        <span>Driver</span>
                    </div>
                    <div class="flex items-center space-x-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-clipboard-check text-emerald-500/70 text-[10px]"></i>
                        <span>Checker</span>
                    </div>
                </div>

                <!-- Footer for mobile -->
                <p class="lg:hidden text-center text-xs text-gray-400 dark:text-gray-600 mt-8">
                    &copy; <?= date('Y') ?> SSV Trucking. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  SCRIPTS                                                 ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <script>
        // ── Theme Toggle ──
        document.addEventListener("DOMContentLoaded", function() {
            if (document.documentElement.classList.contains('dark')) {
                document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
            }

            // Animate stat counters with live data
            animateCounter('statFleet', <?= $statFleet ?>, 1500);
            animateCounter('statDeliveries', <?= $statDeliveries ?>, 2000);
            animateCounter('statUptime', <?= $statOnTime ?>, 1800);
        });

        function toggleTheme(event) {
            const htmlTag = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');

            // Ripple effect from button
            if (event) {
                const x = event.clientX;
                const y = event.clientY;
                const circle = document.createElement('div');
                circle.className = 'fixed rounded-full pointer-events-none z-[9999] transition-all duration-[700ms] ease-out';
                circle.style.left = x + 'px';
                circle.style.top = y + 'px';
                circle.style.width = '0px';
                circle.style.height = '0px';
                circle.style.transform = 'translate(-50%, -50%)';

                const isGoingDark = !htmlTag.classList.contains('dark');
                circle.style.backgroundColor = isGoingDark ? 'rgba(56, 189, 248, 0.08)' : 'rgba(250, 204, 21, 0.08)';
                circle.style.backdropFilter = 'contrast(1.05)';

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
            if (isNowDark) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
            localStorage.setItem('theme', isNowDark ? "dark" : "light");
            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }

        // ── Password Visibility Toggle ──
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // ── Button Ripple Effect ──
        function updateRipple(e, btn) {
            const rect = btn.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            btn.style.setProperty('--x', x + '%');
            btn.style.setProperty('--y', y + '%');
        }

        // ── Form Submit Loading State ──
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            const btnSpinner = document.getElementById('btnSpinner');

            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            btnText.textContent = 'Signing in...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });

        // ── Animated Counter ──
        function animateCounter(id, target, duration) {
            const el = document.getElementById(id);
            if (!el) return;

            const start = 0;
            const startTime = performance.now();
            const isPercent = el.innerHTML.includes('%');

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (target - start) * eased);

                if (isPercent) {
                    el.innerHTML = current + '<span class="text-lg">%</span>';
                } else {
                    el.textContent = current.toLocaleString();
                }

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            requestAnimationFrame(update);
        }

        // ── Auto-dismiss error after 6s ──
        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                errorAlert.style.opacity = '0';
                errorAlert.style.transform = 'translateY(-10px)';
                setTimeout(() => errorAlert.remove(), 400);
            }, 6000);
        }

        // ── Enter key visual feedback ──
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const btn = document.getElementById('loginBtn');
                if (btn && !btn.disabled) {
                    btn.classList.add('scale-[0.98]');
                    setTimeout(() => btn.classList.remove('scale-[0.98]'), 150);
                }
            }
        });
    </script>
</body>

</html>
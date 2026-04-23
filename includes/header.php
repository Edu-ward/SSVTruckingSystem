<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSV Trucking - Admin System</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        #map {
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
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <nav class="bg-blue-600 dark:bg-gray-800 text-white px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xl font-bold">
            <i class="fa-solid fa-truck"></i>
            <span>SSV Trucking</span>
        </div>

        <div class="flex space-x-2 text-sm font-medium text-blue-100 items-center">

            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn flex items-center space-x-1 text-white bg-blue-700 px-3 py-1.5 rounded transition"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></button>
            <button onclick="switchTab('tracking')" id="nav-tracking" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-map-location-dot"></i><span>Live Tracking</span></button>
            <button onclick="switchTab('dispatches')" id="nav-dispatches" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-file-lines"></i><span>Dispatches</span></button>
            <button onclick="switchTab('fleet')" id="nav-fleet" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-truck-fast"></i><span>Fleet</span></button>
            <button onclick="switchTab('drivers')" id="nav-drivers" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-user"></i><span>Drivers</span></button>
            <button onclick="switchTab('reports')" id="nav-reports" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-chart-column"></i><span>Reports</span></button>
            <button id="themeToggle" onclick="toggleTheme(event)" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-700 hover:bg-blue-800 text-white transition-colors focus:outline-none shadow-sm ml-2 relative overflow-hidden">
                <i id="themeIcon" class="fa-solid fa-moon text-lg"></i>
            </button>

            <div class="w-px h-5 bg-blue-400 mx-2 opacity-50"></div>

            <a href="dashboard.php?action=logout" class="flex items-center space-x-1 px-3 py-1.5 rounded transition text-red-200 hover:text-white hover:bg-red-500">
                <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
            </a>
        </div>
    </nav>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (document.documentElement.classList.contains('dark')) {
                document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
            }
        });

        function toggleTheme(event) {
            const htmlTag = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
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
            if (isNowDark) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            localStorage.setItem('theme', isNowDark ? "dark" : "light");
            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }
    </script>
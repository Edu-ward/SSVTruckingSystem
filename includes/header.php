<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSV Trucking - Admin System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
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

<body>
    <nav class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xl font-bold"><i class="fa-solid fa-truck"></i><span>SSV Trucking</span></div>

        <div class="flex space-x-2 text-sm font-medium text-blue-100 items-center">
            <button onclick="document.getElementById('addDriverModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Add Driver
            </button>
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn flex items-center space-x-1 text-white bg-blue-700 px-3 py-1.5 rounded transition"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></button>
            <button onclick="switchTab('tracking')" id="nav-tracking" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-map-location-dot"></i><span>Live Tracking</span></button>
            <button onclick="switchTab('dispatches')" id="nav-dispatches" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-file-lines"></i><span>Dispatches</span></button>
            <button onclick="switchTab('fleet')" id="nav-fleet" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-truck-fast"></i><span>Fleet</span></button>
            <button onclick="switchTab('drivers')" id="nav-drivers" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-regular fa-user"></i><span>Drivers</span></button>
            <button onclick="switchTab('reports')" id="nav-reports" class="nav-btn flex items-center space-x-1 hover:text-white px-3 py-1.5 rounded transition"><i class="fa-solid fa-chart-column"></i><span>Reports</span></button>

            <div class="w-px h-5 bg-blue-400 mx-2 opacity-50"></div>

            <a href="dashboard.php?action=logout" class="flex items-center space-x-1 px-3 py-1.5 rounded transition text-red-200 hover:text-white hover:bg-red-500">
                <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
            </a>
        </div>
    </nav>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Panel - SSV Trucking</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

    <nav class="bg-[#3b82f6] dark:bg-gray-800 text-white py-4 px-6 shadow-md">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center space-x-2 text-xl font-bold">
                <div class="flex-shrink-0">
                    <img src="../src/ssvLogo.png" alt="SSV Logo" class="h-8 block dark:hidden">
                    <img src="../src/ssvLogoLight.png" alt="SSV Logo" class="h-8 hidden dark:block">
                </div>
                <span>SSV Trucking</span>
            </div>

            <div class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <span class="text-blue-100">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
                <button onclick="openChangePasswordModal()" class="flex items-center px-3 py-2 text-white bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 dark:hover:bg-gray-600 rounded-md transition shadow-sm">
                    <i class="fa-solid fa-key mr-2"></i> Change Password
                </button>

                <div class="w-px h-5 bg-blue-400 mx-2 opacity-50"></div>

                <a href="../logout.php" class="flex items-center px-3 py-2 text-blue-100 hover:text-white hover:bg-red-500 rounded transition">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
                </a>

                <button id="themeToggle" onclick="toggleTheme(event)" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-700 dark:bg-gray-700 hover:bg-blue-800 dark:hover:bg-gray-600 text-white transition-colors focus:outline-none shadow-sm ml-2 relative overflow-hidden">
                    <i id="themeIcon" class="fa-solid fa-moon text-lg"></i>
                </button>
            </div>
        </div>
    </nav>

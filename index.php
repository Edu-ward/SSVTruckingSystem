<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSV Trucking System Login</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-900 dark:bg-gray-900 h-screen flex items-center justify-center transition-colors duration-200">

    <button id="themeToggle" onclick="toggleTheme(event)" class="fixed top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 dark:bg-gray-700 hover:bg-blue-700 text-white transition-colors focus:outline-none shadow-sm z-50 overflow-hidden">
        <i id="themeIcon" class="fa-solid fa-moon text-lg"></i>
    </button>

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-2xl shadow-2xl w-[92%] max-w-md border-t-4 border-blue-600 transition-all duration-300">
        <div class="text-center mb-8">
            <img src="src/ssvLogo.png" alt="SSV Trucking Logo" class="mx-auto h-20 mb-3 block dark:hidden">
            <img src="src/ssvLogoLight.png" alt="SSV Trucking Logo" class="mx-auto h-20 mb-3 hidden dark:block">
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-gray-100 tracking-tight">SSV Trucking</h1>
            <p class="text-slate-500 dark:text-gray-400 mt-1.5 text-sm">Sign in to access your portal</p>
        </div>

        <?php if (isset($_GET['error']) && htmlspecialchars($_GET['error']) == 'invalid'): ?>
            <div class="bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 p-4 rounded-xl mb-6 text-sm text-center border border-red-100 dark:border-red-900/50">
                <i class="fa-solid fa-circle-exclamation mr-1.5 text-base"></i> Invalid Username or Password
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Username</label>
                <input type="text" name="username" required class="w-full border border-gray-300 dark:border-gray-600 px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500" placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 dark:border-gray-600 px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500" placeholder="••••••••">
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Login to Portal
            </button>
        </form>
    </div>

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
</body>

</html>
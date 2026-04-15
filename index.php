<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | TruckFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 dark:bg-gray-900 h-screen flex items-center justify-center">

    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl w-96 border-t-4 border-blue-600">
        <div class="text-center mb-6">
            <i class="fa-solid fa-user-shield text-4xl text-blue-600 mb-2"></i>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-200">Admin Access</h1>
        </div>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center border border-red-200">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> Invalid Username or Password
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" name="username" required class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" placeholder="Username">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" placeholder="••••••">
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition shadow-lg">
                Login
            </button>
        </form>
    </div>

</body>
</html>
<?php
session_start();
require '../db.php';

// Protect the page: only logged-in Drivers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver') {
    header("Location: index.php");
    exit;
}

$driver_id = $_SESSION['user_id'];

// Fetch Payroll Data
$stmt = $pdo->prepare("SELECT * FROM driver_payroll WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$payroll = $stmt->fetch() ?: ['total_amount' => 0, 'amount_claimed' => 0, 'available_balance' => 0];

// Fetch Past Trips
$stmt2 = $pdo->prepare("SELECT * FROM driver_trips WHERE driver_id = ? ORDER BY trip_date DESC");
$stmt2->execute([$driver_id]);
$trips = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Panel - SSV Trucking</title>
    <script>
        // Check localStorage instantly to avoid FOUC and bypass PHP caching
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
                <i class="fa-solid fa-truck-fast"></i>
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

    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-[#4a8df8] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Payroll Amount</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($payroll['total_amount'], 2); ?></h3>
                    <p class="text-blue-100 text-sm mt-2">Lifetime earnings</p>
                </div>
                <i class="fa-solid fa-wallet absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#2ccb72] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-green-100 text-sm font-medium mb-1">Payroll Claimed</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($payroll['amount_claimed'], 2); ?></h3>
                    <p class="text-green-100 text-sm mt-2">Successfully withdrawn</p>
                </div>
                <i class="fa-solid fa-hand-holding-dollar absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

            <div class="bg-[#fa7d20] rounded-xl p-6 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-orange-100 text-sm font-medium mb-1">Available Balance</p>
                    <h3 class="text-4xl font-bold tracking-tight">₱<?= number_format($payroll['available_balance'], 2); ?></h3>
                    <p class="text-orange-100 text-sm mt-2">Ready to claim</p>
                </div>
                <i class="fa-solid fa-coins absolute -right-6 -bottom-6 text-9xl text-white opacity-20 transform -rotate-12"></i>
            </div>

        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-6">Past Trip History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 px-2 font-medium">Date</th>
                            <th class="pb-3 px-2 font-medium">Destination</th>
                            <th class="pb-3 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-200">
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-900 transition-colors">
                                    <td class="py-4 px-2"><?= date('M d, Y', strtotime($trip['trip_date'])); ?></td>
                                    <td class="py-4 px-2 font-medium"><?= htmlspecialchars($trip['destination']); ?></td>
                                    <td class="py-4 px-2">
                                        <?php if ($trip['status'] == 'Completed'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fa-solid fa-check mr-1"></i> Completed
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fa-solid fa-truck-fast mr-1"></i> <?= htmlspecialchars($trip['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-8 px-2 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-road text-4xl mb-3 text-gray-300 block"></i>
                                    No trips recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- OTP Modals -->
    <div id="otpModalOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-50">
        <div id="stepRequest" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-center transform scale-95 opacity-0 transition-all duration-300 hidden">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Change Password</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">We will send a 6-digit OTP code to your registered mobile number for verification.</p>
            
            <div class="flex space-x-3">
                <button onclick="closeOtpModal()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="requestOtp()" id="btnRequestOtp" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Send OTP</button>
            </div>
        </div>

        <div id="stepVerify" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 text-center">Enter OTP</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center">Code sent to <strong id="otpPhoneText" class="text-gray-800 dark:text-gray-200"></strong>. Enter it below.</p>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">6-Digit OTP</label>
                <input type="text" id="otpInput" maxlength="6" placeholder="XXXXXX" class="w-full text-center tracking-widest text-lg border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">New Password</label>
                <input type="password" id="newPasswordInput" class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <div class="flex space-x-3">
                <button onclick="closeOtpModal()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="verifyAndChangePwd()" id="btnVerifyOtp" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">Update</button>
            </div>
        </div>
    </div>

    <script>
        function openChangePasswordModal() {
            document.getElementById('otpModalOverlay').classList.remove('hidden');
            document.getElementById('otpModalOverlay').classList.add('flex');
            showStep('stepRequest');
        }

        function closeOtpModal() {
            document.getElementById('otpModalOverlay').classList.add('hidden');
            document.getElementById('otpModalOverlay').classList.remove('flex');
            document.getElementById('stepRequest').classList.remove('scale-100', 'opacity-100');
            document.getElementById('stepVerify').classList.remove('scale-100', 'opacity-100');
        }

        function showStep(stepId) {
            document.getElementById('stepRequest').classList.add('hidden');
            document.getElementById('stepVerify').classList.add('hidden');
            
            const step = document.getElementById(stepId);
            step.classList.remove('hidden');
            
            setTimeout(() => {
                step.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function requestOtp() {
            const btn = document.getElementById('btnRequestOtp');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch('otp_handler.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("SIMULATED SMS: Your SSV Trucking OTP code is " + data.simulated_otp);
                    document.getElementById('otpPhoneText').innerText = "***-***-" + data.phone_last_4;
                    btn.innerHTML = 'Send OTP';
                    btn.disabled = false;
                    showStep('stepVerify');
                } else {
                    alert('Error: ' + data.message);
                    btn.innerHTML = 'Send OTP';
                    btn.disabled = false;
                }
            }).catch(e => {
                alert('Network Error');
                btn.innerHTML = 'Send OTP';
                btn.disabled = false;
            });
        }

        function verifyAndChangePwd() {
            const otp = document.getElementById('otpInput').value;
            const pwd = document.getElementById('newPasswordInput').value;

            if(!otp || !pwd) {
                alert("Please fill all fields");
                return;
            }

            const btn = document.getElementById('btnVerifyOtp');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const fn = new FormData();
            fn.append('otp', otp);
            fn.append('new_password', pwd);

            fetch('change_pwd.php', {
                method: 'POST',
                body: fn
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    closeOtpModal();
                    document.getElementById('otpInput').value = '';
                    document.getElementById('newPasswordInput').value = '';
                } else {
                    alert('Error: ' + data.message);
                }
                btn.innerHTML = 'Update';
                btn.disabled = false;
            }).catch(e => {
                alert("Network Error");
                btn.innerHTML = 'Update';
                btn.disabled = false;
            });
        }

        // 1. Set the correct icon instantly when the page loads
        document.addEventListener("DOMContentLoaded", function() {
            if (document.documentElement.classList.contains('dark')) {
                document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
            }
        });

        // 2. The function that runs ONLY when you click the button
        function toggleTheme(event) {
            const htmlTag = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');

            // Trigger premium ripple animation
            if (event) {
                const x = event.clientX;
                const y = event.clientY;
                const circle = document.createElement('div');
                
                // Add styling for expanding wave
                circle.className = 'fixed rounded-full pointer-events-none z-[9999] transition-all duration-[700ms] ease-out';
                circle.style.left = x + 'px';
                circle.style.top = y + 'px';
                circle.style.width = '0px';
                circle.style.height = '0px';
                circle.style.transform = 'translate(-50%, -50%)';
                
                const isGoingDark = !htmlTag.classList.contains('dark');
                // Give a magical glowing tint to the ripple (blue glow for dark mode, golden glow for light mode)
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

            // Toggle the dark class
            const isNowDark = htmlTag.classList.toggle('dark');

            // Swap the icon
            if (isNowDark) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }

            // Save to store explicitly
            localStorage.setItem('theme', isNowDark ? "dark" : "light");
            
            // Save to cookie as backup
            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }
    </script>
</body>

</html>
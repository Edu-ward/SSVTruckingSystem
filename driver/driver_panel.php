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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <nav class="bg-[#3b82f6] text-white py-4 px-6 shadow-md">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center space-x-2 text-xl font-bold">
                <i class="fa-solid fa-truck-fast"></i>
                <span>SSV Trucking</span>
            </div>

            <div class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <span class="text-blue-100">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
                <span class="flex items-center px-3 py-2 rounded-md transition">
                    <i class="fa-solid fa-table-columns mr-2"></i> Dashboard
                </span>
                <a href="../logout.php" class="flex items-center px-3 py-2 text-blue-100 hover:text-white transition">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
                </a>
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Past Trip History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 text-sm border-b border-gray-200">
                            <th class="pb-3 px-2 font-medium">Date</th>
                            <th class="pb-3 px-2 font-medium">Destination</th>
                            <th class="pb-3 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
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
                                <td colspan="3" class="py-8 px-2 text-center text-gray-500">
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

</body>

</html>
<?php
// view-pwd_requests — Password Reset Requests Management
?>
<div id="view-pwd_requests" class="tab-content hidden">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-key text-indigo-500"></i>
                Password Reset Requests
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Approve or reject password reset requests from drivers and checkers.</p>
        </div>
        <?php if ($pendingPwdResetCount > 0): ?>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <?= $pendingPwdResetCount ?> pending
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($pwdResetRequests)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow p-16 text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-check-circle text-green-500 text-3xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-lg">All clear!</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">No pending password reset requests at this time.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($pwdResetRequests as $req): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-amber-200 dark:border-amber-800/50 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Avatar + Info -->
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 <?= $req['role'] === 'Driver' ? 'bg-blue-500' : 'bg-indigo-500' ?>">
                            <?= strtoupper(substr($req['username'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                <?= htmlspecialchars($req['username']) ?>
                                <span class="ml-1.5 text-xs font-bold px-2 py-0.5 rounded-full <?= $req['role'] === 'Driver' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' ?>">
                                    <?= htmlspecialchars($req['role']) ?>
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-clock"></i>
                                Requested <?= date('M d, Y \a\t h:i A', strtotime($req['requested_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status badge -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 flex-shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i> Pending
                    </span>

                    <!-- Actions -->
                    <div class="flex gap-2 flex-shrink-0">
                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Approve this password reset request for <?= htmlspecialchars(addslashes($req['username'])) ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="approve_pwd_reset">
                            <input type="hidden" name="req_id" value="<?= $req['id'] ?>">
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                        </form>
                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Reject this password reset request?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="reject_pwd_reset">
                            <input type="hidden" name="req_id" value="<?= $req['id'] ?>">
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold bg-red-500 hover:bg-red-600 text-white transition">
                                <i class="fa-solid fa-xmark"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

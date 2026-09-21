<?php

if (!defined('IS_ADMIN_PANEL')) {
    define('IS_ADMIN_PANEL', true);
}

if (!$isSuperadmin) {
?>
    <div id="view-admin_management" class="tab-content hidden">
        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-2xl p-8 text-center max-w-lg mx-auto my-12">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-shield-xmark"></i>
            </div>
            <h3 class="text-lg font-bold text-red-800 dark:text-red-300">Access Restricted</h3>
            <p class="text-sm text-red-600/80 dark:text-red-400/80 mt-2">This panel is restricted exclusively to Super Administrators. Your current role does not grant permission to view or manipulate administrator accounts.</p>
        </div>
    </div>
<?php
    return;
}

$totalAdmins = count($adminAccounts);
$totalSuperadmins = count(array_filter($adminAccounts, fn($a) => $a['role'] === 'Superadmin'));
$totalActiveAdmins = count(array_filter($adminAccounts, fn($a) => ($a['status'] ?? 'Active') === 'Active'));
$totalInactiveAdmins = $totalAdmins - $totalActiveAdmins;
?>

<div id="view-admin_management" class="tab-content hidden space-y-6">

    
    <?php if (!empty($_SESSION['admin_msg_success'])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 text-sm flex items-start justify-between gap-3 animate-slide-up shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg flex-shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($_SESSION['admin_msg_success']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <?php unset($_SESSION['admin_msg_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['admin_msg_error'])): ?>
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-sm flex items-start justify-between gap-3 animate-slide-up shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg flex-shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($_SESSION['admin_msg_error']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-200 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <?php unset($_SESSION['admin_msg_error']); ?>
    <?php endif; ?>

    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20 text-lg">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        Admin Account Management
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold uppercase tracking-wider">Superadmin Only</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Control administrative access, provision staff accounts, manage credentials, and toggle account states.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openCreateAdminModal()" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98]">
                <i class="fa-solid fa-user-plus"></i>
                <span>Create Admin Account</span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Administrators</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users-gear"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white mt-2"><?= $totalAdmins ?></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">All authorized personnel</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Superadmin Tier</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-crown"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2"><?= $totalSuperadmins ?></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Elevated root controllers</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Active Accounts</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2"><?= $totalActiveAdmins ?></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Ready to authenticate</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Inactive / Disabled</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-ban"></i>
                </span>
            </div>
            <div class="text-2xl font-black <?= $totalInactiveAdmins > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' ?> mt-2"><?= $totalInactiveAdmins ?></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Access suspended</p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="adminSearchInput" oninput="filterAdminAccounts()" placeholder="Search username or role..."
                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select id="adminRoleFilter" onchange="filterAdminAccounts()" class="w-1/2 sm:w-auto text-xs px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Roles</option>
                <option value="Superadmin">Superadmins</option>
                <option value="Admin">Regular Admins</option>
            </select>
            <select id="adminStatusFilter" onchange="filterAdminAccounts()" class="w-1/2 sm:w-auto text-xs px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300" id="adminAccountsTable">
                <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3.5">Administrator</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Account Status</th>
                        <th class="px-5 py-3.5">Created Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    <?php if (empty($adminAccounts)): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-user-shield text-3xl mb-2 block opacity-40"></i>
                                No administrator accounts found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($adminAccounts as $adm): 
                            $isSelf = ($adm['id'] == $_SESSION['user_id']);
                            $isSuper = ($adm['role'] === 'Superadmin');
                            $isActive = (($adm['status'] ?? 'Active') === 'Active');
                        ?>
                            <tr class="admin-row hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition-colors"
                                data-username="<?= htmlspecialchars(strtolower($adm['username'])) ?>"
                                data-role="<?= htmlspecialchars($adm['role']) ?>"
                                data-status="<?= $isActive ? 'Active' : 'Inactive' ?>">
                                
                                
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl <?= $isSuper ? 'bg-gradient-to-tr from-indigo-600 to-purple-600 text-white shadow-indigo-500/20' : 'bg-blue-600 text-white' ?> flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                                            <?= $isSuper ? 'SA' : 'AD' ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span><?= htmlspecialchars($adm['username']) ?></span>
                                                <?php if ($isSelf): ?>
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">ID: #<?= $adm['id'] ?></div>
                                        </div>
                                    </div>
                                </td>

                                
                                <td class="px-5 py-3.5">
                                    <?php if ($isSuper): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">
                                            <i class="fa-solid fa-crown text-[10px]"></i>
                                            Superadmin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                                            <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                            Admin
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-5 py-3.5">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-[11px]">
                                    <?= !empty($adm['created_at']) ? date('M d, Y h:i A', strtotime($adm['created_at'])) : '—' ?>
                                </td>

                                
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        
                                        <button type="button" onclick="openEditAdminModal(<?= (int)$adm['id'] ?>, '<?= htmlspecialchars(addslashes($adm['username'])) ?>', '<?= htmlspecialchars($adm['role']) ?>', '<?= htmlspecialchars($adm['status'] ?? 'Active') ?>')"
                                            class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center transition active:scale-95" title="Edit Admin">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>

                                        
                                        <button type="button" onclick="openResetAdminPasswordModal(<?= (int)$adm['id'] ?>, '<?= htmlspecialchars(addslashes($adm['username'])) ?>')"
                                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center transition active:scale-95" title="Reset Password">
                                            <i class="fa-solid fa-key text-xs"></i>
                                        </button>

                                        
                                        <?php if ($isSelf): ?>
                                            <button type="button" disabled title="You cannot deactivate your current account" class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/40 text-gray-400 cursor-not-allowed flex items-center justify-center">
                                                <i class="fa-solid fa-power-off text-xs opacity-40"></i>
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Are you sure you want to <?= $isActive ? 'DEACTIVATE' : 'ACTIVATE' ?> account <?= htmlspecialchars(addslashes($adm['username'])) ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="action" value="toggle_admin_status">
                                                <input type="hidden" name="admin_id" value="<?= (int)$adm['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $isActive ? 'Inactive' : 'Active' ?>">
                                                <button type="submit" class="w-8 h-8 rounded-xl <?= $isActive ? 'bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 text-rose-600 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400' ?> flex items-center justify-center transition active:scale-95"
                                                    title="<?= $isActive ? 'Deactivate Account' : 'Activate Account' ?>">
                                                    <i class="fa-solid <?= $isActive ? 'fa-user-slash' : 'fa-user-check' ?> text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        
                                        <?php if ($isSelf): ?>
                                            <button type="button" disabled title="You cannot delete your own account" class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/40 text-gray-400 cursor-not-allowed flex items-center justify-center">
                                                <i class="fa-solid fa-trash text-xs opacity-40"></i>
                                            </button>
                                        <?php elseif ($isSuper && $totalSuperadmins <= 1): ?>
                                            <button type="button" disabled title="Cannot delete the sole Superadmin" class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/40 text-gray-400 cursor-not-allowed flex items-center justify-center">
                                                <i class="fa-solid fa-trash text-xs opacity-40"></i>
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('PERMANENT DELETION: Are you sure you want to permanently delete administrator account <?= htmlspecialchars(addslashes($adm['username'])) ?>? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="action" value="delete_admin_account">
                                                <input type="hidden" name="admin_id" value="<?= (int)$adm['id'] ?>">
                                                <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center transition active:scale-95" title="Permanently Delete Account">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterAdminAccounts() {
        const query = (document.getElementById('adminSearchInput')?.value || '').toLowerCase().trim();
        const roleFilter = document.getElementById('adminRoleFilter')?.value || 'all';
        const statusFilter = document.getElementById('adminStatusFilter')?.value || 'all';

        const rows = document.querySelectorAll('#adminAccountsTable tbody tr.admin-row');
        rows.forEach(row => {
            const username = row.getAttribute('data-username') || '';
            const role = row.getAttribute('data-role') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesQuery = !query || username.includes(query) || role.toLowerCase().includes(query);
            const matchesRole = (roleFilter === 'all') || (role === roleFilter);
            const matchesStatus = (statusFilter === 'all') || (status === statusFilter);

            if (matchesQuery && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function openCreateAdminModal() {
        const modal = document.getElementById('createAdminModal');
        if (modal) {
            modal.classList.remove('hidden');
            const userInp = document.getElementById('newAdminUsername');
            if (userInp) userInp.focus();
        }
    }

    function openEditAdminModal(id, username, role, status) {
        const modal = document.getElementById('editAdminModal');
        if (!modal) return;
        document.getElementById('editAdminId').value = id;
        document.getElementById('editAdminUsername').value = username;
        document.getElementById('editAdminRole').value = role;
        document.getElementById('editAdminStatus').value = status;
        modal.classList.remove('hidden');
    }

    function openResetAdminPasswordModal(id, username) {
        const modal = document.getElementById('resetAdminPasswordModal');
        if (!modal) return;
        document.getElementById('resetAdminPwdId').value = id;
        document.getElementById('resetAdminPwdUsername').textContent = username;
        document.getElementById('resetAdminNewPwd').value = '';
        document.getElementById('resetAdminConfirmPwd').value = '';
        modal.classList.remove('hidden');
    }
</script>

<div id="pwdResetOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden justify-center items-center z-50 p-4">

    <!-- Step 1: Request -->
    <div id="prStepRequest" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-sm p-6 text-center transform scale-95 opacity-0 transition-all duration-300">
        <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-key"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Reset Password</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Send a reset request to the Admin. Once approved, you can set your new password.</p>
        <div id="prRequestMsg" class="hidden mb-4 text-sm rounded-lg px-3 py-2"></div>
        <div class="flex space-x-3">
            <button onclick="closePwdResetModal()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
            <button onclick="submitResetRequest()" id="btnSendRequest" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">Send Request</button>
        </div>
    </div>

    <!-- Step 2: Waiting -->
    <div id="prStepWaiting" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-sm p-6 text-center transform scale-95 opacity-0 transition-all duration-300">
        <div class="w-16 h-16 bg-amber-50 dark:bg-amber-900/30 text-amber-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 animate-pulse">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Awaiting Approval</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Your request has been sent. Please wait for Admin approval.</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-6">This page will automatically notify you when approved.</p>
        <div class="flex items-center justify-center gap-2 text-xs text-indigo-500 mb-4">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Checking for approval...</span>
        </div>
        <button onclick="closePwdResetModal()" class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Close & Wait</button>
    </div>

    <!-- Step 3: Set new password -->
    <div id="prStepSetPwd" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300">
        <div class="w-16 h-16 bg-green-50 dark:bg-green-900/30 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fa-solid fa-unlock"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1 text-center">Request Approved!</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 text-center">You can now set your new password.</p>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1 text-left">New Password <span class="text-red-500">*</span></label>
            <input type="password" id="prNewPassword" placeholder="At least 8 characters..." class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors text-sm">
            <div class="mt-2 space-y-1 text-xs text-left text-gray-400 dark:text-gray-500">
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-[5px]"></i> At least 8 characters</div>
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-[5px]"></i> At least one uppercase letter (A-Z)</div>
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-[5px]"></i> At least one lowercase letter (a-z)</div>
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-[5px]"></i> At least one number (0-9)</div>
            </div>
        </div>

        <div id="prSetPwdMsg" class="hidden mb-3 text-sm rounded-lg px-3 py-2"></div>

        <div class="flex space-x-3">
            <button onclick="closePwdResetModal()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
            <button onclick="submitNewPassword()" id="btnSetPwd" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">Update Password</button>
        </div>
    </div>
</div>

<script>
    function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (show) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    function openChangePasswordModal() { openResetPasswordModal(); } // alias kept for safety

    function openChangePasswordModal() {
        openResetPasswordModal();
    }

    function openResetPasswordModal() {
        // handled by global scripts
    }

    function closeOtpModal() { closePwdResetModal(); }
</script>

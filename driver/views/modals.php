    <!-- ====== PASSWORD RESET MODAL ====== -->
    <div id="pwdResetOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden justify-center items-center z-[99999] p-4">

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

        <!-- Step 2: Waiting for approval -->
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

        <!-- Step 3: Set new password (after approval) -->
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

    <!-- Cancel Trip Modal -->
    <div id="cancelTripModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-[99999] p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-md p-6 transform scale-95 opacity-0 transition-all duration-300" id="cancelTripModalContent">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <i class="fa-solid fa-ban text-orange-500 mr-2"></i> Request Cancellation
            </h3>
            <p class="text-sm text-gray-655 dark:text-gray-400 mb-4">
                Did your truck break down? You can request a trip cancellation. This will notify the Admin for approval.
            </p>
            <form method="POST" action="dashboard.php" id="cancelTripForm">
                <input type="hidden" name="action" value="request_cancel_trip">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Reason for Request</label>
                    <input type="text" name="reason" required placeholder="e.g. Engine failure, flat tire..." class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelTripModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Go Back</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 transition">Send Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== CASH ADVANCE REQUEST MODAL ====== -->
    <div id="cashAdvanceModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-[99999] p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-md p-6 transform scale-95 opacity-0 transition-all duration-300" id="cashAdvanceModalContent">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-orange-500"></i> Request Cash Advance
                </h3>
                <button onclick="closeCashAdvanceModal()" class="text-gray-400 hover:text-gray-700 dark:text-gray-300">
                    <i class="fa-solid fa-xmark fa-lg"></i>
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Submit a cash advance request. Once approved by the Admin, the amount will be automatically deducted from your payroll.
            </p>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="request_cash_advance">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Amount (₱) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-gray-500 font-bold text-sm">₱</span>
                        <input type="number" name="ca_amount" min="100" step="50" required placeholder="e.g. 500"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-xl pl-8 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Minimum: ₱100</p>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Reason <span class="text-gray-400 font-normal">(required)</span></label>
                    <textarea name="ca_reason" rows="2" placeholder="e.g. Emergency, fuel expense..." required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none text-sm"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeCashAdvanceModal()" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 transition shadow-sm">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelTripModal() {
            const modal = document.getElementById('cancelTripModal');
            const content = document.getElementById('cancelTripModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function closeCancelTripModal() {
            const modal = document.getElementById('cancelTripModal');
            const content = document.getElementById('cancelTripModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function openCashAdvanceModal() {
            const modal = document.getElementById('cashAdvanceModal');
            const content = document.getElementById('cashAdvanceModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function closeCashAdvanceModal() {
            const modal = document.getElementById('cashAdvanceModal');
            const content = document.getElementById('cashAdvanceModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    </script>
    <div id="otpModalOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-50 p-4">
        <div id="stepRequest" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-sm p-6 text-center transform scale-95 opacity-0 transition-all duration-300 hidden">
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

        <div id="stepVerify" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 text-center">Enter OTP</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center">Code sent to <strong id="otpPhoneText" class="text-gray-800 dark:text-gray-200"></strong>. Enter it below.</p>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">6-Digit OTP</label>
                <input type="text" id="otpInput" maxlength="6" placeholder="XXXXXX" class="w-full text-center tracking-widest text-lg border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1 text-left">New Password <span class="text-red-500">*</span></label>
                <input type="password" id="newPasswordInput" placeholder="At least 8 characters..." class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors text-sm">
                
                <div class="mt-3 space-y-1.5 text-xs text-left">
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least 8 characters
                    </div>
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one uppercase letter (A-Z)
                    </div>
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one lowercase letter (a-z)
                    </div>
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle text-[6px] mr-2"></i> At least one number (0-9)
                    </div>
                </div>
            </div>

            <div class="flex space-x-3">
                <button onclick="closeOtpModal()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="verifyAndChangePwd()" id="btnVerifyOtp" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">Update</button>
            </div>
        </div>
    </div>

    <!-- Cancel Trip Modal -->
    <div id="cancelTripModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-50 p-4">
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
    </script>

<div id="otpModalOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-50 p-4">
    <!-- Step 1: Request OTP -->
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

    <!-- Step 2: Verify OTP & Change Password -->
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

<script>
    // These functions are already defined in includes/scripts.php for Driver role, 
    // but since we are in Checker role, we can rely on them being available if we include scripts.php
    
    function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (show) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    // Alias for header compatibility
    function openChangePasswordModal() {
        const overlay = document.getElementById('otpModalOverlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        showStep('stepRequest');
    }

    function closeOtpModal() {
        const overlay = document.getElementById('otpModalOverlay');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        
        document.querySelectorAll('#otpModalOverlay > div').forEach(div => {
            div.classList.add('hidden');
            div.classList.remove('scale-100', 'opacity-100');
            div.classList.add('scale-95', 'opacity-0');
        });
    }

    function showStep(stepId) {
        document.querySelectorAll('#otpModalOverlay > div').forEach(div => {
            div.classList.add('hidden');
            div.classList.remove('scale-100', 'opacity-100');
            div.classList.add('scale-95', 'opacity-0');
        });
        const step = document.getElementById(stepId);
        step.classList.remove('hidden');
        setTimeout(() => {
            step.classList.remove('scale-95', 'opacity-0');
            step.classList.add('scale-100', 'opacity-100');
        }, 50);
    }
</script>

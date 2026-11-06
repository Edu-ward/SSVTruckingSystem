<div id="pwdResetOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden justify-center items-center z-50 p-4">

    
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

<div id="checkerPhotoCropModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[85vh]">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-crop-simple"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white leading-tight">Crop Profile Photo</h3>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Position & frame your avatar (1:1 square)</p>
                </div>
            </div>
            <button type="button" onclick="closeCheckerPhotoCropModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center transition active:scale-95" aria-label="Close">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="relative bg-gray-950 p-2 sm:p-3 flex items-center justify-center overflow-hidden h-[300px] sm:h-[350px]">
            <img id="checkerPhotoCropImage" src="" alt="Crop image" class="max-w-full max-h-full block">
        </div>

        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0 text-xs">
            <div class="flex items-center space-x-1 sm:space-x-1.5">
                <button type="button" onclick="checkerCropperZoom(0.1)" title="Zoom In" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                    <span class="hidden sm:inline text-[11px]">Zoom In</span>
                </button>
                <button type="button" onclick="checkerCropperZoom(-0.1)" title="Zoom Out" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                    <span class="hidden sm:inline text-[11px]">Zoom Out</span>
                </button>
                <button type="button" onclick="checkerCropperRotate(-90)" title="Rotate Left" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
                <button type="button" onclick="checkerCropperRotate(90)" title="Rotate Right" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center gap-1 font-semibold active:scale-95">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
            <button type="button" onclick="checkerCropperReset()" class="px-2 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition font-medium flex items-center gap-1 text-[11px]">
                <i class="fa-solid fa-arrow-rotate-left"></i>
                <span>Reset</span>
            </button>
        </div>

        <div class="px-5 py-3.5 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-2.5 flex-shrink-0">
            <button type="button" onclick="closeCheckerPhotoCropModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Cancel
            </button>
            <button type="button" onclick="saveCheckerCroppedPhoto()" id="btnSaveCheckerCroppedPhoto" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition active:scale-95 flex items-center gap-2">
                <i id="btnSaveCheckerCroppedSpinner" class="fa-solid fa-spinner fa-spin text-xs hidden"></i>
                <span id="btnSaveCheckerCroppedText">Save & Set Photo</span>
            </button>
        </div>
    </div>
</div>

<script>
    function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (!modal) return;
        if (show) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    function openChangePasswordModal() { 
        if (typeof openResetPasswordModal === 'function') {
            openResetPasswordModal(); 
        }
    }

    function closeOtpModal() { 
        if (typeof closePwdResetModal === 'function') {
            closePwdResetModal(); 
        }
    }

    // -------------------------------------------------------------
    // Checker Profile Photo Cropping & Upload
    // -------------------------------------------------------------
    let checkerCropperInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('checkerPhotoInput');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) {
                    if (typeof showToast === 'function') showToast('Please select a valid image file (JPG, PNG, GIF, or WEBP).', 'error');
                    else alert('Please select a valid image file (JPG, PNG, GIF, or WEBP).');
                    this.value = '';
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    if (typeof showToast === 'function') showToast('Selected image must be under 10MB.', 'error');
                    else alert('Selected image must be under 10MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(evt) {
                    openCheckerPhotoCropModal(evt.target.result);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    function openCheckerPhotoCropModal(imageSrc) {
        const modal = document.getElementById('checkerPhotoCropModal');
        const img = document.getElementById('checkerPhotoCropImage');
        if (!modal || !img) return;

        img.src = imageSrc;
        modal.classList.remove('hidden');

        if (checkerCropperInstance) {
            checkerCropperInstance.destroy();
            checkerCropperInstance = null;
        }

        setTimeout(() => {
            if (typeof Cropper !== 'undefined') {
                checkerCropperInstance = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            }
        }, 100);
    }

    function closeCheckerPhotoCropModal() {
        const modal = document.getElementById('checkerPhotoCropModal');
        if (modal) modal.classList.add('hidden');
        if (checkerCropperInstance) {
            checkerCropperInstance.destroy();
            checkerCropperInstance = null;
        }
        const input = document.getElementById('checkerPhotoInput');
        if (input) input.value = '';
    }

    function checkerCropperZoom(val) {
        if (checkerCropperInstance) checkerCropperInstance.zoom(val);
    }

    function checkerCropperRotate(deg) {
        if (checkerCropperInstance) checkerCropperInstance.rotate(deg);
    }

    function checkerCropperReset() {
        if (checkerCropperInstance) checkerCropperInstance.reset();
    }

    function saveCheckerCroppedPhoto() {
        const form = document.getElementById('checkerPhotoForm');
        if (!checkerCropperInstance) {
            if (form) form.submit();
            return;
        }

        const btn = document.getElementById('btnSaveCheckerCroppedPhoto');
        const btnText = document.getElementById('btnSaveCheckerCroppedText');
        const btnSpinner = document.getElementById('btnSaveCheckerCroppedSpinner');

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
        }
        if (btnText) btnText.textContent = 'Uploading...';
        if (btnSpinner) btnSpinner.classList.remove('hidden');

        const canvas = checkerCropperInstance.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            if (form) form.submit();
            return;
        }

        canvas.toBlob(function(blob) {
            if (!blob) {
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                }
                if (btnText) btnText.textContent = 'Save & Set Photo';
                if (btnSpinner) btnSpinner.classList.add('hidden');
                return;
            }

            try {
                const croppedFile = new File([blob], 'profile_cropped.jpg', {
                    type: 'image/jpeg'
                });
                const dt = new DataTransfer();
                dt.items.add(croppedFile);
                document.getElementById('checkerPhotoInput').files = dt.files;
                form.submit();
            } catch (err) {
                const formData = new FormData();
                const csrfInput = form.querySelector('[name="csrf_token"]');
                if (csrfInput) formData.append('csrf_token', csrfInput.value);
                formData.append('profile_photo', blob, 'profile_cropped.jpg');
                formData.append('ajax', '1');

                fetch('upload_profile_photo.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).then(r => r.json()).then(res => {
                    if (res && res.success) {
                        window.location.reload();
                    } else {
                        alert(res.message || 'Upload failed.');
                        window.location.reload();
                    }
                }).catch(() => {
                    window.location.reload();
                });
            }
        }, 'image/jpeg', 0.92);
    }
</script>

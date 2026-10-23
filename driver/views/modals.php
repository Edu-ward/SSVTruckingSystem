    <div id="pwdResetOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden justify-center items-center z-[99999] p-4">


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


    <div id="cancelTripModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-[99999] p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[92%] max-w-md p-6 transform scale-95 opacity-0 transition-all duration-300" id="cancelTripModalContent">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <i class="fa-solid fa-ban text-orange-500 mr-2"></i> Request Cancellation
            </h3>
            <p class="text-sm text-gray-655 dark:text-gray-400 mb-4">
                Did your truck break down? You can request a trip cancellation. This will notify the Admin for approval.
            </p>
            <form method="POST" action="dashboard.php" id="cancelTripForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="request_cancel_trip">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">Reason for Request <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <button type="button" onclick="setCancelReason('Flat Tire')" class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-500 text-gray-600 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 bg-gray-50 dark:bg-gray-700 transition font-medium">Flat Tire</button>
                        <button type="button" onclick="setCancelReason('Engine Failure')" class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-500 text-gray-600 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 bg-gray-50 dark:bg-gray-700 transition font-medium">Engine Failure</button>
                        <button type="button" onclick="setCancelReason('Brake Malfunction')" class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-500 text-gray-600 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 bg-gray-50 dark:bg-gray-700 transition font-medium">Brake Malfunction</button>
                        <button type="button" onclick="setCancelReason('Overheating')" class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-500 text-gray-600 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 bg-gray-50 dark:bg-gray-700 transition font-medium">Overheating</button>
                    </div>
                    <input type="text" id="cancelReasonInput" name="reason" required placeholder="e.g. Engine failure, flat tire..." class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <!-- Photo Attachment Option -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-camera text-orange-500"></i> Photo Attachment
                        </span>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">Optional proof (flat tire, broken part)</span>
                    </label>

                    <!-- Dropzone / Tap to snap or upload -->
                    <div id="cancelPhotoDropzone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-orange-500 dark:hover:border-orange-400 rounded-xl p-4 text-center cursor-pointer transition bg-gray-50/60 dark:bg-gray-750/30" onclick="document.getElementById('cancelPhotoInput').click()">
                        <input type="file" name="cancellation_photo" id="cancelPhotoInput" accept="image/*" class="hidden" onchange="handleCancelPhotoSelected(this)">
                        <div class="w-10 h-10 mx-auto mb-1.5 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Take photo or upload image proof</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">JPG, PNG, WEBP (Max 5MB)</p>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="cancelPhotoPreviewContainer" class="hidden mt-2 relative border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                        <img id="cancelPhotoPreviewImg" src="" alt="Photo Preview" class="w-full h-36 object-cover">
                        <div class="absolute top-2 right-2">
                            <button type="button" onclick="clearCancelPhoto()" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow transition" title="Remove Photo">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <div class="p-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm text-xs flex justify-between items-center text-gray-700 dark:text-gray-300 border-t border-gray-100 dark:border-gray-700">
                            <span id="cancelPhotoFileName" class="truncate max-w-[200px] font-medium"></span>
                            <span id="cancelPhotoFileSize" class="text-gray-400 text-[11px] ml-2"></span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelTripModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Go Back</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>


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
                        <input type="number" name="ca_amount" min="100" max="2000" step="50" required placeholder="e.g. 500"
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

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- DIGITAL DISPATCH TICKET MODAL (TRIPS HISTORY)              -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div id="tripTicketModal" class="fixed inset-0 bg-gray-950/75 backdrop-blur-sm hidden justify-center items-center z-[99999] p-2.5 sm:p-4 overflow-y-auto" onclick="handleTripTicketModalOverlayClick(event)">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[95%] max-w-2xl border border-gray-200 dark:border-gray-700/80 overflow-hidden transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] flex flex-col my-auto" id="tripTicketModalContent">

            <!-- Top Bar with Dismiss and Print Actions -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white leading-tight">Digital Dispatch Ticket</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Trip Waybill &amp; Delivery Milestones</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="ttModalPrintBtnTop" onclick="printTripModalTicket()" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-print text-blue-500"></i>
                        <span class="hidden sm:inline">Print</span>
                    </button>
                    <button type="button" onclick="closeTripTicketModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition" title="Close Modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable Ticket Body -->
            <div class="overflow-y-auto flex-1 p-3 sm:p-5 space-y-4">

                <!-- TICKET CONTAINER (Mirrors Active Ticket Look) -->
                <div class="rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">

                    <!-- Ticket Header Gradient -->
                    <div id="ttModalHeaderGradient" class="bg-gradient-to-r from-blue-700 via-indigo-700 to-sky-700 px-5 sm:px-6 pt-5 pb-5 text-white relative overflow-hidden">
                        <!-- Watermark -->
                        <div class="absolute -right-6 -bottom-8 opacity-10 pointer-events-none select-none">
                            <i class="fa-solid fa-truck-moving text-[12rem]"></i>
                        </div>

                        <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-sm">
                                        <i class="fa-solid fa-truck text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-extrabold uppercase tracking-[0.16em] text-white/80">SSV Trucking System</span>
                                            <span class="w-1 h-1 rounded-full bg-white/40"></span>
                                            <span class="text-[9px] font-semibold text-white/70">San Leonardo, N.E.</span>
                                        </div>
                                        <p class="text-[11px] font-bold text-white tracking-wide uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-ticket text-cyan-300 text-[10px]"></i> Digital Dispatch Waybill
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5 mt-1.5 flex-wrap">
                                    <h2 id="ttModalTicketNumber" class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white leading-none">
                                        TKT-0000-0000
                                    </h2>
                                    <button type="button" id="ttModalCopyBtn" onclick="copyDriverModalTicket(this)" class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-white/15 hover:bg-white/25 active:scale-95 border border-white/20 transition flex items-center gap-1 text-white">
                                        <i class="fa-regular fa-copy text-[10px]"></i>
                                        <span>Copy</span>
                                    </button>
                                </div>
                                <p class="text-white/70 text-[11px] mt-1.5 flex items-center gap-2 flex-wrap">
                                    <span><i class="fa-regular fa-calendar text-[10px] mr-1"></i> Issued: <strong id="ttModalIssuedDate">—</strong></span>
                                    <span>&bull;</span>
                                    <span id="ttModalTripTag" class="font-mono text-white/60">Trip #—</span>
                                </p>
                            </div>

                            <div class="flex flex-col items-start sm:items-end gap-2">
                                <span id="ttModalStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider backdrop-blur-md border shadow-md bg-emerald-500/25 text-emerald-100 border-emerald-400/40">
                                    <span id="ttModalStatusDot" class="w-2 h-2 rounded-full bg-emerald-300 shadow-sm shadow-emerald-300"></span>
                                    <span id="ttModalStatusText">Delivered</span>
                                </span>
                            </div>
                        </div>

                        <!-- ── DISPATCH MILESTONES: TIME OF EXIT & TIME OF ARRIVAL ── -->
                        <div class="relative z-10 mt-5 pt-4 border-t border-white/20">
                            <div class="flex items-center justify-between mb-2.5 flex-wrap gap-2">
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-white/80 flex items-center gap-1.5">
                                    <i class="fa-solid fa-route text-cyan-300"></i> Dispatch Milestones &bull; Site Exit &amp; Arrival Tracking
                                </p>
                                <span id="ttModalDistanceBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/15 text-white/90 border border-white/20 font-mono">
                                    0.0 km
                                </span>
                            </div>

                            <!-- 2 Milestones Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 relative">
                                <!-- Milestone 1: Time of Exit -->
                                <div class="rounded-xl p-3.5 bg-white/10 backdrop-blur-md border border-white/20 shadow-sm flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <div id="ttModalExitIconWrap" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm bg-emerald-400 text-slate-900 ring-2 ring-emerald-300/40">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                            <div>
                                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-cyan-200 block">Milestone 1</span>
                                                <h4 class="text-xs font-black text-white tracking-wide uppercase">Time of Exit</h4>
                                            </div>
                                        </div>
                                        <span id="ttModalExitBadge" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                                            Exited Site
                                        </span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-white/15 flex items-baseline justify-between flex-wrap gap-2">
                                        <div>
                                            <p id="ttModalExitTime" class="text-xl sm:text-2xl font-black font-mono tracking-tight text-white leading-none">
                                                --:-- --
                                            </p>
                                            <p class="text-[11px] text-white/70 mt-1 flex items-center gap-1 truncate max-w-[200px]">
                                                <i class="fa-solid fa-warehouse text-[9px] text-cyan-300"></i>
                                                <span id="ttModalExitOrigin" class="truncate">SSV Quarry Garage</span>
                                            </p>
                                        </div>
                                        <span id="ttModalExitDate" class="text-[10px] text-white/60 font-mono">—</span>
                                    </div>
                                </div>

                                <!-- Milestone 2: Time of Arrival -->
                                <div class="rounded-xl p-3.5 bg-white/10 backdrop-blur-md border border-white/20 shadow-sm flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <div id="ttModalArrivalIconWrap" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm bg-emerald-400 text-slate-900 ring-2 ring-emerald-300/40">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </div>
                                            <div>
                                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-cyan-200 block">Milestone 2</span>
                                                <h4 class="text-xs font-black text-white tracking-wide uppercase">Time of Arrival</h4>
                                            </div>
                                        </div>
                                        <span id="ttModalArrivalBadge" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                                            Arrived / Delivered
                                        </span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-white/15 flex items-baseline justify-between flex-wrap gap-2">
                                        <div>
                                            <p id="ttModalArrivalTime" class="text-xl sm:text-2xl font-black font-mono tracking-tight text-white leading-none">
                                                --:-- --
                                            </p>
                                            <p class="text-[11px] text-white/70 mt-1 flex items-center gap-1 truncate max-w-[200px]">
                                                <i class="fa-solid fa-location-dot text-[9px] text-rose-300"></i>
                                                <span id="ttModalArrivalDest" class="truncate">—</span>
                                            </p>
                                        </div>
                                        <span id="ttModalArrivalDate" class="text-[10px] text-white/60 font-mono">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 2-Point Visual Progress Bar -->
                            <div class="mt-3.5 pt-2.5 border-t border-white/15 flex items-center justify-between relative px-2">
                                <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-white/20 rounded-full overflow-hidden">
                                    <div id="ttModalProgressBar" class="h-full w-full bg-emerald-400 transition-all duration-500"></div>
                                </div>

                                <div class="relative z-10 flex items-center gap-1.5 bg-slate-900/90 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/25 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-white">1. Time of Exit</span>
                                </div>

                                <div class="relative z-10 hidden sm:flex items-center gap-1 bg-slate-900/95 backdrop-blur-md px-3 py-1 rounded-full border border-cyan-400/40 text-[9px] font-bold text-cyan-200 shadow-sm">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    <span id="ttModalMidStatusLabel">Trip Completed</span>
                                </div>

                                <div class="relative z-10 flex items-center gap-1.5 bg-slate-900/90 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/25 shadow-sm">
                                    <span id="ttModalArrivalDot" class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-white">2. Time of Arrival</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perforated Tear-Line Notch -->
                    <div class="relative bg-white dark:bg-gray-800 flex items-center overflow-hidden" style="height:18px;">
                        <div class="absolute -left-2.5 w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-inner"></div>
                        <div class="flex-1 border-t-2 border-dashed border-gray-300 dark:border-gray-700 mx-5"></div>
                        <div class="absolute -right-2.5 w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-inner"></div>
                    </div>

                    <!-- Ticket Details Grid -->
                    <div class="p-4 sm:p-5 space-y-3">
                        <!-- Origin & Destination -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-3 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 flex items-center gap-1 mb-1">
                                    <i class="fa-solid fa-warehouse text-blue-500"></i> Origin / Facility
                                </span>
                                <p id="ttModalOrigin" class="font-bold text-gray-900 dark:text-white text-sm">SSV Quarry Garage</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">San Leonardo, Nueva Ecija</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-3 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 flex items-center gap-1 mb-1">
                                    <i class="fa-solid fa-location-dot text-rose-500"></i> Destination
                                </span>
                                <p id="ttModalDestination" class="font-bold text-gray-900 dark:text-white text-sm">—</p>
                                <div id="ttModalLandmarkWrap" class="mt-1 hidden">
                                    <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200/60 dark:border-amber-800/40 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-map-pin text-[9px]"></i>
                                        <span id="ttModalLandmark"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Truck, Driver, Client, Cargo -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5 border border-gray-100 dark:border-gray-800">
                                <span class="text-[9px] font-bold uppercase text-gray-400 block">Truck Code</span>
                                <span id="ttModalTruckCode" class="font-mono font-bold text-gray-900 dark:text-white text-xs">—</span>
                                <span id="ttModalRfidTag" class="text-[10px] text-gray-500 block">RFID: —</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5 border border-gray-100 dark:border-gray-800">
                                <span class="text-[9px] font-bold uppercase text-gray-400 block">Driver</span>
                                <span id="ttModalDriverName" class="font-bold text-gray-900 dark:text-white text-xs truncate block">—</span>
                                <span class="text-[10px] text-gray-500 block">Authorized</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5 border border-gray-100 dark:border-gray-800">
                                <span class="text-[9px] font-bold uppercase text-gray-400 block">Client</span>
                                <span id="ttModalClientName" class="font-bold text-gray-900 dark:text-white text-xs truncate block">—</span>
                                <a id="ttModalClientPhoneLink" href="#" class="text-[10px] text-blue-500 dark:text-blue-400 truncate block hover:underline">
                                    <i class="fa-solid fa-phone text-[8px] mr-0.5"></i><span id="ttModalClientPhone">—</span>
                                </a>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5 border border-gray-100 dark:border-gray-800">
                                <span class="text-[9px] font-bold uppercase text-gray-400 block">Cargo Volume</span>
                                <span id="ttModalCargoVolume" class="font-bold text-gray-900 dark:text-white text-xs block">— cu.m</span>
                                <span id="ttModalOrderNumber" class="text-[10px] text-gray-500 block font-mono truncate">—</span>
                            </div>
                        </div>

                        <!-- Distance & Pay Highlight Strip -->
                        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-xl p-3.5 flex items-center justify-between border border-white/10 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-sm font-bold text-cyan-300">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-cyan-200/80 block">Trip Earnings</span>
                                    <span id="ttModalPayAmount" class="text-xl font-black font-mono text-emerald-300">₱0.00</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-white/60 block">Travel Distance</span>
                                <span id="ttModalDistanceValue" class="text-sm font-black font-mono text-cyan-200">0.0 km</span>
                            </div>
                        </div>

                        <!-- Cancellation Box (hidden unless cancelled) -->
                        <div id="ttModalCancelBox" class="hidden rounded-xl p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 space-y-2">
                            <div class="flex items-center gap-2 font-bold">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                <span>Trip Cancellation Record</span>
                            </div>
                            <p id="ttModalCancelReason" class="text-rose-700 dark:text-rose-300"></p>
                            <div id="ttModalCancelPhotoWrap" class="hidden pt-1">
                                <a id="ttModalCancelPhotoLink" href="#" target="_blank" class="inline-block group">
                                    <img id="ttModalCancelPhotoImg" src="" alt="Proof" class="w-28 h-20 object-cover rounded-lg border border-rose-300 dark:border-rose-700 shadow-sm group-hover:opacity-90 transition">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 flex-shrink-0">
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                    SSV Digital Dispatch System
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeTripTicketModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:scale-95 transition">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function openCancelTripModal() {
            const modal = document.getElementById('cancelTripModal');
            const content = document.getElementById('cancelTripModalContent');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (content) {
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 50);
            }
        }

        function closeCancelTripModal() {
            const modal = document.getElementById('cancelTripModal');
            const content = document.getElementById('cancelTripModalContent');
            if (!modal) return;
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function setCancelReason(reason) {
            const input = document.getElementById('cancelReasonInput');
            if (input) {
                input.value = reason;
                input.focus();
            }
        }

        function handleCancelPhotoSelected(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('Photo size exceeds the 5MB limit. Please choose a smaller photo.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('cancelPhotoPreviewImg');
                    if (img) img.src = e.target.result;
                    const nameEl = document.getElementById('cancelPhotoFileName');
                    if (nameEl) nameEl.innerText = file.name;
                    const sizeEl = document.getElementById('cancelPhotoFileSize');
                    if (sizeEl) sizeEl.innerText = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                    const dropzone = document.getElementById('cancelPhotoDropzone');
                    const preview = document.getElementById('cancelPhotoPreviewContainer');
                    if (dropzone) dropzone.classList.add('hidden');
                    if (preview) preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function clearCancelPhoto() {
            const input = document.getElementById('cancelPhotoInput');
            if (input) input.value = '';
            const img = document.getElementById('cancelPhotoPreviewImg');
            if (img) img.src = '';
            const dropzone = document.getElementById('cancelPhotoDropzone');
            const preview = document.getElementById('cancelPhotoPreviewContainer');
            if (preview) preview.classList.add('hidden');
            if (dropzone) dropzone.classList.remove('hidden');
        }

        function openCashAdvanceModal() {
            const modal = document.getElementById('cashAdvanceModal');
            const content = document.getElementById('cashAdvanceModalContent');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (content) {
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 50);
            }
        }

        function closeCashAdvanceModal() {
            const modal = document.getElementById('cashAdvanceModal');
            const content = document.getElementById('cashAdvanceModalContent');
            if (!modal) return;
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        // ══════════════════════════════════════════════════════════
        // DIGITAL TICKET MODAL LOGIC (TRIPS HISTORY)
        // ══════════════════════════════════════════════════════════
        let currentTripModalData = null;

        function openTripTicketModal(data) {
            if (!data) return;
            currentTripModalData = data;

            const modal = document.getElementById('tripTicketModal');
            const content = document.getElementById('tripTicketModalContent');
            if (!modal) return;

            // Ticket Number & IDs
            const ticketNo = data.ticket_number || ('TRIP-' + String(data.trip_id).padStart(4, '0'));
            document.getElementById('ttModalTicketNumber').textContent = ticketNo;
            document.getElementById('ttModalTripTag').textContent = 'Trip #' + data.trip_id + (data.dispatch_id ? ' • DISP #' + data.dispatch_id : '');

            // Format Issued Date
            let issuedDateStr = '—';
            if (data.trip_date) {
                const parts = data.trip_date.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    issuedDateStr = d.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            } else if (data.created_at) {
                const d = new Date(data.created_at.replace(' ', 'T'));
                if (!isNaN(d)) issuedDateStr = d.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
            }
            document.getElementById('ttModalIssuedDate').textContent = issuedDateStr;

            // Status Styling
            const status = (data.status || 'Delivered').trim();
            const isDelivered = (status === 'Delivered' || status === 'Completed');
            const isCancelled = (status === 'Cancelled' || status === 'Cancellation Requested');
            const isInTransit = (status === 'In Transit');

            const statusBadge = document.getElementById('ttModalStatusBadge');
            const statusDot = document.getElementById('ttModalStatusDot');
            const statusText = document.getElementById('ttModalStatusText');
            const headerGrad = document.getElementById('ttModalHeaderGradient');
            const progressBar = document.getElementById('ttModalProgressBar');
            const midStatusLabel = document.getElementById('ttModalMidStatusLabel');

            if (isDelivered) {
                headerGrad.className = 'bg-gradient-to-r from-emerald-700 via-teal-700 to-cyan-800 px-5 sm:px-6 pt-5 pb-5 text-white relative overflow-hidden';
                statusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider backdrop-blur-md border shadow-md bg-emerald-500/25 text-emerald-100 border-emerald-400/40';
                statusDot.className = 'w-2 h-2 rounded-full bg-emerald-300 shadow-sm shadow-emerald-300';
                statusText.textContent = 'Delivered';
                progressBar.className = 'h-full w-full bg-emerald-400 transition-all duration-500';
                midStatusLabel.textContent = 'Trip Completed';
            } else if (isInTransit) {
                headerGrad.className = 'bg-gradient-to-r from-blue-700 via-indigo-700 to-sky-700 px-5 sm:px-6 pt-5 pb-5 text-white relative overflow-hidden';
                statusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider backdrop-blur-md border shadow-md bg-cyan-500/25 text-cyan-100 border-cyan-400/40 animate-pulse';
                statusDot.className = 'w-2 h-2 rounded-full bg-cyan-300 animate-ping';
                statusText.textContent = 'In Transit';
                progressBar.className = 'h-full w-1/2 bg-cyan-300 animate-pulse transition-all duration-500';
                midStatusLabel.textContent = 'En Route';
            } else if (isCancelled) {
                headerGrad.className = 'bg-gradient-to-r from-rose-800 via-red-800 to-orange-800 px-5 sm:px-6 pt-5 pb-5 text-white relative overflow-hidden';
                statusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider backdrop-blur-md border shadow-md bg-rose-500/25 text-rose-100 border-rose-400/40';
                statusDot.className = 'w-2 h-2 rounded-full bg-rose-300';
                statusText.textContent = status === 'Cancellation Requested' ? 'Pending Cancel' : 'Cancelled';
                progressBar.className = 'h-full w-0 bg-transparent';
                midStatusLabel.textContent = 'Cancelled';
            } else {
                headerGrad.className = 'bg-gradient-to-r from-slate-700 via-indigo-800 to-slate-800 px-5 sm:px-6 pt-5 pb-5 text-white relative overflow-hidden';
                statusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider backdrop-blur-md border shadow-md bg-amber-500/25 text-amber-100 border-amber-400/40';
                statusDot.className = 'w-2 h-2 rounded-full bg-amber-300';
                statusText.textContent = status;
                progressBar.className = 'h-full w-0 bg-transparent';
                midStatusLabel.textContent = status;
            }

            // Distance
            const distKm = parseFloat(data.distance_km || 0).toFixed(1);
            document.getElementById('ttModalDistanceBadge').textContent = distKm + ' km';
            document.getElementById('ttModalDistanceValue').textContent = distKm + ' km';

            // Milestone 1: Time of Exit
            let exitTimeStr = '--:-- --';
            let exitDateStr = '—';
            const exitRaw = data.transit_start_time || data.created_at;
            if (exitRaw) {
                const d = new Date(exitRaw.replace(' ', 'T'));
                if (!isNaN(d)) {
                    exitTimeStr = d.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    exitDateStr = d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            }
            document.getElementById('ttModalExitTime').textContent = exitTimeStr;
            document.getElementById('ttModalExitDate').textContent = exitDateStr;
            document.getElementById('ttModalExitOrigin').textContent = data.origin || 'SSV Quarry Garage';

            // Milestone 2: Time of Arrival
            let arrivalTimeStr = '--:-- --';
            let arrivalDateStr = '—';
            const arrivalRaw = data.transit_end_time || data.estimated_arrival_time;
            if (arrivalRaw) {
                const d = new Date(arrivalRaw.replace(' ', 'T'));
                if (!isNaN(d)) {
                    arrivalTimeStr = d.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    arrivalDateStr = d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            } else if (isDelivered && exitRaw) {
                // If delivered and end time wasn't logged separately, show completion timestamp
                const d = new Date(exitRaw.replace(' ', 'T'));
                if (!isNaN(d)) {
                    arrivalTimeStr = d.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    arrivalDateStr = d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            }
            document.getElementById('ttModalArrivalTime').textContent = arrivalTimeStr;
            document.getElementById('ttModalArrivalDate').textContent = arrivalDateStr;
            document.getElementById('ttModalArrivalDest').textContent = data.destination || '—';

            // Arrival badge & dot
            const arrivalBadge = document.getElementById('ttModalArrivalBadge');
            const arrivalDot = document.getElementById('ttModalArrivalDot');
            if (isDelivered) {
                arrivalBadge.textContent = 'Delivered / Arrived';
                arrivalBadge.className = 'px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/40';
                arrivalDot.className = 'w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400';
            } else if (isInTransit) {
                arrivalBadge.textContent = 'En Route';
                arrivalBadge.className = 'px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-cyan-500/30 text-cyan-200 border border-cyan-400/40';
                arrivalDot.className = 'w-2 h-2 rounded-full bg-cyan-300 animate-ping';
            } else if (isCancelled) {
                arrivalBadge.textContent = 'Cancelled';
                arrivalBadge.className = 'px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-rose-500/30 text-rose-200 border border-rose-400/40';
                arrivalDot.className = 'w-2 h-2 rounded-full bg-rose-400';
            } else {
                arrivalBadge.textContent = 'Pending';
                arrivalBadge.className = 'px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-white/80 border border-white/25';
                arrivalDot.className = 'w-2 h-2 rounded-full bg-white/40';
            }

            // Logistics fields
            document.getElementById('ttModalOrigin').textContent = data.origin || 'SSV Quarry Garage';
            document.getElementById('ttModalDestination').textContent = data.destination || '—';

            // Landmark
            const lmWrap = document.getElementById('ttModalLandmarkWrap');
            if (data.landmark && data.landmark.trim()) {
                document.getElementById('ttModalLandmark').textContent = data.landmark.trim();
                lmWrap.classList.remove('hidden');
            } else {
                lmWrap.classList.add('hidden');
            }

            // Truck & Driver
            document.getElementById('ttModalTruckCode').textContent = data.truck_code || 'SSV Fleet';
            document.getElementById('ttModalRfidTag').textContent = 'RFID: ' + (data.rfid_tag || 'Standard');
            document.getElementById('ttModalDriverName').textContent = data.driver_name || 'SSV Driver';

            // Client
            document.getElementById('ttModalClientName').textContent = data.client_name || 'Direct Client';
            const phoneEl = document.getElementById('ttModalClientPhone');
            const phoneLink = document.getElementById('ttModalClientPhoneLink');
            if (data.contact_number && data.contact_number.trim()) {
                phoneEl.textContent = data.contact_number.trim();
                phoneLink.href = 'tel:' + data.contact_number.trim();
                phoneLink.classList.remove('hidden');
            } else {
                phoneEl.textContent = '—';
                phoneLink.removeAttribute('href');
            }

            // Cargo & Order #
            const cuM = parseFloat(data.cubic_meters || 0);
            document.getElementById('ttModalCargoVolume').textContent = (cuM > 0 ? cuM.toFixed(2) : '15.00') + ' cu.m';
            document.getElementById('ttModalOrderNumber').textContent = data.order_number ? ('Order #' + data.order_number) : 'Quarry Aggregates';

            // Trip Pay
            const payVal = parseFloat(data.pay_amount || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('ttModalPayAmount').textContent = '₱' + payVal;

            // Cancellation Section
            const cancelBox = document.getElementById('ttModalCancelBox');
            if (isCancelled && (data.cancellation_reason || data.cancellation_photo)) {
                cancelBox.classList.remove('hidden');
                document.getElementById('ttModalCancelReason').textContent = 'Reason: ' + (data.cancellation_reason || 'Trip cancelled by driver/dispatch.');
                const photoWrap = document.getElementById('ttModalCancelPhotoWrap');
                if (data.cancellation_photo) {
                    photoWrap.classList.remove('hidden');
                    const cleanPath = data.cancellation_photo.startsWith('../') ? data.cancellation_photo : ('../' + data.cancellation_photo);
                    document.getElementById('ttModalCancelPhotoLink').href = cleanPath;
                    document.getElementById('ttModalCancelPhotoImg').src = cleanPath;
                } else {
                    photoWrap.classList.add('hidden');
                }
            } else {
                cancelBox.classList.add('hidden');
            }

            // Animate Modal Open
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function closeTripTicketModal() {
            const modal = document.getElementById('tripTicketModal');
            const content = document.getElementById('tripTicketModalContent');
            if (!modal) return;
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function handleTripTicketModalOverlayClick(e) {
            if (e.target && e.target.id === 'tripTicketModal') {
                closeTripTicketModal();
            }
        }

        function copyDriverModalTicket(btn) {
            if (!currentTripModalData) return;
            const ticketNo = currentTripModalData.ticket_number || ('TRIP-' + String(currentTripModalData.trip_id).padStart(4, '0'));
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(ticketNo).then(() => {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-check text-[10px] text-emerald-300"></i> Copied!';
                    setTimeout(() => {
                        btn.innerHTML = orig;
                    }, 2000);
                });
            }
        }

        function printTripModalTicket() {
            if (currentTripModalData && currentTripModalData.dispatch_id) {
                window.open('../admin/print_ticket.php?id=' + encodeURIComponent(currentTripModalData.dispatch_id), '_blank', 'noopener,noreferrer');
            } else {
                window.print();
            }
        }

        // Global utilities fallback
        if (typeof window.printDriverDispatchTicket !== 'function') {
            window.printDriverDispatchTicket = function(id) {
                if (!id) return;
                window.open('../admin/print_ticket.php?id=' + encodeURIComponent(id), '_blank', 'noopener,noreferrer');
            };
        }
        if (typeof window.copyDriverTicketNumber !== 'function') {
            window.copyDriverTicketNumber = function(btn, text) {
                if (!text) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        const orig = btn.innerHTML;
                        btn.innerHTML = '<i class="fa-solid fa-check text-xs mr-1 text-emerald-300"></i> Copied!';
                        setTimeout(() => {
                            btn.innerHTML = orig;
                        }, 2000);
                    });
                }
            };
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('tripTicketModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeTripTicketModal();
                }
            }
        });
    </script>
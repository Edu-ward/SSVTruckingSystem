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

            fetch('otp_handler.php', {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
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

            if (!otp || !pwd) {
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
                    if (data.success) {
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

        document.addEventListener("DOMContentLoaded", function() {
            if (document.documentElement.classList.contains('dark')) {
                document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
            }
        });

        function toggleTheme(event) {
            const htmlTag = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');

            if (event) {
                const x = event.clientX;
                const y = event.clientY;
                const circle = document.createElement('div');

                circle.className = 'fixed rounded-full pointer-events-none z-[9999] transition-all duration-[700ms] ease-out';
                circle.style.left = x + 'px';
                circle.style.top = y + 'px';
                circle.style.width = '0px';
                circle.style.height = '0px';
                circle.style.transform = 'translate(-50%, -50%)';

                const isGoingDark = !htmlTag.classList.contains('dark');
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

            const isNowDark = htmlTag.classList.toggle('dark');

            if (isNowDark) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }

            localStorage.setItem('theme', isNowDark ? "dark" : "light");

            document.cookie = "theme=" + (isNowDark ? "dark" : "light") + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }
    </script>
</body>

</html>

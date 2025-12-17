<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Eduface</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; }
        .modal-fade-enter { opacity: 0; transform: scale(0.95); }
        .modal-fade-enter-active { opacity: 1; transform: scale(1); transition: opacity 0.3s, transform 0.3s; }
    </style>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-lg shadow-xl overflow-hidden m-4">
        <div class="bg-gradient-to-b from-[#3FA0FF] to-[#2F80ED] text-white py-6 text-center">
            <div class="w-14 h-14 bg-white rounded-full mx-auto flex items-center justify-center mb-3">
                <i class="fa-solid fa-lock text-2xl text-[#2F80ED]"></i>
            </div>
            <h1 class="text-xl font-bold">Reset Password</h1>
            <p class="text-xs opacity-90 mt-1">Ikuti langkah-langkah berikut untuk mengatur ulang password Anda</p>
        </div>

        <div class="p-6">
            <div id="progress" class="flex items-center justify-center gap-3 mb-6">
                <span id="dot-1" class="w-10 h-2 bg-green-400 rounded"></span>
                <span id="dot-2" class="w-10 h-2 bg-gray-300 rounded"></span>
                <span id="dot-3" class="w-10 h-2 bg-gray-300 rounded"></span>
            </div>

            <!-- Step 1: Email -->
            <div id="step-1" class="step">
                <h2 class="text-lg font-bold mb-2">Masukkan Email Anda</h2>
                <p class="text-sm text-gray-500 mb-4">Kami akan mengirimkan kode verifikasi ke email Anda untuk mereset password</p>

                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Email *</label>
                <input id="fpEmail" type="email" placeholder="nama@email.com" class="w-full border-b border-gray-300 py-2 text-sm mb-4 focus:outline-none focus:border-[#2F80ED] bg-transparent">

                <button id="fpSendBtn" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2.5 rounded-lg shadow-md transition duration-200">Kirim Kode Verifikasi</button>
                <div class="mt-4 text-center">
                    <a href="{{ route('login.perform') }}" class="text-[#2F80ED] text-sm hover:underline">← Kembali ke Login</a>
                </div>
            </div>

            <!-- Step 2: Enter Code -->
            <div id="step-2" class="step hidden text-center">
                <h2 class="text-lg font-bold mb-2">Masukkan Kode Verifikasi</h2>
                <p id="fpCodeMsg" class="text-sm text-gray-500 mb-4">Kode verifikasi telah dikirim ke <span id="fpDisplayEmail" class="font-bold"></span></p>

                <div class="flex justify-center gap-2 mb-4">
                    <input id="fpDigit1" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                    <input id="fpDigit2" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                    <input id="fpDigit3" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                    <input id="fpDigit4" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                    <input id="fpDigit5" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                    <input id="fpDigit6" maxlength="1" class="w-10 h-10 text-center border border-gray-200 rounded-md" />
                </div>

                <div class="text-sm text-gray-500 mb-2">Tidak menerima kode? <button id="fpResend" class="text-[#2F80ED] font-bold text-xs" style="background:none;border:none;padding:0;cursor:not-allowed;">Kirim Ulang <span id="fpResendTimer" class="text-[11px] text-gray-400 ml-1"></span></button></div>

                <button id="fpVerifyBtn" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2.5 rounded-lg shadow-md transition duration-200">Kirim Kode Verifikasi</button>
                <div class="mt-3 text-center"><button id="fpBackToEmail" class="text-xs text-gray-500">Kembali</button></div>
            </div>

            <!-- Step 3: New Password -->
            <div id="step-3" class="step hidden">
                <h2 class="text-lg font-bold mb-2">Buat Password Baru</h2>
                <p class="text-sm text-gray-500 mb-4">Password baru Anda harus berbeda dari password sebelumnya</p>

                <div class="bg-gray-50 border border-gray-200 rounded-md p-3 mb-3 text-sm text-gray-700">
                    <strong>Password harus mengandung:</strong>
                    <ul class="list-disc list-inside mt-2 text-xs">
                        <li>Minimal 8 karakter</li>
                        <li>Kombinasi huruf besar dan kecil</li>
                        <li>Minimal 1 angka</li>
                        <li>Minimal 1 karakter spesial (!@#$%)</li>
                    </ul>
                </div>

                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Password Baru</label>
                <input id="fpPassword" type="password" placeholder="Masukkan password baru" class="w-full border-b border-gray-300 py-2 text-sm mb-3 focus:outline-none focus:border-[#2F80ED] bg-transparent">

                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Konfirmasi Password Baru</label>
                <input id="fpPasswordConfirm" type="password" placeholder="Ulangi password baru" class="w-full border-b border-gray-300 py-2 text-sm mb-4 focus:outline-none focus:border-[#2F80ED] bg-transparent">

                <button id="fpResetBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg shadow-md transition duration-200">Reset Password</button>
            </div>

            <!-- Step 4: Success -->
            <div id="step-4" class="step hidden text-center">
                <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Password Berhasil Direset</h3>
                <p class="text-sm text-gray-600 mb-6">Password Anda telah berhasil diubah. Silakan login dengan password baru Anda</p>
                <button id="fpToLogin" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2.5 rounded-lg">Kembali ke Login</button>
            </div>

        </div>
    </div>

    <script>

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        function showToast(icon, title, colorClass) {
            Toast.fire({
                icon: icon,
                title: title,
                customClass: {
                    popup: `font-roboto flex items-center p-4 bg-white/95 backdrop-blur shadow-lg border-l-4 ${colorClass} rounded-r-lg border-gray-100`,
                    title: 'text-sm font-medium text-gray-700 ml-2',
                    timerProgressBar: 'bg-gray-300'
                }
            });
        }

        @if (session('success'))
            showToast('success', "{{ session('success') }}", 'border-emerald-500');
        @endif

        @if (session('error'))
            showToast('error', "{{ session('error') }}", 'border-red-500');
        @endif

        @if ($errors->any())
            showToast('error', "Mohon periksa kembali input form Anda.", 'border-red-500');
        @endif

        // helper
        function setStep(n) {
            document.querySelectorAll('.step').forEach((el,i)=>el.classList.add('hidden'));
            document.getElementById('step-' + n).classList.remove('hidden');
            document.getElementById('dot-1').classList.toggle('bg-green-400', n>1);
            document.getElementById('dot-2').classList.toggle('bg-green-400', n>2);
            document.getElementById('dot-1').classList.toggle('bg-gray-300', !(n>1));
            document.getElementById('dot-2').classList.toggle('bg-gray-300', !(n>2));
            document.getElementById('dot-3').classList.toggle('bg-green-400', n>3);
            document.getElementById('dot-3').classList.toggle('bg-gray-300', !(n>3));
        }

        // simple email masking
        function sensorEmail(email) {
            if (!email || !email.includes('@')) return email;
            const [name, domain] = email.split('@');
            const visibleLength = name.length > 2 ? 3 : 1; 
            const visibleName = name.substring(0, visibleLength);
            return `${visibleName}****@${domain}`;
        }

        // resend timer
        let fpResendSeconds = 60; // default
        let fpResendInterval = null;
        function formatMmSs(seconds) {
            const mm = String(Math.floor(seconds/60)).padStart(2,'0');
            const ss = String(seconds%60).padStart(2,'0');
            return `${mm}:${ss}`;
        }
        function startFpResendTimer() {
            const btn = document.getElementById('fpResend');
            const t = document.getElementById('fpResendTimer');
            let rem = fpResendSeconds;
            btn.style.cursor = 'not-allowed'; btn.setAttribute('aria-disabled','true');
            t.innerText = formatMmSs(rem);
            fpResendInterval = setInterval(()=>{
                rem -= 1; t.innerText = ' - ' + formatMmSs(rem);
                if (rem <= 0) resetFpResendTimer();
            }, 1000);
        }
        function resetFpResendTimer() {
            const btn = document.getElementById('fpResend');
            const t = document.getElementById('fpResendTimer');
            if (fpResendInterval) clearInterval(fpResendInterval);
            btn.style.cursor = 'pointer'; btn.removeAttribute('aria-disabled');
            t.innerText = '';
        }

        // Step bindings
        document.getElementById('fpSendBtn').addEventListener('click', async ()=>{
            const email = document.getElementById('fpEmail').value;
            if (!email) return showToast('error', 'Masukkan email valid', 'border-red-500');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            document.getElementById('fpSendBtn').disabled = true;
            try {
                const res = await fetch("{{ route('password.sendCode') }}", {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    document.getElementById('fpDisplayEmail').innerText = sensorEmail(email);
                    setStep(2);
                    startFpResendTimer();
                } else {
                    showToast('error', data.message || 'Gagal mengirim kode', 'border-red-500');
                }
            } catch (err) { showToast('error', 'Kesalahan jaringan', 'border-red-500'); }
            finally { document.getElementById('fpSendBtn').disabled = false; }
        });

        document.getElementById('fpVerifyBtn').addEventListener('click', async ()=>{
            const code = [1,2,3,4,5,6].map(i=>document.getElementById('fpDigit'+i).value).join('');
            if (code.length < 6) return showToast('error', 'Masukkan 6 digit kode', 'border-red-500');
            const email = document.getElementById('fpEmail').value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            document.getElementById('fpVerifyBtn').disabled = true;
            try {
                const res = await fetch("{{ route('password.verifyCode') }}", {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, code })
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    setStep(3);
                } else {
                    showToast('error', data.message || 'Kode salah', 'border-red-500');
                }
            } catch (err) { showToast('error', 'Kesalahan server', 'border-red-500'); }
            finally { document.getElementById('fpVerifyBtn').disabled = false; }
        });

        document.getElementById('fpResetBtn').addEventListener('click', async ()=>{
            const pass = document.getElementById('fpPassword').value;
            const pass2 = document.getElementById('fpPasswordConfirm').value;
            const email = document.getElementById('fpEmail').value;
            if (pass.length < 8) return showToast('error', 'Password min 8 karakter', 'border-red-500');
            if (pass !== pass2) return showToast('error', 'Password tidak cocok', 'border-red-500');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            document.getElementById('fpResetBtn').disabled = true;
            try {
                const res = await fetch("{{ route('password.reset') }}", {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password: pass })
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    setStep(4);
                } else {
                    showToast('error', data.message || 'Gagal mereset password', 'border-red-500');
                }
            } catch (err) { showToast('error', 'Kesalahan server', 'border-red-500'); }
            finally { document.getElementById('fpResetBtn').disabled = false; }
        });

        document.getElementById('fpBackToEmail').addEventListener('click', ()=>setStep(1));
        document.getElementById('fpToLogin').addEventListener('click', ()=>window.location.href = "{{ route('login.perform') }}");

        document.getElementById('fpResend').addEventListener('click', async ()=>{
            const btn = document.getElementById('fpResend'); if (btn.getAttribute('aria-disabled')) return;
            const email = document.getElementById('fpEmail').value; if (!email) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch("{{ route('password.sendCode') }}", {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') { startFpResendTimer(); showToast('success', 'Kode OTP berhasil dikirim ulang.', 'border-green-500'); }
                else showToast('error', data.message || 'Gagal mengirim ulang', 'border-red-500');
            } catch (err) { showToast('error', 'Kesalahan jaringan', 'border-red-500'); }
        });

        // optional: auto-focus digits
        document.querySelectorAll('[id^=fpDigit]').forEach((el,i,arr)=>{
            el.addEventListener('input', ()=>{ if (el.value && arr[i+1]) arr[i+1].focus(); });
            el.addEventListener('keydown', (e)=>{ if (e.key === 'Backspace' && !el.value && arr[i-1]) arr[i-1].focus(); });
        });

        // init
        setStep(1);
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun - Eduface</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; }
        ::placeholder { color: #9CA3AF; font-size: 0.8rem; }
        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; background: transparent; }
        input[type="file"]::file-selector-button { display: none; }
        
        .modal-fade-enter { opacity: 0; transform: scale(0.95); }
        .modal-fade-enter-active { opacity: 1; transform: scale(1); transition: opacity 0.3s, transform 0.3s; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center overflow-hidden">

    <div class="w-full max-w-4xl bg-white rounded-lg shadow-xl overflow-hidden flex flex-col md:flex-row m-4 h-auto max-h-[95vh] relative z-10">
        
        <div class="hidden md:flex flex-col justify-center items-center bg-[#2F80ED] w-1/3 p-8 text-center text-white">
            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg mb-4 relative">
                <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-16 h-16 object-contain">
            </div>
            <h1 class="text-2xl font-bold mb-2">Eduface</h1>
            <p class="text-sm opacity-90">Just Face It</p>
        </div>

        <div class="w-full md:w-2/3 p-6 md:p-8 flex flex-col justify-center relative">
            
            <div class="md:hidden flex items-center gap-3 mb-4 pb-4 border-b">
                <div class="w-10 h-10 bg-[#2F80ED] rounded-full flex items-center justify-center">
                    <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-6 h-6 object-contain brightness-0 invert">
                </div>
                <h1 class="text-[#2F80ED] font-bold text-lg">Eduface Register</h1>
            </div>

            <div class="mb-5">
                <h2 class="text-xl font-bold text-gray-900">Buat Akun Baru</h2>
                <p class="text-gray-400 text-xs">Lengkapi data di bawah ini</p>
            </div>

            <form id="regForm" enctype="multipart/form-data" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    
                    <div class="space-y-3">
                        <h3 class="text-[#2F80ED] text-xs font-bold uppercase tracking-wider border-b pb-1 mb-2">Informasi Akun</h3>
                        
                        <div>
                            <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Username (Opsional)</label>
                            <input type="text" name="username" placeholder="Kosongkan untuk otomatis" autocomplete="off"
                                class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Email (Wajib Aktif)</label>
                            <input type="email" id="emailInput" name="email" required placeholder="email@sekolah.sch.id" autocomplete="off"
                                class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Password</label>
                                <input type="password" id="password" name="password" required placeholder="***" autocomplete="new-password"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Konfirmasi</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="***" autocomplete="new-password"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                            </div>
                        </div>
                        <p id="pass-error" class="text-red-500 text-[10px] hidden text-right font-medium">Password tidak cocok</p>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-[#2F80ED] text-xs font-bold uppercase tracking-wider border-b pb-1 mb-2">Data Pribadi</h3>
                        
                        <div>
                            <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Sesuai Identitas" autocomplete="off"
                                class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Peran</label>
                                <select name="role" id="roleSelect" onchange="updateIdLabel()" required autocomplete="off"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent cursor-pointer">
                                    <option value="" disabled selected>Pilih</option>
                                    <option value="student">Siswa</option>
                                    <option value="teacher">Guru</option>
                                    <option value="parent">Wali</option>
                                </select>
                            </div>
                            <div>
                                <label id="idLabel" class="block text-gray-800 text-[11px] font-bold mb-0.5">Nomor ID</label>
                                <input type="number" name="id_number" id="idInput" placeholder="NIP/NISN" autocomplete="off"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Gender</label>
                                <select name="gender" required autocomplete="off"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent cursor-pointer">
                                    <option value="" disabled selected>Pilih</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Tgl Lahir</label>
                                <input type="date" name="dob" required autocomplete="off"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                            </div>
                        </div>

                        <div id="student-fields" class="hidden grid-cols-2 gap-3 mt-3 animate-fade-in">
                            <div class="relative">
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Kelas</label>
                                <select name="class_id" id="classSelect"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent cursor-pointer">
                                    <option value="" disabled selected>Pilih Kelas</option>
                                    @foreach($classroom as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="relative">
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Orang Tua / Wali</label>
                                <select name="parent_id" id="parentSelect"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent cursor-pointer">
                                    <option value="" selected>Tidak Ada / Pilih Nanti</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->full_name }} ({{ $parent->phone ?? $parent->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">No HP</label>
                                <input type="number" name="phone" placeholder="08..." autocomplete="off"
                                    class="w-full border-b border-gray-300 py-1 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] bg-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Foto</label>
                                <input type="file" name="photo" accept="image/*"
                                    class="w-full text-xs text-gray-500 py-1 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-2">
                    <button type="button" id="btn-submit" onclick="initiateRegistration()"
                        class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2.5 text-sm rounded-lg shadow-md transition duration-200">
                        Daftar
                    </button>
                    
                    <div class="text-center text-xs text-gray-500 mt-3">
                        Sudah punya akun? <a href="{{ route('login.perform') }}" class="text-[#2F80ED] font-bold hover:underline">Masuk disini</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="otpModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-80 text-center shadow-2xl transform transition-all scale-95 opacity-0" id="otpContent">
            <div class="w-12 h-12 bg-blue-100 text-[#2F80ED] rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-envelope-open-text text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Verifikasi Email</h3>
            <p class="text-xs text-gray-500 mb-4">Kode OTP telah dikirim ke <span id="displayEmail" class="font-bold"></span></p>
            
            <input type="text" id="otpCode" maxlength="6" class="w-full text-center text-2xl tracking-widest border border-gray-300 rounded-md py-2 mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono" placeholder="123456">
            
            <button onclick="verifyAndSubmit()" id="btn-verify" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2 rounded-md text-sm transition">
                Verifikasi & Daftar
            </button>
            <div class="mt-2 text-center text-sm text-gray-500">
                <div id="resendQuestion" class="text-xs text-gray-500 mb-1">Belum menerima kode?</div>
                    <button id="resendLabel" onclick="resendOtp()" class="text-gray-400 text-xs font-bold" style="background:none;border:none;padding:0;cursor:not-allowed;">
                    Kirim Ulang<span id="resendTimer" class="text-[11px] text-gray-400 ml-1"></span>
                </button>
            </div>
            <div class="mt-3 text-center">
                <button onclick="closeOtp()" class="text-xs text-gray-400 hover:text-gray-600">Batal</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 w-96 text-center shadow-2xl">
            <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-check text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Registrasi Berhasil!</h3>
            <p class="text-sm text-gray-600 mb-4">Akun Anda telah dibuat. Silakan gunakan username di bawah ini untuk login:</p>
            
            <div class="bg-gray-100 border border-gray-200 rounded-md p-3 mb-6">
                <span class="text-xs text-gray-500 uppercase block mb-1">Username Anda</span>
                <span id="finalUsername" class="text-2xl font-mono font-bold text-[#2F80ED] tracking-wide select-all"></span>
            </div>

            <button onclick="redirectToLogin()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-lg transition">
                Lanjut ke Login <i class="fa-solid fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <script>
        function updateIdLabel() {
            const role = document.getElementById('roleSelect').value;
            const label = document.getElementById('idLabel');
            const input = document.getElementById('idInput');

            const studentFields = document.getElementById('student-fields');
            const classInput = document.getElementById('classSelect');

            studentFields.classList.add('hidden');
            studentFields.classList.remove('grid');
            classInput.required = false;

            if (role === 'student') {
                label.innerText = "NISN";
                input.placeholder = "Isi NISN";
                input.required = true;

                studentFields.classList.remove('hidden');
                studentFields.classList.add('grid');
                classInput.required = true;
            } else if (role === 'teacher') {
                label.innerText = "NIP"; input.placeholder = "Isi NIP"; input.required = true;
            } else {
                label.innerText = "NIK/ID"; input.placeholder = "Opsional"; input.required = false;
            }
        }

        function sensorEmail(email) {
            if (!email || !email.includes('@')) return email;
            
            const [name, domain] = email.split('@');
            
            const visibleLength = name.length > 2 ? 3 : 1; 
            const visibleName = name.substring(0, visibleLength);
            
            return `${visibleName}****@${domain}`;
        }
        async function initiateRegistration() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('password_confirmation').value;
            
            if (p1.length < 8) { alert("Password min 8 karakter"); return; }
            if (p1 !== p2) { document.getElementById('pass-error').classList.remove('hidden'); return; }
            
            const btn = document.getElementById('btn-submit');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim OTP...';

            const form = document.getElementById('regForm');
            const formData = new FormData(form);

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch("{{ route('register.sendOtp') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    const emailAsli = document.getElementById('emailInput').value;
                    document.getElementById('displayEmail').innerText = sensorEmail(emailAsli);
                    const modal = document.getElementById('otpModal');
                    const content = document.getElementById('otpContent');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    setTimeout(() => {
                        content.classList.remove('scale-95', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                    }, 10);
                    startResendTimer();
                } else {
                    alert(data.message || "Gagal memvalidasi data.");
                }
            } catch (err) {
                alert("Kesalahan jaringan.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function verifyAndSubmit() {
            const otp = document.getElementById('otpCode').value;
            if(otp.length < 6) { alert("Masukkan 6 digit kode"); return; }

            const btn = document.getElementById('btn-verify');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const form = document.getElementById('regForm');
            const formData = new FormData(form);
            formData.append('otp_code', otp);

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch("{{ route('register.verify') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    closeOtp();
                    document.getElementById('finalUsername').innerText = data.username;
                    const successModal = document.getElementById('successModal');
                    successModal.classList.remove('hidden');
                    successModal.classList.add('flex');
                    window.redirectUrl = data.redirect;
                    resetResendTimer();
                } else {
                    alert(data.message || "Kode OTP salah.");
                }
            } catch (err) {
                alert("Kesalahan server.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Resend timer logic (shows under verify button in mm:ss)
        let resendSeconds = {{ config('otp.resend_ttl', 60) }};
        let resendInterval = null;

        function formatMmSs(seconds) {
            const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
            const ss = String(seconds % 60).padStart(2, '0');
            return `${mm}:${ss}`;
        }

        function startResendTimer() {
            const label = document.getElementById('resendLabel');
            const timer = document.getElementById('resendTimer');
            let remaining = resendSeconds;
            // set disabled visual state
            label.classList.remove('text-[#2F80ED]');
            label.classList.add('text-gray-400');
            label.setAttribute('aria-disabled', 'true');
            label.style.cursor = 'not-allowed';
            timer.innerText = formatMmSs(remaining);

            resendInterval = setInterval(() => {
                remaining -= 1;
                timer.innerText = ' - ' + formatMmSs(remaining);
                if (remaining <= 0) {
                    resetResendTimer();
                }
            }, 1000);
        }

        function resetResendTimer() {
            const label = document.getElementById('resendLabel');
            const timer = document.getElementById('resendTimer');
            if (resendInterval) clearInterval(resendInterval);
            label.classList.remove('text-gray-400');
            label.classList.add('text-[#2F80ED]');
            label.removeAttribute('aria-disabled');
            label.style.cursor = 'pointer';
            timer.innerText = '';
        }

        async function resendOtp() {
            const label = document.getElementById('resendLabel');
            if (label.classList.contains('pointer-events-none')) return;

            const form = document.getElementById('regForm');
            const formData = new FormData(form);
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch("{{ route('register.sendOtp') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    startResendTimer();
                    alert('Kode OTP berhasil dikirim ulang.');
                } else {
                    alert(data.message || 'Gagal mengirim ulang OTP.');
                }
            } catch (err) {
                alert('Kesalahan jaringan.');
            }
        }

        function closeOtp() {
            const modal = document.getElementById('otpModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function redirectToLogin() {
            window.location.href = window.redirectUrl || "{{ route('login.perform') }}";
        }
    </script>
</body>
</html> 
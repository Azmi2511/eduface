<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Eduface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; }
        
        .step-content { transition: all 0.3s ease-in-out; }
        .hidden-step { display: none; opacity: 0; }
        .active-step { display: block; opacity: 1; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-4">

    <div class="w-full max-w-[340px] mx-4 bg-white rounded-lg shadow-xl overflow-hidden relative">
        
        <div class="bg-[#2F80ED] p-5 text-center pb-9">
            <div class="w-14 h-14 bg-white rounded-full mx-auto flex items-center justify-center shadow-md mb-2">
                <img src="{{ asset('assets/logo.png') }}" onerror="this.src='https://via.placeholder.com/64?text=EF'" alt="logo" class="w-8 h-8 object-contain">
            </div>
            <h1 class="text-white text-base font-bold">Eduface Registration</h1>
            <p class="text-blue-100 text-[10px] mt-0.5">Buat akun untuk akses akademik</p>
        </div>

        <div class="px-5 pb-5 pt-4 bg-white relative -mt-5 rounded-t-3xl">
            
            <div id="alert-box" class="hidden mb-3 p-2 text-[10px] rounded text-center font-medium"></div>

            <div id="step-1" class="step-content active-step">
                <div class="text-center mb-4">
                    <h2 class="text-sm font-bold text-gray-800">Siapa Anda?</h2>
                    <p class="text-gray-400 text-[10px]">Pilih peran untuk melanjutkan</p>
                </div>

                <div class="flex justify-between gap-2 mb-5">
                    <button onclick="selectRole('GURU')" id="btn-GURU" class="role-btn flex-1 py-1.5 text-[10px] font-bold border rounded transition-colors border-gray-200 text-gray-500 hover:border-[#2F80ED] hover:text-[#2F80ED]">
                        <i class="fas fa-chalkboard-teacher mb-0.5 block text-sm"></i> GURU
                    </button>
                    <button onclick="selectRole('SISWA')" id="btn-SISWA" class="role-btn flex-1 py-1.5 text-[10px] font-bold border rounded transition-colors bg-[#2F80ED] text-white border-[#2F80ED] shadow-md">
                        <i class="fas fa-user-graduate mb-0.5 block text-sm"></i> SISWA
                    </button>
                    <button onclick="selectRole('ORTU')" id="btn-ORTU" class="role-btn flex-1 py-1.5 text-[10px] font-bold border rounded transition-colors border-gray-200 text-gray-500 hover:border-[#2F80ED] hover:text-[#2F80ED]">
                        <i class="fas fa-user-friends mb-0.5 block text-sm"></i> ORTU
                    </button>
                </div>

                <form onsubmit="handleValidate(event)">
                    <input type="hidden" id="selected-role" value="SISWA">
                    
                    <div class="mb-3">
                        <label id="label-id" class="block text-gray-800 text-[11px] font-bold mb-0.5">Masukkan NISN</label>
                        <input type="text" id="id_number" required
                            class="w-full border-b border-gray-300 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent placeholder-gray-400"
                            placeholder="Nomor Identitas">
                    </div>

                    <div class="mb-5">
                        <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Tanggal Lahir</label>
                        <input type="date" id="dob" required
                            class="w-full border-b border-gray-300 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent">
                        <p id="helper-dob" class="text-[9px] text-[#2F80ED] mt-0.5 italic">Masukkan Tanggal Lahir Anda</p>
                    </div>

                    <button type="submit" id="btn-validate"
                        class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2 text-xs rounded-md shadow-md transition duration-200">
                        Lanjut <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </form>
            </div>

            <div id="step-2" class="step-content hidden-step text-center pt-1">
                <div class="mb-4">
                    <div class="w-10 h-10 bg-blue-50 text-[#2F80ED] rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-shield-alt text-lg"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800">Verifikasi OTP</h2>
                    <p class="text-gray-400 text-[10px] mt-0.5">
                        Kode dikirim ke <span id="masked-phone" class="font-bold text-gray-600"></span>
                    </p>
                </div>

                <div class="mb-5">
                    <input type="text" id="otp-input" maxlength="4" placeholder="0 - 0 - 0 - 0"
                        class="w-40 text-center text-xl font-bold tracking-[0.4em] border-b-2 border-gray-300 focus:border-[#2F80ED] focus:outline-none py-1.5 text-gray-700 placeholder-gray-200">
                </div>

                <button onclick="handleCheckOtp()" 
                    class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2 text-xs rounded-md shadow-md transition duration-200 mb-2">
                    Verifikasi
                </button>

                <div class="flex justify-between items-center mt-3 px-2">
                    <button onclick="resetStep()" class="text-gray-400 hover:text-gray-600 text-[10px]">Ubah Data</button>
                    <span class="text-gray-300 text-[10px]">|</span>
                    <button class="text-gray-400 hover:text-[#2F80ED] text-[10px]">Kirim Ulang</button>
                </div>
            </div>

            <div id="step-3" class="step-content hidden-step pt-1">
                <div class="text-center mb-4">
                    <h2 class="text-sm font-bold text-gray-800">Amankan Akun</h2>
                    <p class="text-gray-400 text-[10px]">Buat password login</p>
                </div>

                <div class="space-y-3 mb-5">
                    <div>
                        <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Password Baru</label>
                        <input type="password" id="password"
                            class="w-full border-b border-gray-300 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent placeholder-gray-400"
                            placeholder="Min 8 karakter">
                    </div>
                    <div>
                        <label class="block text-gray-800 text-[11px] font-bold mb-0.5">Konfirmasi Password</label>
                        <input type="password" id="password_confirm"
                            class="w-full border-b border-gray-300 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent placeholder-gray-400"
                            placeholder="Ulangi password">
                        <p id="pass-error" class="text-[10px] text-red-500 mt-0.5 hidden">Password tidak cocok!</p>
                    </div>
                </div>

                <button onclick="handleFinalSubmit()" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 text-xs rounded-md shadow-md transition duration-200">
                    Selesai & Masuk
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 text-center text-[10px] text-gray-500">
                Sudah punya akun? <a href="{{ route('login.perform') }}" class="text-[#2F80ED] font-bold hover:underline">Masuk Disini</a>
            </div>
        </div>
    </div>

    <script>
        let tempToken = ''; 
        let currentRole = 'SISWA';

        function selectRole(role) {
            currentRole = role;
            document.getElementById('selected-role').value = role;

            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.className = "role-btn flex-1 py-1.5 text-[10px] font-bold border rounded transition-colors border-gray-200 text-gray-500 hover:border-[#2F80ED] hover:text-[#2F80ED]";
            });

            const activeBtn = document.getElementById(`btn-${role}`);
            activeBtn.className = "role-btn flex-1 py-1.5 text-[10px] font-bold border rounded transition-colors bg-[#2F80ED] text-white border-[#2F80ED] shadow-md";

            const labelId = document.getElementById('label-id');
            const helperDob = document.getElementById('helper-dob');

            if (role === 'GURU') {
                labelId.innerText = "Masukkan NIP";
                helperDob.innerText = "Masukkan Tanggal Lahir Anda";
            } else if (role === 'SISWA') {
                labelId.innerText = "Masukkan NISN";
                helperDob.innerText = "Masukkan Tanggal Lahir Anda";
            } else {
                labelId.innerText = "Masukkan NISN Anak";
                helperDob.innerText = "Info: Masukkan Tanggal Lahir Anak (Siswa)";
            }
        }

        function showAlert(msg, type = 'error') {
            const box = document.getElementById('alert-box');
            box.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
            
            if (type === 'error') {
                box.classList.add('bg-red-100', 'text-red-700');
            } else {
                box.classList.add('bg-green-100', 'text-green-700');
            }
            
            box.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} mr-1"></i> ${msg}`;
            box.classList.remove('hidden');
        }

        function switchStep(stepNumber) {
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.remove('active-step');
                el.classList.add('hidden-step');
            });
            document.getElementById(`step-${stepNumber}`).classList.remove('hidden-step');
            document.getElementById(`step-${stepNumber}`).classList.add('active-step');
            document.getElementById('alert-box').classList.add('hidden');
        }

        function resetStep() {
            switchStep(1);
            tempToken = '';
        }

        async function postApi(url, data) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });
                return { ok: res.ok, status: res.status, json: await res.json() };
            } catch (e) {
                return { ok: false, json: { message: "Gagal menghubungi server." } };
            }
        }

        async function handleValidate(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-validate');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

            const payload = {
                role: currentRole,
                id_number: document.getElementById('id_number').value,
                dob: document.getElementById('dob').value
            };

            const response = await postApi('/auth/validate', payload);

            if (response.ok) {
                tempToken = response.json.temp_token;
                document.getElementById('masked-phone').innerText = response.json.masked_phone;
                console.info("DEBUG OTP:", response.json.debug_otp);
                switchStep(2);
            } else {
                showAlert(response.json.message, 'error');
            }
            
            btn.disabled = false;
            btn.innerHTML = originalText;
        }

        async function handleCheckOtp() {
            const otpVal = document.getElementById('otp-input').value;
            if (otpVal.length < 4) return showAlert("Masukkan 4 digit OTP");

            const response = await postApi('/auth/check-otp', {
                temp_token: tempToken,
                otp: otpVal
            });

            if (response.ok) {
                switchStep(3);
            } else {
                showAlert(response.json.message || "OTP Salah");
            }
        }

        async function handleFinalSubmit() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('password_confirm').value;

            if (p1.length < 8) return showAlert("Min 8 karakter");
            if (p1 !== p2) {
                document.getElementById('pass-error').classList.remove('hidden');
                return;
            } else {
                document.getElementById('pass-error').classList.add('hidden');
            }

            const payload = {
                temp_token: tempToken,
                otp: document.getElementById('otp-input').value,
                password: p1
            };

            const response = await postApi('/auth/final', payload);

            if (response.ok) {
                showAlert("Berhasil! Mengalihkan...", 'success');
                setTimeout(() => {
                    window.location.href = response.json.redirect;
                }, 1500);
            } else {
                showAlert(response.json.message || "Gagal membuat akun.");
            }
        }
    </script>
</body>
</html>
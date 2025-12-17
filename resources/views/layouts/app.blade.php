<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Eduface: Just Face It')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/logo.png') }}">

    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap'); */

        body {
           font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        /* Transisi Sidebar Halus */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }

        /* Scrollbar custom */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    @stack('head')
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden bg-gray-100">

        <div id="sidebar-overlay"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none md:hidden">
        </div>

        @include('partials.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden relative">
            @include('partials.header')

            <main class="flex-1 overflow-y-auto bg-[#F3F6FD]">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('script/script.js') }}"></script>
    <script>
        // --- KONFIGURASI STYLE GLOBAL (Diselaraskan dengan Eduface) ---
        const swalBaseConfig = {
            buttonsStyling: false,
            // Backdrop lebih gelap sedikit agar fokus pengguna terarah
            backdrop: `rgba(0,0,0, 0.5) left top no-repeat`, 
            // Animasi diperhalus
            showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },
            customClass: {
                // PENTING: Menambahkan 'font-roboto' dan menyesuaikan rounded
                popup: 'font-roboto rounded-2xl p-6 bg-white shadow-xl border border-gray-100',
                title: 'text-gray-800 text-xl font-bold mt-2',
                htmlContainer: 'text-gray-500 text-sm leading-relaxed mt-2',
                // Tombol Utama: Menggunakan Blue-600 agar senada dengan tema Eduface
                confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-md hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 mx-2',
                // Tombol Batal: Lebih clean
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2.5 px-6 rounded-lg transition-all transform hover:-translate-y-0.5 mx-2',
                // Tombol Bahaya (Delete/Logout): Merah
                denyButton: 'bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-6 rounded-lg shadow-md hover:shadow-red-500/30 transition-all transform hover:-translate-y-0.5 mx-2',
            }
        };

        // --- 1. TOAST NOTIFICATION (Minimalis Modern) ---
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
                icon: icon, // success, error, warning, info
                title: title,
                customClass: {
                    // Style Glassmorphism tipis + Border kiri berwarna
                    popup: `font-roboto flex items-center p-4 bg-white/95 backdrop-blur shadow-lg border-l-4 ${colorClass} rounded-r-lg border-gray-100`,
                    title: 'text-sm font-medium text-gray-700 ml-2',
                    timerProgressBar: 'bg-gray-300' // Progress bar neutral
                }
            });
        }

        // Menangkap Session Laravel
        @if (session('success'))
            showToast('success', "{{ session('success') }}", 'border-emerald-500');
        @endif

        @if (session('error'))
            showToast('error', "{{ session('error') }}", 'border-red-500');
        @endif

        @if ($errors->any())
            showToast('error', "Mohon periksa kembali input form Anda.", 'border-red-500');
        @endif


        // --- 2. CONFIRM ACTION (Hapus/Simpan) ---
        function confirmAction(event, formId, title, text, confirmBtnText = 'Ya, Lanjutkan', confirmBtnColor = 'primary') {
            event.preventDefault();

            // Logika warna tombol
            let btnClass = swalBaseConfig.customClass.confirmButton;
            if(confirmBtnColor === 'danger') {
                btnClass = swalBaseConfig.customClass.denyButton;
            }

            Swal.fire({
                ...swalBaseConfig,
                title: title,
                text: text, // Gunakan text biasa agar font roboto dari parent inheritance jalan
                icon: 'warning',
                iconColor: confirmBtnColor === 'danger' ? '#EF4444' : '#3B82F6', // Ikon merah jika danger, biru jika normal
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Batal',
                customClass: {
                    ...swalBaseConfig.customClass,
                    confirmButton: btnClass
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoadingState(formId);
                }
            });
        }

        // --- 3. CONFIRM LOGOUT (Dengan FontAwesome) ---
        function confirmLogout(event) {
            event.preventDefault();

            Swal.fire({
                ...swalBaseConfig,
                title: 'Selesai untuk hari ini?',
                text: 'Anda akan keluar dari sesi aplikasi Eduface.',
                // Menggunakan FontAwesome custom icon biar lebih tajam
                iconHtml: '<i class="fa-solid fa-right-from-bracket text-3xl"></i>',
                iconColor: '#EF4444', // Merah
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Logout',
                cancelButtonText: 'Batal',
                customClass: {
                    ...swalBaseConfig.customClass,
                    // Override tombol confirm jadi merah
                    confirmButton: swalBaseConfig.customClass.denyButton 
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoadingState('logout-form');
                }
            });
        }

        // --- HELPER: Loading State ---
        function showLoadingState(formId) {
            Swal.fire({
                title: 'Memproses...',
                html: '<span class="text-sm text-gray-500">Mohon tunggu sebentar</span>',
                allowOutsideClick: false,
                showConfirmButton: false,
                width: 300,
                padding: '2rem',
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    popup: 'font-roboto rounded-2xl bg-white shadow-2xl border border-gray-100',
                    title: 'text-gray-700 font-bold text-lg mt-4'
                }
            });
            
            // Submit form
            setTimeout(() => {
                document.getElementById(formId).submit();
            }, 500);
        }
    </script>
    @stack('scripts')
</body>

</html>
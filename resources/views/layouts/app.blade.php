<!doctype html>
<html lang="{{ Auth::check() ? Auth::user()->getPref('locale', 'id') : 'id' }}" class="{{ (Auth::check() ? Auth::user()->getPref('theme', 'light') : 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Eduface: Just Face It')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/logo.png') }}">

    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap'); */

        /* Tema Gelap & Terang Utama */

        /* Dark Mode support overrides */
        html.dark, html.dark body {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }
        
        /* Layout Backgrounds */
        html.dark .bg-\[\#F3F6FD\], html.dark .bg-\[\#F8FAFC\], html.dark main, html.dark .bg-gray-100 {
            background-color: #0f172a !important;
        }
        
        /* Tables & Gray Backgrounds */
        html.dark .bg-gray-50, html.dark .bg-slate-50, html.dark .hover\:bg-gray-50:hover,
        html.dark .bg-gray-50\/50, html.dark .bg-gray-50\/30, html.dark .hover\:bg-gray-100:hover,
        html.dark .hover\:bg-indigo-50\/30:hover, html.dark .hover\:bg-blue-50\/30:hover,
        html.dark .hover\:bg-slate-50:hover {
            background-color: #111827 !important;
            color: #cbd5e1 !important;
        }

        /* Unread Notification */
        html.dark .bg-blue-50\/20 {
            background-color: rgba(59, 130, 246, 0.15) !important;
        }
        
        /* Cards and White Backgrounds */
        html.dark .bg-white {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }
        
        /* Sidebar Redesign for Dark Mode */
        html.dark #sidebar {
            background-color: #1e293b !important; /* Make sidebar dark navy instead of bright blue */
            border-color: #334155 !important;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3) !important;
        }
        
        /* Active menu item inside Sidebar */
        html.dark #sidebar .menu-item.bg-white {
            background-color: #3b82f6 !important; /* Vibrant blue for active item */
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4) !important;
            border: none !important;
        }
        html.dark #sidebar .menu-item.bg-white i, html.dark #sidebar .menu-item.bg-white span {
            color: #ffffff !important;
        }
        html.dark #sidebar .menu-item:not(.bg-white):hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        
        /* Header Fixes & Dropdowns */
        html.dark header, html.dark header.bg-white {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2) !important;
        }
        html.dark .hover\:bg-blue-50:hover, html.dark .hover\:bg-blue-100:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        
        /* Text Colors */
        html.dark .text-gray-900, html.dark .text-slate-800, html.dark .text-gray-800, html.dark .text-gray-700 {
            color: #f1f5f9 !important;
        }
        html.dark .text-gray-500, html.dark .text-slate-500, html.dark .text-gray-600, html.dark .text-gray-400, html.dark .text-slate-600 {
            color: #94a3b8 !important;
        }
        
        /* Borders */
        html.dark .border-gray-200, html.dark .border-slate-100, html.dark .border-gray-100, html.dark .border-slate-50 {
            border-color: #334155 !important;
        }
        
        /* Forms */
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        html.dark input::placeholder {
            color: #475569 !important;
        }
        html.dark .text-slate-700 {
            color: #cbd5e1 !important;
        }

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

<body class="bg-gray-50 text-gray-800" x-data="{ sidebarMinimized: localStorage.getItem('sidebarMinimized') === 'true' }" x-init="$watch('sidebarMinimized', val => localStorage.setItem('sidebarMinimized', val))" :class="sidebarMinimized ? 'sidebar-minimized' : ''">

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
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        }
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

        @if (session('info'))
            showToast('info', "{{ session('info') }}", 'border-blue-500');
        @endif

        @if ($errors->any())
            showToast('error', "Mohon periksa kembali input form Anda.", 'border-red-500');
        @endif


            // --- 2. CONFIRM ACTION (Hapus/Simpan) ---
            function confirmAction(event, formId, title, text, confirmBtnText = 'Ya, Lanjutkan', confirmBtnColor = 'primary') {
                event.preventDefault();

                // Logika warna tombol
                let btnClass = swalBaseConfig.customClass.confirmButton;
                if (confirmBtnColor === 'danger') {
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
                confirmButtonText: 'Keluar',
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
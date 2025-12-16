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
    <link rel="shortcut icon" href="{{ asset('assets/logo.png') }}">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; }
        
        /* Transisi Sidebar Halus */
        #sidebar { transition: transform 0.3s ease-in-out; }
        
        /* Scrollbar custom */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

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
<body class="bg-gray-50 font-roboto text-gray-800">
    
    <div class="flex h-screen overflow-hidden bg-gray-100">
        
        <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none md:hidden"></div>

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
        const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'flex items-center w-full max-w-sm p-4 bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-2xl',
            title: 'text-sm font-semibold text-gray-800',
            timerProgressBar: 'bg-blue-500'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if (session('success'))
        Toast.fire({
            icon: 'success',
            iconColor: '#10B981',
            title: '{{ session('success') }}'
        });
    @endif

    @if (session('error'))
        Toast.fire({
            icon: 'error',
            iconColor: '#EF4444',
            title: '{{ session('error') }}'
        });
    @endif

    @if ($errors->any())
        Toast.fire({
            icon: 'error',
            title: 'Terdapat kesalahan pada input Anda. Silakan cek kembali.'
        });
    @endif

    function confirmAction(event, formId, title, text) {
        event.preventDefault();

        Swal.fire({
            title: `<span class="text-gray-800 font-bold text-2xl">${title}</span>`,
            html: `<p class="text-gray-500 text-sm mt-2">${text}</p>`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            width: '400px',
            buttonsStyling: false,
            backdrop: `rgba(0,0,0, 0.5) left top no-repeat`,
            customClass: {
                popup: 'rounded-[2rem] p-6 shadow-2xl bg-white/90 backdrop-blur-sm border border-gray-100',
                confirmButton: 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform transition hover:scale-105 hover:shadow-red-500/30 ml-3',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-6 rounded-xl transition hover:scale-105',
                icon: 'border-none'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            },
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    timer: 1000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                }).then(() => {
                    document.getElementById(formId).submit();
                });
            }
        });
    }

        function confirmLogout(event) {
            event.preventDefault();

            Swal.fire({
                title: '<span class="text-gray-800 font-bold text-2xl">Ingin beristirahat?</span>',
                html: '<p class="text-gray-500 text-sm mt-2">Sesi Anda akan diakhiri sekarang. Sampai jumpa lagi!</p>',
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                
                width: '400px',
                buttonsStyling: false,
                backdrop: `
                    rgba(0,0,0, 0.5)
                    left top
                    no-repeat
                `,
                customClass: {
                    popup: 'rounded-[2rem] p-6 shadow-2xl bg-white/90 backdrop-blur-sm border border-gray-100',
                    title: 'font-sans',
                    htmlContainer: 'font-sans',
                    confirmButton: 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform transition hover:scale-105 hover:shadow-red-500/30 ml-3',
                    cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-6 rounded-xl transition hover:scale-105',
                    icon: 'border-none'
                },
                
                showClass: {
                    popup: 'animate__animated animate__fadeInUp animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutDown animate__faster'
                },

                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang memproses...',
                        timer: 1000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    }).then(() => {
                        document.getElementById('logout-form').submit();
                    });
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
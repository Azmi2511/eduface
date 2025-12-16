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
                popup: 'flex items-center w-full max-w-sm p-4 bg-white border border-gray-100 rounded-xl shadow-lg',
                title: 'text-sm font-medium text-gray-700'
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
                title: title,
                text: text,
                icon: 'warning',
                iconColor: '#ef4444',
                showCancelButton: true,
                
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                
                buttonsStyling: false,
                
                customClass: {
                    popup: 'rounded-xl shadow-lg border border-gray-100',
                    title: 'text-lg font-bold text-gray-800',
                    htmlContainer: 'text-sm text-gray-600',
                    actions: 'space-x-3',
                    
                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-5 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
                    
                    cancelButton: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-medium py-2 px-5 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
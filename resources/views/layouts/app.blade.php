<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Eduface: Just Face It')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    @stack('scripts')
</body>
</html>
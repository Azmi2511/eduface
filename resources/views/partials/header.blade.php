@php
    $full_name = session('full_name', 'Guest User');
    $role_session = session('role', 'user');
@endphp
<header class="h-20 bg-white shadow-sm px-8 flex items-center justify-between z-40 border-b border-gray-200 sticky top-0">
    <h2 class="text-2xl font-bold text-gray-800">@yield('header_title', 'Dashboard')</h2>

    <div class="flex items-center space-x-6">
        <div class="relative hidden md:block">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <form action="" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari data..." class="border border-gray-300 rounded-full py-2 pl-10 pr-4 focus:outline-none focus:border-blue-500 text-sm w-64 placeholder-gray-400 transition-all shadow-sm">
            </form>
        </div>

        <div class="flex items-center space-x-4">
            <div class="relative">
                <button id="notificationBtn" onclick="toggleNotification()" class="text-gray-400 hover:text-blue-600 transition relative p-2 focus:outline-none">
                    <i class="far fa-bell text-xl pointer-events-none"></i>
                </button>
            </div>
            <div class="flex items-center pl-4 border-l border-gray-200">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-sm mr-3 shadow-md shadow-blue-500/20">
                    {{ strtoupper(substr($full_name, 0, 2)) }}
                </div>
                <div class="hidden md:block text-left">
                    <h4 class="text-sm font-bold text-gray-800 leading-none">{{ Str::limit($full_name, 15) }}</h4>
                    <p class="text-xs text-gray-500 mt-1 font-medium">{{ ucfirst($role_session) }}</p>
                </div>
            </div>
        </div>
    </div>
</header>

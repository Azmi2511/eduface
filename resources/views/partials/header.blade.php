@php
    $full_name = session('full_name', 'Tamu');
    $role_session = session('role', 'user');
@endphp
<header class="h-20 bg-white shadow-sm px-8 flex items-center justify-between z-40 border-b border-gray-200 sticky top-0">
    <div class="flex items-center">
        <!-- Toggle Mobile -->
        <button id="sidebar-toggle" class="w-10 h-10 rounded-xl bg-gray-50 hover:bg-gray-100 active:scale-95 border border-gray-200 text-gray-500 hover:text-blue-600 flex items-center justify-center md:hidden mr-4 transition-all duration-200 shadow-sm focus:outline-none" title="Buka Menu">
            <i class="fas fa-bars-staggered text-lg"></i>
        </button>
        
        <h2 class="text-2xl font-bold text-gray-800">@yield('header_title', 'Dashboard')</h2>
    </div>

    <div class="flex items-center space-x-6">
       @include('partials.header_content')
    </div>
</header>
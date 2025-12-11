@php
    $full_name = session('full_name', 'Guest User');
    $role_session = session('role', 'user');
@endphp
<header class="h-20 bg-white shadow-sm px-8 flex items-center justify-between z-40 border-b border-gray-200 sticky top-0">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="text-gray-500 focus:outline-none md:hidden mr-4">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <h2 class="text-2xl font-bold text-gray-800">@yield('header_title', 'Dashboard')</h2>
    </div>

    <div class="flex items-center space-x-6">
       @include('partials.header_content')
    </div>
</header>
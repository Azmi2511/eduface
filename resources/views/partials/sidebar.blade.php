@php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

    $active = $active_menu ?? '';
    $master_data_pages = ['users','students','teachers','parents','classes'];
    $is_master_open = in_array($active, $master_data_pages);
    $school_name = DB::table('system_settings')->value('school_name');
@endphp

<aside id="sidebar" 
       :class="sidebarMinimized ? 'md:w-20 w-72' : 'w-72'"
       class="fixed inset-y-0 left-0 z-50 bg-[#2F80ED] text-white flex flex-col transition-all duration-300 transform -translate-x-full md:relative md:translate-x-0 border-r border-white/20 shadow-2xl">
    
    <div class="h-24 flex items-center border-b border-white/20 transition-all duration-300" :class="sidebarMinimized ? 'px-4 justify-center' : 'justify-between px-6'">
        <div class="flex items-center gap-4 transition-all duration-300" :class="sidebarMinimized ? 'gap-0' : 'gap-4'">
            <div class="w-10 h-10 bg-white backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 shadow-inner shrink-0">
                <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-7 h-7">
            </div>
            <div class="flex flex-col whitespace-nowrap overflow-hidden transition-all duration-300" :class="sidebarMinimized ? 'w-0 opacity-0' : 'w-32 opacity-100'">
                <h1 class="text-xl font-bold tracking-tight text-white leading-none drop-shadow-md">EduFace</h1>
                <p class="text-[11px] text-white/80 font-medium mt-1 tracking-wide">{{ $school_name }}</p>
            </div>
        </div>
        <!-- Close button for Mobile -->
        <button onclick="closeSidebar()" class="md:hidden text-white/80 hover:text-white hover:bg-white/10 w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-200 focus:outline-none" title="Tutup Menu">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <nav class="flex-1 py-6 space-y-2 overflow-y-auto scrollbar-hide transition-all duration-300" :class="sidebarMinimized ? 'px-2' : 'px-4'">
        @include('partials.sidebar_menu_content') 
    </nav>

    <!-- Floating Toggle Button on Edge (Desktop) -->
    <button @click="sidebarMinimized = !sidebarMinimized" 
            class="hidden md:flex absolute top-1/2 -right-3 transform -translate-y-1/2 w-6 h-6 bg-[#2F80ED] text-white border-2 border-white rounded-full items-center justify-center shadow-lg hover:bg-blue-600 hover:scale-110 active:scale-95 transition-all duration-200 focus:outline-none z-50 cursor-pointer"
            title="Sembunyikan/Tampilkan Menu">
        <i class="fas text-[9px] transition-transform duration-200" :class="sidebarMinimized ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
    </button>
</aside>

<style>
    /* Utility untuk hide scrollbar tapi tetap bisa scroll */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* Transisi untuk teks dan ikon */
    .menu-text {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: left center;
        white-space: nowrap;
        display: inline-block;
        max-width: 200px; 
        opacity: 1;
    }
    
    .menu-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .menu-icon-wrapper {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .section-title {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    @media (min-width: 768px) {
        .sidebar-minimized .menu-text {
            opacity: 0;
            max-width: 0;
            transform: scaleX(0);
            padding: 0;
            margin: 0;
        }
        .sidebar-minimized .menu-icon-wrapper {
            margin-right: 0 !important;
            width: 100% !important;
            display: flex;
            justify-content: center;
        }
        .sidebar-minimized .menu-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 3rem !important;
            height: 3rem !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .sidebar-minimized .menu-arrow {
            opacity: 0;
            max-width: 0;
            margin: 0;
            overflow: hidden;
            display: none;
        }
        .sidebar-minimized .section-title {
            opacity: 0;
            height: 0;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .sidebar-minimized #submenu-master {
            margin-left: 0 !important;
            padding-left: 0 !important;
            border-left: none !important;
        }
        .sidebar-minimized #submenu-master .menu-item {
            width: 2.75rem !important;
            height: 2.75rem !important;
            margin-bottom: 0.25rem;
        }
    }
</style>

<script>
function toggleMasterMenu() {
    const submenu = document.getElementById('submenu-master');
    const arrow = document.getElementById('arrow-master');
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        setTimeout(() => submenu.classList.remove('opacity-0', '-translate-y-2'), 10);
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('opacity-0', '-translate-y-2');
        arrow.style.transform = 'rotate(0deg)';
        setTimeout(() => submenu.classList.add('hidden'), 300);
    }
}
</script>
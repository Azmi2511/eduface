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
       class="fixed inset-y-0 left-0 z-50 bg-[#2F80ED] text-white flex flex-col transform -translate-x-full md:relative md:translate-x-0 border-r border-white/20 shadow-2xl sidebar-transition">
    
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
            class="hidden md:flex absolute top-1/2 -right-3 transform -translate-y-1/2 w-6 h-6 bg-blue-100 text-blue-600 border-2 border-white rounded-full items-center justify-center shadow-md hover:bg-blue-200 hover:scale-110 active:scale-95 transition-all duration-200 focus:outline-none z-50 cursor-pointer hover-ripple"
            title="Sembunyikan/Tampilkan Menu">
        <i class="fas text-[9px] transition-transform duration-200" :class="sidebarMinimized ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
    </button>
</aside>

<style>
    /* Animasi Hover Circular Ripple */
    @keyframes circular-ripple {
        0% {
            transform: translate(-50%, -50%) scale(0.9);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0;
        }
    }
    
    .hover-ripple::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid rgba(37, 99, 235, 0.4);
        background-color: rgba(37, 99, 235, 0.08);
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        z-index: -1;
        pointer-events: none;
    }

    .hover-ripple:hover::after {
        animation: circular-ripple 1s cubic-bezier(0.1, 0.8, 0.3, 1) infinite;
    }

    /* Utility untuk hide scrollbar tapi tetap bisa scroll */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* Transisi Custom Premium untuk Sidebar & Menu */
    .sidebar-transition {
        will-change: width, transform;
        transition: width 0.38s cubic-bezier(0.25, 1, 0.5, 1), transform 0.38s cubic-bezier(0.25, 1, 0.5, 1);
    }

    /* Transisi untuk teks dan ikon */
    .menu-text {
        will-change: opacity, max-width;
        transition: opacity 0.15s ease-out, max-width 0.35s cubic-bezier(0.25, 1, 0.5, 1);
        transform-origin: left center;
        white-space: nowrap;
        display: inline-block;
        max-width: 140px; 
        opacity: 1;
    }
    
    .menu-item {
        will-change: padding;
        transition: background-color 0.2s ease, padding 0.35s cubic-bezier(0.25, 1, 0.5, 1);
        overflow: hidden;
    }
    
    .menu-icon-wrapper {
        will-change: margin-right;
        transition: margin-right 0.35s cubic-bezier(0.25, 1, 0.5, 1);
    }
    
    .section-title {
        will-change: opacity, height, margin, padding;
        transition: opacity 0.12s ease-out, height 0.35s cubic-bezier(0.25, 1, 0.5, 1), margin 0.35s cubic-bezier(0.25, 1, 0.5, 1), padding 0.35s cubic-bezier(0.25, 1, 0.5, 1);
        overflow: hidden;
    }

    .menu-arrow {
        will-change: opacity;
        transition: opacity 0.15s ease-out, transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    }

    @media (min-width: 768px) {
        /* Percepat transisi saat menyusut (collapse) */
        .sidebar-minimized #sidebar {
            transition: width 0.28s cubic-bezier(0.25, 1, 0.5, 1), transform 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized .menu-text {
            opacity: 0;
            max-width: 0;
            padding: 0;
            margin: 0;
            transition: opacity 0.1s ease-out, max-width 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized .menu-icon-wrapper {
            margin-right: 0;
            transition: margin-right 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized .menu-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            transition: padding 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized .menu-arrow {
            opacity: 0;
            max-width: 0;
            margin: 0;
            overflow: hidden;
            pointer-events: none;
            transition: opacity 0.1s ease-out, max-width 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized .section-title {
            opacity: 0;
            height: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            transition: opacity 0.1s ease-out, height 0.28s cubic-bezier(0.25, 1, 0.5, 1), margin 0.28s cubic-bezier(0.25, 1, 0.5, 1), padding 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .sidebar-minimized #submenu-master {
            display: none;
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
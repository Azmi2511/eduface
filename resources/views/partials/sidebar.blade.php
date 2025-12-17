@php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

    $active = $active_menu ?? '';
    $master_data_pages = ['users','students','teachers','parents','classes'];
    $is_master_open = in_array($active, $master_data_pages);
    $school_name = DB::table('system_settings')->value('school_name');
@endphp

<aside id="sidebar" 
       class="fixed inset-y-0 left-0 z-50 w-72 bg-blue-700 text-white flex flex-col transition-transform duration-300 transform -translate-x-full md:relative md:translate-x-0 border-r border-white/20 shadow-2xl">
    
    <div class="h-24 flex items-center px-6 border-b border-white/20">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-white backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 shadow-inner">
                <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-7 h-7">
            </div>
            <div class="flex flex-col">
                <h1 class="text-xl font-bold tracking-tight text-white leading-none drop-shadow-md">EduFace</h1>
                <p class="text-[11px] text-white/80 font-medium mt-1 tracking-wide">{{ $school_name }}</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-hide">
        @include('partials.sidebar_menu_content') 
    </nav>
</aside>

<style>
    /* Utility untuk hide scrollbar tapi tetap bisa scroll */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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
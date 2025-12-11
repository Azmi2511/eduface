@php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

    $active = $active_menu ?? '';
    $master_data_pages = ['users','students','teachers','parents','classes'];
    $is_master_open = in_array($active, $master_data_pages);
    $school_name = DB::table('system_settings')->value('school_name');
@endphp

<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#3B82F6] text-white flex flex-col transition-transform duration-300 transform -translate-x-full md:relative md:translate-x-0 shadow-xl md:shadow-none">
    
    <div class="h-20 flex items-center px-6 py-4 border-b border-blue-400/30">
        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm mr-3">
            <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-9 h-9 object-contain">
        </div>
        <div>
            <h1 class="text-md font-bold leading-tight">EduFace</h1>
            <p class="text-xs text-blue-100 truncate w-32">{{ $school_name }}</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
        @include('partials.sidebar_menu_content') 
        </nav>

    <div class="p-4 border-t border-blue-400/30">
        <a href="{{ route('logout') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-red-200 hover:bg-red-500/20 hover:text-white transition-colors">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</aside>

<script>
function toggleMasterMenu() {
    const submenu = document.getElementById('submenu-master');
    const arrow = document.getElementById('arrow-master');
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}
</script>
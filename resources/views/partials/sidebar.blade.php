@php
    $active = $active_menu ?? '';
    $master_data_pages = ['users','students','teachers','parents','classes'];
    $is_master_open = in_array($active, $master_data_pages);
@endphp
<aside class="w-64 bg-[#3B82F6] text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300">
    <div class="h-20 flex items-center px-6 py-4 border-b border-blue-400/30">
        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm mr-3">
            <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-9 h-9 object-contain">
        </div>
        <div>
            <h1 class="text-md font-bold leading-tight">EduFace</h1>
            <p class="text-xs text-blue-100">Sistem Absensi</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
        <div>
            <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Menu Utama</h3>
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ $active == 'dashboard' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                <i class="fas fa-home w-6 h-6 mr-2 text-lg flex items-center justify-center"></i>
                Dashboard
            </a>
        </div>

        <div>
            <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Manajemen</h3>
            <div class="space-y-1">
                <div>
                    <button type="button" onclick="toggleMasterMenu()" class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg {{ $is_master_open ? 'text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                        <div class="flex items-center">
                            <i class="fas fa-database w-6 h-6 mr-2 text-lg flex items-center justify-center"></i>
                            Data Master
                        </div>
                        <i id="arrow-master" class="fas fa-chevron-down text-xs {{ $is_master_open ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="submenu-master" class="mt-1 space-y-1 {{ $is_master_open ? '' : 'hidden' }}">
                        <a href="{{ route('users.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'users' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Semua Pengguna</a>
                        <a href="{{ route('students.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'students' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Data Siswa</a>
                        <a href="{{ route('teachers.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'teachers' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Data Guru</a>
                        <a href="{{ route('parents.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'parents' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Data Orang Tua</a>
                        <a href="{{ route('classes.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'classes' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Data Kelas</a>
                    </div>
                </div>

                <a href="{{ route('attendance.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ $active == 'attendance' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Absensi</a>
                <a href="{{ route('announcements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ $active == 'announcements' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Pengumuman</a>
                <a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ $active == 'notifications' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Notifikasi</a>
            </div>
        </div>

        <div>
            <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Sistem</h3>
            <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ $active == 'settings' ? 'bg-blue-700 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">Pengaturan Sistem</a>
        </div>
    </nav>

    <div class="p-4 border-t border-blue-400/30">
        <a href="{{ route('logout') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-red-200 hover:bg-red-500/20 hover:text-white transition-colors">Logout</a>
    </div>
</aside>

<script>
function toggleMasterMenu() {
    const submenu = document.getElementById('submenu-master');
    const arrow = document.getElementById('arrow-master');
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        submenu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}
</script>

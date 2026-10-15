@php
    $active = $active ?? ''; 
    $master_data_pages = ['users','students','teachers','parents','classes', 'schedules'];
    $is_master_open = in_array($active, $master_data_pages);
    $userRole = session('role');
    
    $canAccessUsers = in_array($userRole, ['admin']);
    $canAccessStudents = in_array($userRole, ['admin', 'teacher']);
    $canAccessTeachers = in_array($userRole, ['admin']);
    $canAccessParents = in_array($userRole, ['admin', 'parent']);
    $canAccessClasses = in_array($userRole, ['admin', 'teacher']);
    $canAccessPermissions = in_array($userRole, ['admin', 'teacher', 'parent']);
    $canAccessSchedules = true;
    
    $canAccessAttendance = true; 
    $canAccessAnnouncements = in_array($userRole, ['admin']);
    $canAccessNotifications = true; 
    $canAccessSettings = in_array($userRole, ['admin']);

    $masterItems = [];
    if ($canAccessUsers) $masterItems[] = ['route' => 'users.index', 'active' => 'users', 'icon' => 'fa-users-cog', 'label' => 'Pengguna'];
    if ($canAccessStudents) $masterItems[] = ['route' => 'students.index', 'active' => 'students', 'icon' => 'fa-user-graduate', 'label' => 'Siswa'];
    if ($canAccessTeachers) $masterItems[] = ['route' => 'teachers.index', 'active' => 'teachers', 'icon' => 'fa-chalkboard-teacher', 'label' => 'Guru'];
    if ($canAccessParents) $masterItems[] = ['route' => 'parents.index', 'active' => 'parents', 'icon' => 'fa-user-friends', 'label' => 'Orang Tua/Wali'];
    if ($canAccessSchedules) $masterItems[] = ['route' => 'schedules.index', 'active' => 'schedules', 'icon' => 'fa-calendar-alt', 'label' => 'Jadwal'];
    if ($canAccessClasses) $masterItems[] = ['route' => 'classes.index', 'active' => 'classes', 'icon' => 'fa-school', 'label' => 'Kelas'];
    $hasMasterAccess = count($masterItems) > 0;
@endphp

<div class="mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest section-title">
    Menu Utama
</div>

<a href="{{ route('dashboard') }}" 
   class="menu-item flex items-center px-4 py-3 mb-2 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
   {{ $active == 'dashboard' 
      ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
      : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
    <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
        {{-- Ikon Dashboard lebih modern --}}
        <i class="fas fa-home text-lg {{ $active == 'dashboard' ? 'text-blue-600' : 'text-white' }}"></i>
    </div>
    <span class="menu-text">Dasbor</span>
</a>

<div class="mt-6 mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest section-title">
    Operasional
</div>

<div class="space-y-1">
    @if($hasMasterAccess)
    @if(count($masterItems) === 1)
        @php($masterItem = $masterItems[0])
        <a href="{{ route($masterItem['route']) }}" class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap {{ $active == $masterItem['active'] ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
            <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
                <i class="fas {{ $masterItem['icon'] }} text-lg {{ $active == $masterItem['active'] ? 'text-blue-600' : 'text-white' }}"></i>
            </div>
            <span class="menu-text">{{ $masterItem['label'] }}</span>
        </a>
    @else
    <button type="button" onclick="toggleMasterMenu()" 
            class="menu-item w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
            {{ $is_master_open ? 'bg-white/10 border-white/20 text-white' : 'text-white/90 hover:bg-white/20 hover:border-white/30' }}">
        <div class="flex items-center">
             <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
                {{-- Ikon Master Data: Layer Group --}}
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <span class="menu-text">Data Sekolah</span>
        </div>
        <i id="arrow-master" class="menu-arrow fas fa-chevron-down text-xs transition-transform duration-300 shrink-0 {{ $is_master_open ? 'rotate-180' : '' }}"></i>
    </button>

    <div id="submenu-master" class="{{ $is_master_open ? '' : 'hidden opacity-0 -translate-y-2' }} transition-all duration-300 ml-4 pl-4 border-l-2 border-white/30 space-y-1 mt-1">
        @foreach($masterItems as $masterItem)
            <a href="{{ route($masterItem['route']) }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == $masterItem['active'] ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <i class="fas {{ $masterItem['icon'] }} w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i>
                <span class="menu-text">{{ $masterItem['label'] }}</span>
            </a>
        @endforeach
    </div>
    @endif
    @endif

    @if($canAccessAttendance)
    <a href="{{ route('attendance.index') }}" 
       class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
       {{ $active == 'attendance' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
            {{-- Ikon Absensi: User Clock lebih relevan untuk kehadiran --}}
            <i class="fas fa-user-check text-lg {{ $active == 'attendance' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        <span class="menu-text">Absensi</span>
    </a>
    @endif

    @if($canAccessPermissions)
    <a href="{{ route('permissions.index') }}" 
       class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
       {{ $active == 'permissions' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
            {{-- Ikon Izin: File Signature --}}
            <i class="fas fa-file-signature text-lg {{ $active == 'permissions' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        <span class="menu-text">Pengajuan Izin</span>
    </a>
    @endif
    
    @if($canAccessAnnouncements)
    <a href="{{ route('announcements.index') }}" 
       class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
       {{ $active == 'announcements' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
            <i class="fas fa-bullhorn text-lg {{ $active == 'announcements' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        <span class="menu-text">Pengumuman</span>
    </a>
    @endif
    
    @if($canAccessNotifications)
    <a href="{{ route('notifications.index') }}" 
       class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
       {{ $active == 'notifications' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
            <i class="fas fa-bell text-lg {{ $active == 'notifications' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        <span class="menu-text">Notifikasi</span>
    </a>
    @endif
</div>

@if($canAccessSettings)
<div class="mt-6 mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest section-title">
    Sistem
</div>
<a href="{{ route('settings.index') }}" 
   class="menu-item flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
   {{ $active == 'settings' 
      ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
      : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
    <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
        {{-- Ikon Setting: Cogs (Jamak) --}}
        <i class="fas fa-cogs text-lg {{ $active == 'settings' ? 'text-blue-600' : 'text-white' }}"></i>
    </div>
    <span class="menu-text">Konfigurasi</span>
</a>
@endif
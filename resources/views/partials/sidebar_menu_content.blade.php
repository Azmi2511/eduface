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
    $canAccessPermissions = in_array($userRole, ['admin', 'teacher']);
    $canAccessSchedules = true;
    
    $canAccessAttendance = true; 
    $canAccessAnnouncements = in_array($userRole, ['admin']);
    $canAccessNotifications = true; 
    $canAccessSettings = in_array($userRole, ['admin']);

    $hasMasterAccess = $canAccessUsers || $canAccessStudents || $canAccessTeachers || $canAccessParents || $canAccessClasses || $canAccessSchedules;
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
    <span class="menu-text">Dashboard</span>
</a>

<div class="mt-6 mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest section-title">
    Manajemen
</div>

<div class="space-y-1">
    @if($hasMasterAccess)
    <button type="button" onclick="toggleMasterMenu()" 
            class="menu-item w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent overflow-hidden whitespace-nowrap
            {{ $is_master_open ? 'bg-white/10 border-white/20 text-white' : 'text-white/90 hover:bg-white/20 hover:border-white/30' }}">
        <div class="flex items-center w-full">
             <div class="w-6 mr-3 flex justify-center menu-icon-wrapper shrink-0">
                {{-- Ikon Master Data: Layer Group --}}
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <span class="menu-text">Data Master</span>
        </div>
        <i id="arrow-master" class="menu-arrow fas fa-chevron-down text-xs transition-transform duration-300 shrink-0 {{ $is_master_open ? 'rotate-180' : '' }}"></i>
    </button>

    <div id="submenu-master" class="{{ $is_master_open ? '' : 'hidden opacity-0 -translate-y-2' }} transition-all duration-300 ml-4 pl-4 border-l-2 border-white/30 space-y-1 mt-1">
        
        @if($canAccessUsers)
        <a href="{{ route('users.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'users' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
             <i class="fas fa-users-cog w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Semua Pengguna</span>
        </a>
        @endif
        
        @if($canAccessStudents)
        <a href="{{ route('students.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'students' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-user-graduate w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Data Siswa</span>
        </a>
        @endif
        
        @if($canAccessTeachers)
        <a href="{{ route('teachers.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'teachers' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-chalkboard-teacher w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Data Guru</span>
        </a>
        @endif
        
        @if($canAccessParents)
        <a href="{{ route('parents.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'parents' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            {{-- Mengganti ikon person-breastfeeding ke yang lebih umum --}}
            <i class="fas fa-user-friends w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Data Orang Tua</span>
        </a>
        @endif
        
        @if($canAccessSchedules)
        <a href="{{ route('schedules.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'schedules' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            {{-- Mengganti ke ikon Kalender Jadwal --}}
            <i class="fas fa-calendar-alt w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Data Jadwal</span>
        </a>
        @endif
        
        @if($canAccessClasses)
        <a href="{{ route('classes.index') }}" class="menu-item flex items-center px-3 py-2 text-sm rounded-lg transition-all overflow-hidden whitespace-nowrap {{ $active == 'classes' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-school w-6 h-6 mr-2 text-lg flex items-center justify-center menu-icon-wrapper shrink-0"></i> <span class="menu-text">Data Kelas</span>
        </a>
        @endif
    </div>
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
        <span class="menu-text">Perizinan</span>
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
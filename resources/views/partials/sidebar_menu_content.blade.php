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
    $canAccessSchedules = true;
    
    $canAccessAttendance = true; 
    $canAccessAnnouncements = in_array($userRole, ['admin']);
    $canAccessNotifications = true; 
    $canAccessSettings = in_array($userRole, ['admin']);

    $hasMasterAccess = $canAccessUsers || $canAccessStudents || $canAccessTeachers || $canAccessParents || $canAccessClasses || $canAccessSchedules;
@endphp

<div class="mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest">
    Menu Utama
</div>

<a href="{{ route('dashboard') }}" 
   class="flex items-center px-4 py-3 mb-2 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
   {{ $active == 'dashboard' 
      ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
      : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
    <div class="w-6 mr-3 flex justify-center">
        <i class="fas fa-home text-lg {{ $active == 'dashboard' ? 'text-blue-600' : 'text-white' }}"></i>
    </div>
    Dashboard
</a>

<div class="mt-6 mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest">
    Manajemen
</div>

<div class="space-y-1">
    @if($hasMasterAccess)
    <button type="button" onclick="toggleMasterMenu()" 
            class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
            {{ $is_master_open ? 'bg-white/10 border-white/20 text-white' : 'text-white/90 hover:bg-white/20 hover:border-white/30' }}">
        <div class="flex items-center">
             <div class="w-6 mr-3 flex justify-center">
                <i class="fas fa-database text-lg"></i>
            </div>
            Data Master
        </div>
        <i id="arrow-master" class="fas fa-chevron-down text-xs transition-transform duration-300 {{ $is_master_open ? 'rotate-180' : '' }}"></i>
    </button>

    <div id="submenu-master" class="{{ $is_master_open ? '' : 'hidden opacity-0 -translate-y-2' }} transition-all duration-300 ml-4 pl-4 border-l-2 border-white/30 space-y-1 mt-1">
        
        @if($canAccessUsers)
        <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'users' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
             <i class="fas fa-users w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Semua Pengguna
        </a>
        @endif
        
        @if($canAccessStudents)
        <a href="{{ route('students.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'students' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-user-graduate w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Data Siswa
        </a>
        @endif
        
        @if($canAccessTeachers)
        <a href="{{ route('teachers.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'teachers' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-chalkboard-teacher w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Data Guru
        </a>
        @endif
        
        @if($canAccessParents)
        <a href="{{ route('parents.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'parents' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-solid fa-person-breastfeeding w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Data Orang Tua
        </a>
        @endif
        
        @if($canAccessSchedules)
        <a href="{{ route('schedules.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'schedules' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-book-reader w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Data Jadwal
        </a>
        @endif
        
        @if($canAccessClasses)
        <a href="{{ route('classes.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all {{ $active == 'classes' ? 'bg-white text-blue-700 font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <i class="fas fa-school w-6 h-6 mr-2 text-lg flex items-center justify-center"></i> Data Kelas
        </a>
        @endif
    </div>
    @endif

    @if($canAccessAttendance)
    <a href="{{ route('attendance.index') }}" 
       class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
       {{ $active == 'attendance' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center">
            <i class="fas fa-calendar-check text-lg {{ $active == 'attendance' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        Absensi
    </a>
    @endif
    
    @if($canAccessAnnouncements)
    <a href="{{ route('announcements.index') }}" 
       class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
       {{ $active == 'announcements' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center">
            <i class="fas fa-bullhorn text-lg {{ $active == 'announcements' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        Pengumuman
    </a>
    @endif
    
    @if($canAccessNotifications)
    <a href="{{ route('notifications.index') }}" 
       class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
       {{ $active == 'notifications' 
          ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
          : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
        <div class="w-6 mr-3 flex justify-center">
            <i class="fas fa-bell text-lg {{ $active == 'notifications' ? 'text-blue-600' : 'text-white' }}"></i>
        </div>
        Notifikasi
    </a>
    @endif
</div>

@if($canAccessSettings)
<div class="mt-6 mb-2 px-3 text-[10px] font-bold text-white/60 uppercase tracking-widest">
    Sistem
</div>
<a href="{{ route('settings.index') }}" 
   class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border border-transparent
   {{ $active == 'settings' 
      ? 'bg-white text-blue-700 shadow-[0_0_20px_rgba(255,255,255,0.3)]' 
      : 'text-white/90 hover:bg-white/20 hover:border-white/30 hover:shadow-lg' }}">
    <div class="w-6 mr-3 flex justify-center">
        <i class="fas fa-cog text-lg {{ $active == 'settings' ? 'text-blue-600' : 'text-white' }}"></i>
    </div>
    Konfigurasi Sistem
</a>
@endif
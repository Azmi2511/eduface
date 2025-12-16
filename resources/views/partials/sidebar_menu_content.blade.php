@php
    $active = $active ?? ''; 
    $master_data_pages = ['users','students','teachers','parents','classes'];
    $is_master_open = in_array($active, $master_data_pages);
    
    // Get user role from session
    $userRole = session('role');
    
    // Define role permissions for each menu
    $canAccessUsers = in_array($userRole, ['admin']);
    $canAccessStudents = in_array($userRole, ['admin', 'teacher']);
    $canAccessTeachers = in_array($userRole, ['admin']);
    $canAccessParents = in_array($userRole, ['admin', 'parent']);
    $canAccessClasses = in_array($userRole, ['admin', 'teacher']);
    $canAccessAttendance = true; // All authenticated users
    $canAccessAnnouncements = in_array($userRole, ['admin']);
    $canAccessNotifications = true; // All authenticated users
    $canAccessSettings = in_array($userRole, ['admin']);
    
    // Check if Data Master menu should be visible
    $hasMasterAccess = $canAccessUsers || $canAccessStudents || $canAccessTeachers || $canAccessParents || $canAccessClasses;
@endphp

<div>
    <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Menu Utama</h3>
    <a href="{{ route('dashboard') }}" 
       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $active == 'dashboard' ? 'bg-blue-800 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
        <div class="w-6 mr-2 flex justify-center"><i class="fas fa-home text-lg"></i></div>
        Dashboard
    </a>
</div>

<div>
    <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Manajemen</h3>
    <div class="space-y-1">
        @if($hasMasterAccess)
        <div>
            <button type="button" onclick="toggleMasterMenu()" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $is_master_open ? 'text-white bg-blue-700/50' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                <div class="flex items-center">
                    <div class="w-6 mr-2 flex justify-center"><i class="fas fa-database text-lg"></i></div>
                    Data Master
                </div>
                <i id="arrow-master" class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $is_master_open ? 'rotate-180' : '' }}"></i>
            </button>

            <div id="submenu-master" class="mt-1 space-y-1 {{ $is_master_open ? '' : 'hidden' }}">
                @if($canAccessUsers)
                <a href="{{ route('users.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'users' ? 'text-white bg-blue-700' : 'text-blue-200 hover:text-white hover:bg-blue-600' }}">
                    <i class="fas fa-users w-5 text-center mr-2 text-xs opacity-80"></i> Semua Pengguna
                </a>
                @endif
                
                @if($canAccessStudents)
                <a href="{{ route('students.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'students' ? 'text-white bg-blue-700' : 'text-blue-200 hover:text-white hover:bg-blue-600' }}">
                    <i class="fas fa-user-graduate w-5 text-center mr-2 text-xs opacity-80"></i> Data Siswa
                </a>
                @endif
                
                @if($canAccessTeachers)
                <a href="{{ route('teachers.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'teachers' ? 'text-white bg-blue-700' : 'text-blue-200 hover:text-white hover:bg-blue-600' }}">
                    <i class="fas fa-chalkboard-teacher w-5 text-center mr-2 text-xs opacity-80"></i> Data Guru
                </a>
                @endif
                
                @if($canAccessParents)
                <a href="{{ route('parents.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'parents' ? 'text-white bg-blue-700' : 'text-blue-200 hover:text-white hover:bg-blue-600' }}">
                    <i class="fas fa-user-tie w-5 text-center mr-2 text-xs opacity-80"></i> Data Orang Tua
                </a>
                @endif
                
                @if($canAccessClasses)
                <a href="{{ route('classes.index') }}" class="flex items-center pl-11 pr-3 py-2 text-sm font-medium rounded-lg {{ $active == 'classes' ? 'text-white bg-blue-700' : 'text-blue-200 hover:text-white hover:bg-blue-600' }}">
                    <i class="fas fa-door-open w-5 text-center mr-2 text-xs opacity-80"></i> Data Kelas
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($canAccessAttendance)
        <a href="{{ route('attendance.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $active == 'attendance' ? 'bg-blue-800 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
            <div class="w-6 mr-2 flex justify-center"><i class="fas fa-calendar-check text-lg"></i></div>
            Absensi
        </a>
        @endif
        
        @if($canAccessAnnouncements)
        <a href="{{ route('announcements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $active == 'announcements' ? 'bg-blue-800 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
            <div class="w-6 mr-2 flex justify-center"><i class="fas fa-bullhorn text-lg"></i></div>
            Pengumuman
        </a>
        @endif
        
        @if($canAccessNotifications)
        <a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $active == 'notifications' ? 'bg-blue-800 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
            <div class="w-6 mr-2 flex justify-center"><i class="fas fa-bell text-lg"></i></div>
            Notifikasi
        </a>
        @endif
    </div>
</div>

@if($canAccessSettings)
<div>
    <h3 class="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-3 px-2">Sistem</h3>
    <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $active == 'settings' ? 'bg-blue-800 text-white shadow-md' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
        <div class="w-6 mr-2 flex justify-center"><i class="fas fa-cog text-lg"></i></div>
        Pengaturan Sistem
    </a>
</div>
@endif
@php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$userId = auth()->id();
$userRole = session('role');

$notifications = DB::table('notifications')
    ->where('user_id', $userId)
    ->where('is_read', 0)
    ->orderBy('created_at', 'desc')
    ->get();

$role = 'Guest';
if ($userRole == 'admin' || $userRole == 'Admin'){
    $role = 'Administrator';
} elseif ($userRole == "teacher" || $userRole == "Teacher"){
    $role = 'Guru';
} elseif ($userRole == "student" || $userRole == "Student"){
    $role = 'Siswa';
}
@endphp

<div class="hidden md:block relative flex-1 max-w-md ml-8">
    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-search text-gray-400"></i>
    </span>
    <form action="" method="GET">
        <input type="text" 
               name="search" 
               value="{{ request('search') }}" 
               placeholder="Cari..." 
               class="w-full border border-gray-300 bg-gray-50 rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm placeholder-gray-400 transition-all shadow-sm">
    </form>
</div>

<div class="flex items-center space-x-3 ml-auto">
    
    <div class="relative">
        <button id="notificationBtn" onclick="toggleNotification()" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition focus:outline-none relative">
            <i class="far fa-bell text-xl"></i>
            @if($notifications->count() > 0)
                <span class="absolute top-2 right-2 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            @endif
        </button>
        
        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 overflow-hidden transform origin-top-right transition-all">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Notifikasi</h3>
            </div>
            <div class="max-h-64 overflow-y-auto">
                @if($notifications->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                        <i class="far fa-bell-slash text-5xl text-gray-300 mb-3"></i>
                        <p class="text-sm text-gray-500 font-medium">Tidak ada notifikasi baru.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($notifications as $notif)
                            <li class="px-4 py-3 hover:bg-gray-50 transition cursor-pointer">
                                <p class="text-sm text-gray-800 font-medium">{{ $notif->title ?? 'Pemberitahuan' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $notif->message ?? 'Isi pesan tidak tersedia' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="h-8 w-px bg-gray-200 mx-2"></div>

    <div class="relative">
        <button onclick="toggleProfileMenu()" id="profileBtn" class="flex items-center cursor-pointer group focus:outline-none">
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-transparent group-hover:ring-blue-200 transition-all">
                {{ asset('/storage').session('profile_picture') }}
            </div>
            
            <div class="hidden md:block ml-3 text-left">
                <h4 class="text-sm font-bold text-gray-800 leading-none group-hover:text-blue-600 transition-colors">
                    {{ Str::limit(session('full_name', 'Guest User'), 15) }}
                </h4>
                <p class="text-[11px] text-gray-500 mt-0.5 font-medium uppercase tracking-wide">
                    {{ $role }}
                </p>
            </div>
            
            <i id="profileChevron" class="fas fa-chevron-down text-gray-300 text-xs ml-3 hidden md:block group-hover:text-blue-500 transition-transform duration-200"></i>
        </button>

        <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 border border-gray-100 overflow-hidden transform origin-top-right transition-all">
            
            <div class="block md:hidden px-4 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-bold text-gray-800">{{ session('full_name', 'Guest User') }}</p>
                <p class="text-xs text-gray-500">{{ $role }}</p>
            </div>

            <div class="py-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="far fa-user w-5 text-gray-400 group-hover:text-blue-500 mr-2 text-center"></i>
                    Profil Saya
                </a>
                @if(in_array($userRole, ['admin']))
                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="fas fa-cog w-5 text-gray-400 group-hover:text-blue-500 mr-2 text-center"></i>
                    Pengaturan
                </a>
                @endif
            </div>
            
            <div class="border-t border-gray-100 my-0.5"></div>
            
            <div class="py-1">
                <a href="{{ route('logout') }}" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                    <i class="fas fa-sign-out-alt w-5 text-red-400 group-hover:text-red-600 mr-2 text-center"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleProfileMenu() {
    const dropdown = document.getElementById('profileDropdown');
    const chevron = document.getElementById('profileChevron');
    const notifDropdown = document.getElementById('notificationDropdown');

    if (!notifDropdown.classList.contains('hidden')) {
        notifDropdown.classList.add('hidden');
    }

    dropdown.classList.toggle('hidden');
    
    if (dropdown.classList.contains('hidden')) {
        chevron.classList.remove('rotate-180');
    } else {
        chevron.classList.add('rotate-180');
    }
}

function toggleNotification() {
    const dropdown = document.getElementById('notificationDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    const chevron = document.getElementById('profileChevron');

    if (!profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }

    dropdown.classList.toggle('hidden');
}

window.onclick = function(event) {
    const notifBtn = document.getElementById('notificationBtn');
    const notifDrop = document.getElementById('notificationDropdown');
    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    const chevron = document.getElementById('profileChevron');

    if (!notifBtn.contains(event.target) && !notifDrop.contains(event.target)) {
        notifDrop.classList.add('hidden');
    }

    if (!profileBtn.contains(event.target) && !profileDrop.contains(event.target)) {
        profileDrop.classList.add('hidden');
        if(chevron) chevron.classList.remove('rotate-180');
    }
}
</script>
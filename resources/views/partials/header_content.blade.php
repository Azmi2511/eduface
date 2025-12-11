@php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$userId = auth()->id();
$userRole = session('role');

$notifications = DB::table('notifications')
    ->where('user_id', $userId)
    ->where('user_role', $userId)
    ->orderBy('created_at', 'desc')
    ->get();
@endphp

<div class="hidden md:block relative flex-1 max-w-md ml-8">
    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-search text-gray-400"></i>
    </span>
    <form action="" method="GET">
        <input type="text" 
               name="search" 
               value="{{ request('search') }}" 
               placeholder="Cari data siswa, guru, dll..." 
               class="w-full border border-gray-300 bg-gray-50 rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm placeholder-gray-400 transition-all shadow-sm">
    </form>
</div>

<div class="flex items-center space-x-3 ml-auto">
    <div class="relative">
        <button id="notificationBtn" onclick="toggleNotification()" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition focus:outline-none">
            <i class="far fa-bell text-xl"></i>
        </button>
        
        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 overflow-hidden">
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

    <div class="flex items-center cursor-pointer group">
        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-transparent group-hover:ring-blue-200 transition-all">
            {{ strtoupper(substr(session('full_name', 'Guest'), 0, 2)) }}
        </div>
        
        <div class="hidden md:block ml-3 text-left">
            <h4 class="text-sm font-bold text-gray-800 leading-none group-hover:text-blue-600 transition-colors">
                {{ Str::limit(session('full_name', 'Guest User'), 15) }}
            </h4>
            <p class="text-[11px] text-gray-500 mt-0.5 font-medium uppercase tracking-wide">
                {{ session('role', 'User') }}
            </p>
        </div>
        
        <i class="fas fa-chevron-down text-gray-300 text-xs ml-3 hidden md:block group-hover:text-blue-500 transition-colors"></i>
    </div>
</div>
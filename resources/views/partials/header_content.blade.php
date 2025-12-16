@php
    $userId = auth()->id();
    $userRole = session('role');

    $notifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('user_id', $userId)
        ->where('is_read', 0)
        ->orderBy('created_at', 'desc')
        ->get();

    $roleLabel = 'Guest';
    $checkRole = strtolower($userRole);
    
    if ($checkRole == 'admin') $roleLabel = 'Administrator';
    elseif ($checkRole == 'teacher') $roleLabel = 'Guru';
    elseif ($checkRole == 'student') $roleLabel = 'Siswa';
@endphp

<div class="flex items-center space-x-4 ml-auto z-20">
    
    <div class="relative z-50" x-data="{ open: false }">
        <button @click="open = !open" @click.away="open = false" type="button" 
            class="group relative inline-flex items-center justify-center p-2.5 rounded-full hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            
            <i class="far fa-bell text-xl text-gray-500 group-hover:text-blue-600 transition-colors"></i>

            @if($notifications->count() > 0)
                <span class="absolute top-2 right-2 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
                </span>
            @endif
        </button>

        <div x-show="open" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="absolute right-0 mt-3 w-80 sm:w-96 origin-top-right bg-white rounded-2xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden"
            style="display: none;">
            
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $notifications->count() }} pesan baru</p>
                </div>
                @if($notifications->count() > 0)
                    <button class="text-xs font-medium text-blue-600 hover:text-blue-800 transition">Tandai dibaca</button>
                @endif
            </div>

            <div class="max-h-[24rem] overflow-y-auto custom-scrollbar">
                @if($notifications->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
                        <div class="bg-gray-50 p-4 rounded-full mb-3 shadow-sm">
                            <i class="far fa-bell-slash text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Tidak ada notifikasi baru.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach($notifications as $notif)
                            <li>
                                <a href="{{ route('notifications.read', $notif->id) }}" class="relative group block px-5 py-4 hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="flex gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 ring-4 ring-white group-hover:ring-blue-50 transition-all">
                                                <i class="fas fa-info-circle text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600">
                                                {{ $notif->title ?? 'Info' }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                                {{ $notif->message }}
                                            </p>
                                            <span class="text-[10px] text-gray-400 mt-1 block">
                                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans(null, true) }}
                                            </span>
                                        </div>
                                        <div class="self-center flex-shrink-0">
                                            <div class="h-2 w-2 bg-blue-600 rounded-full shadow-sm ring-2 ring-white"></div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            
            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-gray-600 hover:text-blue-600 transition">Lihat Semua</a>
            </div>
        </div>
    </div>

    <div class="h-8 w-px bg-gray-200"></div>

    <div class="relative z-40" x-data="{ openProfile: false }">
        
        <button @click="openProfile = !openProfile" @click.away="openProfile = false" type="button" 
            class="flex items-center cursor-pointer group focus:outline-none">
            
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-transparent group-hover:ring-blue-200 transition-all overflow-hidden shrink-0">
                @if(session('profile_picture') && file_exists(public_path('storage/'.session('profile_picture'))))
                     <img src="{{ asset('storage/'.session('profile_picture')) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                     <span>{{ substr(session('full_name', 'G'), 0, 1) }}</span>
                @endif
            </div>
            
            <div class="hidden md:block ml-3 text-left max-w-[150px]">
                <h4 class="text-sm font-bold text-gray-800 leading-none group-hover:text-blue-600 transition-colors truncate">
                    {{ session('full_name', 'Guest User') }}
                </h4>
                <p class="text-[11px] text-gray-500 mt-1 font-medium uppercase tracking-wide truncate">
                    {{ $roleLabel }}
                </p>
            </div>
            
            <i class="fas fa-chevron-down text-gray-300 text-xs ml-3 hidden md:block group-hover:text-blue-500 transition-transform duration-200"
               :class="{'rotate-180': openProfile}"></i>
        </button>

        <div x-show="openProfile"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 border border-gray-100 overflow-hidden"
             style="display: none;">
            
            <div class="block md:hidden px-4 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-bold text-gray-800">{{ session('full_name', 'Guest User') }}</p>
                <p class="text-xs text-gray-500">{{ $roleLabel }}</p>
            </div>

            <div class="py-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="far fa-user w-5 text-gray-400 group-hover:text-blue-500 mr-2 text-center"></i>
                    Profil Saya
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="fas fa-cog w-5 text-gray-400 group-hover:text-blue-500 mr-2 text-center"></i>
                    Pengaturan
                </a>
            </div>
            
            <div class="border-t border-gray-100 my-0.5"></div>
            
            <div class="py-1">
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" onclick="confirmLogout(event)" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors group text-left">
                        <i class="fas fa-sign-out-alt w-5 text-red-400 group-hover:text-red-600 mr-2 text-center"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
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

    $initials = substr(session('full_name', 'G'), 0, 1);
@endphp

<div class="flex items-center space-x-4 ml-auto z-20">
    
    <div class="relative z-50" x-data="{ open: false }">
        <button @click="open = !open" @click.away="open = false" type="button" 
            class="group relative inline-flex items-center justify-center p-3 rounded-xl hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            
            <i class="far fa-bell text-xl text-gray-500 group-hover:text-blue-600 transition-colors"></i>

            @if($notifications->count() > 0)
                <span class="absolute top-1 right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600 border-2 border-white items-center justify-center text-[10px] font-bold text-white">
                        {{ $notifications->count() }}
                    </span>
                </span>
            @endif
        </button>

        <div x-show="open" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-3 w-80 sm:w-96 origin-top-right bg-white rounded-2xl shadow-2xl ring-1 ring-black ring-opacity-5 overflow-hidden transform"
            style="display: none;">
            
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-tr from-blue-600 to-blue-400">
                <div>
                    <h3 class="text-base font-bold text-white"><i class="fas fa-bell h-5 w-5"></i> Notifikasi Baru</h3>
                    <p class="text-xs text-white mt-0.5">{{ $notifications->count() }} pesan belum dibaca</p>
                </div>
                @if($notifications->count() > 0)
                    <a href="{{ route('notifications.markAllRead') ?? '#' }}" class="text-xs font-medium text-white hover:text-gray-200 transition">
                        Tandai semua dibaca
                    </a>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto custom-scrollbar">
                @if($notifications->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
                        <div class="bg-gray-100 p-5 rounded-full mb-4 shadow-inner">
                            <i class="far fa-bell-slash text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-600 font-medium">Tidak ada notifikasi baru.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($notifications as $notif)
                            <li class="{{ $notif->is_read == 0 ? 'bg-blue-50/50' : '' }}"> 
                                <a href="{{ route('notifications.read', $notif->id) }}" class="relative group block px-5 py-4 hover:bg-blue-100 transition duration-150 ease-in-out">
                                    <div class="flex gap-4 items-start">
                                        <div class="flex-shrink-0">
                                            {{-- Ganti ikon berdasarkan tipe notifikasi jika perlu, di sini menggunakan info sebagai default --}}
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 ring-4 ring-white group-hover:ring-blue-200 transition-all shadow-md">
                                                <i class="fas fa-info-circle text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-700">
                                                {{ $notif->title ?? 'Pesan Baru' }}
                                            </p>
                                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                {{ $notif->message }}
                                            </p>
                                            <span class="text-[10px] text-gray-400 mt-1.5 block font-medium">
                                                <i class="far fa-clock mr-1"></i> 
                                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                        @if($notif->is_read == 0)
                                            <div class="self-start flex-shrink-0 mt-1">
                                                <div class="h-2 w-2 bg-red-500 rounded-full"></div>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            
            <div class="bg-white px-5 py-3 border-t border-gray-100 text-center">
                <a href="{{ route('notifications.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                    Lihat Semua Notifikasi <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="h-8 w-px bg-gray-200"></div>

    <div class="relative z-40" x-data="{ openProfile: false }">
        
        <button @click="openProfile = !openProfile" @click.away="openProfile = false" type="button" 
            class="flex items-center cursor-pointer group focus:outline-none">
            
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-lg shadow-lg ring-2 ring-transparent group-hover:ring-blue-300 transition-all overflow-hidden shrink-0">
                @if(session('profile_picture') && file_exists(public_path('storage/'.session('profile_picture'))))
                     <img src="{{ asset('storage/'.session('profile_picture')) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                     <span>{{ $initials }}</span>
                @endif
            </div>
            
            <div class="hidden lg:block ml-3 text-left max-w-[150px]">
                <h4 class="text-sm font-extrabold text-gray-900 leading-none group-hover:text-blue-600 transition-colors truncate">
                    {{ session('full_name', 'Guest User') }}
                </h4>
                <p class="text-[11px] text-blue-500 mt-1 font-bold uppercase tracking-wider truncate">
                    {{ $roleLabel }}
                </p>
            </div>
            
            <i class="fas fa-chevron-down text-gray-400 text-[10px] ml-3 hidden lg:block group-hover:text-blue-500 transition-transform duration-200"
                :class="{'rotate-180': openProfile}"></i>
        </button>

        <div x-show="openProfile"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-2xl z-50 border border-gray-100 overflow-hidden transform"
             style="display: none;">
            
            <div class="px-4 py-3 bg-blue-600 text-white flex items-center">
                 <div class="w-8 h-8 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold text-sm mr-3">
                    @if(session('profile_picture') && file_exists(public_path('storage/'.session('profile_picture'))))
                     <img src="{{ asset('storage/'.session('profile_picture')) }}" alt="Profile" class="w-full h-full object-cover rounded-full">
                    @else
                     <span>{{ $initials }}</span>
                    @endif
                 </div>
                 <div>
                    <p class="text-sm font-bold truncate">{{ session('full_name', 'Guest User') }}</p>
                    <p class="text-xs font-medium opacity-80">{{ $roleLabel }}</p>
                 </div>
            </div>

            <div class="py-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="far fa-user-circle w-5 text-lg text-gray-400 group-hover:text-blue-500 mr-3 text-center"></i>
                    Profil Saya
                </a>
                <a href="{{ route('settings.preferences.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="fas fa-cog w-5 text-lg text-gray-400 group-hover:text-blue-500 mr-3 text-center"></i>
                    Pengaturan
                </a>
                <a href="{{ route('support.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                    <i class="fas fa-question-circle w-5 text-lg text-gray-400 group-hover:text-blue-500 mr-3 text-center"></i>
                    Bantuan & Dukungan
                </a>
            </div>
            
            <div class="border-t border-gray-100 my-0.5"></div>
            
            <div class="py-1">
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" onclick="confirmLogout(event)" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors group text-left font-bold">
                        <i class="fas fa-sign-out-alt w-5 text-lg text-red-400 group-hover:text-red-600 mr-3 text-center"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi JavaScript untuk konfirmasi logout yang lebih baik (opsional)
    function confirmLogout(event) {
        event.preventDefault();
        
        // Anda bisa menggantinya dengan library SweetAlert2 atau modal kustom Tailwind
        if (confirm('Apakah Anda yakin ingin keluar dari sesi Anda?')) {
            document.getElementById('logout-form').submit();
        }
    }

    // Custom scrollbar (Anda harus menambahkan CSS untuk ini)
    /*
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5); // gray-400 with opacity
        border-radius: 3px;
    }
    */
</script>
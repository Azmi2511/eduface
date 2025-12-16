@php
    // Sebaiknya logic ini ada di Controller, tapi untuk snippet ini kita amankan di sini
    $userId = auth()->id();
    $userRole = session('role');

    // Menggunakan optional() atau check null untuk menghindari error jika user belum login
    $notifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('user_id', $userId)
        ->where('is_read', 0)
        ->orderBy('created_at', 'desc')
        ->get();

    $roleLabel = 'Guest';
    // Normalisasi role agar tidak sensitif huruf besar/kecil
    $checkRole = strtolower($userRole);
    
    if ($checkRole == 'admin'){
        $roleLabel = 'Administrator';
    } elseif ($checkRole == 'teacher'){
        $roleLabel = 'Guru';
    } elseif ($checkRole == 'student'){
        $roleLabel = 'Siswa';
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

<div class="flex items-center space-x-4 ml-auto z-20">
    
    <div class="relative shrink-0 z-30">
        <button id="notificationBtn" type="button" onclick="toggleNotification(event)" 
                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition focus:outline-none relative">
            <i class="far fa-bell text-xl pointer-events-none"></i> @if($notifications->count() > 0)
                <span class="absolute top-2 right-2 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white pointer-events-none"></span>
            @endif
        </button>
        
        <div id="notificationDropdown" class="hidden absolute right-0 mt-3 w-80 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 overflow-hidden transform origin-top-right transition-all">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-700">Notifikasi</h3>
                @if($notifications->count() > 0)
                    <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold">{{ $notifications->count() }} Baru</span>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto custom-scrollbar">
                @if($notifications->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                        <div class="bg-gray-50 p-3 rounded-full mb-3">
                            <i class="far fa-bell-slash text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Tidak ada notifikasi baru.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($notifications as $notif)
                            <li>
                                <a href="{{ route('notifications.read', $notif->id) }}" class="block px-4 py-3 hover:bg-blue-50 transition cursor-pointer group">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="h-2 w-2 bg-blue-500 rounded-full shadow-sm"></div>
                                        </div>
                                        <div class="ml-3 w-full">
                                            <p class="text-sm text-gray-800 font-semibold group-hover:text-blue-600 transition">
                                                {{ $notif->title ?? 'Pemberitahuan' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                                {{ $notif->message ?? '-' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 mt-1.5 flex items-center">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="h-8 w-px bg-gray-200"></div>

    <div class="relative z-20">
        <button onclick="toggleProfileMenu(event)" id="profileBtn" type="button" 
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
            
            <i id="profileChevron" class="fas fa-chevron-down text-gray-300 text-xs ml-3 hidden md:block group-hover:text-blue-500 transition-transform duration-200"></i>
        </button>

        <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 border border-gray-100 overflow-hidden transform origin-top-right transition-all">
            
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
                <form id="logout-form" action="{{ route('logout') }}" method="POST" >
                    @csrf
                    <button type="button" onclick="confirmAction(event, 'logout-form', 'Yakin ingin keluar?', 'Sesi Anda akan diakhiri.')" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors group text-left">
                        <i class="fas fa-sign-out-alt w-5 text-red-400 group-hover:text-red-600 mr-2 text-center"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Helper function untuk menutup semua dropdown
    function closeAllDropdowns() {
        const notifDrop = document.getElementById('notificationDropdown');
        const profileDrop = document.getElementById('profileDropdown');
        const chevron = document.getElementById('profileChevron');

        if(notifDrop) notifDrop.classList.add('hidden');
        if(profileDrop) profileDrop.classList.add('hidden');
        if(chevron) chevron.classList.remove('rotate-180');
    }

    function toggleProfileMenu(event) {
        // Mencegah event bubbling (klik tembus)
        event.stopPropagation();
        
        const dropdown = document.getElementById('profileDropdown');
        const chevron = document.getElementById('profileChevron');
        const notifDropdown = document.getElementById('notificationDropdown');

        // Tutup notifikasi jika sedang terbuka
        if (!notifDropdown.classList.contains('hidden')) {
            notifDropdown.classList.add('hidden');
        }

        // Toggle profile
        dropdown.classList.toggle('hidden');
        
        // Atur rotasi chevron
        if (dropdown.classList.contains('hidden')) {
            chevron.classList.remove('rotate-180');
        } else {
            chevron.classList.add('rotate-180');
        }
    }

    function toggleNotification(event) {
        // Mencegah event bubbling (klik tembus)
        event.stopPropagation();

        const dropdown = document.getElementById('notificationDropdown');
        const profileDropdown = document.getElementById('profileDropdown');
        const chevron = document.getElementById('profileChevron');

        // Tutup profile jika sedang terbuka
        if (!profileDropdown.classList.contains('hidden')) {
            profileDropdown.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }

        // Toggle notifikasi
        dropdown.classList.toggle('hidden');
    }

    // Event listener pada document untuk menutup dropdown saat klik di luar
    document.addEventListener('click', function(event) {
        const notifBtn = document.getElementById('notificationBtn');
        const notifDrop = document.getElementById('notificationDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileDrop = document.getElementById('profileDropdown');

        // Cek klik diluar Notifikasi
        const clickedOutsideNotif = notifBtn && notifDrop && !notifBtn.contains(event.target) && !notifDrop.contains(event.target);
        
        // Cek klik diluar Profile
        const clickedOutsideProfile = profileBtn && profileDrop && !profileBtn.contains(event.target) && !profileDrop.contains(event.target);

        if (clickedOutsideNotif && clickedOutsideProfile) {
            closeAllDropdowns();
        }
    });
</script>
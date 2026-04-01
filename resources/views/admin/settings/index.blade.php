@php
$active_menu = 'settings';
@endphp
@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="flex-1 flex flex-col bg-[#F8FAFC]">
    <main class="flex-1 overflow-y-auto p-6 lg:p-10">
        
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Konfigurasi Sistem</h1>
            <p class="text-slate-500 mt-2">Kelola parameter operasional dan keamanan platform Anda.</p>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3"></i> {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-80 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 p-2 sticky top-6 shadow-sm">
                    <nav class="space-y-1">
                        <button onclick="switchTab('general', this)" class="tab-link active-tab w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all bg-blue-600 text-white shadow-md">
                            <i class="fas fa-school w-5 mr-3"></i>Umum
                        </button>
                        <button onclick="switchTab('attendance', this)" class="tab-link w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="far fa-clock w-5 mr-3"></i>Absensi
                        </button>
                        <button onclick="switchTab('notification', this)" class="tab-link w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="far fa-bell w-5 mr-3"></i>Notifikasi
                        </button>
                        <button onclick="switchTab('security', this)" class="tab-link w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="fas fa-shield-alt w-5 mr-3"></i>Keamanan
                        </button>
                        <button onclick="switchTab('backup', this)" class="tab-link w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="fas fa-database w-5 mr-3"></i>Backup
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div id="general" class="tab-content animate-in fade-in duration-300">
                    <form action="{{ route('settings.update.general') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="p-8">
                                <h3 class="text-lg font-bold text-slate-800 mb-6">Informasi Sekolah</h3>
                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Nama Sekolah</label>
                                        <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">NPSN</label>
                                        <input type="text" name="npsn" value="{{ old('npsn', $settings->npsn) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white outline-none transition-all">
                                    </div>
                                    <div class="xl:col-span-2 space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Alamat</label>
                                        <textarea name="address" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white outline-none transition-all">{{ old('address', $settings->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-sm transition-all active:scale-95">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="attendance" class="tab-content hidden animate-in fade-in duration-300">
                    <form action="{{ route('settings.update.attendance') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">Waktu Operasional</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                                    <p class="text-xs font-bold text-blue-600 uppercase mb-4">Sesi Masuk</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] text-slate-400 block mb-1">Jam Mulai</label>
                                            <input type="time" name="entry_time" 
                                                value="{{ date('H:i', strtotime($settings->entry_time)) }}" 
                                                step="60"
                                                class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-slate-400 block mb-1">Batas Telat</label>
                                            <input type="time" name="late_limit" 
                                                value="{{ date('H:i', strtotime($settings->late_limit)) }}" 
                                                step="60"
                                                class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 bg-orange-50/50 rounded-2xl border border-orange-100">
                                    <p class="text-xs font-bold text-orange-600 uppercase mb-4">Sesi Pulang</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] text-slate-400 block mb-1">Jam Pulang</label>
                                            <input type="time" name="exit_time" value="{{ $settings->exit_time }}" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-slate-400 block mb-1">Toleransi (Min)</label>
                                            <input type="number" name="tolerance_minutes" value="{{ $settings->tolerance_minutes }}" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-6 bg-slate-900 rounded-2xl text-white">
                                    <div>
                                        <p class="font-bold text-sm">Face Recognition AI</p>
                                        <p class="text-slate-400 text-xs">Validasi kehadiran berbasis wajah.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="face_rec_enabled" class="sr-only peer" {{ $settings->face_rec_enabled ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-6 bg-slate-900 rounded-2xl text-white">
                                    <div>
                                        <p class="font-bold text-sm">Upload File</p>
                                        <p class="text-slate-400 text-xs">Izinkan lampiran surat izin/sakit.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="upload_file_enabled" class="sr-only peer" {{ $settings->upload_file_enabled ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-slate-800 hover:bg-black text-white px-8 py-2.5 rounded-xl font-bold transition-all active:scale-95">Update Absensi</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="notification" class="tab-content hidden animate-in fade-in duration-300">
                    <form action="{{ route('settings.update.notification') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">Preferensi Notifikasi</h3>
                            <div class="space-y-4 mb-8">
                                @foreach([['notif_late', 'Terlambat', 'Kirim email saat siswa terlambat'], ['notif_absent', 'Tidak Hadir', 'Kirim email jika tidak ada keterangan']] as $n)
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div>
                                        <p class="text-sm font-bold text-slate-700">Notifikasi {{ $n[1] }}</p>
                                        <p class="text-xs text-slate-500">{{ $n[2] }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="{{ $n[0] }}" class="sr-only peer" {{ $settings->{$n[0]} ? 'checked' : '' }}>
                                        <div class="w-10 h-5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl font-bold">Simpan Notifikasi</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="security" class="tab-content hidden animate-in fade-in duration-300">
                    <form action="{{ route('settings.update.security') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                            <h3 class="text-lg font-bold text-slate-800 mb-6 text-center lg:text-left">Perbarui Kata Sandi</h3>
                            <div class="max-w-md mx-auto lg:mx-0 space-y-4">
                                <div>
                                    <input type="password" name="current_password" placeholder="Password Saat Ini" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20">
                                </div>
                                <div>
                                    <input type="password" name="new_password" placeholder="Password Baru" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20">
                                </div>
                                <div>
                                    <input type="password" name="new_password_confirmation" placeholder="Konfirmasi Password Baru" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20">
                                </div>
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold transition-all shadow-lg shadow-blue-100">Update Keamanan</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="backup" class="tab-content hidden animate-in fade-in duration-300">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 flex flex-col items-center text-center py-16">
                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-6 text-blue-600">
                            <i class="fas fa-database text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Backup Database</h3>
                        <p class="text-slate-500 max-w-sm mb-8 text-sm">Unduh cadangan data sistem EduFace saat ini dalam format SQL untuk keperluan restorasi di masa mendatang.</p>
                        <a href="{{ route('settings.backup') }}" class="bg-slate-900 hover:bg-black text-white px-10 py-3 rounded-xl font-bold transition-all flex items-center">
                            <i class="fas fa-file-export mr-3"></i> Export SQL Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    function switchTab(tabId, element) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        
        document.getElementById(tabId).classList.remove('hidden');
        
        document.querySelectorAll('.tab-link').forEach(l => {
            l.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
            l.classList.add('text-slate-600', 'hover:bg-slate-50');
        });
        
        element.classList.add('bg-blue-600', 'text-white', 'shadow-md');
        element.classList.remove('text-slate-600', 'hover:bg-slate-50');
    }
</script>
@endpush
@endsection
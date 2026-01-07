<?php $active_menu = 'settings'; ?>

@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="flex-1 flex flex-col bg-[#F8FAFC]">
    <main class="flex-1 overflow-y-auto p-6 lg:p-10">
        
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Konfigurasi Sistem</h1>
            <p class="text-slate-500 mt-2">Kelola parameter operasional dan keamanan platform Anda.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-80 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 p-2 sticky top-6">
                    <nav class="space-y-1">
                        <button onclick="switchTab('general', this)" class="tab-link active-tab w-full flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all bg-blue-600 text-white shadow-md shadow-blue-100">
                            <i class="fas fa-cog w-5 mr-3"></i>Umum
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
                            <i class="fas fa-database w-5 mr-3"></i>Backup & Restore
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
                                        <input type="text" name="school_name" value="{{ $settings->school_name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">NPSN</label>
                                        <input type="text" name="npsn" value="{{ $settings->npsn }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                    </div>
                                    <div class="xl:col-span-2 space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Alamat</label>
                                        <textarea name="address" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">{{ $settings->address }}</textarea>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Email</label>
                                        <input type="email" name="email" value="{{ $settings->email }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Telepon</label>
                                        <input type="text" name="phone" value="{{ $settings->phone }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                    </div>
                                </div>
                            </div>
                            <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold shadow-sm transition-all active:scale-95">Simpan Perubahan</button>
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
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-xs font-bold text-blue-500 uppercase mb-4">Sesi Masuk</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input type="time" name="entry_time" value="{{ $settings->entry_time }}" class="bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        <input type="time" name="late_limit" value="{{ $settings->late_limit }}" class="bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                    </div>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-xs font-bold text-orange-500 uppercase mb-4">Sesi Pulang</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input type="time" name="exit_time" value="{{ $settings->exit_time }}" class="bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                        <input type="number" name="tolerance_minutes" value="{{ $settings->tolerance_minutes }}" class="bg-white border border-slate-200 rounded-lg px-3 py-2 outline-none">
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-6 bg-slate-900 rounded-2xl text-white">
                                <div>
                                    <p class="font-bold">Face Recognition AI</p>
                                    <p class="text-slate-400 text-xs">Validasi kehadiran berbasis wajah.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="face_rec_enabled" class="sr-only peer" {{ $settings->face_rec_enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                </label>
                            </div>
                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-slate-800 hover:bg-black text-white px-6 py-2.5 rounded-xl font-semibold transition-all">Update Absensi</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="notification" class="tab-content hidden animate-in fade-in duration-300">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-4">
                        <h3 class="text-lg font-bold text-slate-800 mb-6">Email Notifikasi</h3>
                        @foreach([['notif_late', 'Terlambat'], ['notif_absent', 'Tidak Hadir']] as $n)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <span class="text-sm font-semibold text-slate-700">Notifikasi {{ $n[1] }}</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="{{ $n[0] }}" class="sr-only peer" {{ $settings->{$n[0]} ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div id="security" class="tab-content hidden animate-in fade-in duration-300">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-6">Ganti Password</h3>
                        <div class="max-w-md space-y-4">
                            <input type="password" placeholder="Password Saat Ini" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500">
                            <input type="password" placeholder="Password Baru" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500">
                            <button class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold">Perbarui Password</button>
                        </div>
                    </div>
                </div>

                <div id="backup" class="tab-content hidden animate-in fade-in duration-300">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Database Backup</h3>
                                <p class="text-slate-500 text-sm">Unduh salinan data sistem Anda (.sql)</p>
                            </div>
                            <button class="bg-white border border-slate-200 text-slate-700 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </button>
                        </div>
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
            l.classList.remove('active-tab', 'bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-100');
            l.classList.add('text-slate-600', 'hover:bg-slate-50');
        });
        element.classList.add('active-tab', 'bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-100');
        element.classList.remove('text-slate-600', 'hover:bg-slate-50');
    }
</script>
@endpush
@endsection
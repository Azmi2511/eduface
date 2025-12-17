<?php
$active_menu = 'settings';
?>

@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')
@section('header_title', 'Konfigurasi Sistem')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">

    <main class="flex-1 overflow-y-auto p-8">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Sidebar Tabs --}}
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden sticky top-8">
                    <nav class="flex flex-col py-3">
                        <a href="#" onclick="switchTab('general', this)"
                            class="tab-link active-tab flex items-center px-6 py-3.5 text-sm font-medium text-white bg-[#2F80ED] border-l-4 border-blue-800 transition-colors">
                            <i class="fas fa-sliders-h w-6 text-center mr-2"></i> Umum
                        </a>
                        <a href="#" onclick="switchTab('attendance', this)"
                            class="tab-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2F80ED] transition-colors">
                            <i class="far fa-clock w-6 text-center mr-2"></i> Absensi
                        </a>
                        <a href="#" onclick="switchTab('notification', this)"
                            class="tab-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2F80ED] transition-colors">
                            <i class="far fa-bell w-6 text-center mr-2"></i> Notifikasi
                        </a>
                        <a href="#" onclick="switchTab('security', this)"
                            class="tab-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2F80ED] transition-colors">
                            <i class="fas fa-shield-alt w-6 text-center mr-2"></i> Keamanan
                        </a>
                        <a href="#" onclick="switchTab('backup', this)"
                            class="tab-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2F80ED] transition-colors">
                            <i class="fas fa-cloud-upload-alt w-6 text-center mr-2"></i> Backup & Restore
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Tab Contents --}}
            <div class="w-full lg:w-3/4">

                {{-- Tab Umum --}}
                <div id="general" class="tab-content">
                    <form action="{{ route('settings.update.general') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="mb-6 border-b border-gray-100 pb-4">
                                <h3 class="text-lg font-bold text-gray-800">Informasi Sekolah</h3>
                                <p class="text-sm text-gray-500">Kelola informasi dasar sekolah atau institusi Anda</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Sekolah</label>
                                    <input type="text" name="school_name" value="{{ $settings->school_name }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">NPSN</label>
                                    <input type="text" name="npsn" value="{{ $settings->npsn }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                                <textarea name="address" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">{{ $settings->address }}</textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" value="{{ $settings->email }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                    <input type="text" name="phone" value="{{ $settings->phone }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#2F80ED] rounded-lg hover:bg-blue-600 transition shadow-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tab Absensi --}}
                <div id="attendance" class="tab-content hidden">
                    <form action="{{ route('settings.update.attendance') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="mb-6 border-b border-gray-100 pb-4">
                                <h3 class="text-lg font-bold text-gray-800">Pengaturan Jam Absensi</h3>
                                <p class="text-sm text-gray-500">Atur jam masuk, pulang, dan batas keterlambatan</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Masuk</label>
                                    <input type="time" name="entry_time" value="{{ $settings->entry_time }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Batas Keterlambatan</label>
                                    <input type="time" name="late_limit" value="{{ $settings->late_limit }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Pulang</label>
                                    <input type="time" name="exit_time" value="{{ $settings->exit_time }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Toleransi (Menit)</label>
                                    <input type="number" name="tolerance_minutes" value="{{ $settings->tolerance_minutes }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800">Aktifkan Face Recognition</h4>
                                    <p class="text-xs text-gray-500">Gunakan pengenalan wajah untuk absensi</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="face_rec_enabled" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $settings->face_rec_enabled ? 'checked' : '' }} />
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#2F80ED] rounded-lg hover:bg-blue-600 transition shadow-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tab Notifikasi --}}
                <div id="notification" class="tab-content hidden">
                    <form action="{{ route('settings.update.notification') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="mb-6 border-b border-gray-100 pb-4">
                                <h3 class="text-lg font-bold text-gray-800">Notifikasi</h3>
                                <p class="text-sm text-gray-500">Atur notifikasi yang dikirim melalui email</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800">Notifikasi Keterlambatan</h4>
                                    <p class="text-xs text-gray-500">Kirim email saat siswa terlambat</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="notif_late" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $settings->notif_late ? 'checked' : '' }} />
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800">Notifikasi Tidak Hadir</h4>
                                    <p class="text-xs text-gray-500">Kirim email saat siswa tidak hadir</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="notif_absent" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $settings->notif_absent ? 'checked' : '' }} />
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#2F80ED] rounded-lg hover:bg-blue-600 transition shadow-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tab Keamanan --}}
                <div id="security" class="tab-content hidden">
                    <form action="{{ route('settings.update.security') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="mb-6 border-b border-gray-100 pb-4">
                                <h3 class="text-lg font-bold text-gray-800">Ganti Password</h3>
                                <p class="text-sm text-gray-500">Perbarui kata sandi akun Anda secara berkala untuk keamanan</p>
                            </div>
                            <div class="max-w-md">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                                    <input type="password" name="current_password" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                    <input type="password" name="new_password" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 mt-4">
                                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#2F80ED] rounded-lg hover:bg-blue-600 transition shadow-sm">
                                    Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tab Backup --}}
                <div id="backup" class="tab-content hidden">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="mb-4 border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-bold text-gray-800">Backup Database</h3>
                            <p class="text-sm text-gray-500">Unduh salinan database sistem untuk keamanan data</p>
                        </div>
                        <div class="flex items-center justify-between bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-200 text-blue-600 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-database text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Download SQL Backup</h4>
                                    <p class="text-xs text-gray-500">Format file: .sql</p>
                                </div>
                            </div>
                            <form action="{{ route('settings.backup') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                                    <i class="fas fa-download mr-2"></i> Download
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Placeholder Restore (Belum fungsional penuh di Laravel standar tanpa library) --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="mb-4 border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-bold text-gray-800">Restore Database</h3>
                            <p class="text-sm text-gray-500">Pulihkan data sistem dari file backup sebelumnya</p>
                        </div>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-cloud-upload-alt text-4xl"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Drag & Drop file SQL di sini</h4>
                            <p class="text-xs text-gray-500 mb-4">atau klik untuk memilih file dari komputer</p>
                            <label class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition cursor-pointer">
                                Pilih File
                                <input type="file" class="hidden">
                            </label>
                        </div>
                        <p class="text-xs text-red-400 mt-2 italic">* Fitur restore memerlukan konfigurasi lanjutan server.</p>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    function switchTab(tabId, element) {
        // Hide all contents
        const contents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < contents.length; i++) {
            contents[i].classList.add('hidden');
        }

        // Show selected content
        document.getElementById(tabId).classList.remove('hidden');

        // Reset all link styles
        const links = document.getElementsByClassName('tab-link');
        for (let i = 0; i < links.length; i++) {
            links[i].classList.remove('active-tab', 'bg-[#2F80ED]', 'text-white', 'border-l-4', 'border-blue-800');
            links[i].classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-[#2F80ED]');
        }

        // Set active style to clicked element
        element.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-[#2F80ED]');
        element.classList.add('active-tab', 'bg-[#2F80ED]', 'text-white', 'border-l-4', 'border-blue-800');
    }
</script>
@endpush
@endsection
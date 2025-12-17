@php
$db_phone = '085213345165'; 
$db_email = 'eduface.atlas@gmail.com';
$wa_number = preg_replace('/[^0-9]/', '', $db_phone);
if (substr($wa_number, 0, 1) == '0') {
    $wa_number = '62' . substr($wa_number, 1);
}

// 3. Pesan Template WhatsApp (Optional)
$wa_message = urlencode("Halo Admin, saya butuh bantuan terkait aplikasi sistem sekolah.");
$wa_link = "https://wa.me/{$wa_number}?text={$wa_message}";

// 4. Link Email
$email_link = "mailto:{$db_email}?subject=Permintaan Bantuan Aplikasi";
@endphp

@extends('layouts.app')

@section('title', 'Bantuan & Dukungan')

@section('header_title', 'Bantuan & Dukungan')

@section('content')
    <div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
        <main class="flex-1 overflow-y-auto p-8">
            
            {{-- SECTION 1: SEARCH & HEADER --}}
            <div class="bg-white p-8 rounded-xl shadow-sm mb-8 text-center relative overflow-hidden">
                <div class="relative z-10 max-w-3xl mx-auto">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Bagaimana kami bisa membantu?</h2>
                    <p class="text-gray-500 mb-8 font-medium">Cari panduan, tutorial, dan jawaban atas pertanyaan umum.</p>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-lg group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        <input type="text" 
                            class="block w-full pl-14 pr-6 py-4 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 shadow-sm" 
                            placeholder="Ketik kata kunci (misal: 'lupa password', 'input nilai')..."
                        >
                    </div>
                </div>
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-50 opacity-50 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-purple-50 opacity-50 blur-3xl pointer-events-none"></div>
            </div>

            {{-- SECTION 2: TOPIC CARDS (Grid Style like Dashboard Stats) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="#" class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1 group border border-transparent hover:border-blue-100">
                    <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-500 flex items-center justify-center text-2xl mr-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Akun</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Profil & Password</p>
                    </div>
                </a>

                <a href="#" class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1 group border border-transparent hover:border-green-100">
                    <div class="w-14 h-14 rounded-xl bg-green-100 text-green-500 flex items-center justify-center text-2xl mr-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Akademik</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Nilai & Absensi</p>
                    </div>
                </a>

                <a href="#" class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1 group border border-transparent hover:border-purple-100">
                    <div class="w-14 h-14 rounded-xl bg-purple-100 text-purple-500 flex items-center justify-center text-2xl mr-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Keuangan</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Tagihan & SPP</p>
                    </div>
                </a>

                <a href="#" class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1 group border border-transparent hover:border-orange-100">
                    <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center text-2xl mr-4 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Keamanan</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Privasi Data</p>
                    </div>
                </a>
            </div>

            {{-- SECTION 3: FAQ & CONTACT (Grid Layout) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                {{-- FAQ Section (Takes 2 Columns) --}}
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-bold text-gray-800">Pertanyaan Umum (FAQ)</h3>
                        <span class="text-sm text-blue-500 font-semibold cursor-pointer hover:underline">Lihat Semua</span>
                    </div>

                    <div class="space-y-4">
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <details class="group">
                                <summary class="flex items-center justify-between p-4 cursor-pointer list-none bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                    <span class="font-semibold text-gray-700 text-sm">Bagaimana cara mereset password?</span>
                                    <span class="transition transform group-open:rotate-180 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </span>
                                </summary>
                                <div class="px-4 pb-4 pt-2 text-sm text-gray-500 leading-relaxed bg-white">
                                    Buka halaman Login dan klik link "Lupa Password". Masukkan email terdaftar Anda untuk menerima instruksi reset.
                                </div>
                            </details>
                        </div>

                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <details class="group">
                                <summary class="flex items-center justify-between p-4 cursor-pointer list-none bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                    <span class="font-semibold text-gray-700 text-sm">Mengapa data absensi saya tidak muncul?</span>
                                    <span class="transition transform group-open:rotate-180 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </span>
                                </summary>
                                <div class="px-4 pb-4 pt-2 text-sm text-gray-500 leading-relaxed bg-white">
                                    Data absensi diupdate secara <i>real-time</i> saat Anda melakukan fingerprint/scan wajah. Jika masih tidak muncul, hubungi admin TU.
                                </div>
                            </details>
                        </div>

                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <details class="group">
                                <summary class="flex items-center justify-between p-4 cursor-pointer list-none bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                    <span class="font-semibold text-gray-700 text-sm">Bagaimana cara ganti foto profil?</span>
                                    <span class="transition transform group-open:rotate-180 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </span>
                                </summary>
                                <div class="px-4 pb-4 pt-2 text-sm text-gray-500 leading-relaxed bg-white">
                                    Masuk ke menu <b>Pengaturan > Profil Saya</b>. Klik pada avatar Anda untuk mengunggah foto baru (Max 2MB).
                                </div>
                            </details>
                        </div>
                    </div>
                </div>

                {{-- Contact Section (Takes 1 Column) --}}
                <div class="flex flex-col gap-6">
                    <div class="bg-[#1e293b] p-6 rounded-xl shadow-sm text-white relative overflow-hidden flex-1 flex flex-col justify-center">
                        <div class="relative z-10">
                            <h3 class="text-xl font-bold mb-2">Butuh Bantuan?</h3>
                            <p class="text-gray-300 text-sm mb-6 leading-relaxed">
                                Tim support kami siap membantu Anda pada jam kerja (Senin - Jumat, 08:00 - 16:00).
                            </p>
                            
                            {{-- TOMBOL AKTIF --}}
                            <div class="space-y-3">
                                {{-- 1. Tombol WhatsApp --}}
                                <a href="{{ $wa_link }}" target="_blank" class="flex items-center justify-center w-full py-3 bg-white text-gray-900 font-bold rounded-xl text-sm hover:bg-green-50 hover:text-green-700 transition shadow-sm group">
                                    <i class="fab fa-whatsapp text-green-500 text-lg mr-2 group-hover:scale-110 transition-transform"></i> 
                                    Chat WhatsApp
                                </a>

                                {{-- 2. Tombol Email --}}
                                <a href="{{ $email_link }}" class="flex items-center justify-center w-full py-3 bg-gray-700 border border-gray-600 text-white font-bold rounded-xl text-sm hover:bg-gray-600 hover:border-gray-500 transition group">
                                    <i class="fas fa-envelope text-blue-400 text-lg mr-2 group-hover:scale-110 transition-transform"></i> 
                                    Kirim Email
                                </a>
                            </div>
                            
                            {{-- Info Kontak Text Kecil --}}
                            <div class="mt-6 pt-4 border-t border-gray-700 text-xs text-gray-400 flex flex-col gap-1">
                                <div class="flex items-center">
                                    <i class="fas fa-phone w-5 text-center mr-2"></i> {{ $db_phone }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-at w-5 text-center mr-2"></i> {{ $db_email }}
                                </div>
                            </div>

                        </div>
                        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 rounded-full bg-blue-500 opacity-10 blur-2xl"></div>
                        <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 rounded-full bg-purple-500 opacity-10 blur-2xl"></div>
                    </div>
                </div>

            </div>

        </main>
    </div>
@endsection
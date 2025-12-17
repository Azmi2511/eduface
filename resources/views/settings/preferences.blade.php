@extends('layouts.app')
@section('title', 'Preferensi Pengguna')
@section('header_title', 'Preferensi Pengguna')
@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <div class="flex-1 overflow-y-auto p-8">

        <form action="{{ route('settings.preferences.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- KOLOM KIRI (UTAMA) --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- CARD 1: VISUAL --}}
                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                        
                        {{-- Card Header --}}
                        <div class="px-8 py-6 border-b border-slate-50 flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mr-4">
                                <i class="fas fa-palette text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">Tampilan Visual</h3>
                        </div>

                        <div class="p-8 space-y-8">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Mode Tema</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="theme" value="light" class="peer sr-only" {{ Auth::user()->getPref('theme') == 'light' ? 'checked' : '' }}>
                                        <div class="border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/30 hover:border-blue-200">
                                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mb-3 text-orange-500 text-xl">
                                                <i class="fas fa-sun"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700">Terang</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="theme" value="dark" class="peer sr-only" {{ Auth::user()->getPref('theme') == 'dark' ? 'checked' : '' }}>
                                        <div class="border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center transition-all duration-200 peer-checked:border-slate-800 peer-checked:bg-slate-100 hover:border-slate-300">
                                            <div class="w-10 h-10 rounded-full bg-slate-800 shadow-sm flex items-center justify-center mb-3 text-white text-xl">
                                                <i class="fas fa-moon"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700">Gelap</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Warna Aksen</label>
                                <div class="flex flex-wrap gap-4">
                                    @foreach(['blue', 'indigo', 'emerald', 'rose', 'orange'] as $color)
                                        <label class="cursor-pointer relative">
                                            <input type="radio" name="accent_color" value="{{ $color }}" class="peer sr-only" {{ Auth::user()->getPref('accent_color', 'blue') == $color ? 'checked' : '' }}>
                                            <div class="w-14 h-14 rounded-2xl bg-{{ $color }}-500 hover:bg-{{ $color }}-400 shadow-sm transition-all duration-200 peer-checked:ring-4 peer-checked:ring-{{ $color }}-100 scale-100 peer-checked:scale-110 flex items-center justify-center text-white">
                                                @if(Auth::user()->getPref('accent_color', 'blue') == $color)
                                                    <i class="fas fa-check text-lg"></i>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-2">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Kepadatan Tabel</label>
                                    <div class="relative">
                                        <select name="layout_density" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none appearance-none font-medium">
                                            <option value="comfortable" {{ Auth::user()->getPref('layout_density') == 'comfortable' ? 'selected' : '' }}>Comfortable (Longgar)</option>
                                            <option value="compact" {{ Auth::user()->getPref('layout_density') == 'compact' ? 'selected' : '' }}>Compact (Padat)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Menu Sidebar</label>
                                    <div class="relative">
                                        <select name="sidebar_mode" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none appearance-none font-medium">
                                            <option value="fixed" {{ Auth::user()->getPref('sidebar_mode') == 'fixed' ? 'selected' : '' }}>Selalu Terbuka</option>
                                            <option value="collapsed" {{ Auth::user()->getPref('sidebar_mode') == 'collapsed' ? 'selected' : '' }}>Icon Saja</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: REGIONAL --}}
                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                        
                         {{-- Card Header --}}
                         <div class="px-8 py-6 border-b border-slate-50 flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-4">
                                <i class="fas fa-globe-asia text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">Regional & Waktu</h3>
                        </div>

                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                             <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Bahasa</label>
                                <div class="relative">
                                    <select name="locale" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none appearance-none font-medium">
                                        <option value="id" {{ Auth::user()->getPref('locale') == 'id' ? 'selected' : '' }}>🇮🇩 Bahasa Indonesia</option>
                                        <option value="en" {{ Auth::user()->getPref('locale') == 'en' ? 'selected' : '' }}>🇬🇧 English (US)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
    
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Format Tanggal</label>
                                <div class="relative">
                                    <select name="date_format" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none appearance-none font-medium">
                                        <option value="d/m/Y" {{ Auth::user()->getPref('date_format') == 'd/m/Y' ? 'selected' : '' }}>31/12/2023</option>
                                        <option value="Y-m-d" {{ Auth::user()->getPref('date_format') == 'Y-m-d' ? 'selected' : '' }}>2023-12-31</option>
                                        <option value="d M Y" {{ Auth::user()->getPref('date_format') == 'd M Y' ? 'selected' : '' }}>31 Des 2023</option>
                                        <option value="l, d F Y" {{ Auth::user()->getPref('date_format') == 'l, d F Y' ? 'selected' : '' }}>Senin, 31 Desember 2023</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                 <label class="block text-sm font-bold text-slate-700 mb-2">Zona Waktu</label>
                                 <div class="relative">
                                     <select name="timezone" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none appearance-none font-medium">
                                        <option value="Asia/Jakarta" {{ Auth::user()->getPref('timezone') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB - Jakarta (GMT+7)</option>
                                        <option value="Asia/Makassar" {{ Auth::user()->getPref('timezone') == 'Asia/Makassar' ? 'selected' : '' }}>WITA - Makassar (GMT+8)</option>
                                        <option value="Asia/Jayapura" {{ Auth::user()->getPref('timezone') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT - Jayapura (GMT+9)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN (NOTIFIKASI) --}}
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-100 sticky top-8 overflow-hidden">
                        
                        {{-- Card Header --}}
                        <div class="px-8 py-6 border-b border-slate-50 flex items-center bg-rose-50/30">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 mr-4">
                                <i class="fas fa-bell text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">Notifikasi</h3>
                        </div>
                        
                        <div class="p-8">
                            <p class="text-slate-500 mb-8 leading-relaxed font-light text-sm">
                                Atur notifikasi mana saja yang penting bagi Anda untuk diterima via email atau aplikasi.
                            </p>

                            <div class="space-y-6">
                                @php
                                    $toggles = [
                                        ['key' => 'notify_grades', 'label' => 'Update Nilai', 'desc' => 'Info nilai baru'],
                                        ['key' => 'notify_attendance', 'label' => 'Absensi Harian', 'desc' => 'Laporan kehadiran'],
                                        ['key' => 'notify_announcements', 'label' => 'Pengumuman', 'desc' => 'Berita sekolah', 'default' => 1],
                                    ];
                                @endphp

                                @foreach($toggles as $toggle)
                                <div class="flex items-center justify-between group">
                                    <div class="pr-4">
                                        <span class="block text-base font-bold text-slate-800 group-hover:text-blue-600 transition-colors">{{ $toggle['label'] }}</span>
                                        <span class="text-sm text-slate-400">{{ $toggle['desc'] }}</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="{{ $toggle['key'] }}" value="1" class="sr-only peer" {{ Auth::user()->getPref($toggle['key'], $toggle['default'] ?? 0) ? 'checked' : '' }}>
                                        <div class="w-12 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all after:shadow-sm peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                @if(!$loop->last) <hr class="border-slate-100"> @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FLOATING SAVE BUTTON --}}
            <div class="fixed bottom-10 right-10 z-50">
                <button type="submit" class="flex items-center px-8 py-4 bg-slate-900 text-white font-bold text-lg rounded-full shadow-2xl hover:shadow-xl hover:bg-black hover:scale-105 transition-all duration-300">
                    <i class="fas fa-save mr-3"></i> 
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
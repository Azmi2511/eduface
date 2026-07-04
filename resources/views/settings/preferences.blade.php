@extends('layouts.app')
@section('title', 'Preferensi Pengguna')
@section('header_title', 'Preferensi Pengguna')
@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <div class="flex-1 overflow-y-auto p-8">

        <form action="{{ route('settings.preferences.update') }}" method="POST" class="max-w-3xl mx-auto">
            @csrf
            
            <div class="bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                
                {{-- Card Header --}}
                <div class="px-8 py-6 border-b border-slate-50 flex items-center bg-blue-50/10">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mr-4">
                        <i class="fas fa-sliders-h text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Preferensi Aplikasi</h3>
                        <p class="text-xs text-slate-400 mt-1">Personalisasikan tema tampilan dan bahasa aplikasi Anda</p>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    {{-- Tema --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-3 uppercase tracking-wider">Mode Tema</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" name="theme" value="light" class="peer sr-only" {{ Auth::user()->getPref('theme', 'light') == 'light' ? 'checked' : '' }}>
                                <div class="border-2 border-slate-100 rounded-2xl p-5 flex flex-col items-center justify-center transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/30 hover:border-blue-200">
                                    <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center mb-3 text-orange-500 text-xl border border-slate-100">
                                        <i class="fas fa-sun"></i>
                                    </div>
                                    <span class="font-bold text-slate-700">Terang</span>
                                    <span class="text-xs text-slate-400 mt-1">Default bersih dan cerah</span>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer group">
                                <input type="radio" name="theme" value="dark" class="peer sr-only" {{ Auth::user()->getPref('theme') == 'dark' ? 'checked' : '' }}>
                                <div class="border-2 border-slate-100 rounded-2xl p-5 flex flex-col items-center justify-center transition-all duration-200 peer-checked:border-slate-800 peer-checked:bg-slate-50 hover:border-slate-300">
                                    <div class="w-12 h-12 rounded-full bg-slate-800 shadow-sm flex items-center justify-center mb-3 text-white text-xl">
                                        <i class="fas fa-moon"></i>
                                    </div>
                                    <span class="font-bold text-slate-700">Gelap</span>
                                    <span class="text-xs text-slate-400 mt-1">Mengurangi ketegangan mata</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Bahasa --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">Bahasa Aplikasi</label>
                        <div class="relative max-w-md">
                            <select name="locale" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none appearance-none font-medium">
                                <option value="id" {{ Auth::user()->getPref('locale', 'id') == 'id' ? 'selected' : '' }}>🇮🇩 Bahasa Indonesia</option>
                                <option value="en" {{ Auth::user()->getPref('locale') == 'en' ? 'selected' : '' }}>🇬🇧 English (US)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="flex items-center px-6 py-3 bg-slate-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition-all">
                        <i class="fas fa-save mr-2"></i> 
                        <span>Simpan Perubahan</span>
                    </button>
                </div>

            </div>

        </form>
    </div>
</div>
@endsection
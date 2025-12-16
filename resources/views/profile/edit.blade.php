@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header_title', 'Profil Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3 text-lg"></i>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 text-lg"></i>
                <p class="text-sm font-bold text-red-700">Terdapat kesalahan:</p>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 ml-8">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: KARTU PROFIL --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                {{-- Banner Background --}}
                <div class="h-32 bg-gradient-to-r from-blue-600 to-blue-400"></div>
                
                <div class="px-6 pb-8 relative text-center">
                    {{-- Avatar --}}
                    <div class="relative -mt-16 inline-block">
                        <div class="w-32 h-32 rounded-full border-4 border-white bg-white shadow-md flex items-center justify-center overflow-hidden">
                            <div class="w-full h-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-4xl">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->full_name }}">
                                @else
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-indigo-600 bg-indigo-100 border border-indigo-200 rounded-full shadow-sm">
                                        {{ substr($user->full_name, 0, 2) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        {{-- Tombol Edit Foto (Hiasan dulu) --}}
                        <button class="absolute bottom-2 right-2 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700 transition shadow-sm" title="Ubah Foto">
                            <i class="fas fa-camera text-xs"></i>
                        </button>
                    </div>

                    <h2 class="mt-4 text-xl font-bold text-gray-800">{{ $user->full_name }}</h2>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide mb-4">
                        {{ $user->role == 'admin' ? 'Administrator' : ucfirst($user->role) }}
                    </p>

                    <div class="flex justify-center space-x-2 mb-6">
                        <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs rounded-full font-medium border border-blue-100">
                            {{ $user->email }}
                        </span>
                    </div>

                    <hr class="border-gray-100 mb-6">

                    <div class="text-left space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-calendar-alt w-6 text-center text-gray-400 mr-3"></i>
                            <span>Bergabung sejak <b>{{ $user->created_at->format('d M Y') }}</b></span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-clock w-6 text-center text-gray-400 mr-3"></i>
                            <span>Status: <span class="text-green-600 font-bold">Aktif</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM EDIT --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- 1. Form Informasi Dasar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Informasi Pribadi</h3>
                    <span class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                        <i class="far fa-id-card"></i>
                    </span>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('patch')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="far fa-user"></i>
                                </span>
                                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="far fa-envelope"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                        </div> 
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg transition shadow-md flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Form Ganti Password --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Keamanan (Ganti Password)</h3>
                    <span class="bg-yellow-50 text-yellow-600 p-2 rounded-lg">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('put')

                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                            <input type="password" name="current_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" placeholder="Masukkan password lama">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" placeholder="Minimal 8 karakter">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-gray-800 hover:bg-black text-white font-medium py-2.5 px-6 rounded-lg transition shadow-md flex items-center">
                            <i class="fas fa-key mr-2"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
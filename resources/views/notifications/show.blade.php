@extends('layouts.app')

@section('title', 'Detail Notifikasi')
@section('header_title', 'Detail Notifikasi')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8">
        
        <div class="mb-6">
            <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 transition font-semibold text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 mr-4">
                            <i class="fas fa-bell text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Detail Pesan</h3>
                            <p class="text-[11px] text-gray-400 uppercase tracking-widest font-bold">
                                {{ $notification->created_at->translatedFormat('d F Y • H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="prose max-w-none text-gray-700 leading-relaxed text-lg font-medium">
                        {{ $notification->message }}
                    </div>
                </div>

                <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-50 flex justify-between items-center text-[11px] text-gray-400 font-bold uppercase tracking-wider">
                    <span>ID Notifikasi: #{{ $notification->id }}</span>
                    <span class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-1.5"></i>
                        Status: <span class="text-gray-600 ml-1">Sudah Dibaca</span>
                    </span>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold text-red-400 hover:text-red-600 transition flex items-center bg-white px-4 py-2 rounded-lg border border-red-50 shadow-sm">
                        <i class="fas fa-trash-alt mr-2"></i> Hapus Notifikasi Ini
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
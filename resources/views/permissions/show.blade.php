@extends('layouts.app')

@section('title', 'Detail Izin')
@section('header_title', 'Detail Pengajuan Izin')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8">
        
        {{-- Tombol Kembali --}}
        <div class="mb-6">
            <a href="{{ route('permissions.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Kolom Kiri: Informasi Detail --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Informasi Pengajuan</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-bold 
                            {{ $permit->approval_status == 'Approved' ? 'bg-green-100 text-green-700' : ($permit->approval_status == 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ $permit->approval_status }}
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Nama Siswa</label>
                                <p class="text-sm font-bold text-gray-800">{{ $permit->student->user->full_name }}</p>
                                <p class="text-xs text-gray-500 italic">NISN: {{ $permit->student->nisn }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Diajukan Oleh</label>
                                <p class="text-sm font-bold text-gray-800">{{ $permit->parent->user->full_name }}</p>
                                <p class="text-xs text-gray-500 italic">Hubungan: {{ $permit->parent->relationship }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Tipe Izin</label>
                                <p class="text-sm font-bold {{ $permit->type == 'Sakit' ? 'text-red-600' : 'text-blue-600' }}">
                                    <i class="fas {{ $permit->type == 'Sakit' ? 'fa-hand-holding-medical' : 'fa-envelope-open-text' }} mr-1"></i>
                                    {{ $permit->type }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Rentang Tanggal</label>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($permit->start_date)->format('d M Y') }} 
                                    <span class="text-gray-400 font-normal mx-1">s/d</span> 
                                    {{ \Carbon\Carbon::parse($permit->end_date)->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Alasan / Keterangan</label>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-gray-700 text-sm leading-relaxed">
                                {{ $permit->description }}
                            </div>
                        </div>

                        @if($permit->approval_status != 'Pending')
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Diverifikasi Oleh</label>
                            <div class="flex items-center mt-2 text-sm text-gray-600">
                                <i class="fas fa-user-check mr-2 text-blue-500"></i>
                                <span>{{ $permit->approvedBy->full_name ?? 'Sistem' }} pada {{ $permit->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Bukti Lampiran & Aksi --}}
            <div class="space-y-6">
                
                {{-- Card Bukti --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-paperclip mr-2 text-blue-500"></i> Lampiran Bukti
                    </h3>
                    
                    @if($permit->proof_file_path)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $permit->proof_file_path) }}" 
                                 alt="Bukti Izin" 
                                 class="w-full h-auto rounded-lg border border-gray-200 shadow-sm">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center rounded-lg">
                                <a href="{{ asset('storage/' . $permit->proof_file_path) }}" target="_blank" class="bg-white text-gray-800 px-4 py-2 rounded-lg text-sm font-bold shadow-lg">
                                    <i class="fas fa-search-plus mr-1"></i> Perbesar
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                            <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                            <p class="text-xs text-gray-400">Tidak ada lampiran gambar</p>
                        </div>
                    @endif
                </div>

                {{-- Card Aksi (Hanya muncul untuk Teacher/Admin jika status Pending) --}}
                @if(auth()->user()->role !== 'parent' && $permit->approval_status == 'Pending')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Verifikasi Izin</h3>
                        <p class="text-xs text-gray-500 mb-4">Pastikan data dan bukti sudah sesuai sebelum memberikan keputusan.</p>
                        
                        <div class="flex flex-col gap-3">
                            <form action="{{ route('permissions.updateStatus', $permit->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Approved">
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-lg transition shadow-md flex items-center justify-center">
                                    <i class="fas fa-check-circle mr-2"></i> Setujui Izin
                                </button>
                            </form>

                            <form action="{{ route('permissions.updateStatus', $permit->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" class="w-full bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 rounded-lg transition flex items-center justify-center">
                                    <i class="fas fa-times-circle mr-2"></i> Tolak Pengajuan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>
@endsection
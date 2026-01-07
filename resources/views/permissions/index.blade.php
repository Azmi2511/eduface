<?php
$active_menu = 'permissions';
?>

@extends('layouts.app')

@section('title', 'Manajemen Izin')
@section('header_title', 'Manajemen Izin Siswa')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8 bg-[#F8FAFC]">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">Manajemen Izin & Sakit</h2>
                <p class="text-sm text-gray-500 mt-1">Pantau dan kelola pengajuan ketidakhadiran siswa secara real-time.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-14 h-14 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $pending_count ?? 0 }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Menunggu Persetujuan</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $permissions->total() }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Pengajuan</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-14 h-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $permissions->where('approval_status', 'Approved')->count() }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Izin Disetujui</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
            <form method="GET" action="{{ route('permissions.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Cari Data Izin</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama siswa, NISN, atau alasan..."
                            class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-sm transition-all">
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full bg-slate-800 hover:bg-black text-white font-bold py-2.5 px-8 rounded-xl transition duration-200 shadow-lg shadow-gray-200">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-50 bg-white">
                <h3 class="font-bold text-gray-800">Riwayat Pengajuan Siswa</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-400 text-[11px] uppercase font-bold tracking-widest">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">Tipe & Keterangan</th>
                            <th class="px-6 py-4">Rentang Tanggal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($permissions as $permit)
                            <tr class="hover:bg-indigo-50/30 transition duration-150">
                                <td class="px-6 py-5 text-sm text-center text-gray-400">
                                    {{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10 mr-3 rounded-xl bg-gray-100 overflow-hidden border border-gray-100 shadow-sm">
                                            <img src="{{ $permit->student->user->profile_picture ? asset('storage/'.$permit->student->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($permit->student->user->full_name).'&background=6366f1&color=fff' }}" alt="">
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">{{ $permit->student->user->full_name }}</div>
                                            <div class="text-[11px] text-gray-400 font-medium">NISN: {{ $permit->student->nisn }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold {{ $permit->type == 'Sakit' ? 'text-rose-600' : 'text-indigo-600' }}">
                                            {{ $permit->type }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 italic truncate max-w-[180px]">{{ $permit->description }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-700">
                                            {{ \Carbon\Carbon::parse($permit->start_date)->translatedFormat('d M Y') }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            s/d {{ \Carbon\Carbon::parse($permit->end_date)->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusClasses = [
                                            'Approved' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'Rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                            'Pending'  => 'bg-amber-50 text-amber-600 border-amber-100'
                                        ];
                                        $statusLabels = ['Approved' => 'Disetujui', 'Rejected' => 'Ditolak', 'Pending' => 'Pending'];
                                        $currentStatus = $permit->approval_status;
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-bold tracking-wide uppercase border {{ $statusClasses[$currentStatus] ?? $statusClasses['Pending'] }}">
                                        {{ $statusLabels[$currentStatus] ?? 'Pending' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('permissions.show', $permit->id) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition shadow-sm">
                                        <i class="fas fa-search-plus mr-2"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                            <i class="fas fa-file-signature text-3xl text-gray-200"></i>
                                        </div>
                                        <h4 class="text-lg font-bold text-gray-800">Tidak Ada Data Izin</h4>
                                        <p class="text-sm text-gray-400 mt-1">Belum ada pengajuan izin yang masuk saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($permissions->hasPages())
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-50 bg-gray-50/30">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Halaman {{ $permissions->currentPage() }} dari {{ $permissions->lastPage() }}
                    </div>
                    <div class="flex space-x-2">
                        @if ($permissions->onFirstPage())
                            <span class="px-4 py-2 text-xs font-bold text-gray-300 bg-white border border-gray-100 rounded-xl cursor-not-allowed">Prev</span>
                        @else
                            <a href="{{ $permissions->appends(request()->query())->previousPageUrl() }}" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm">Prev</a>
                        @endif

                        @if ($permissions->hasMorePages())
                            <a href="{{ $permissions->appends(request()->query())->nextPageUrl() }}" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm">Next</a>
                        @else
                            <span class="px-4 py-2 text-xs font-bold text-gray-300 bg-white border border-gray-100 rounded-xl cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
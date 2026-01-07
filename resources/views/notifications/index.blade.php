@php
$active = 'notifications';
@endphp

@extends('layouts.app')

@section('title', 'Notifikasi Saya')
@section('header_title', 'Notifikasi Saya')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Pusat Notifikasi</h2>
                <p class="text-sm text-gray-500">Pantau semua aktivitas dan informasi terbaru Anda.</p>
            </div>
            @if($total_unread > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-blue-200 text-blue-600 rounded-xl hover:bg-blue-50 transition text-sm font-semibold shadow-sm">
                    <i class="fas fa-check-double mr-2"></i> Tandai Semua Dibaca
                </button>
            </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_unread ?? 0) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Pesan Belum Dibaca</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_data ?? 0) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Riwayat</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 border border-gray-100">
            <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Cari Notifikasi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari kata kunci pesan..."
                            class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm transition-all">
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-xl transition duration-200 shadow-lg shadow-blue-200">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Daftar Notifikasi</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-400 text-[11px] uppercase font-bold tracking-widest">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Informasi Pesan</th>
                            <th class="px-6 py-4">Waktu Terima</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($notifications as $notif)
                            <tr class="hover:bg-blue-50/30 transition duration-150 {{ $notif->is_read == 0 ? 'bg-blue-50/20 font-medium' : '' }}">
                                <td class="px-6 py-5 text-sm text-center text-gray-400">
                                    {{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10 mr-3 rounded-xl flex items-center justify-center 
                                            {{ $notif->is_read == 0 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400' }}">
                                            <i class="{{ $notif->is_read == 0 ? 'fas fa-envelope' : 'fas fa-envelope-open' }}"></i>
                                        </div>
                                        <div class="max-w-md truncate text-sm {{ $notif->is_read == 0 ? 'text-gray-900 font-bold' : 'text-gray-600' }}">
                                            {{ Str::limit($notif->message, 80) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-sm text-gray-500">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-700">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d M Y') }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-5 text-center">
                                    @if($notif->is_read == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            Sudah Dibaca
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-600 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5 animate-pulse"></span>
                                            Baru
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('notifications.show', $notif->id) }}"
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition shadow-sm">
                                        <i class="fas fa-search-plus mr-2"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-bell-slash text-3xl text-gray-200"></i>
                                        </div>
                                        <h4 class="text-lg font-bold text-gray-800">Kotak Masuk Kosong</h4>
                                        <p class="text-sm text-gray-400 mt-1">Anda belum menerima notifikasi apapun saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->lastPage() > 1)
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-50 bg-gray-50/30">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Hal {{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}
                    </div>

                    <div class="flex space-x-2">
                        @if ($notifications->onFirstPage())
                            <span class="px-4 py-2 text-xs font-bold text-gray-300 bg-white border border-gray-100 rounded-xl cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $notifications->appends(request()->query())->previousPageUrl() }}" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-blue-600 hover:text-white hover:border-blue-600 transition shadow-sm">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        @if ($notifications->hasMorePages())
                            <a href="{{ $notifications->appends(request()->query())->nextPageUrl() }}" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-blue-600 hover:text-white hover:border-blue-600 transition shadow-sm">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="px-4 py-2 text-xs font-bold text-gray-300 bg-white border border-gray-100 rounded-xl cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
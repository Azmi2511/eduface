@extends('layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8">

        {{-- 1. Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Card Unread --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-message"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_unread) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Pesan Belum Dibaca</p>
                </div>
            </div>

            {{-- Card Total --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_data) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Notifikasi</p>
                </div>
            </div>
        </div>

        {{-- 2. Search Form --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Notifikasi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari berdasarkan isi pesan..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                </div>
                <div>
                    <button type="submit"
                        class="bg-[#2F80ED] hover:bg-blue-600 text-white font-medium py-2.5 px-6 rounded-lg transition duration-200">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Data Table --}}
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Riwayat Pesan Masuk</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Isi Pesan</th>
                            <th class="px-6 py-4">Waktu Diterima</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($notifications as $notif)
                            {{-- Row Class: Jika belum dibaca (0), beri background biru tipis --}}
                            <tr class="hover:bg-gray-50 transition duration-150 {{ $notif->is_read == 0 ? 'bg-blue-50/30' : '' }}">
                                
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 mr-3 rounded-full flex items-center justify-center 
                                            {{ $notif->is_read == 0 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400' }}">
                                            <i class="{{ $notif->is_read == 0 ? 'fas fa-envelope' : 'fas fa-envelope-open' }}"></i>
                                        </div>
                                        <div class="text-sm text-gray-700 font-medium">
                                            {{ Str::limit($notif->message, 60) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col">
                                        {{-- Asumsi kolom created_at sudah dicast ke datetime di Model --}}
                                        <span class="font-medium text-gray-700">{{ $notif->created_at->format('d M Y') }}</span>
                                        <span class="text-xs">{{ $notif->created_at->format('H:i') }} WIB</span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    @if($notif->is_read == 1)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-semibold border border-gray-200">
                                            Sudah Dibaca
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold border border-blue-200 animate-pulse">
                                            Baru
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    {{-- Menggunakan helper url() jika link di DB adalah string path --}}
                                    <a href="{{ url($notif->link) }}" 
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition shadow-sm">
                                        <i class="far fa-eye mr-2"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="far fa-bell-slash text-2xl text-gray-400"></i>
                                        </div>
                                        <h4 class="text-lg font-medium text-gray-900">Belum ada notifikasi</h4>
                                        <p class="text-sm text-gray-500 mt-1">Semua informasi terbaru akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 4. Pagination --}}
            @if($notifications->lastPage() > 1)
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="text-sm text-gray-500">
                        Halaman <span class="font-medium">{{ $notifications->currentPage() }}</span> dari <span class="font-medium">{{ $notifications->lastPage() }}</span>
                    </div>

                    <div class="flex space-x-2">
                        {{-- Previous Button --}}
                        @if ($notifications->onFirstPage())
                            <span class="px-3 py-1.5 text-sm font-medium text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i> Prev
                            </span>
                        @else
                            <a href="{{ $notifications->previousPageUrl() }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                                <i class="fas fa-chevron-left mr-1"></i> Prev
                            </a>
                        @endif

                        {{-- Next Button --}}
                        @if ($notifications->hasMorePages())
                            <a href="{{ $notifications->nextPageUrl() }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        @else
                            <span class="px-3 py-1.5 text-sm font-medium text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </main>
</div>
@endsection
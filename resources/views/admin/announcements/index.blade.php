<?php
$active_menu = 'announcements';
?>

@extends('layouts.app')

@section('title', 'Pengumuman')
@section('header_title', 'Pengumuman')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">

    <main class="flex-1 overflow-y-auto p-8">

        {{-- Flash Message Helper (Jika belum ada di layout) --}}
        @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- Header Card --}}
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-800">Riwayat Pengumuman Terbaru</h3>
                <button onclick="toggleModal('addAnnModal')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center cursor-pointer shadow-lg shadow-green-500/30">
                    <i class="fas fa-plus mr-2"></i> Pengumuman Baru
                </button>
            </div>
            
            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">Pesan</th>
                            <th class="px-6 py-4">Berkas</th>
                            <th class="px-6 py-4">Link</th>
                            <th class="px-6 py-4">Pengiriman</th>
                            <th class="px-6 py-4">Penerima</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($announcements as $announcement)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($announcements->currentPage() - 1) * $announcements->perPage() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 line-clamp-2">{{ $announcement->message }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($announcement->attachment_file)
                                        @php
                                            $ext = strtoupper(pathinfo($announcement->attachment_file, PATHINFO_EXTENSION));
                                            $badgeColor = match($ext) {
                                                'PDF' => 'bg-red-50 text-red-600 border-red-100',
                                                'JPG', 'JPEG', 'PNG' => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'DOC', 'DOCX' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                'XLS', 'XLSX' => 'bg-green-50 text-green-600 border-green-100',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                            $parts = explode('_', $announcement->attachment_file, 2);
                                            $realName = isset($parts[1]) ? $parts[1] : $announcement->attachment_file;
                                            $displayName = strlen($realName) > 15 ? substr($realName, 0, 10) . '...' . strtolower($ext) : $realName;
                                        @endphp

                                        <a href="{{ asset('uploads/' . $announcement->attachment_file) }}" target="_blank" title="{{ $realName }}" class="group flex items-center w-fit">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg border {{ $badgeColor }} mr-2 group-hover:scale-105 transition-transform">
                                                <span class="text-[10px] font-bold">{{ $ext }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 group-hover:underline transition">
                                                    {{ $displayName }}
                                                </span>
                                            </div>
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400 opacity-50">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($announcement->attachment_link)
                                        <a href="{{ $announcement->attachment_link }}" target="_blank" class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition">
                                            <i class="fas fa-external-link-alt mr-2"></i>
                                            <span class="truncate max-w-[150px]">Link</span>
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($announcement->sent_at)->translatedFormat('d M Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($announcement->sent_at)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $labels = [
                                            'all'     => 'Semua Pengguna',
                                            'student' => 'Semua Siswa',
                                            'teacher' => 'Semua Guru',
                                            'parent'  => 'Semua Orang Tua',
                                        ];

                                        // AMBIL DATA
                                        $rawType = $announcement->getRawOriginal('recipient'); 
                                        $recipId = $announcement->recipient_id; // Cek kolom ID

                                        $isSpecific = ($rawType === 'specific' || !empty($recipId));

                                        // Variable Default
                                        $displayName = '-';
                                        $displayEmail = null;
                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                        $icon = 'fas fa-users';

                                        if ($isSpecific) {
                                            $targetUser = $announcement->recipient; 

                                            if ($targetUser) {
                                                $displayName = $targetUser->full_name;
                                                $displayEmail = $targetUser->email;
                                                $badgeColor = 'bg-blue-100 text-blue-800 border border-blue-200';
                                                $icon = 'fas fa-user';
                                            } else {
                                                $displayName = 'User Tidak Ditemukan';
                                                $displayEmail = '(ID: ' . $recipId . ')';
                                                $badgeColor = 'bg-red-50 text-red-600 border border-red-200';
                                                $icon = 'fas fa-user-slash';
                                            }
                                        } else {
                                            $safeType = $rawType ?? 'all';
                                            $displayName = $labels[$safeType] ?? ucfirst($safeType);
                                            if (trim($displayName) == '') $displayName = 'Semua Pengguna';
                                        }
                                    @endphp

                                    <div class="flex flex-col items-start justify-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                            <i class="{{ $icon }} mr-1.5 text-[10px]"></i>
                                            <span class="truncate max-w-[120px]" title="{{ $displayName }}">
                                                {{ $displayName }}
                                            </span>
                                        </span>

                                        @if($isSpecific && $displayEmail)
                                            <span class="text-[10px] text-gray-500 mt-1 ml-1 truncate max-w-[140px]" title="{{ $displayEmail }}">
                                                {{ $displayEmail }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <button onclick="openViewModal(@js($announcement), @js($realName ?? null))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-blue-600 transition" title="Lihat Detail">
                                            <i class="far fa-eye"></i>
                                        </button>

                                        <button onclick="openEditModal(@js($announcement), '{{ route('announcements.update', $announcement->id) }}', @js($realName ?? null))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-yellow-600 transition" title="Edit Pesan">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 transition" title="Hapus">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="far fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                        <p>Tidak ada pengumuman ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($announcements->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    {{ $announcements->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- MODALS SECTION --}}

    {{-- 1. ADD MODAL --}}
   <div id="addAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-bullhorn text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Buat Pengumuman Baru</h3>
                </div>
                <button onclick="toggleModal('addAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1.5 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" id="announcementForm">
                    @csrf
                    <div class="space-y-5">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Target Penerima</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    <select name="recipient" id="recipientSelect" onchange="toggleUserSelect(this.value)" required 
                                        class="w-full pl-10 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm font-medium text-gray-700 appearance-none cursor-pointer">
                                        <option value="" disabled selected>Pilih Target...</option>
                                        <option value="all">Semua Pengguna</option>
                                        <option value="teacher">Guru</option>
                                        <option value="student">Siswa</option>
                                        <option value="parent">Orang Tua</option>
                                        <option value="specific">Perorangan (Spesifik)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jadwal Publikasi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="far fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input type="datetime-local" name="datetime_send" value="{{ date('Y-m-d\TH:i') }}" 
                                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm font-medium text-gray-700 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div id="user_select_container" class="hidden transition-all duration-300 ease-in-out">
                            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                <label class="block text-xs font-bold text-blue-700 uppercase tracking-wider mb-2">Pilih User Spesifik</label>
                                <div class="relative">
                                    <select name="user_id" id="userSpecificSelect" class="w-full pl-4 pr-8 py-2.5 bg-white border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="" disabled selected>-- Cari Nama User --</option>
                                        @foreach($allUsers as $u)
                                            @php
                                                $identity = '';
                                                $role = '';
                                                if($u->role == 'student' && $u->nisn) {
                                                    $identity = '- '.$u->nisn;
                                                    $role = 'Siswa';
                                                } elseif($u->role == 'teacher' && $u->nip) {
                                                    $identity = '- '.$u->nip;
                                                    $role = 'Guru';
                                                }else {
                                                    $role = 'Wali';
                                                }
                                            @endphp
                                            <option value="{{ $u->id }}">
                                                {{ $u->full_name }} 
                                                ({{ ucfirst($role) }} {{ $identity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-blue-400 text-xs"></i>
                                    </div>
                                </div>
                                <p class="text-[10px] text-blue-500 mt-1 italic">*Pengumuman hanya akan dikirim ke user ini.</p>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Isi Pesan</label>
                            <textarea name="message" rows="5" required 
                                class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-gray-700 resize-none leading-relaxed placeholder-gray-400" 
                                placeholder="Ketik isi pengumuman lengkap di sini..."></textarea>
                        </div>

                        <div class="rounded-xl border-2 border-dashed border-gray-200 p-5 hover:border-blue-400 hover:bg-blue-50/30 transition-colors group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded border border-gray-200">OPSIONAL</span>
                                    <span class="text-sm font-semibold text-gray-600 group-hover:text-blue-600 transition">Lampiran</span>
                                </div>
                                <i class="fas fa-paperclip text-gray-300 group-hover:text-blue-400 transition"></i>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Dokumen (PDF/Img Max 2MB)</label>
                                    <input type="file" name="attachment_file" 
                                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition bg-white border border-gray-200 rounded-lg cursor-pointer p-1">
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Tautan Eksternal (URL)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-link text-gray-400 text-xs"></i>
                                        </div>
                                        <input type="url" name="attachment_link" placeholder="https://example.com" 
                                            class="w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-xs text-gray-700">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="flex justify-end pt-6 mt-2">
                        <button type="button" onclick="toggleModal('addAnnModal')" class="px-5 py-2.5 mr-3 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex items-center">
                            <i class="fas fa-paper-plane mr-2 text-xs"></i> Publikasikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-600 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Pengumuman</h3>
                </div>
                <button onclick="toggleModal('editAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1.5 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar">
                {{-- Form ID & Action akan di-handle via JS --}}
                <form id="editAnnForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-5">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Target Penerima</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400 group-focus-within:text-orange-500 transition"></i>
                                    </div>
                                    <select name="recipient" id="edit_recipient" onchange="toggleEditUserSelect(this.value)" class="w-full pl-10 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-sm font-medium text-gray-700 appearance-none cursor-pointer">
                                        <option value="all">Semua Pengguna</option>
                                        <option value="teacher">Guru</option>
                                        <option value="student">Siswa</option>
                                        <option value="parent">Orang Tua</option>
                                        <option value="specific">Perorangan (Spesifik)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jadwal Publikasi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="far fa-calendar-alt text-gray-400 group-focus-within:text-orange-500 transition"></i>
                                    </div>
                                    <input type="datetime-local" name="datetime_send" id="edit_datetime" required class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-sm font-medium text-gray-700 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div id="edit_user_select_container" class="hidden transition-all duration-300">
                            <div class="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                                <label class="block text-xs font-bold text-orange-700 uppercase tracking-wider mb-2">Pilih User Spesifik</label>
                                <div class="relative">
                                    <select name="user_id" id="edit_user_id" class="w-full pl-4 pr-8 py-2.5 bg-white border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                                        @foreach($allUsers as $u)
                                            @php
                                                $identity = '';
                                                $role = '';
                                                if($u->role == 'student' && $u->nisn) {
                                                    $identity = '- '.$u->nisn;
                                                    $role = 'Siswa';
                                                } elseif($u->role == 'teacher' && $u->nip) {
                                                    $identity = '- '.$u->nip;
                                                    $role = 'Guru';
                                                }else {
                                                    $role = 'Wali';
                                                }
                                            @endphp
                                            <option value="{{ $u->id }}">
                                                {{ $u->full_name }} 
                                                ({{ ucfirst($role) }} {{ $identity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-orange-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Isi Pesan</label>
                            <textarea name="message" id="edit_message" rows="5" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-sm text-gray-700 resize-none leading-relaxed placeholder-gray-400"></textarea>
                        </div>

                        <div class="rounded-xl border border-orange-100 bg-orange-50/30 p-5 group">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-bold text-orange-800 flex items-center">
                                    <i class="fas fa-paperclip mr-2"></i> Update Lampiran
                                </span>
                                <span class="text-[10px] bg-white px-2 py-1 rounded text-orange-600 border border-orange-200 shadow-sm">OPSIONAL</span>
                            </div>

                            <div class="space-y-4">
                                <div id="current_file_info" class="hidden bg-white p-3 rounded-lg border border-orange-200 shadow-sm flex items-center justify-between">
                                    <div class="flex items-center overflow-hidden mr-3">
                                        <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="flex flex-col overflow-hidden">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wide">File Terlampir</span>
                                            <a href="#" id="link_current_file" target="_blank" class="text-sm font-medium text-blue-600 hover:underline truncate max-w-[180px]">filename.pdf</a>
                                        </div>
                                    </div>
                                    <label class="flex items-center space-x-2 cursor-pointer bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-100 transition select-none">
                                        <input type="checkbox" name="delete_file" value="1" id="delete_file_checkbox" class="w-3.5 h-3.5 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                                        <span class="text-xs font-bold text-red-600">Hapus</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Ganti File (Max 2MB)</label>
                                        <input type="file" name="attachment_file" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200 transition bg-white border border-gray-200 rounded-lg cursor-pointer p-1">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Update Tautan</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-link text-gray-400 text-xs"></i>
                                            </div>
                                            <input type="url" name="attachment_link" id="edit_link" placeholder="https://..." class="w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-xs text-gray-700">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end pt-6 mt-2">
                        <button type="button" onclick="toggleModal('editAnnModal')" class="px-5 py-2.5 mr-3 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:from-amber-600 hover:to-orange-700 transition shadow-lg shadow-orange-500/30 flex items-center">
                            <i class="fas fa-save mr-2 text-xs"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-white tracking-wide flex items-center">
                        <i class="far fa-newspaper mr-3 opacity-80"></i> Detail Pengumuman
                    </h3>
                    <p class="text-blue-100 text-xs mt-0.5 opacity-80">Informasi lengkap pengumuman terpilih</p>
                </div>
                <button onclick="toggleModal('viewAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Content Scrollable Area --}}
            <div class="p-6 overflow-y-auto custom-scrollbar">
                
                {{-- Info Grid (3 Columns) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    {{-- 1. Pengirim --}}
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Dibuat Oleh</label>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2 font-bold">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="overflow-hidden">
                                {{-- ID ini perlu diisi via JS --}}
                                <p id="view_sender_name" class="text-sm font-bold text-gray-800 truncate">Admin</p> 
                                <p class="text-[10px] text-gray-500">Administrator</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Penerima --}}
                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <label class="block text-[10px] font-bold text-indigo-400 uppercase mb-1">Target Penerima</label>
                        <div class="flex items-center text-indigo-700 h-full">
                            <i class="fas fa-bullhorn mr-2 text-sm"></i>
                            <span id="view_recipient" class="text-sm font-bold capitalize truncate">-</span>
                        </div>
                    </div>

                    {{-- 3. Waktu Kirim --}}
                    <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                        <label class="block text-[10px] font-bold text-purple-400 uppercase mb-1">Waktu Kirim</label>
                        <div class="flex flex-col justify-center h-full">
                            <div class="flex items-center text-purple-700">
                                <i class="far fa-calendar-alt mr-2 text-xs"></i>
                                <span id="view_date" class="text-xs font-bold">-</span>
                            </div>
                            <div class="flex items-center text-purple-600 mt-1">
                                <i class="far fa-clock mr-2 text-xs"></i>
                                <span id="view_time" class="text-xs">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message Body --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase ml-1">Isi Pesan</label>
                        <span class="text-[10px] px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold border border-green-200">
                            Status: Terkirim
                        </span>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 shadow-inner min-h-[120px]">
                        <p class="text-gray-800 text-sm whitespace-pre-wrap leading-relaxed font-medium" id="view_message">
                            -
                        </p>
                    </div>
                </div>

                {{-- Attachments Section --}}
                <div id="view_attachments_container" class="hidden animate-fade-in-down">
                    <div class="relative py-3">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                        <div class="relative flex justify-start"><span class="bg-white pr-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Lampiran & Tautan</span></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Link Button --}}
                        <a id="view_link_btn" href="#" target="_blank" class="hidden flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 group transition duration-200">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white flex items-center justify-center mr-3 transition-colors">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-xs font-bold text-gray-500 uppercase mb-0.5">Tautan Luar</p>
                                <p id="view_link_text" class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition truncate">...</p>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-300 group-hover:text-blue-400 ml-2"></i>
                        </a>

                        {{-- File Button --}}
                        <a id="view_file_btn" href="#" target="_blank" download class="hidden flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:border-green-400 hover:shadow-lg hover:shadow-green-500/10 group transition duration-200">
                            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 group-hover:bg-green-500 group-hover:text-white flex items-center justify-center mr-3 transition-colors">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-xs font-bold text-gray-500 uppercase mb-0.5">Dokumen</p>
                                <p id="view_file_name" class="text-sm font-bold text-gray-800 group-hover:text-green-600 transition truncate">...</p>
                            </div>
                            <i class="fas fa-download text-gray-300 group-hover:text-green-400 ml-2"></i>
                        </a>
                    </div>
                </div>

                {{-- Metadata Footer (Technical Info) --}}
                <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center text-[10px] text-gray-400">
                    <span>ID Pengumuman: <span id="view_id" class="font-mono text-gray-600"># -</span></span>
                    <span>Terakhir diperbarui: <span id="view_updated_at" class="text-gray-600">-</span></span>
                </div>

            </div>

            {{-- Footer Actions --}}
            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200 shrink-0">
                <button type="button" onclick="toggleModal('viewAnnModal')" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 hover:text-gray-800 hover:shadow transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function toggleUserSelect(val) {
        const container = document.getElementById('user_select_container');
        container.style.display = (val === 'specific') ? 'block' : 'none';
    }
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Menutup modal saat klik di luar area modal
    window.onclick = function(event) {
        const modals = ['addAnnModal', 'editAnnModal', 'viewAnnModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                toggleModal(id);
            }
        });
    }

    function openEditModal(data, updateUrl, cleanFileName) {
        // 1. Set Form Action (Dynamic Route)
        const form = document.getElementById('editAnnForm');
        form.action = updateUrl;

        // 2. Populate Text Inputs
        const editMessageEl = document.getElementById('edit_message');
        const editRecipientEl = document.getElementById('edit_recipient');
        const editLinkEl = document.getElementById('edit_link');
        
        if (editMessageEl) editMessageEl.value = data.message ?? '';
        
        // Pilih recipient jika ada (default 'all' jika kosong)
        if (editRecipientEl) {
            editRecipientEl.value = data.recipient ? data.recipient : 'all'; 
        }

        if (editLinkEl) editLinkEl.value = data.attachment_link ?? '';

        // 3. Handle Date Time Input
        // Database: "YYYY-MM-DD HH:MM:SS" -> Input local: "YYYY-MM-DDTHH:MM"
        const editDatetimeEl = document.getElementById('edit_datetime');
        if (editDatetimeEl && data.sent_at) {
            // Ambil string tanggal, ganti spasi dengan T, dan potong detik
            const formattedDate = data.sent_at.replace(' ', 'T').substring(0, 16);
            editDatetimeEl.value = formattedDate;
        }

        // 4. Handle Existing File Info
        const fileInfoBox = document.getElementById('current_file_info');
        const fileNameText = document.getElementById('text_current_filename');
        const deleteCheckbox = document.getElementById('delete_file_checkbox');

        if (data.attachment_file) {
            fileInfoBox.classList.remove('hidden');
            fileNameText.textContent = cleanFileName || data.attachment_file;
            deleteCheckbox.checked = false; // Reset checkbox
        } else {
            fileInfoBox.classList.add('hidden');
            fileNameText.textContent = '';
        }

        toggleModal('editAnnModal');
    }

    function openViewModal(data, cleanFileName) {
        document.getElementById('view_id').textContent = '#' + data.id;
        document.getElementById('view_updated_at').textContent = new Date(data.updated_at).toLocaleDateString('id-ID');
        document.getElementById('view_sender_name').textContent = 'Admin Sekolah';

        const labels = {
            'all': 'Semua Pengguna',
            'student': 'Semua Siswa',
            'teacher': 'Semua Guru',
            'parent': 'Semua Orang Tua'
        };

        let recipientText = labels[data.recipient] || data.recipient;
        if (data.recipient === 'specific' && data.specific_user) {
            recipientText = data.specific_user.full_name;
        } else if (data.recipient === 'specific' && !data.specific_user) {
            recipientText = 'User Tidak Ditemukan';
        }
        
        document.getElementById('view_recipient').textContent = recipientText;
        document.getElementById('view_message').textContent = data.message;

        if (data.sent_at) {
            const d = new Date(data.sent_at);
            document.getElementById('view_date').textContent = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('view_time').textContent = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        }

        const container = document.getElementById('view_attachments_container');
        const linkBtn = document.getElementById('view_link_btn');
        const linkText = document.getElementById('view_link_text');
        const fileBtn = document.getElementById('view_file_btn');
        const fileName = document.getElementById('view_file_name');

        let hasAttachment = false;

        linkBtn.classList.add('hidden');
        linkBtn.classList.remove('flex');
        fileBtn.classList.add('hidden');
        fileBtn.classList.remove('flex');
        container.classList.add('hidden');

        if (data.attachment_link) {
            hasAttachment = true;
            linkBtn.classList.remove('hidden');
            linkBtn.classList.add('flex');
            linkBtn.href = data.attachment_link;
            linkText.textContent = data.attachment_link;
        }

        if (data.attachment_file) {
            hasAttachment = true;
            fileBtn.classList.remove('hidden');
            fileBtn.classList.add('flex');
            fileBtn.href = "{{ asset('uploads') }}/" + data.attachment_file;
            fileName.textContent = cleanFileName || data.attachment_file;
        }

        if (hasAttachment) {
            container.classList.remove('hidden');
        }

        toggleModal('viewAnnModal');
    }
</script>
@endpush
@endsection
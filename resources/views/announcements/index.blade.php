<?php
$active_menu = 'announcements';
?>

@extends('layouts.app')

@section('title', 'Pengumuman')
@section('header_title', 'Pengumuman')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">

    <main class="flex-1 overflow-y-auto p-8">

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- Header Card --}}
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-800">Riwayat Pengumuman Terbaru</h3>
                <button onclick="toggleModal('addAnnModal')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center cursor-pointer">
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
                                            // Bersihkan nama file (hapus timestamp prefix jika ada, misal: 12345_namafile.pdf)
                                            $realName = explode('_', $announcement->attachment_file, 2)[1] ?? $announcement->attachment_file;
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
                                                <span class="text-[10px] text-gray-400">Klik untuk lihat</span>
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
                                            <span class="truncate max-w-[150px]" title="{{ $announcement->attachment_link }}">Buka Tautan</span>
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($announcement->datetime_send)->format('d M Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Pukul {{ \Carbon\Carbon::parse($announcement->datetime_send)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $announcement->recipient }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($announcement))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-blue-600 transition" title="Lihat Detail">
                                            <i class="far fa-eye"></i>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($announcement))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-yellow-600 transition" title="Edit Pesan">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
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
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada laporan ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $announcements->links() }}
            </div>
        </div>
    </main>

    {{-- MODALS SECTION --}}

    {{-- 1. ADD MODAL --}}
    <div id="addAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-bullhorn text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Buat Pengumuman Baru</h3>
                </div>
                <button onclick="toggleModal('addAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Target Penerima</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400 group-focus-within:text-blue-500 transition"></i>
                                    </div>
                                    <select name="recipient" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm font-medium text-gray-700 appearance-none">
                                        <option value="" disabled selected>Pilih Target...</option>
                                        <option value="Semua">Semua</option>
                                        <option value="Guru">Guru</option>
                                        <option value="Siswa">Siswa</option>
                                        <option value="Orang Tua">Orang Tua</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jadwal Publikasi</label>
                                <input type="datetime-local" name="datetime_send" value="{{ date('Y-m-d\TH:i') }}" class="w-full pl-4 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm font-medium text-gray-700">
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Isi Pesan</label>
                            <textarea name="message" rows="5" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm text-gray-700 resize-none leading-relaxed" placeholder="Ketik isi pengumuman Anda di sini..."></textarea>
                        </div>
                        <div class="rounded-xl border-2 border-dashed border-gray-200 p-5 hover:border-blue-400 hover:bg-blue-50/30 transition-colors">
                            <div class="flex items-center space-x-2 mb-4">
                                <span class="bg-blue-100 text-blue-600 text-xs font-bold px-2 py-1 rounded">OPSIONAL</span>
                                <span class="text-sm font-semibold text-gray-500">Lampiran Dokumen & Link</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <label class="block text-xs text-gray-500 mb-1">Upload File (Max 2MB)</label>
                                    <input type="file" name="attachment_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer border border-gray-200 rounded-lg p-1 bg-white">
                                </div>
                                <div class="relative">
                                    <label class="block text-xs text-gray-500 mb-1">Tautan Eksternal</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-link text-gray-400 text-xs"></i>
                                        </div>
                                        <input type="url" name="attachment_link" placeholder="https://..." class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-6 mt-6 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('addAnnModal')" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition focus:ring-4 focus:ring-gray-200">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 focus:ring-4 focus:ring-blue-500/20">
                            <i class="fas fa-paper-plane mr-2"></i>Publikasikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Pengumuman</h3>
                </div>
                <button onclick="toggleModal('editAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editAnnForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Target Penerima</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400 group-focus-within:text-orange-500 transition"></i>
                                    </div>
                                    <select name="recipient" id="edit_recipient" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 transition-all text-sm font-medium text-gray-700 appearance-none">
                                        <option value="Semua">Semua</option>
                                        <option value="Guru">Guru</option>
                                        <option value="Siswa">Siswa</option>
                                        <option value="Orang Tua">Orang Tua</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jadwal Kirim</label>
                                <input type="datetime-local" name="datetime_send" id="edit_datetime" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 transition-all text-sm font-medium text-gray-700">
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Isi Pesan</label>
                            <textarea name="message" id="edit_message" rows="5" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 transition-all text-sm text-gray-700 resize-none leading-relaxed"></textarea>
                        </div>
                        <div class="rounded-xl bg-orange-50 border border-orange-100 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-bold text-orange-800 flex items-center">
                                    <i class="fas fa-paperclip mr-2"></i> Update Lampiran
                                </span>
                                <span class="text-[10px] bg-white px-2 py-1 rounded text-orange-600 border border-orange-200">OPSIONAL</span>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                {{-- Current File Display --}}
                                <div id="current_file_info" class="hidden flex items-center justify-between p-3 bg-white rounded-lg border border-orange-200 shadow-sm transition-all">
                                    <div class="flex items-center overflow-hidden mr-3">
                                        <div class="w-8 h-8 flex-shrink-0 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">File Saat Ini</p>
                                            <p class="text-sm font-medium text-gray-800 truncate max-w-[200px]" id="text_current_filename">...</p>
                                        </div>
                                    </div>
                                    <label class="flex items-center space-x-2 cursor-pointer bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-100 transition group select-none">
                                        <input type="checkbox" name="delete_file" value="1" id="delete_file_checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                                        <span class="text-xs font-bold text-red-600 group-hover:text-red-700">Hapus</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Ganti File Baru</label>
                                    <input type="file" name="attachment_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-orange-200 file:text-orange-800 hover:file:bg-orange-300 transition cursor-pointer bg-white rounded-lg border border-orange-200 p-1">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Update Tautan</label>
                                    <input type="url" name="attachment_link" id="edit_link" placeholder="https://..." class="w-full px-4 py-2 border border-orange-200 rounded-lg focus:outline-none focus:border-orange-500 text-sm bg-white">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-6 mt-6 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('editAnnModal')" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewAnnModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-lg p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide flex items-center">
                    <i class="far fa-newspaper mr-3 opacity-80"></i> Detail Pengumuman
                </h3>
                <button onclick="toggleModal('viewAnnModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6 overflow-y-auto max-h-[85vh] space-y-6">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <label class="block text-[10px] font-bold text-indigo-400 uppercase mb-1">Penerima</label>
                        <div class="flex items-center text-indigo-700">
                            <i class="fas fa-user-tag mr-2 text-sm"></i>
                            <span id="view_recipient" class="text-sm font-bold">-</span>
                        </div>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                        <label class="block text-[10px] font-bold text-purple-400 uppercase mb-1">Dikirim Pada</label>
                        <div class="flex items-center text-purple-700">
                            <i class="far fa-clock mr-2 text-sm"></i>
                            <p id="view_datetime" class="text-sm font-bold truncate">-</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Isi Pesan</label>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 shadow-sm min-h-[100px]">
                        <p class="text-gray-800 text-sm whitespace-pre-wrap leading-relaxed font-medium" id="view_message">
                            -
                        </p>
                    </div>
                </div>

                {{-- Attachments Container --}}
                <div id="view_attachments_container" class="hidden">
                    <div class="relative py-2">
                         <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                         <div class="relative flex justify-center"><span class="bg-white px-2 text-[10px] font-bold text-gray-400 uppercase">Lampiran Tersedia</span></div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 mt-2">
                        {{-- Link Button --}}
                        <a id="view_link_btn" href="#" target="_blank" class="hidden flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md hover:shadow-blue-500/10 group transition duration-200">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition">Buka Tautan</p>
                                <p id="view_link_text" class="text-xs text-gray-500 truncate">https://...</p>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-300 group-hover:text-blue-400 ml-2"></i>
                        </a>

                        {{-- File Button --}}
                        <a id="view_file_btn" href="#" target="_blank" download class="hidden flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:border-green-300 hover:shadow-md hover:shadow-green-500/10 group transition duration-200">
                            <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                <i class="fas fa-file-download"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-sm font-bold text-gray-800 group-hover:text-green-600 transition">Download Dokumen</p>
                                <p id="view_file_name" class="text-xs text-gray-500 truncate">file.pdf</p>
                            </div>
                            <i class="fas fa-download text-gray-300 group-hover:text-green-400 ml-2"></i>
                        </a>
                    </div>
                </div>

            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                <button type="button" onclick="toggleModal('viewAnnModal')" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 hover:text-gray-800 transition shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Menambah class overflow-hidden ke body agar background tidak bisa discroll
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

    function openEditModal(data) {
        // Mengisi Form Edit dengan Data Objek
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_message').value = data.message;
        document.getElementById('edit_recipient').value = data.recipient;
        document.getElementById('edit_datetime').value = data.datetime_send;
        document.getElementById('edit_link').value = data.attachment_link;

        // Mengatur Action URL Form secara dinamis
        let url = "{{ route('announcements.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('editAnnForm').action = url;

        // Logika Tampilan File
        const fileInfoDiv = document.getElementById('current_file_info');
        const deleteCheckbox = document.getElementById('delete_file_checkbox');
        const fileNameText = document.getElementById('text_current_filename');

        // Reset checkbox
        if(deleteCheckbox) deleteCheckbox.checked = false;

        if(data.attachment_file) {
            // Bersihkan nama file dari timestamp prefix (opsional, tergantung cara simpan)
            let cleanName = data.attachment_file.split('_').slice(1).join('_');
            if(!cleanName) cleanName = data.attachment_file;

            fileNameText.innerText = cleanName;
            fileInfoDiv.classList.remove('hidden');
            fileInfoDiv.classList.add('flex');
        } else {
            fileInfoDiv.classList.add('hidden');
            fileInfoDiv.classList.remove('flex');
        }

        toggleModal('editAnnModal');
    }

    function openViewModal(data) {
        document.getElementById('view_message').innerText = data.message;
        document.getElementById('view_recipient').innerText = data.recipient;
        
        // Format Tanggal (JS Native)
        const dateObj = new Date(data.datetime_send);
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        document.getElementById('view_datetime').innerText = dateObj.toLocaleDateString('id-ID', options);

        const container = document.getElementById('view_attachments_container');
        const linkBtn = document.getElementById('view_link_btn');
        const fileBtn = document.getElementById('view_file_btn');
        
        let hasAttachment = false;

        // Cek Link
        if (data.attachment_link) {
            linkBtn.href = data.attachment_link;
            document.getElementById('view_link_text').innerText = data.attachment_link;
            linkBtn.classList.remove('hidden');
            linkBtn.classList.add('flex');
            hasAttachment = true;
        } else {
            linkBtn.classList.add('hidden');
            linkBtn.classList.remove('flex');
        }

        // Cek File
        if (data.attachment_file) {
            // Menggunakan helper asset blade yang sudah dirender atau hardcode path
            fileBtn.href = "{{ asset('uploads') }}/" + data.attachment_file; 
            
            let cleanName = data.attachment_file.split('_').slice(1).join('_');
            if(!cleanName) cleanName = data.attachment_file;
            
            document.getElementById('view_file_name').innerText = cleanName;
            fileBtn.classList.remove('hidden');
            fileBtn.classList.add('flex');
            hasAttachment = true;
        } else {
            fileBtn.classList.add('hidden');
            fileBtn.classList.remove('flex');
        }

        if (hasAttachment) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }

        toggleModal('viewAnnModal');
    }
</script>
@endpush
@endsection
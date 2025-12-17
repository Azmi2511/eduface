<?php
$active_menu = 'students';
?>

@extends('layouts.app')

@section('title', 'Manajemen Siswa')
@section('header_title', 'Manajemen Siswa')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    
    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- ... (Bagian Statistik tidak berubah, aman dilihat guru) ... --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_total) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-green-500">
                <div class="w-14 h-14 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_active) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Siswa Aktif</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-gray-400">
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_inactive) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Siswa Nonaktif</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                {{-- ... (Form pencarian tidak berubah) ... --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Data</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / Email / NISN..."
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Akun</label>
                    <div class="relative">
                        <select name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm appearance-none bg-white">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="col-span-1">
                    <button type="submit" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-100 md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-bold text-gray-800">Daftar Data Siswa</h3>
                
                @if(Auth::user()->role == 'admin')
                    <button onclick="toggleModal('addStudentModal')" class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                        <i class="fas fa-plus mr-2"></i> Tambah Siswa
                    </button>
                @endif

            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Nama Siswa</th>
                            <th class="px-6 py-4">NISN</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Wali Murid</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50 transition duration-150 group">
                            
                            <td class="px-6 py-4 text-sm text-center text-gray-500">
                                {{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $profilePic = $student->user->profile_picture ?? $student->photo_path ?? null;
                                        $fullName = $student->user->full_name ?? 'Tanpa Nama';
                                    @endphp
                                    @if($profilePic)
                                        <img src="{{ asset('storage/' . $profilePic) }}" 
                                            alt="{{ $fullName }}" 
                                            class="h-10 w-10 rounded-full object-cover mr-3 border-2 border-gray-200 flex-shrink-0">
                                    @else
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full shadow-sm">
                                            {{ strtoupper(substr($fullName, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $fullName }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $student->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $student->nisn }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $student->class->class_name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $student->parent?->user?->full_name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($student->user->is_active ?? false)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="openViewModal({{ json_encode($student) }})" 
                                        class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700 focus:outline-none" 
                                        title="Lihat Detail">
                                        <i class="far fa-eye"></i>
                                    </button>
                                    
                                    @if(Auth::user()->role == 'admin')
                                        <button onclick="openEditModal({{ json_encode($student) }})"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700 focus:outline-none" 
                                            title="Edit Data">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        <form id="delete-form-{{ $student->nisn }}" action="{{ route('students.destroy', $student->nisn) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="button" 
                                                onclick="confirmAction(event, 'delete-form-{{ $student->nisn }}', 'Hapus Siswa?', 'Data NISN {{ $student->nisn }} akan hilang permanen!')"
                                                class="p-2 text-red-500 transition rounded-full hover:bg-red-50 hover:text-red-700" 
                                                title="Hapus Data">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="far fa-folder-open text-4xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada data siswa ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="addStudentModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-user-graduate text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Tambah Siswa Baru</h3>
                </div>
                <button onclick="toggleModal('addStudentModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <p class="text-sm text-gray-600 mb-4">Catatan: Buat akun user terlebih dahulu pada menu <a href="{{ route('users.index') }}" target="_blank" class="text-blue-600 font-semibold underline">Pengguna</a>, lalu pilih akun tersebut di formulir di bawah.</p>

                <form action="{{ route('students.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="full_name" id="add_full_name" value="">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Akun Siswa (User)</label>
                            <div class="relative">
                                <select id="add_user_select" name="user_id" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
                                    <option value="">-- Pilih Akun Siswa --</option>
                                    @foreach($users_student as $user)
                                        <option value="{{ $user->id }}" data-full_name="{{ $user->full_name }}">{{ $user->full_name }} ({{ $user->email ?? $user->username }})</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">NISN</label>
                            <input type="text" name="nisn" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nomor Induk Siswa">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Kelas</label>
                            <div class="relative">
                                <select name="class_id" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classmodel as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Orang Tua (opsional)</label>
                            <div class="relative">
                                <select name="parent_id" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
                                    <option value="">-- Pilih Orang Tua --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->user->full_name ?? 'No Name' }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('addStudentModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editStudentModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Data Siswa</h3>
                </div>
                <button onclick="toggleModal('editStudentModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editStudentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_student_id" name="id">
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                            <input type="text" name="nisn" id="edit_nisn" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                            <select name="status" id="edit_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="phone" id="edit_phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="dob" id="edit_dob" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender" id="edit_gender" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                            <div class="relative">
                                <select name="class_id" id="edit_class_id" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-xl appearance-none focus:outline-none focus:border-orange-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classmodel as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Orang Tua</label>
                            <div class="relative">
                                <select name="parent_id" id="edit_parent_id" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-xl appearance-none focus:outline-none focus:border-orange-500">
                                    <option value="">-- Pilih Orang Tua --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->user->full_name ?? 'No Name' }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 space-x-3 border-t border-gray-100 mt-6">
                        <button type="button" onclick="toggleModal('editStudentModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-500 rounded-xl hover:bg-amber-600 shadow-lg">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewStudentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden m-4 border border-gray-100">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide flex items-center">
                    <i class="far fa-user-circle mr-3 opacity-80"></i> Detail Siswa
                </h3>
                <button onclick="toggleModal('viewStudentModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh] space-y-4">
                {{-- Profile Picture Section --}}
                <div class="flex flex-col items-center mb-4 pb-4 border-b border-gray-100">
                    <div id="view_profile_picture_container" class="mb-3">
                        <img id="view_profile_picture" src="" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg hidden">
                        <div id="view_profile_initials" class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg"></div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800" id="view_name">Nama Siswa</h4>
                    <div class="flex items-center text-xs text-gray-500 mt-1">
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded font-mono mr-2" id="view_nisn">NISN</span>
                        <span id="view_status_badge"></span> 
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2">Data Akademik</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-2 rounded border border-gray-100">
                            <span class="text-[10px] text-gray-500 block">Kelas</span>
                            <span class="text-xs font-bold text-gray-700" id="view_class"></span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded border border-gray-100">
                            <span class="text-[10px] text-gray-500 block">Gender</span>
                            <span class="text-xs font-bold text-gray-700" id="view_gender"></span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded border border-gray-100 col-span-2">
                            <span class="text-[10px] text-gray-500 block">Wali Murid</span>
                            <span class="text-xs font-bold text-gray-700" id="view_parent"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2">Akun</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between border-b border-gray-50 pb-1">
                            <span class="text-gray-500">Username</span>
                            <span class="font-mono text-gray-700 font-medium" id="view_username"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-1">
                            <span class="text-gray-500">Email</span>
                            <span class="text-gray-700 font-medium truncate max-w-[150px]" id="view_email"></span>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-gray-500">Terdaftar</span>
                            <span class="text-gray-700" id="view_date"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-3 bg-gray-50 border-t border-gray-100">
                <button onclick="toggleModal('viewStudentModal')" class="w-full bg-white border border-gray-300 text-gray-700 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-50 transition shadow-sm">
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
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Close on outside click
    window.onclick = function(event) {
        const modals = ['addStudentModal', 'editStudentModal', 'viewStudentModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                toggleModal(id);
            }
        });
    }

    function openEditModal(student) {
        const studentIdEl = document.getElementById('edit_student_id');
        const userIdEl = document.getElementById('edit_user_id');
        const nisnEl = document.getElementById('edit_nisn');
        const fullNameEl = document.getElementById('edit_full_name');
        const emailEl = document.getElementById('edit_email');
        const phoneEl = document.getElementById('edit_phone');
        const dobEl = document.getElementById('edit_dob');
        const genderEl = document.getElementById('edit_gender');
        const classIdEl = document.getElementById('edit_class_id');
        const parentIdEl = document.getElementById('edit_parent_id');
        const statusEl = document.getElementById('edit_status');
        const formEl = document.getElementById('editStudentForm');

        // Hidden IDs
        if (studentIdEl) studentIdEl.value = student.id ?? '';
        if (userIdEl) userIdEl.value = student.user?.id ?? '';

        // Student fields
        if (nisnEl) nisnEl.value = student.nisn ?? '';
        if (classIdEl) classIdEl.value = student.class_id ?? student.class?.id ?? '';
        if (parentIdEl) parentIdEl.value = student.parent_id ?? student.parent?.id ?? '';

        // User fields
        if (fullNameEl) fullNameEl.value = student.user?.full_name || '';
        if (emailEl) emailEl.value = student.user?.email || '';
        if (phoneEl) phoneEl.value = student.user?.phone || '';
        
        // DOB: ensure YYYY-MM-DD format
        if (dobEl && student.user?.dob) {
            const dobVal = student.user.dob.split('T')[0].split(' ')[0];
            dobEl.value = dobVal;
        }
        
        if (genderEl) genderEl.value = student.user?.gender || '';
        
        // Status - controller expects 'status' field with value 0 or 1
        if (statusEl) {
            const isActive = student.user?.is_active == 1 || student.user?.is_active === true;
            statusEl.value = isActive ? '1' : '0';
        }

        // Setup action URL for update (student id)
        if (formEl) {
            let url = "{{ route('students.update', ':id') }}";
            url = url.replace(':id', student.id ?? '');
            formEl.action = url;
        }

        toggleModal('editStudentModal');
    }

    function openViewModal(student) {
        const nameEl = document.getElementById('view_name');
        const emailEl = document.getElementById('view_email');
        const usernameEl = document.getElementById('view_username');
        const nisnEl = document.getElementById('view_nisn');
        const classEl = document.getElementById('view_class');
        const parentEl = document.getElementById('view_parent');
        const dateEl = document.getElementById('view_date');
        const genderEl = document.getElementById('view_gender');
        const statusEl = document.getElementById('view_status_badge');
        const profilePicEl = document.getElementById('view_profile_picture');
        const profileInitialsEl = document.getElementById('view_profile_initials');

        if (nameEl) nameEl.innerText = student.user?.full_name || 'Tanpa Nama';
        if (emailEl) emailEl.innerText = student.user?.email || '-';
        if (usernameEl) usernameEl.innerText = student.user?.username || '-';
        if (nisnEl) nisnEl.innerText = student.nisn || '-';
        if (classEl) classEl.innerText = student.class?.class_name || student.class_room?.class_name || '-';
        if (parentEl) parentEl.innerText = student.parent?.user?.full_name || '-';
        
        // Date formatting
        if (dateEl) {
            const dateObj = new Date(student.created_at);
            dateEl.innerText = dateObj.toLocaleDateString('id-ID');
        }
        
        // Gender
        if (genderEl) {
            const genderText = (student.user?.gender === 'L') ? 'Laki-Laki' : (student.user?.gender === 'P' ? 'Perempuan' : '-');
            genderEl.innerText = genderText;
        }

        // Status badge
        if (statusEl) {
            const isActive = student.user?.is_active == 1 || student.user?.is_active === true;
            statusEl.innerHTML = isActive 
                ? '<span class="px-2 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full">Aktif</span>'
                : '<span class="px-2 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-full">Nonaktif</span>';
        }

        // Profile Picture
        const profilePic = student.user?.profile_picture || student.photo_path || null;
        if (profilePic && profilePicEl) {
            profilePicEl.src = "{{ asset('storage') }}/" + profilePic;
            profilePicEl.classList.remove('hidden');
            profileInitialsEl.classList.add('hidden');
        } else if (profileInitialsEl) {
            profilePicEl.classList.add('hidden');
            profileInitialsEl.classList.remove('hidden');
            const fullName = student.user?.full_name || 'Tanpa Nama';
            const initials = fullName.substring(0, 2).toUpperCase();
            profileInitialsEl.innerText = initials;
        }

        toggleModal('viewStudentModal');
    }

    const addUserSelectEl = document.getElementById('add_user_select');
    if (addUserSelectEl) {
        addUserSelectEl.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const fullname = opt ? opt.dataset.full_name || '' : '';
            const hidden = document.getElementById('add_full_name');
            if (hidden) hidden.value = fullname;
        });
    }
</script>
@endpush
@endsection
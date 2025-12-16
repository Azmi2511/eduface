<?php
$active_menu = 'users';
?>

@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    {{-- 2. Edit User Modal (announcement style) --}}
    <div id="editUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Pengguna</h3>
                </div>
                <button onclick="toggleModal('editUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" id="edit_role" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                    <option value="Parent">Orang Tua</option>
                                    <option value="Student">Siswa</option>
                                    <option value="Teacher">Guru</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="is_active" id="edit_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('editUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        </div>

        {{-- 2. Filter Form --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Nama/Email</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama atau email..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="relative">
                        <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm appearance-none bg-white">
                            <option value="">Semua Role</option>
                            <option value="Admin" @selected(request('role') == 'Admin')>Admin</option>
                            <option value="Teacher" @selected(request('role') == 'Teacher')>Guru</option>
                            <option value="Student" @selected(request('role') == 'Student')>Siswa</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="relative">
                        <select name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm appearance-none bg-white">
                            <option value="">Semua Status</option>
                            <option value="1" @selected(request('is_active') == '1')>Aktif</option>
                            <option value="0" @selected(request('is_active') == '0')>Nonaktif</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="col-span-1">
                    <button type="submit" class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Data Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna</h3>
                
                <button onclick="toggleModal('addUserModal')" class="bg-[#2F80ED] hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center cursor-pointer">
                    <i class="fas fa-plus mr-2"></i> Tambah Pengguna
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <th class="px-6 py-4 w-10">#</th>
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Terdaftar</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($user->profile_picture)
                                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                 alt="{{ $user->full_name }}" 
                                                 class="h-10 w-10 rounded-full object-cover mr-3 border-2 border-gray-200">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-bold text-xs mr-3 shadow-sm">
                                                {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->username ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">
                                        @switch(strtolower($user->role))
                                            @case('student') Siswa @break
                                            @case('teacher') Guru @break
                                            @case('admin') Administrator @break
                                            @case('parent') Orang Tua @break
                                            @default {{ $user->role }}
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500">{{ $user->email }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->is_active)
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($user))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-blue-600 transition">
                                            <i class="far fa-eye"></i>
                                        </button>
                                        
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($user))" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-yellow-600 transition">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Form --}}
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- MODALS SECTION --}}

    {{-- 1. Add User Modal (announcement style) --}}
    <div id="addUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-user-plus text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Tambah Pengguna Baru</h3>
                </div>
                <button onclick="toggleModal('addUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm" placeholder="Contoh: budi@sekolah.com">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                    <option value="Student">Siswa</option>
                                    <option value="Teacher">Guru</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Parent">Orang Tua</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="is_active" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 bg-blue-50 p-2 rounded">
                            <i class="fas fa-info-circle mr-1"></i> Password default: <b>123456</b>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('addUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Edit User Modal --}}
    <div id="editUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Pengguna</h3>
                </div>
                <button onclick="toggleModal('editUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" name="username" id="edit_username" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" name="phone" id="edit_phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="dob" id="edit_dob" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" id="edit_gender" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" id="edit_role" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                    <option value="admin">Admin</option>
                                    <option value="teacher">Guru</option>
                                    <option value="student">Siswa</option>
                                    <option value="parent">Orang Tua</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="is_active" id="edit_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                                <input type="password" name="password" id="edit_password" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm" placeholder="Kosongkan jika tidak ingin mengubah">
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('editUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. View User Modal (announcement style) --}}
    <div id="viewUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-lg p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide flex items-center">
                    <i class="far fa-user-circle mr-3 opacity-80"></i> Detail Pengguna
                </h3>
                <button onclick="toggleModal('viewUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh] space-y-4">
                {{-- Profile Picture Section --}}
                <div class="flex flex-col items-center mb-4 pb-4 border-b border-gray-100">
                    <div id="view_profile_picture_container" class="mb-3">
                        <img id="view_profile_picture" src="" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg hidden">
                        <div id="view_profile_initials" class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg"></div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800" id="view_full_name">-</h4>
                    <p class="text-sm text-gray-500" id="view_username">-</p>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Email</label>
                    <p class="text-gray-800 text-sm" id="view_email">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Role</label>
                        <p class="text-gray-800 text-sm font-medium" id="view_role">-</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
                        <p class="text-gray-800 text-sm" id="view_status">-</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Terdaftar</label>
                    <p class="text-gray-800 text-sm" id="view_created_at">-</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                <button type="button" onclick="toggleModal('viewUserModal')" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 hover:text-gray-800 transition shadow-sm">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')
<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.toggle("hidden");
        modal.classList.toggle("flex");
        document.body.classList.toggle("overflow-hidden"); // Prevent scrolling when modal is open
    }

    function openEditModal(user) {
        const fullNameEl = document.getElementById('edit_full_name');
        const usernameEl = document.getElementById('edit_username');
        const emailEl = document.getElementById('edit_email');
        const phoneEl = document.getElementById('edit_phone');
        const dobEl = document.getElementById('edit_dob');
        const genderEl = document.getElementById('edit_gender');
        const roleEl = document.getElementById('edit_role');
        const statusEl = document.getElementById('edit_status');
        const passwordEl = document.getElementById('edit_password');
        const idEl = document.getElementById('edit_user_id');
        const formEl = document.getElementById('editUserForm');

        if (fullNameEl) fullNameEl.value = user.full_name ?? '';
        if (usernameEl) usernameEl.value = user.username ?? '';
        if (emailEl) emailEl.value = user.email ?? '';
        if (phoneEl) phoneEl.value = user.phone ?? '';
        
        // DOB: ensure YYYY-MM-DD format
        if (dobEl && user.dob) {
            const dobVal = user.dob.split('T')[0].split(' ')[0];
            dobEl.value = dobVal;
        }
        
        if (genderEl) genderEl.value = user.gender || '';
        
        // Ensure role is lowercase for controller validation
        const userRole = (user.role || '').toString().toLowerCase();
        if (roleEl) {
            // Map common variations to lowercase
            const roleMap = {
                'admin': 'admin',
                'administrator': 'admin',
                'teacher': 'teacher',
                'guru': 'teacher',
                'student': 'student',
                'siswa': 'student',
                'parent': 'parent',
                'orang tua': 'parent'
            };
            roleEl.value = roleMap[userRole] || userRole || 'student';
        }
        
        if (statusEl) statusEl.value = (user.is_active == 1 || user.is_active === true) ? '1' : '0';
        if (passwordEl) passwordEl.value = ''; // Clear password field
        if (idEl) idEl.value = user.id ?? '';

        // Set Action URL Dynamically (only if form exists)
        if (formEl) {
            let url = "{{ route('users.update', ':id') }}";
            url = url.replace(':id', user.id);
            formEl.action = url;
        }

        toggleModal('editUserModal');
    }

    function openViewModal(user) {
        const nameEl = document.getElementById('view_full_name');
        const usernameEl = document.getElementById('view_username');
        const emailEl = document.getElementById('view_email');
        const roleEl = document.getElementById('view_role');
        const statusEl = document.getElementById('view_status');
        const createdEl = document.getElementById('view_created_at');
        const profilePicEl = document.getElementById('view_profile_picture');
        const profilePicContainer = document.getElementById('view_profile_picture_container');
        const profileInitialsEl = document.getElementById('view_profile_initials');

        if (nameEl) nameEl.innerText = user.full_name ?? '-';
        if (usernameEl) usernameEl.innerText = user.username ?? '-';
        if (emailEl) emailEl.innerText = user.email ?? '-';

        // Map English role values from DB to Indonesian labels for display
        const roleMap = {
            'student': 'Siswa',
            'teacher': 'Guru',
            'admin': 'Administrator',
            'parent': 'Orang Tua'
        };
        const rawRole = (user.role || '').toString().toLowerCase();
        if (roleEl) roleEl.innerText = roleMap[rawRole] ?? user.role ?? '-';

        // Status with badge
        if (statusEl) {
            const isActive = (user.is_active == 1 || user.is_active === true);
            statusEl.innerHTML = isActive 
                ? '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Aktif</span>'
                : '<span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">Nonaktif</span>';
        }

        // Profile Picture
        if (user.profile_picture) {
            profilePicEl.src = "{{ asset('storage') }}/" + user.profile_picture;
            profilePicEl.classList.remove('hidden');
            profileInitialsEl.classList.add('hidden');
        } else {
            profilePicEl.classList.add('hidden');
            profileInitialsEl.classList.remove('hidden');
            const initials = (user.full_name || user.username || '?').substring(0, 2).toUpperCase();
            profileInitialsEl.innerText = initials;
        }

        // Format Date (Simple JS)
        if (createdEl) {
            const date = new Date(user.created_at);
            if (!isNaN(date)) {
                createdEl.innerText = date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            } else {
                createdEl.innerText = user.created_at ?? '-';
            }
        }

        toggleModal('viewUserModal');
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modals = ['addUserModal', 'editUserModal', 'viewUserModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                toggleModal(id);
            }
        });
    }
</script>
@endpush
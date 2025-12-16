@extends('layouts.app')
@php
$active_menu = 'users';
@endphp
@section('title', 'Manajemen Pengguna')
@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    
    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Cards --}}
        {{-- Pastikan variabel $count_total, $count_active, $count_inactive dikirim dari Controller, 
             atau hapus bagian ini jika belum ada --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-indigo-500">
                <div class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $count_total ?? $users->total() }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Pengguna</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-green-500">
                <div class="w-14 h-14 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $count_active ?? '-' }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Pengguna Aktif</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-gray-400">
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-times"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $count_inactive ?? '-' }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Pengguna Nonaktif</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Data</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / Username / Email..."
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                
                {{-- Filter Role (Khusus User) --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="relative">
                        <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm appearance-none bg-white">
                            <option value="">Semua Role</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="parent" {{ request('role') === 'parent' ? 'selected' : '' }}>Parent</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="col-span-1">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-100 md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-bold text-gray-800">Daftar Data Pengguna</h3>
                <button onclick="toggleModal('createUserModal')" class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 shadow-indigo-200">
                    <i class="fas fa-plus mr-2"></i> Tambah Pengguna
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition duration-150 group">
                            
                            <td class="px-6 py-4 text-sm text-center text-gray-500">
                                {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($user->profile_picture)
                                        <img class="h-10 w-10 rounded-full object-cover mr-3 border-2 border-gray-200 flex-shrink-0" src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->full_name }}">
                                    @else
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-indigo-600 bg-indigo-100 border border-indigo-200 rounded-full shadow-sm">
                                            {{ substr($user->full_name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->full_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ '@' . $user->username }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $user->role === 'teacher' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'student' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $user->role === 'parent' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                ">
                                    {{ $user->role }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>{{ $user->email }}</div>
                                <div class="text-xs text-gray-400">{{ $user->phone ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-gray-800 bg-gray-100 rounded-full">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="openViewModal({{ json_encode($user) }})" 
                                        class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700 focus:outline-none" 
                                        title="Lihat Detail">
                                        <i class="far fa-eye"></i>
                                    </button>
                                    
                                    <button onclick="openEditModal({{ json_encode($user) }})"
                                        class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700 focus:outline-none" 
                                        title="Edit Data">
                                        <i class="far fa-edit"></i>
                                    </button>

                                    <button onclick="openDeleteModal('{{ route('users.destroy', $user->id) }}')" 
                                        class="p-2 text-red-500 transition rounded-full hover:bg-red-50 hover:text-red-700 focus:outline-none" 
                                        title="Hapus Data">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="far fa-folder-open text-4xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada data pengguna ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                {{ $users->links() }}
            </div>
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="createUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-3xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-user-plus text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Tambah Pengguna Baru</h3>
                </div>
                <button onclick="toggleModal('createUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label><input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" name="username" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label><input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label><input type="date" name="dob" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <div class="relative">
                                <select name="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm appearance-none bg-white">
                                    <option value="">Pilih</option><option value="L">Laki-laki</option><option value="P">Perempuan</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <div class="relative">
                                <select name="role" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm appearance-none bg-white">
                                    <option value="student">Student</option><option value="teacher">Teacher</option><option value="admin">Admin</option><option value="parent">Parent</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="relative">
                                <select name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm appearance-none bg-white">
                                    <option value="1">Aktif</option><option value="0">Nonaktif</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label><input type="file" name="profile_picture" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"></div>

                    </div>

                    <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('createUserModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editUserModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-3xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-user-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Pengguna</h3>
                </div>
                <button onclick="toggleModal('editUserModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editUserForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_user_id" name="id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label><input type="text" id="edit_full_name" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" id="edit_username" name="username" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" id="edit_email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label><input type="text" id="edit_phone" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label><input type="date" id="edit_dob" name="dob" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <div class="relative">
                                <select id="edit_gender" name="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm appearance-none bg-white">
                                    <option value="">Pilih</option><option value="L">Laki-laki</option><option value="P">Perempuan</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <div class="relative">
                                <select id="edit_role" name="role" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm appearance-none bg-white">
                                    <option value="student">Student</option><option value="teacher">Teacher</option><option value="admin">Admin</option><option value="parent">Parent</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="relative">
                                <select id="edit_status" name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm appearance-none bg-white">
                                    <option value="1">Aktif</option><option value="0">Nonaktif</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Password (Opsional)</label><input type="password" id="edit_password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label><input type="file" name="profile_picture" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"></div>

                    </div>

                    <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('editUserModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-500 rounded-xl hover:bg-amber-600 shadow-lg">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden m-4 border border-gray-100">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-500 flex justify-between items-center">
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
                    <div class="mb-3 relative">
                        <img id="view_profile_picture" src="" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg hidden">
                        <div id="view_profile_initials" class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-lg hidden"></div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800" id="view_full_name">Nama Pengguna</h4>
                    <div class="flex items-center text-xs text-gray-500 mt-1">
                        <span id="view_role_badge" class="px-2 py-1 rounded font-mono mr-2 uppercase">Role</span>
                        <span id="view_status_badge"></span> 
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2">Informasi Pribadi</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-2 rounded border border-gray-100">
                            <span class="text-[10px] text-gray-500 block">Gender</span>
                            <span class="text-xs font-bold text-gray-700" id="view_gender"></span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded border border-gray-100">
                            <span class="text-[10px] text-gray-500 block">Tanggal Lahir</span>
                            <span class="text-xs font-bold text-gray-700" id="view_dob"></span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded border border-gray-100 col-span-2">
                            <span class="text-[10px] text-gray-500 block">Bergabung Sejak</span>
                            <span class="text-xs font-bold text-gray-700" id="view_created_at"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2">Akun & Kontak</h4>
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
                            <span class="text-gray-500">Telepon</span>
                            <span class="text-gray-700 font-medium" id="view_phone"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-3 bg-gray-50 border-t border-gray-100">
                <button onclick="toggleModal('viewUserModal')" class="w-full bg-white border border-gray-300 text-gray-700 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeDeleteModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                Hapus Data User
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang permanen.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus
                        </button>
                    </form>

                    <button type="button" onclick="closeDeleteModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal) {
            modal.classList.toggle("hidden");
            modal.classList.toggle("flex");
            // Mencegah scroll pada body saat modal terbuka
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }
    }

    function openEditModal(user) {
        // Helper untuk set value elemen input
        const setValue = (id, val) => {
            const el = document.getElementById(id);
            if(el) el.value = val !== null ? val : '';
        };

        setValue('edit_full_name', user.full_name);
        setValue('edit_username', user.username);
        setValue('edit_email', user.email);
        setValue('edit_phone', user.phone);
        setValue('edit_gender', user.gender);
        setValue('edit_role', user.role);
        setValue('edit_user_id', user.id);
        setValue('edit_password', ''); // Reset password field

        // Handle Date Format for Input Date (YYYY-MM-DD)
        if(user.dob) {
            const dateStr = user.dob.split('T')[0].split(' ')[0];
            setValue('edit_dob', dateStr);
        } else {
            setValue('edit_dob', '');
        }

        // Handle Select Status (Boolean/Int to String)
        const statusEl = document.getElementById('edit_status');
        if(statusEl) statusEl.value = (user.is_active == 1) ? '1' : '0';

        // Update Action URL Form
        const formEl = document.getElementById('editUserForm');
        if (formEl) {
            // Asumsi route menggunakan format /users/{id}
            let url = "{{ route('users.update', ':id') }}";
            url = url.replace(':id', user.id);
            formEl.action = url;
        }

        toggleModal('editUserModal');
    }

    function openViewModal(user) {
        // Helper Text
        const setText = (id, val) => {
            const el = document.getElementById(id);
            if(el) el.innerText = val || '-';
        };

        setText('view_full_name', user.full_name);
        setText('view_username', user.username);
        setText('view_email', user.email);
        setText('view_phone', user.phone);

        // Gender Map
        const genderMap = {'L': 'Laki-laki', 'P': 'Perempuan'};
        setText('view_gender', genderMap[user.gender] || user.gender || '-');

        // Date Format (Indonesian)
        if(user.dob) {
            const date = new Date(user.dob);
            setText('view_dob', date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        } else {
            setText('view_dob', '-');
        }

        if(user.created_at) {
             const dateCreated = new Date(user.created_at);
             setText('view_created_at', dateCreated.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        }

        // Role Badge Styling
        const roleEl = document.getElementById('view_role_badge');
        roleEl.innerText = user.role;
        roleEl.className = 'px-2 py-1 rounded font-mono mr-2 uppercase text-[10px] font-bold ';
        if(user.role === 'admin') roleEl.classList.add('bg-purple-100', 'text-purple-700');
        else if(user.role === 'teacher') roleEl.classList.add('bg-blue-100', 'text-blue-700');
        else if(user.role === 'student') roleEl.classList.add('bg-green-100', 'text-green-700');
        else roleEl.classList.add('bg-yellow-100', 'text-yellow-700');

        // Status Badge
        const statusEl = document.getElementById('view_status_badge');
        if(user.is_active == 1) {
            statusEl.innerHTML = '<span class="text-green-600 font-bold text-xs"><i class="fas fa-check-circle"></i> Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="text-gray-400 font-bold text-xs"><i class="fas fa-times-circle"></i> Nonaktif</span>';
        }

        // Profile Picture Logic
        const imgEl = document.getElementById('view_profile_picture');
        const initEl = document.getElementById('view_profile_initials');
        
        if (user.profile_picture) {
            imgEl.src = "{{ asset('storage/') }}/" + user.profile_picture;
            imgEl.classList.remove('hidden');
            initEl.classList.add('hidden');
        } else {
            imgEl.classList.add('hidden');
            initEl.classList.remove('hidden');
            const initials = user.full_name 
                ? user.full_name.match(/(\b\S)?/g).join("").match(/(^\S|\S$)?/g).join("").toUpperCase()
                : '??';
            initEl.textContent = initials.substring(0, 2);
        }

        toggleModal('viewUserModal');
    }

    function openDeleteModal(actionUrl) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');

        form.action = actionUrl;

        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
    }
</script>
@endpush
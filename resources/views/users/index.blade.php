<?php
$active_menu = 'users';
?>

@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('header_title', 'Data Semua Pengguna')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto p-8">
        
        {{-- 1. Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Users --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-blue-500 text-white flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($counts['total']) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Pengguna</p>
                </div>
            </div>

            {{-- Active Users --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-[#00B074] text-white flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($counts['active']) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Pengguna Aktif</p>
                </div>
            </div>

            {{-- Teachers --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-[#FF9F43] text-white flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($counts['teachers']) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Guru</p>
                </div>
            </div>

            {{-- Admins --}}
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-[#9B88FA] text-white flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($counts['admins']) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Admin</p>
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
                                        <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs mr-3">
                                            {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                        </div>
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
                                    <span class="text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</span>
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
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </main>

    {{-- MODALS SECTION --}}

    {{-- 1. Add User Modal --}}
    <div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-800 bg-opacity-50 overflow-y-auto">
        <div class="relative w-full max-w-md p-4 bg-white rounded-xl shadow-lg m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Tambah Pengguna Baru</h3>
                <button onclick="toggleModal('addUserModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm" placeholder="Contoh: budi@sekolah.com">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                                <option value="Student">Siswa</option>
                                <option value="Teacher">Guru</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
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
                    <button type="button" onclick="toggleModal('addUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#2F80ED] rounded-lg hover:bg-blue-600 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Edit User Modal --}}
    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-800 bg-opacity-50 overflow-y-auto">
        <div class="relative w-full max-w-md p-4 bg-white rounded-xl shadow-lg m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Edit Pengguna</h3>
                <button onclick="toggleModal('editUserModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" id="edit_role" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                                <option value="Student">Siswa</option>
                                <option value="Teacher">Guru</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="is_active" id="edit_status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                    <button type="button" onclick="toggleModal('editUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 transition">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. View User Modal --}}
    <div id="viewUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-800 bg-opacity-50 overflow-y-auto">
        <div class="relative w-full max-w-md p-4 bg-white rounded-xl shadow-lg m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Pengguna</h3>
                <button onclick="toggleModal('viewUserModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase">Nama Lengkap</label>
                    <p class="text-gray-800 text-sm font-medium" id="view_full_name">-</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase">Email</label>
                    <p class="text-gray-800 text-sm" id="view_email">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase">Role</label>
                        <p class="text-gray-800 text-sm" id="view_role">-</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase">Status</label>
                        <p class="text-gray-800 text-sm" id="view_status">-</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase">Tanggal Terdaftar</label>
                    <p class="text-gray-800 text-sm" id="view_created_at">-</p>
                </div>
            </div>
            <div class="flex justify-end pt-4 mt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('viewUserModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Tutup</button>
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
        document.getElementById('edit_full_name').value = user.full_name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_status').value = user.is_active;
        
        // Set Action URL Dynamically
        let url = "{{ route('users.update', ':id') }}";
        url = url.replace(':id', user.id);
        document.getElementById('editUserForm').action = url;
        
        toggleModal('editUserModal');
    }

    function openViewModal(user) {
        document.getElementById('view_full_name').innerText = user.full_name;
        document.getElementById('view_email').innerText = user.email;
        document.getElementById('view_role').innerText = user.role;
        document.getElementById('view_status').innerText = user.is_active == 1 ? 'Aktif' : 'Nonaktif';
        
        // Format Date (Simple JS version, or use a library like moment.js)
        const date = new Date(user.created_at);
        document.getElementById('view_created_at').innerText = date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
        
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
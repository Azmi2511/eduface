<?php
$active_menu = 'teachers';
?>

@extends('layouts.app')

@section('title', 'Manajemen Guru')
@section('header_title', 'Manajemen Guru')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">

    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_total) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Guru</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-green-500">
                <div class="w-14 h-14 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_active) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Guru Aktif</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-gray-400">
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_inactive) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Guru Nonaktif</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('teachers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Nama/Email Guru</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama guru..."
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
                <h3 class="text-lg font-bold text-gray-800">Daftar Data Guru</h3>
                <button onclick="toggleModal('addTeacherModal')" class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                    <i class="fas fa-plus mr-2"></i> Tambah Guru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Nama Guru</th>
                            <th class="px-6 py-4">NIP</th>
                            <th class="px-6 py-4">Email / Username</th>
                            <th class="px-6 py-4">No. Telepon</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($teachers as $teacher)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-blue-600 bg-blue-100 rounded-full">
                                            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $teacher->full_name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $teacher->nip ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $teacher->user->email ?? $teacher->user->username }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $teacher->phone_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($teacher->user->is_active)
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Aktif</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($teacher))" 
                                            class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700" title="Lihat Detail">
                                            <i class="far fa-eye"></i>
                                        </button>
                                        
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($teacher))"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700" title="Edit Data">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('teachers.destroy', $teacher->user_id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data guru ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 transition rounded-full hover:bg-red-50 hover:text-red-700" title="Hapus Data">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data guru ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $teachers->withQueryString()->links() }}
            </div>
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="addTeacherModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-2xl p-6 m-4 transition-all transform bg-white shadow-2xl rounded-xl">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Tambah Guru Baru</h3>
                <button onclick="toggleModal('addTeacherModal')" class="text-gray-400 transition hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('teachers.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Guru (User ID)</label>
                        <div class="relative">
                            <select name="user_id" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500 transition">
                                <option value="">-- Pilih Akun Guru --</option>
                                @forelse($users_teacher as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->username }})</option>
                                @empty
                                    <option value="" disabled>Tidak ada user role teacher tersedia</option>
                                @endforelse
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Hanya menampilkan user dengan Role 'Teacher'.</p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" name="nip" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition" placeholder="Nomor Induk Guru">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition" placeholder="Nama Guru">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">No. Telepon</label>
                        <input type="text" name="phone_number" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition" placeholder="No Telepon Guru">
                    </div>
                </div>
                <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('addTeacherModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editTeacherModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-2xl m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Edit Data Guru</h3>
                <button onclick="toggleModal('editTeacherModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editTeacherForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input type="text" name="nip" id="edit_nip" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="text" name="phone_number" id="edit_phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="edit_status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end pt-6 space-x-3">
                    <button type="button" onclick="toggleModal('editTeacherModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewTeacherModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-sm p-0 bg-white rounded-xl shadow-2xl m-4 overflow-hidden">
            <div class="bg-[#2F80ED] p-6 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center text-3xl text-blue-600 mb-3 shadow-lg">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="text-xl font-bold text-white" id="view_name">Nama Guru</h3>
                <p class="text-blue-100 text-sm" id="view_email">email@guru.com</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500 text-sm">Status Akun</span>
                        <span id="view_status"></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500 text-sm">Bergabung Sejak</span>
                        <span class="font-medium text-gray-800 text-sm" id="view_date"></span>
                    </div>
                </div>
                <button onclick="toggleModal('viewTeacherModal')" class="mt-6 w-full bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Tutup</button>
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
        const modals = ['addTeacherModal', 'editTeacherModal', 'viewTeacherModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                toggleModal(id);
            }
        });
    }

    function openEditModal(teacher) {
        document.getElementById('edit_user_id').value = teacher.user_id;
        document.getElementById('edit_nip').value = teacher.nip;
        document.getElementById('edit_full_name').value = teacher.full_name;
        document.getElementById('edit_email').value = teacher.user.email;
        document.getElementById('edit_phone').value = teacher.phone_number;
        document.getElementById('edit_status').value = teacher.user.is_active;
        
        let url = "{{ route('teachers.update', ':id') }}";
        url = url.replace(':id', teacher.user_id);
        document.getElementById('editTeacherForm').action = url;
        
        toggleModal('editTeacherModal');
    }

    function openViewModal(teacher) {
        document.getElementById('view_name').innerText = teacher.full_name;
        document.getElementById('view_email').innerText = teacher.user.email;
        
        // Date formatting
        const dateObj = new Date(teacher.created_at);
        document.getElementById('view_date').innerText = dateObj.toLocaleDateString('id-ID');

        const elStatus = document.getElementById('view_status');
        if (teacher.user.is_active == 1) {
            elStatus.innerHTML = '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Aktif</span>';
        } else {
            elStatus.innerHTML = '<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Nonaktif</span>';
        }

        toggleModal('viewTeacherModal');
    }
</script>
@endpush
@endsection
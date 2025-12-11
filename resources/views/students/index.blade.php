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
                <button onclick="toggleModal('addStudentModal')" class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                    <i class="fas fa-plus mr-2"></i> Tambah Siswa
                </button>
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
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-blue-600 bg-blue-100 rounded-full">
                                            {{ strtoupper(substr($student->user->full_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $student->user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $student->user->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $student->nisn }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $student->classRoom->class_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $student->parent->user->full_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($student->user->is_active)
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Aktif</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($student))" 
                                            class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700" title="Lihat Detail">
                                            <i class="far fa-eye"></i>
                                        </button>
                                        
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($student))"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700" title="Edit Data">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('students.destroy', $student->nisn) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data siswa ini?')">
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
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data siswa ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $students->withQueryString()->links() }}
            </div>
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="addStudentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-2xl p-6 m-4 transition-all transform bg-white shadow-2xl rounded-xl">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Tambah Siswa Baru</h3>
                <button onclick="toggleModal('addStudentModal')" class="text-gray-400 transition hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('students.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Siswa (User ID)</label>
                        <div class="relative">
                            <select name="user_id" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
                                <option value="">-- Pilih Akun Siswa --</option>
                                @foreach($users_student as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->username }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">NISN</label>
                        <input type="text" name="nisn" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nomor Induk">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <div class="relative">
                            <select name="gender" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nama Siswa">
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
                        <label class="block mb-1 text-sm font-medium text-gray-700">Pilih Orang Tua</label>
                        <div class="relative">
                            <select name="parent_id" required class="w-full px-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:border-blue-500">
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
                    <button type="button" onclick="toggleModal('addStudentModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editStudentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-2xl m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Edit Data Siswa</h3>
                <button onclick="toggleModal('editStudentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editStudentForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                        <input type="text" name="nisn" id="edit_nisn" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="edit_status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end pt-6 space-x-3">
                    <button type="button" onclick="toggleModal('editStudentModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewStudentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden m-4 border border-gray-100">
            <div class="flex items-center p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white text-lg shadow-sm mr-3 flex-shrink-0">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="flex-1 min-w-0"> 
                    <h3 class="text-base font-bold text-gray-800 truncate" id="view_name">Nama Siswa</h3>
                    <div class="flex items-center text-xs text-gray-500 mt-0.5">
                        <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-mono mr-2" id="view_nisn">NISN</span>
                        <span id="view_status_badge"></span> 
                    </div>
                </div>
                <button onclick="toggleModal('viewStudentModal')" class="text-gray-400 hover:text-red-500 transition ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-4 space-y-4">
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
        document.getElementById('edit_user_id').value = student.user.id;
        document.getElementById('edit_nisn').value = student.nisn;
        document.getElementById('edit_full_name').value = student.user.full_name;
        document.getElementById('edit_email').value = student.user.email;
        document.getElementById('edit_status').value = student.user.is_active;
        
        // Setup action URL
        let url = "{{ route('students.update', ':id') }}";
        url = url.replace(':id', student.id); // Menggunakan ID Student/User untuk route update
        document.getElementById('editStudentForm').action = url;
        
        toggleModal('editStudentModal');
    }

    function openViewModal(student) {
        document.getElementById('view_name').innerText = student.user.full_name;
        document.getElementById('view_email').innerText = student.user.email;
        document.getElementById('view_username').innerText = student.user.username;
        document.getElementById('view_nisn').innerText = student.nisn;
        document.getElementById('view_class').innerText = student.class_room ? student.class_room.class_name : '-';
        document.getElementById('view_parent').innerText = student.parent && student.parent.user ? student.parent.user.full_name : '-';
        
        // Date formatting
        const dateObj = new Date(student.created_at);
        document.getElementById('view_date').innerText = dateObj.toLocaleDateString('id-ID');
        
        const genderText = (student.gender === 'L') ? 'Laki-Laki' : (student.gender === 'P' ? 'Perempuan' : '-');
        document.getElementById('view_gender').innerText = genderText;

        const elStatus = document.getElementById('view_status_badge');
        if (student.user.is_active == 1) {
            elStatus.innerHTML = '<span class="px-2 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full">Aktif</span>';
        } else {
            elStatus.innerHTML = '<span class="px-2 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-full">Nonaktif</span>';
        }

        toggleModal('viewStudentModal');
    }
</script>
@endpush
@endsection
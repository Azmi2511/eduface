<?php
$active_menu = 'classes';
?>

@extends('layouts.app')

@section('title', 'Manajemen Kelas')
@section('header_title', 'Manajemen Kelas')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    
    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Card --}}
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-chalkboard"></i> {{-- Icon diubah agar sesuai konteks Kelas --}}
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_classes) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Kelas</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('classes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Kelas</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Masukkan nama kelas..."
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 text-sm">
                </div>

                <div class="col-span-1">
                    <button type="submit"
                        class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-100 md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-bold text-gray-800">Daftar Data Kelas</h3>
                <button onclick="toggleModal('addClassModal')"
                    class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                    <i class="fas fa-plus mr-2"></i> Tambah Kelas
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Nama Kelas</th>
                            <th class="px-6 py-4">Level</th>
                            <th class="px-6 py-4">Tahun Akademik</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($classmodel as $class)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($classmodel->currentPage() - 1) * $classmodel->perPage() }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full shadow-sm">
                                            {{ strtoupper(substr($class->class_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $class->class_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span class="block truncate w-32" title="{{ $class->grade_level }}">
                                        {{ $class->grade_level }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $class->academic_year }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($class))"
                                            class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700">
                                            <i class="far fa-eye"></i>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($class))"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data kelas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 transition rounded-full hover:bg-red-50 hover:text-red-700">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p>Tidak ada data kelas ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-500">
                    Halaman <span class="font-medium">{{ $classmodel->currentPage() }}</span> dari <span class="font-medium">{{ $classmodel->lastPage() }}</span>
                </div>

                <div class="flex space-x-2">
                    {{-- Previous Page Link --}}
                    @if ($classmodel->onFirstPage())
                        <span class="px-3 py-1.5 text-sm font-medium text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </span>
                    @else
                        <a href="{{ $classmodel->previousPageUrl() }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </a>
                    @endif

                    {{-- Next Page Link --}}
                    @if($classmodel->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                            {{ $classmodel->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="addClassModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-2xl m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Tambah Kelas Baru</h3>
                <button onclick="toggleModal('addClassModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('classes.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                        <input type="text" name="class_name" required placeholder="Contoh: X IPA 1"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Level Angkatan</label>
                        <input type="text" name="grade_level" required placeholder="Contoh: 10"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                        <input type="text" name="academic_year" required placeholder="Contoh: 2023/2024"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="flex justify-end pt-6 space-x-3">
                    <button type="button" onclick="toggleModal('addClassModal')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editClassModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Data Kelas</h3>
                </div>
                <button onclick="toggleModal('editClassModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editClassForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                            <input type="text" name="class_name" id="edit_class_name" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level Angkatan</label>
                            <input type="text" name="grade_level" id="edit_grade" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                            <input type="text" name="academic_year" id="edit_academic_year" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('editClassModal')"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewClassModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-sm p-6 bg-white rounded-xl shadow-2xl m-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Kelas</h3>
                <button onclick="toggleModal('viewClassModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="flex flex-col items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-3xl text-blue-600 mb-3">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <h4 id="view_class_name" class="text-xl font-bold text-gray-800"></h4>
                    <p id="view_grade" class="text-sm text-gray-500"></p>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Tahun Akademik</span>
                        <span id="view_academic_year" class="text-sm font-medium text-gray-800"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Dibuat Pada</span>
                        <span id="view_created_at" class="text-sm font-medium text-gray-800"></span>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button onclick="toggleModal('viewClassModal')"
                    class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Tutup</button>
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

    function openEditModal(data) {
        document.getElementById('edit_class_name').value = data.class_name;
        document.getElementById('edit_grade').value = data.grade_level;
        document.getElementById('edit_academic_year').value = data.academic_year;
        
        let url = "{{ route('classes.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('editClassForm').action = url;
        
        toggleModal('editClassModal');
    }

    function openViewModal(data) {
        document.getElementById('view_class_name').innerText = data.class_name;
        document.getElementById('view_grade').innerText = 'Level: ' + data.grade_level;
        document.getElementById('view_academic_year').innerText = data.academic_year;
        
        // Simple date formatting
        const date = new Date(data.created_at);
        document.getElementById('view_created_at').innerText = date.toLocaleDateString('id-ID');
        
        toggleModal('viewClassModal');
    }

    window.onclick = function (e) {
        const modals = ['addClassModal', 'editClassModal', 'viewClassModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (e.target == modal) {
                toggleModal(id);
            }
        });
    }
</script>
@endpush
@endsection
<?php
$active_menu = 'schedules';
?>

@extends('layouts.app')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    
    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Card --}}
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-indigo-500">
                <div class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    {{-- Menghitung total jadwal di halaman ini atau total global --}}
                    <h3 class="text-2xl font-bold text-gray-900">{{ $schedules->total() }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Jadwal Aktif</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('schedules.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                {{-- Filter Hari --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Hari</label>
                    <select name="day" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm">
                        <option value="">Semua Hari</option>
                        @foreach(['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'] as $eng => $id)
                            <option value="{{ $eng }}" {{ request('day') == $eng ? 'selected' : '' }}>{{ $id }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kelas (Admin Only) --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Kelas</label>
                    <select name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-indigo-500 text-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($classroom as $cls)
                            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-1">
                    <button type="submit"
                        class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Pesan Error / Sukses --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Data Table --}}
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-100 md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-bold text-gray-800">Daftar Jadwal Pelajaran</h3>
                @if(Auth::user()->role == 'admin')
                    <button onclick="toggleModal('addScheduleModal')"
                        class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 shadow-indigo-200">
                        <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Hari & Waktu</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Mata Pelajaran</th>
                            <th class="px-6 py-4">Guru Pengajar</th>
                            @if(Auth::user()->role == 'admin')
                            <th class="px-6 py-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-white bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full shadow-sm">
                                            {{ substr($schedule->day_of_week, 0, 1) }}
                                        </div>
                                        <div>
                                            @php
                                                $days = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
                                            @endphp
                                            <div class="text-sm font-bold text-gray-900">{{ $days[$schedule->day_of_week] ?? $schedule->day_of_week }}</div>
                                            <div class="text-xs text-gray-500 font-medium">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                    {{ $schedule->class->class_name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $schedule->subject->subject_name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                        {{ $schedule->teacher->user->full_name ?? '-' }}
                                    </div>
                                </td>

                                @if(Auth::user()->role == 'admin')
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($schedule))"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form id="delete-form-{{ $schedule->id }}" action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="button" 
                                                onclick="confirmAction(event, 'delete-form-{{ $schedule->id }}', 'Hapus Jadwal?', 'Jadwal yang dihapus tidak dapat dikembalikan!')"
                                                class="p-2 text-red-500 transition rounded-full hover:bg-red-50 hover:text-red-700"
                                                title="Hapus Data">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                        <p>Tidak ada jadwal ditemukan.</p>
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
                    Halaman <span class="font-medium">{{ $schedules->currentPage() }}</span> dari <span class="font-medium">{{ $schedules->lastPage() }}</span>
                </div>
                <div class="flex space-x-2">
                    {{ $schedules->appends(request()->query())->links('pagination::tailwind') }} 
                </div>
            </div>
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    @if(Auth::user()->role == 'admin')
    <div id="addScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-500 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide"><i class="fas fa-plus-circle mr-2"></i> Tambah Jadwal Baru</h3>
                <button onclick="toggleModal('addScheduleModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('schedules.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
                            <select name="day_of_week" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                                <option value="">Pilih Hari</option>
                                @foreach(['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'] as $eng => $id)
                                    <option value="{{ $eng }}">{{ $id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                                <option value="">Pilih Kelas</option>
                                @foreach($classroom as $c)
                                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                            <select name="subject_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                                <option value="">Pilih Mapel</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Guru Pengajar</label>
                            <select name="teacher_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                                <option value="">Pilih Guru</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->user->full_name ?? 'No Name' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 space-x-3 mt-4 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('addScheduleModal')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editScheduleModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide"><i class="fas fa-edit mr-2"></i> Edit Jadwal</h3>
                <button onclick="toggleModal('editScheduleModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="editScheduleForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
                            <select name="day_of_week" id="edit_day" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                                @foreach(['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'] as $eng => $id)
                                    <option value="{{ $eng }}">{{ $id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <select name="class_id" id="edit_class_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                                @foreach($classroom as $c)
                                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                            <select name="subject_id" id="edit_subject_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Guru Pengajar</label>
                            <select name="teacher_id" id="edit_teacher_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->user->full_name ?? 'No Name' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" id="edit_start_time" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" id="edit_end_time" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-orange-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 space-x-3 mt-4 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('editScheduleModal')" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-orange-500 rounded-lg hover:bg-orange-600">Update Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

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
        // Populate Form Fields
        document.getElementById('edit_day').value = data.day_of_week;
        document.getElementById('edit_class_id').value = data.class_id;
        document.getElementById('edit_subject_id').value = data.subject_id;
        document.getElementById('edit_teacher_id').value = data.teacher_id;
        
        // Format Time (HH:mm:ss -> HH:mm) agar terbaca input type="time"
        document.getElementById('edit_start_time').value = data.start_time.substring(0, 5);
        document.getElementById('edit_end_time').value = data.end_time.substring(0, 5);
        
        // Update URL Action
        let url = "{{ route('schedules.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('editScheduleForm').action = url;
        
        toggleModal('editScheduleModal');
    }

    window.onclick = function (e) {
        const modals = ['addScheduleModal', 'editScheduleModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (modal && e.target == modal) {
                toggleModal(id);
            }
        });
    }
</script>
@endpush
@endsection
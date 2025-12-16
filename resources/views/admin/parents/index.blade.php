<?php
$active_menu = 'parents';
?>

@extends('layouts.app')

@section('title', 'Manajemen Orang Tua')
@section('header_title', 'Manajemen Orang Tua')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">

    <main class="flex-1 overflow-y-auto p-8">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-blue-500">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_total) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Orang Tua</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-green-500">
                <div class="w-14 h-14 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_active) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Akun Aktif</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center border-l-4 border-gray-400">
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($count_inactive) }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Akun Nonaktif</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('parents.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Nama/Email</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama atau email..."
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
                <h3 class="text-lg font-bold text-gray-800">Daftar Data Orang Tua</h3>
                <button onclick="toggleModal('addParentModal')" class="flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                    <i class="fas fa-plus mr-2"></i> Tambah Orang Tua
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">Nama Orang Tua</th>
                            <th class="px-6 py-4">FCM Token</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">No. Telepon</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($parents as $parent)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    {{ $loop->iteration + ($parents->currentPage() - 1) * $parents->perPage() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($parent->user->profile_picture ?? null)
                                            <img src="{{ asset('storage/' . $parent->user->profile_picture) }}" 
                                                 alt="{{ $parent->user->full_name ?? '-' }}" 
                                                 class="h-10 w-10 rounded-full object-cover mr-3 border-2 border-gray-200 flex-shrink-0">
                                        @else
                                            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-sm font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full shadow-sm">
                                                {{ strtoupper(substr($parent->user->full_name ?? 'OR', 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $parent->user->full_name ?? '-' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $parent->user->email ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span class="block truncate w-32" title="{{ $parent->fcm_token ?? '-' }}">
                                        {{ $parent->fcm_token ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $parent->user->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $parent->phone_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($parent->user->is_active)
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Aktif</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- View Button --}}
                                        <button onclick="openViewModal(@js($parent))" 
                                            class="p-2 text-blue-500 transition rounded-full hover:bg-blue-50 hover:text-blue-700" title="Lihat Detail">
                                            <i class="far fa-eye"></i>
                                        </button>
                                        
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal(@js($parent))"
                                            class="p-2 text-yellow-500 transition rounded-full hover:bg-yellow-50 hover:text-yellow-700" title="Edit Data">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('parents.destroy', $parent->user_id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data orang tua ini? Tindakan ini juga akan menghapus akun login.')">
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
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data orang tua ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($parents->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    {{-- Tambahkan appends(request()->query()) --}}
                    {{ $parents->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- 1. ADD MODAL --}}
    <div id="addParentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-2xl p-6 m-4 transition-all transform bg-white shadow-2xl rounded-xl">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Tambah Orang Tua Baru</h3>
                <button onclick="toggleModal('addParentModal')" class="text-gray-400 transition hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('parents.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">No. Telepon</label>
                        <input type="text" name="phone_number" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
                    </div>
                </div>
                <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('addParentModal')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. EDIT MODAL --}}
    <div id="editParentModal" class="fixed inset-0 z-50 hidden w-full h-full bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-0 bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all scale-100">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Edit Data Orang Tua</h3>
                </div>
                <button onclick="toggleModal('editParentModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form id="editParentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="edit_full_name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" name="phone" id="edit_phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
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
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan</label>
                            <select name="relationship" id="edit_relationship" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                <option value="">-- Pilih --</option>
                                <option value="Ayah">Ayah</option>
                                <option value="Ibu">Ibu</option>
                                <option value="Wali">Wali</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">FCM Token</label>
                            <input type="text" name="fcm_token" id="edit_fcm" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="edit_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100 space-x-3">
                        <button type="button" onclick="toggleModal('editParentModal')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. VIEW MODAL --}}
    <div id="viewParentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-gray-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="relative w-full max-w-lg p-0 bg-white rounded-2xl shadow-2xl m-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white tracking-wide flex items-center">
                    <i class="far fa-user-circle mr-3 opacity-80"></i> Detail Orang Tua
                </h3>
                <button onclick="toggleModal('viewParentModal')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-1 w-8 h-8 flex items-center justify-center transition">
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
                    <h4 class="text-lg font-bold text-gray-800" id="view_name">Nama Orang Tua</h4>
                    <p class="text-sm text-gray-500" id="view_email">email@parent.com</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">No. Telepon</label>
                        <p class="text-gray-800 text-sm font-medium" id="view_phone">-</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Hubungan</label>
                        <p class="text-gray-800 text-sm font-medium" id="view_relationship">-</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Akun</label>
                    <p class="text-gray-800 text-sm" id="view_status">-</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Bergabung Sejak</label>
                    <p class="text-gray-800 text-sm" id="view_date">-</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                <button type="button" onclick="toggleModal('viewParentModal')" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 hover:text-gray-800 transition shadow-sm">Tutup</button>
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
        const modals = ['addParentModal', 'editParentModal', 'viewParentModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                toggleModal(id);
            }
        });
    }

    function openEditModal(parent) {
        const userIdEl = document.getElementById('edit_user_id');
        const fcmEl = document.getElementById('edit_fcm');
        const fullNameEl = document.getElementById('edit_full_name');
        const emailEl = document.getElementById('edit_email');
        const phoneEl = document.getElementById('edit_phone');
        const dobEl = document.getElementById('edit_dob');
        const genderEl = document.getElementById('edit_gender');
        const relationshipEl = document.getElementById('edit_relationship');
        const statusEl = document.getElementById('edit_status');
        const formEl = document.getElementById('editParentForm');

        if (userIdEl) userIdEl.value = parent.user_id || '';
        if (fcmEl) fcmEl.value = parent.fcm_token || '';
        
        // Ambil semua data dari user
        if (fullNameEl) fullNameEl.value = parent.user?.full_name || '';
        if (emailEl) emailEl.value = parent.user?.email || '';
        if (phoneEl) phoneEl.value = parent.user?.phone || '';
        
        // DOB: ensure YYYY-MM-DD format
        if (dobEl && parent.user?.dob) {
            const dobVal = parent.user.dob.split('T')[0].split(' ')[0];
            dobEl.value = dobVal;
        }
        
        if (genderEl) genderEl.value = parent.user?.gender || '';
        if (relationshipEl) relationshipEl.value = parent.relationship || '';
        
        // Controller expects 'status' field with value 0 or 1
        if (statusEl) {
            const isActive = parent.user?.is_active == 1 || parent.user?.is_active === true;
            statusEl.value = isActive ? '1' : '0';
        }
        
        if (formEl) {
            let url = "{{ route('parents.update', ':id') }}";
            url = url.replace(':id', parent.user_id || '');
            formEl.action = url;
        }
        
        toggleModal('editParentModal');
    }

    function openViewModal(parent) {
        const nameEl = document.getElementById('view_name');
        const emailEl = document.getElementById('view_email');
        const phoneEl = document.getElementById('view_phone');
        const relationshipEl = document.getElementById('view_relationship');
        const statusEl = document.getElementById('view_status');
        const dateEl = document.getElementById('view_date');
        const profilePicEl = document.getElementById('view_profile_picture');
        const profileInitialsEl = document.getElementById('view_profile_initials');

        // Ambil semua data dari user
        if (nameEl) nameEl.innerText = parent.user?.full_name || '-';
        if (emailEl) emailEl.innerText = parent.user?.email || '-';
        if (phoneEl) phoneEl.innerText = parent.user?.phone || '-';
        if (relationshipEl) relationshipEl.innerText = parent.relationship || '-';
        
        // Date formatting
        if (dateEl) {
            const dateObj = new Date(parent.created_at);
            dateEl.innerText = dateObj.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Status badge
        if (statusEl) {
            const isActive = parent.user?.is_active == 1 || parent.user?.is_active === true;
            statusEl.innerHTML = isActive 
                ? '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Aktif</span>'
                : '<span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">Nonaktif</span>';
        }

        // Profile Picture
        if (parent.user?.profile_picture && profilePicEl) {
            profilePicEl.src = "{{ asset('storage') }}/" + parent.user.profile_picture;
            profilePicEl.classList.remove('hidden');
            profileInitialsEl.classList.add('hidden');
        } else if (profileInitialsEl) {
            profilePicEl.classList.add('hidden');
            profileInitialsEl.classList.remove('hidden');
            const fullName = parent.user?.full_name || 'Orang Tua';
            const initials = fullName.substring(0, 2).toUpperCase();
            profileInitialsEl.innerText = initials;
        }

        toggleModal('viewParentModal');
    }
</script>
@endpush
@endsection
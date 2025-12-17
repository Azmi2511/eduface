<?php
$active_menu = 'attendance';
?>

@extends('layouts.app')

@section('title', 'Absensi')

@section('header_title', 'Absensi')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
    <main class="flex-1 overflow-y-auto p-8">
        
        {{-- 1. Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $counts['present'] }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Hadir</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $counts['late'] }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Terlambat</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $counts['permit'] }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Izin</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center">
                <div class="w-14 h-14 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center text-2xl mr-4">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $counts['absent'] }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Belum Hadir / Alpha</p>
                </div>
            </div>
        </div>

        {{-- 2. Filters & Export --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center text-gray-800">
                    <div class="bg-blue-50 p-2 rounded-lg mr-3 text-blue-600">
                        <i class="fas fa-filter text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold">Filter Data Absensi</h3>
                </div>
                
                <form action="{{ route('attendance.export') }}" method="POST" class="contents">
                    @csrf
                    <input type="hidden" name="date" value="{{ request('date', $dateFilter) }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    
                    {{-- Pastikan schedule_id ikut terkirim saat export --}}
                    @if(request('schedule_id'))
                        <input type="hidden" name="schedule_id" value="{{ request('schedule_id') }}">
                        <input type="hidden" name="class_id" value="{{ $selectedSchedule->class_id ?? '' }}">
                    @endif
                    
                    <button type="submit" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center justify-center md:w-auto w-full">
                        <i class="fas fa-file-excel mr-2"></i> Export Excel
                    </button>
                </form>
            </div>

            <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                {{-- FILTER TANGGAL --}}
                <div class="{{ Auth::user()->role == 'teacher' ? 'md:col-span-2' : 'md:col-span-3' }}">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date" name="date" value="{{ request('date', $dateFilter) }}" onchange="this.form.submit()"
                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                </div>

                {{-- FILTER JADWAL (KHUSUS GURU) --}}
                @if(Auth::user()->role == 'teacher')
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pilih Jadwal</label>
                    <div class="relative">
                        <select name="schedule_id" onchange="this.form.submit()" 
                            class="w-full appearance-none border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm cursor-pointer truncate">
                            <option value="">-- Semua Siswa Saya --</option>
                            @foreach($availableSchedules as $schedule)
                                <option value="{{ $schedule->id }}" @selected(request('schedule_id') == $schedule->id)>
                                    {{ substr($schedule->start_time, 0, 5) }} - {{ $schedule->subject->subject_name ?? 'Mapel' }} ({{ $schedule->class->class_name ?? 'Kls' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                @endif

                {{-- FILTER STATUS --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                    <div class="relative">
                        <select name="status" class="w-full appearance-none border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm cursor-pointer">
                            <option value="">Semua</option>
                            <option value="Hadir" @selected(request('status') == 'Hadir')>Hadir</option>
                            <option value="Terlambat" @selected(request('status') == 'Terlambat')>Terlambat</option>
                            <option value="Izin" @selected(request('status') == 'Izin')>Izin</option>
                            <option value="Alpha" @selected(request('status') == 'Alpha')>Alpha / Belum</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- SEARCH --}}
                <div class="{{ Auth::user()->role == 'teacher' ? 'md:col-span-3' : 'md:col-span-4' }}">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." 
                            class="w-full pl-10 border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition shadow-sm hover:shadow-md flex-1 text-center">
                        Filter
                    </button>
                    <a href="{{ route('attendance.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2.5 rounded-lg text-sm font-medium transition flex-none flex items-center justify-center" title="Reset Filter">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- 3. Data Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-0 border-b border-gray-100">
                <div class="flex space-x-8">
                    <a href="{{ route('attendance.index') }}" class="pb-4 text-sm font-medium {{ !request('status') && !request('date') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Default</a>
                    <a href="{{ route('attendance.index', ['date' => date('Y-m-d')]) }}" class="pb-4 text-sm font-medium {{ request('date') == date('Y-m-d') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Hari Ini</a>
                </div>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            Laporan: {{ \Carbon\Carbon::parse($dateFilter)->translatedFormat('d F Y') }}
                        </h3>
                        {{-- INFO JADWAL TERPILIH --}}
                        @if(isset($selectedSchedule) && $selectedSchedule)
                            <p class="text-sm text-blue-600 mt-1 flex items-center">
                                <i class="fas fa-chalkboard-teacher mr-2"></i> 
                                <span>
                                    <strong>{{ $selectedSchedule->subject->subject_name }}</strong> - Kelas {{ $selectedSchedule->class->class_name }}
                                    <span class="text-gray-500 text-xs ml-1">({{ substr($selectedSchedule->start_time, 0, 5) }} - {{ substr($selectedSchedule->end_time, 0, 5) }})</span>
                                </span>
                            </p>
                        @elseif(Auth::user()->role == 'teacher')
                            <p class="text-xs text-gray-500 mt-1">Menampilkan seluruh siswa bimbingan Anda.</p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button onclick="toggleModal('cameraModal')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center">
                                <i class="fas fa-camera mr-2"></i> Otomatis
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">NISN</th>
                                <th class="px-6 py-4">Kelas</th>
                                <th class="px-6 py-4 text-center">Jam Masuk</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50 transition group">
                                    {{-- Data Siswa --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->user->full_name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ $student->nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ $student->class->class_name ?? '-' }}</div>
                                    </td>

                                    {{-- Jam Masuk --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-mono">
                                            {{ $student->today_time != '-' ? \Carbon\Carbon::parse($student->today_time)->format('H:i') : '-' }}
                                        </span>
                                    </td>

                                    {{-- Badge Status (Text) --}}
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $statusLabel = $student->today_status;
                                            $statusClass = match($statusLabel) {
                                                'Hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'Terlambat' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'Izin' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'Sakit' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                'Alpha' => 'bg-rose-100 text-rose-700 border-rose-200',
                                                default => 'bg-gray-100 text-gray-400 border-gray-200 dashed border'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    {{-- KOLOM AKSI (TOMBOL H, I, S, A) --}}
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            // Ambil Log berdasarkan Filter Query Controller
                                            $log = $student->attendanceLogs->first();
                                            $routeUrl = $log ? route('attendance.update', $log->id) : route('attendance.store');
                                            $currentStatus = $log ? $log->status : null;
                                        @endphp

                                        <form action="{{ $routeUrl }}" method="POST" class="flex justify-center items-center gap-1">
                                            @csrf
                                            @if($log)
                                                @method('PUT')
                                            @endif
                                            
                                            <input type="hidden" name="student_nisn" value="{{ $student->nisn }}">
                                            <input type="hidden" name="date" value="{{ request('date', $dateFilter) }}">
                                            
                                            {{-- LOGIKA PENTING: Jika update, pertahankan jam lama. Jika baru, pakai jam sekarang --}}
                                            <input type="hidden" name="time_log" value="{{ $log ? $log->time_log : \Carbon\Carbon::now()->toTimeString() }}">
                                            
                                            {{-- LOGIKA PENTING: Kirim Schedule ID agar status tidak lari ke jadwal lain --}}
                                            @if(isset($selectedSchedule))
                                                <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                                            @endif

                                            {{-- Tombol H (Hadir) --}}
                                            <button type="submit" name="status" value="Hadir" 
                                                class="w-8 h-8 rounded-lg font-bold text-xs transition border shadow-sm
                                                {{ in_array($currentStatus, ['Hadir', 'Terlambat']) 
                                                    ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-200' 
                                                    : 'bg-white text-emerald-600 border-gray-200 hover:bg-emerald-50 hover:border-emerald-300' 
                                                }}" title="Set Hadir">H</button>

                                            {{-- Tombol I (Izin) --}}
                                            <button type="submit" name="status" value="Izin" 
                                                class="w-8 h-8 rounded-lg font-bold text-xs transition border shadow-sm
                                                {{ $currentStatus == 'Izin' 
                                                    ? 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200' 
                                                    : 'bg-white text-blue-600 border-gray-200 hover:bg-blue-50 hover:border-blue-300' 
                                                }}" title="Set Izin">I</button>

                                            {{-- Tombol S (Sakit) --}}
                                            <button type="submit" name="status" value="Sakit" 
                                                class="w-8 h-8 rounded-lg font-bold text-xs transition border shadow-sm
                                                {{ $currentStatus == 'Sakit' 
                                                    ? 'bg-purple-600 text-white border-purple-600 ring-2 ring-purple-200' 
                                                    : 'bg-white text-purple-600 border-gray-200 hover:bg-purple-50 hover:border-purple-300' 
                                                }}" title="Set Sakit">S</button>

                                            {{-- Tombol A (Alpha) --}}
                                            <button type="submit" name="status" value="Alpha" 
                                                class="w-8 h-8 rounded-lg font-bold text-xs transition border shadow-sm
                                                {{ $currentStatus == 'Alpha' 
                                                    ? 'bg-rose-600 text-white border-rose-600 ring-2 ring-rose-200' 
                                                    : 'bg-white text-rose-600 border-gray-200 hover:bg-rose-50 hover:border-rose-300' 
                                                }}" title="Set Alpha">A</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Data siswa tidak ditemukan untuk kriteria ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl text-xs text-gray-500">
                    Menampilkan seluruh siswa untuk tanggal {{ \Carbon\Carbon::parse($dateFilter)->format('d-m-Y') }}
                </div>
            </div>
        </div>
    </main>

    {{-- MODALS SECTION --}}

    {{-- Camera (CCTV) Modal --}}
   <div id="cameraModal" class="fixed inset-0 z-50 flex items-center justify-center hidden w-full h-full bg-slate-900/80 backdrop-blur-md transition-all duration-300">
        <div class="relative w-full max-w-5xl mx-4 bg-white/95 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-white/20">
            
            <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-white to-gray-50">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <i class="fas fa-expand"></i>
                        </span>
                        Eduface: Just Face It
                    </h3>
                    <p class="text-xs font-medium text-slate-400 mt-1 ml-11 uppercase tracking-wider">Sistem Absensi Wajah</p>
                </div>
                <button onclick="toggleModal('cameraModal')" class="group p-2 rounded-full hover:bg-red-50 transition-all duration-200">
                    <i class="fas fa-times text-xl text-gray-300 group-hover:text-red-500 transition-colors"></i>
                </button>
            </div>

            <div class="p-8 overflow-y-auto custom-scrollbar bg-[#F8FAFC]">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-full">
                    
                    <div class="lg:col-span-7 flex flex-col gap-6">
                        <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-blue-500">
                                    <i class="fas fa-video"></i>
                                </div>
                                <select id="cameraSelect" class="w-full bg-transparent text-slate-700 text-sm font-medium focus:ring-0 border-none block pl-10 py-2.5 cursor-pointer">
                                    <option value="">Inisiasi Sumber Kamera...</option>
                                </select>
                            </div>
                            <button id="btn-refresh" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>

                        <div class="relative w-full aspect-video bg-slate-900 rounded-3xl overflow-hidden shadow-2xl shadow-blue-900/10 ring-4 ring-white">
                            <video id="video" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1] opacity-90"></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent pointer-events-none"></div>

                            <div class="absolute top-0 w-full h-1 bg-blue-400 shadow-[0_0_20px_rgba(59,130,246,0.8)] animate-scan opacity-80 pointer-events-none"></div>

                            <div class="absolute top-6 left-6 flex items-center gap-3">
                                <div class="backdrop-blur-md bg-black/40 px-4 py-2 rounded-full border border-white/10 flex items-center gap-2.5 shadow-lg">
                                    <span class="relative flex h-3 w-3">
                                        <span id="status-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75 hidden"></span>
                                        <span id="status-indicator" class="relative inline-flex rounded-full h-3 w-3 bg-slate-400"></span>
                                    </span>
                                    <span id="status-text" class="text-xs font-bold text-white tracking-wide uppercase">Offline</span>
                                </div>
                            </div>

                            <div class="absolute inset-0 border-[3px] border-white/10 rounded-3xl pointer-events-none"></div>
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 border border-blue-400/30 rounded-2xl">
                                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-blue-400"></div>
                                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-blue-400"></div>
                                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-blue-400"></div>
                                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-blue-400"></div>
                            </div>
                        </div>

                        <button id="btn-cctv" onclick="toggleCCTV()" class="w-full group relative overflow-hidden rounded-2xl bg-blue-600 px-8 py-4 transition-all duration-300 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/25 active:scale-[0.99]">
                            <div class="relative flex items-center justify-center gap-2 text-white font-bold tracking-wide">
                                <i class="fas fa-play text-sm group-hover:scale-110 transition-transform"></i>
                                <span>MULAI</span>
                            </div>
                        </button>
                    </div>

                    <div class="lg:col-span-5 flex flex-col h-full gap-6">
                        <div class="flex-1 bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-50 bg-white flex justify-between items-center">
                                <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Live Logs</h4>
                                <span id="detection-count" class="bg-blue-50 text-blue-600 text-[10px] font-bold px-3 py-1 rounded-full">0 Terdeteksi</span>
                            </div>
                            
                            <div id="logContainer" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar bg-slate-50/50 relative">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-300 pointer-events-none">
                                    <div class="w-20 h-20 bg-white rounded-full shadow-sm flex items-center justify-center mb-4">
                                        <i class="fas fa-fingerprint text-3xl text-slate-200"></i>
                                    </div>
                                    <p class="text-sm font-medium">Menunggu Data...</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/20">
                            <div class="flex items-start gap-4">
                                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                    <i class="fas fa-lightbulb text-white text-lg"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-sm mb-1">Tips Pro</h5>
                                    <p class="text-xs text-blue-50 leading-relaxed opacity-90">
                                        Pastikan wajah siswa mendapat pencahayaan yang merata. Sistem akan mencatat kehadiran secara otomatis setelah pengenalan berhasil.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> 
@endsection
@push('scripts')
<script>
    const API_URL = "http://127.0.0.1:8001";
    
    let isRunning = false;
    let isProcessing = false;
    let currentStream = null;

    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            
            if (modalID === 'cameraModal') {
                setTimeout(initCameraModal, 100);
            }
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            
            if (modalID === 'cameraModal') {
                stopCCTV();
                stopStream();
            }
        }
    }

    window.onclick = function(event) {
        const modals = ['addModal', 'editModal', 'viewModal', 'cameraModal'];
        modals.forEach(id => {
            if (event.target == document.getElementById(id)) {
                toggleModal(id);
            }
        });
    }

    function openEditModal(log) {
        document.getElementById('edit_date').value = log.date;
        document.getElementById('edit_time_log').value = log.time_log;
        document.getElementById('edit_status').value = log.status;
        
        let url = "{{ route('attendance.update', ':id') }}";
        url = url.replace(':id', log.id);
        document.getElementById('editForm').action = url;
        toggleModal('editModal');
    }

    function openViewModal(name, date, timeLog, status) {
        document.getElementById('view_name').innerText = name;
        document.getElementById('view_date').innerText = date;
        document.getElementById('view_in').innerText = timeLog ? timeLog.substring(0,5) : '-';
        document.getElementById('view_status').innerText = status;
        toggleModal('viewModal');
    }

    async function populateCameraList() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            const cameraSelect = document.getElementById('cameraSelect');
            cameraSelect.innerHTML = '';
            
            videoDevices.forEach((device, idx) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Camera ${idx + 1}`;
                cameraSelect.appendChild(option);
            });
            
            if (videoDevices.length > 0 && !currentStream) {
                startStream(videoDevices[0].deviceId);
            }
        } catch (err) { 
            console.error("Error accessing cameras:", err);
            showCameraError("Tidak dapat mengakses kamera.");
        }
    }

    async function startStream(deviceId) {
        if (currentStream) {
            currentStream.getTracks().forEach(t => t.stop());
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    deviceId: deviceId ? { exact: deviceId } : undefined, 
                    width: { ideal: 640 },
                    height: { ideal: 480 } 
                },
                audio: false
            });
            currentStream = stream;
            const video = document.getElementById('video');
            video.srcObject = stream;
            
            const errorDiv = document.querySelector('.camera-error');
            if(errorDiv) errorDiv.remove();

        } catch (err) { 
            console.error("Error starting stream:", err);
            showCameraError("Gagal memulai kamera.");
        }
    }

    function stopStream() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        const video = document.getElementById('video');
        video.srcObject = null;
    }

    function showCameraError(message) {
        const videoContainer = document.querySelector('.relative.bg-black.rounded-lg');
        let errorDiv = videoContainer.querySelector('.camera-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'camera-error absolute inset-0 bg-gray-900 flex flex-col items-center justify-center text-white p-4 text-center z-10';
            videoContainer.appendChild(errorDiv);
        }
        
        errorDiv.innerHTML = `<i class="fas fa-camera-slash text-3xl mb-3 text-red-500"></i><p class="text-sm font-medium">${message}</p>`;
    }

    function initCameraModal() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(s => { 
                s.getTracks().forEach(t => t.stop()); 
                populateCameraList(); 
            })
            .catch(e => {
                showCameraError("Izin kamera ditolak.");
            });
            
        const refreshBtn = document.getElementById('btn-refresh');
        if(refreshBtn) refreshBtn.onclick = populateCameraList;
        
        const camSelect = document.getElementById('cameraSelect');
        if(camSelect) camSelect.onchange = (e) => startStream(e.target.value);
    }

    async function checkServerStatus() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 2000);
            const response = await fetch(`${API_URL}/`, { method: 'GET', signal: controller.signal });
            clearTimeout(timeoutId);
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    async function toggleCCTV() {
        const btnCctv = document.getElementById('btn-cctv');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        
        if (isRunning) {
            stopCCTV();
        } else {
            const video = document.getElementById('video');
            if (!video.srcObject) {
                alert('Kamera belum siap.');
                return;
            }
            
            statusText.innerText = "Mengecek server...";
            statusIndicator.className = "w-3 h-3 bg-yellow-400 rounded-full animate-pulse";
            
            const isServerReady = await checkServerStatus();
            
            if (!isServerReady) {
                statusText.innerText = "Server Error";
                statusIndicator.className = "w-3 h-3 bg-red-500 rounded-full";
                showAPIError("Server Python tidak terdeteksi di " + API_URL);
                return;
            }
            
            isRunning = true;
            btnCctv.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Monitoring';
            btnCctv.classList.replace('bg-blue-600', 'bg-red-600');
            btnCctv.classList.replace('hover:bg-blue-700', 'hover:bg-red-700');
            
            statusIndicator.className = "w-3 h-3 bg-green-500 rounded-full animate-pulse";
            statusText.innerText = "Mendeteksi wajah...";
            
            kirimFrame(); 
        }
    }

    function stopCCTV() {
        isRunning = false; 
        isProcessing = false;
        
        const btnCctv = document.getElementById('btn-cctv');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        
        if(btnCctv) {
            btnCctv.innerHTML = '<i class="fas fa-play mr-2"></i> Start CCTV';
            btnCctv.classList.replace('bg-red-600', 'bg-blue-600');
            btnCctv.classList.replace('hover:bg-red-700', 'hover:bg-blue-700');
        }
        
        if(statusIndicator) statusIndicator.className = "w-3 h-3 bg-gray-400 rounded-full";
        if(statusText) statusText.innerText = "Standby";
    }
    
    async function kirimFrame() {
        if (!isRunning) return;
        
        const canvas = document.getElementById('canvas');
        const video = document.getElementById('video');
        
        if (video.readyState !== video.HAVE_ENOUGH_DATA || isProcessing) {
            if(isRunning) requestAnimationFrame(kirimFrame);
            return;
        }

        isProcessing = true;

        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        canvas.toBlob(async (blob) => {
            const formData = new FormData();
            formData.append("file", blob, "frame.jpg");
            
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000); 
                
                const response = await fetch(`${API_URL}/predict`, {
                    method: "POST",
                    body: formData,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                
                if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
                
                const data = await response.json();
                await processResponse(data);
                
            } catch (error) {
                console.error('API Error:', error);
            } finally {
                isProcessing = false;
                if (isRunning) {
                    requestAnimationFrame(kirimFrame);
                }
            }
        }, 'image/jpeg', 0.7);
    }

    async function processResponse(data) {
        const statusText = document.getElementById('status-text');
        
        if (data.status === 'success' && data.new_entries && data.new_entries.length > 0) {
            statusText.innerText = `Terdeteksi: ${data.new_entries.length} wajah baru`;
            for (const siswa of data.new_entries) {
                await kirimAbsensiKeServer(siswa.nisn, siswa.name);
            }
        } 
        else if (data.all_detected && data.all_detected.length > 0) {
             statusText.innerText = "Wajah terdeteksi (Sudah Absen)";
        } 
        else {
             statusText.innerText = "Mencari wajah...";
        }
    }
    
    function tambahLog(nisn, name, waktu) {
        const logContainer = document.getElementById('logContainer');
        const countBadge = document.getElementById('detection-count');
        
        if (logContainer.querySelector('.text-center')) {
            logContainer.innerHTML = "";
        }
        
        const existingLogs = Array.from(logContainer.querySelectorAll('.nisn'));
        const isDuplicate = existingLogs.some(log => log.textContent.trim() === String(nisn));
        
        if (!isDuplicate) {
            const div = document.createElement('div');
            div.className = 'bg-white p-3 rounded-lg border border-green-200 flex justify-between items-center shadow-sm animate-fade-in-up mb-2';
            div.innerHTML = `
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="nisn font-bold text-gray-900 text-xs">${nisn}</span>
                        <span class="bg-green-100 text-green-800 text-[10px] px-2 py-0.5 rounded-full">Hadir</span>
                    </div>
                    <div class="name text-sm font-medium text-gray-700">${name}</div>
                    <div class="time text-xs text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>${waktu}</div>
                </div>
                <div class="text-green-500 text-lg ml-2">
                    <i class="fas fa-check-circle"></i>
                </div>
            `;
            
            logContainer.prepend(div);
            
            let currentCount = parseInt(countBadge.innerText) || 0;
            countBadge.innerText = currentCount + 1;
            countBadge.classList.remove('hidden');
        }
    }

    async function kirimAbsensiKeServer(nisn, name) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch('/attendance/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    nisn: nisn,
                })
            });
            
            const data = await response.json();

            if (response.ok && data.success) {
                console.log('✅ Sukses DB:', data.message);
                
                const serverTime = data.data.time_log || new Date().toLocaleTimeString('id-ID');
                
                tambahLog(nisn, name, serverTime);
                
                if (typeof showSuccessFeedback === "function") showSuccessFeedback(name);
                if (typeof playTTS === "function") playTTS(name);
                
            } else {
                console.warn('⚠️ Gagal Simpan:', data.message);
            }
        } catch (error) {
            console.error('❌ Error Network Laravel:', error);
        }
    }

    function playTTS(studentName) {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(`${studentName}, absen berhasil.`);
            utterance.lang = 'id-ID';
            utterance.rate = 1;
            speechSynthesis.speak(utterance);
        }
    }

    function showSuccessFeedback(name) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl z-50 flex items-center space-x-3 transition-all duration-500 transform translate-x-full';
        toast.innerHTML = `<i class="fas fa-check-circle"></i> <span>${name} Berhasil Absen!</span>`;
        document.body.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
        });
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    function showAPIError(message) {
        const logContainer = document.getElementById('logContainer');
        if (!logContainer.querySelector('.bg-green-100')) {
            logContainer.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg mb-1"></i>
                    <p class="text-red-700 text-xs font-medium">${message}</p>
                </div>
            `;
        }
    }
</script>
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
    @keyframes scan {
        0% { top: 0%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .animate-scan {
        animation: scan 2.5s linear infinite;
    }
</style>
@endpush
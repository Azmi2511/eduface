<?php
$active_menu = 'dashboard';
?>

@extends('layouts.app')

@section('title', 'Dashboard')

@section('header_title', 'Dashboard')

@section('content')
    <div class="flex-1 flex flex-col overflow-hidden bg-[#F3F6FD]">
        <main class="flex-1 overflow-y-auto p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-100 text-blue-500 flex items-center justify-center text-2xl mr-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_students) }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                        <p class="text-xs text-blue-500 mt-1 font-semibold">Terdaftar di database</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1">
                    <div
                        class="w-14 h-14 rounded-xl bg-green-100 text-green-500 flex items-center justify-center text-2xl mr-4">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $attendance_percentage }}%</h3>
                        <p class="text-sm text-gray-500 font-medium">Kehadiran Hari Ini</p>
                        <p class="text-xs text-green-500 mt-1 font-semibold">{{ $total_present }} Siswa Hadir</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1">
                    <div
                        class="w-14 h-14 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center text-2xl mr-4">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $total_late }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Terlambat Hari Ini</p>
                        <p class="text-xs text-orange-500 mt-1 font-semibold">Lewat jam {{ $late_limit }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm flex items-center transition-transform hover:-translate-y-1">
                    <div
                        class="w-14 h-14 rounded-xl bg-red-100 text-red-500 flex items-center justify-center text-2xl mr-4">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $total_absent }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Tidak Hadir</p>
                        <p class="text-xs text-red-500 mt-1 font-semibold">Belum check-in</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Statistik 7 Hari Terakhir</h3>
                    </div>
                    <div class="w-full h-80">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Aktivitas Terbaru</h3>
                    <div class="space-y-4 overflow-y-auto max-h-80">
                        @forelse($result_activity as $row)
                            <div
                                class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition-colors border-b border-gray-50">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $row->full_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        Absen jam <span class="font-bold">{{ date('H:i', strtotime($row->time_log)) }}</span>
                                        <span
                                            class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $row->status == 'Hadir' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">{{ $row->status }}</span>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-400 text-sm">Belum ada aktivitas hari ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div
                    class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna Terbaru</h3>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($result_users as $idx => $row)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">{{ $idx + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs mr-3">
                                                {{ strtoupper(substr($row->username, 0, 2)) }}</div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $row->username }}</div>
                                                <div class="text-xs text-gray-500">{{ $row->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-700">{{ $row->role }}</span>
                                    </td>
                                    <td class="px-6 py-4"><span class="text-sm text-gray-500">{{ $row->email ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($row->is_active == '1')
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-700">Aktif</span>
                                        @else
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4"><span
                                            class="text-sm text-gray-500">{{ date('d M Y', strtotime($row->created_at)) }}</span>
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
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        const labelsFromPHP = @json($chart_labels);
        const dataFromPHP = @json($chart_data);

        const ctx = document.getElementById('attendanceChart').getContext('2d');

        const data = {
            labels: labelsFromPHP,
            datasets: [{
                label: 'Jumlah Hadir',
                data: dataFromPHP,
                borderColor: '#2F80ED',
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(47, 128, 237, 0.4)');
                    gradient.addColorStop(1, 'rgba(47, 128, 237, 0.0)');
                    return gradient;
                },
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2F80ED',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6', drawBorder: false }, ticks: { color: '#9ca3af', stepSize: 1 } }, x: { grid: { display: false }, ticks: { color: '#9ca3af' } } }
            }
        };

        new Chart(ctx, config);
    </script>
@endpush
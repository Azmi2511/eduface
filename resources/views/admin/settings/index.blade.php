@php
    $active_menu = 'settings';
@endphp
@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')
@section('header_title', 'Konfigurasi Sistem')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush

@section('content')
    <div class="flex-1 flex flex-col bg-[#F8FAFC]">
        <main class="flex-1 overflow-y-auto p-6 lg:p-10">

            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">Konfigurasi Sistem Absensi</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola parameter operasional dan keamanan platform Anda.</p>
            </div>

            @if(session('success'))
                <div
                    class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-3"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">

                <div
                    class="bg-white rounded-2xl border border-slate-200 p-2 shadow-sm overflow-x-auto whitespace-nowrap scrollbar-none">
                    <nav class="flex flex-row space-x-1 min-w-max">
                        <button onclick="switchTab('general', this)"
                            class="tab-link active-tab flex items-center px-5 py-3 text-sm font-semibold rounded-xl transition-all bg-blue-600 text-white shadow-md">
                            <i class="fas fa-school w-4 mr-2"></i>Umum
                        </button>
                        <button onclick="switchTab('attendance', this)"
                            class="tab-link flex items-center px-5 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="far fa-clock w-4 mr-2"></i>Absensi
                        </button>
                        <button onclick="switchTab('notification', this)"
                            class="tab-link flex items-center px-5 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="far fa-bell w-4 mr-2"></i>Notifikasi
                        </button>
                        <button onclick="switchTab('security', this)"
                            class="tab-link flex items-center px-5 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="fas fa-shield-alt w-4 mr-2"></i>Keamanan
                        </button>
                        <button onclick="switchTab('backup', this)"
                            class="tab-link flex items-center px-5 py-3 text-sm font-semibold rounded-xl transition-all text-slate-600 hover:bg-slate-50">
                            <i class="fas fa-database w-4 mr-2"></i>Backup
                        </button>
                    </nav>
                </div>

                <div class="w-full">

                    <div id="general" class="tab-content animate-in fade-in duration-300">
                        <form action="{{ route('settings.update.general') }}" method="POST">
                            @csrf
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div class="p-6 lg:p-8">
                                    <h3 class="text-lg font-bold text-slate-800 mb-6">Informasi Sekolah</h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama
                                                Sekolah</label>
                                            <input type="text" name="school_name"
                                                value="{{ old('school_name', $settings->school_name) }}"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                        </div>

                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-500 uppercase tracking-wider">NPSN</label>
                                            <input type="text" name="npsn" value="{{ old('npsn', $settings->npsn) }}"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                        </div>

                                        <div class="md:col-span-2 space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat</label>
                                            <textarea name="address" rows="3"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">{{ old('address', $settings->address) }}</textarea>
                                        </div>

                                        <div class="md:col-span-2 space-y-2">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lokasi
                                                Sekolah</label>

                                            <div id="map" class="w-full h-80 rounded-xl border border-slate-200"></div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                <div>
                                                    <label
                                                        class="text-[11px] font-medium text-slate-500 block mb-1">Latitude</label>
                                                    <input type="text" id="school_latitude" name="school_latitude"
                                                        value="{{ old('school_latitude', $settings->school_latitude) }}"
                                                        readonly
                                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                                </div>

                                                <div>
                                                    <label
                                                        class="text-[11px] font-medium text-slate-500 block mb-1">Longitude</label>
                                                    <input type="text" id="school_longitude" name="school_longitude"
                                                        value="{{ old('school_longitude', $settings->school_longitude) }}"
                                                        readonly
                                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                                </div>

                                                <div>
                                                    <label class="text-[11px] font-medium text-slate-500 block mb-1">Radius
                                                        Absensi (meter)</label>
                                                    <input type="number" id="allowed_radius_meters"
                                                        name="allowed_radius_meters"
                                                        value="{{ old('allowed_radius_meters', $settings->allowed_radius_meters ?? 100) }}"
                                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 lg:p-8 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-sm text-sm">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="attendance" class="tab-content hidden"></div>
                    <div id="notification" class="tab-content hidden"></div>
                    <div id="security" class="tab-content hidden"></div>
                    <div id="backup" class="tab-content hidden"></div>

                </div>
            </div>
        </main>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

        <script>
            function switchTab(tabId, element) {
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById(tabId).classList.remove('hidden');

                document.querySelectorAll('.tab-link').forEach(l => {
                    l.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                    l.classList.add('text-slate-600', 'hover:bg-slate-50');
                });

                element.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                element.classList.remove('text-slate-600', 'hover:bg-slate-50');
            }

            const defaultLat = {{ $settings->school_latitude ?? -0.947083 }};
            const defaultLng = {{ $settings->school_longitude ?? 100.417181 }};
            const defaultRadius = {{ $settings->allowed_radius_meters ?? 100 }};

            const map = L.map('map').setView([defaultLat, defaultLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            let circle = L.circle([defaultLat, defaultLng], {
                radius: defaultRadius
            }).addTo(map);

            function updateLocation(lat, lng) {
                document.getElementById('school_latitude').value = lat;
                document.getElementById('school_longitude').value = lng;
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
            }

            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateLocation(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('allowed_radius_meters').addEventListener('input', function () {
                circle.setRadius(this.value);
            });
        </script>
    @endpush
@endsection
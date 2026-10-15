@extends('layouts.app')

@section('title', 'Registrasi Wajah')

@section('header_title', 'Registrasi Wajah Siswa')

@section('content')
    <div class="min-h-screen bg-[#F3F6FD] p-6 lg:p-8">
        <div class="max-w-6xl mx-auto mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-500">Pendaftaran Biometrik</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Daftarkan wajah siswa</h2>
            <p class="mt-1 text-sm text-slate-500">Pastikan wajah berada di tengah frame sebelum menyimpan data.</p>
        </div>

        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-6">

            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Data siswa</h3>
                        <p class="text-xs text-slate-400">Langkah 1 dari 2</p>
                    </div>
                </div>
                <form id="form-register" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">NISN</label>
                        <input type="text" id="nisn" value="{{ $registeredNisn ?? '' }}" {{ !empty($registeredNisn) ? 'readonly' : '' }}
                            class="w-full mt-1 px-4 py-3 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition"
                            placeholder="Masukkan NISN">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Nama Siswa</label>
                        <input type="text" id="name" class="w-full mt-1 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700" readonly>
                    </div>

                    <div class="flex items-center gap-4 mt-3 rounded-2xl bg-slate-50 border border-slate-100 p-3 min-h-[82px]">
                        <img id="student-photo" src="" class="w-14 h-14 rounded-xl object-cover border border-white shadow-sm hidden">

                        <div id="student-info" class="text-sm text-gray-500 hidden">
                            Data siswa ditemukan
                        </div>
                    </div>

                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600">
                            <span id="face-status-dot" class="w-2 h-2 rounded-full bg-slate-300"></span>
                            Status wajah
                        </div>
                        <p id="face-status" class="mt-2 text-sm font-semibold text-slate-700">Menunggu wajah...</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm">
                        Simpan Data Wajah
                    </button>
                </form>
            </div>

            <div class="lg:col-span-3 bg-slate-950 rounded-3xl shadow-xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4 text-white">
                    <div>
                        <h2 class="text-lg font-bold">Posisikan wajah</h2>
                        <p class="text-xs text-white/50 mt-1">Langkah 2 dari 2</p>
                    </div>
                    <div class="text-xs text-white/60"><i class="fas fa-camera mr-1"></i> Live</div>
                </div>

                <div class="bg-black rounded-2xl overflow-hidden relative ring-1 ring-white/10">
                    <video id="video" autoplay muted playsinline class="w-full h-[320px] object-cover scale-x-[-1]"></video>

                    <div id="face-guide" class="register-face-guide pointer-events-none absolute left-1/2 top-[48%] h-[78%] w-[44%] -translate-x-1/2 -translate-y-1/2 border-4 border-white/70 shadow-[0_0_0_9999px_rgba(0,0,0,0.28)] transition-colors duration-200">
                        <div id="face-instruction" class="absolute -bottom-12 left-1/2 w-max max-w-[90vw] -translate-x-1/2 rounded-full bg-black/75 px-4 py-2 text-center text-xs font-semibold text-white">
                            Posisikan wajah di dalam frame
                        </div>
                    </div>

                    <div class="absolute top-3 left-3 bg-black/60 px-3 py-1 rounded text-white text-xs">
                        <span id="indicator" class="w-2 h-2 bg-gray-400 inline-block rounded-full mr-2"></span>
                        <span id="status">Menyiapkan kamera...</span>
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-white/50">Lepaskan masker dan gunakan pencahayaan yang merata.</p>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('script/face-api.min.js') }}"></script>
    <script>
        if (typeof faceapi === 'undefined') {
            document.write('<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"><\/script>');
        }
    </script>

    <script>
        let descriptorData = null;
        let modelsLoaded = false;
        let stream = null;
        let autoSubmitted = false;
        let captureInProgress = false;
        let captureTimer = null;
        let faceReady = false;

        document.addEventListener("DOMContentLoaded", () => {
            initCamera();
            loadModels();
            initNisnListener();
            startAutoCapture();
            initForm();
        });

        async function initCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "user",
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false
                });

                const video = document.getElementById('video');
                video.srcObject = stream;
                await video.play();

            } catch (err) {
                document.getElementById('status').innerText = "Camera Error";
            }
        }

        async function loadModels() {
            const status = document.getElementById('status');
            const indicator = document.getElementById('indicator');

            try {
                status.innerText = "Loading AI...";
                indicator.className = "w-2 h-2 bg-yellow-400 inline-block rounded-full animate-pulse";

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                ]);

                modelsLoaded = true;

                status.innerText = "Camera Ready";
                indicator.className = "w-2 h-2 bg-green-500 inline-block rounded-full";

            } catch (err) {
                status.innerText = "Error";
                indicator.className = "w-2 h-2 bg-red-500 inline-block rounded-full";
            }
        }

        function initNisnListener() {
            const nisnInput = document.getElementById('nisn');
            const nameInput = document.getElementById('name');
            const photo = document.getElementById('student-photo');
            const info = document.getElementById('student-info');

            nisnInput.addEventListener('keyup', async function () {
                const nisn = this.value.trim();

                if (nisn.length < 5) {
                    nameInput.value = '';
                    photo.classList.add('hidden');
                    info.classList.add('hidden');
                    return;
                }

                try {
                    const res = await fetch(`/api/v1/student/${nisn}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!res.ok) {
                        nameInput.value = '';
                        photo.classList.add('hidden');
                        info.classList.add('hidden');
                        return;
                    }

                    const data = await res.json();

                    if (data.status === 'success') {
                        nameInput.value = data.data.name;

                        if (data.data.photo) {
                            photo.src = data.data.photo;
                            photo.classList.remove('hidden');
                        } else {
                            photo.classList.add('hidden');
                        }

                        info.classList.remove('hidden');

                    } else {
                        nameInput.value = '';
                        photo.classList.add('hidden');
                        info.classList.add('hidden');
                    }

                } catch (err) {
                    nameInput.value = '';
                    photo.classList.add('hidden');
                    info.classList.add('hidden');
                }
            });
        }

        async function startAutoCapture() {
            const capture = async () => {

                if (!modelsLoaded || captureInProgress) {
                    captureTimer = setTimeout(capture, 500);
                    return;
                }

                const video = document.getElementById('video');

                if (!video.srcObject) {
                    captureTimer = setTimeout(capture, 500);
                    return;
                }

                captureInProgress = true;
                try {
                    const detection = await faceapi
                        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                            inputSize: 224,
                            scoreThreshold: 0.5
                        }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        faceReady = updateFaceGuide(null);
                        descriptorData = null;
                        setFaceStatus('Tidak ada wajah', 'bg-amber-400');
                    } else {
                        faceReady = updateFaceGuide(detection);
                        descriptorData = Array.from(detection.descriptor);
                        setFaceStatus(faceReady ? "Wajah siap disimpan" : "Wajah terdeteksi, posisi bisa disesuaikan", faceReady ? 'bg-emerald-400' : 'bg-amber-400');
                    }
                } finally {
                    captureInProgress = false;
                    captureTimer = setTimeout(capture, 500);
                }
            };

            capture();
        }

        function updateFaceGuide(detection) {
            const guide = document.getElementById('face-guide');
            const instruction = document.getElementById('face-instruction');
            const video = document.getElementById('video');

            if (!detection || !video.videoWidth) {
                guide.className = guide.className.replace(/border-\S+/g, '') + ' border-white/70';
                instruction.textContent = 'Posisikan wajah di dalam frame';
                return false;
            }

            const box = detection.detection.box;
            const centerX = (box.x + box.width / 2) / video.videoWidth;
            const centerY = (box.y + box.height / 2) / video.videoHeight;
            const faceWidth = box.width / video.videoWidth;
            const centered = centerX > 0.38 && centerX < 0.62 && centerY > 0.35 && centerY < 0.68;
            const correctDistance = faceWidth > 0.28 && faceWidth < 0.62;
            const ready = centered && correctDistance;

            guide.className = guide.className.replace(/border-\S+/g, '') + (ready ? ' border-emerald-400' : ' border-amber-300');
            instruction.textContent = !centered
                ? 'Posisikan wajah di tengah frame'
                : faceWidth <= 0.28
                    ? 'Dekatkan wajah sedikit'
                    : faceWidth >= 0.62
                        ? 'Jauhkan wajah sedikit'
                        : 'Posisi sudah pas, tahan sebentar';
            return ready;
        }

        function setFaceStatus(message, dotClass) {
            const status = document.getElementById('face-status');
            const dot = document.getElementById('face-status-dot');
            status.textContent = message;
            dot.className = `w-2 h-2 rounded-full ${dotClass}`;
        }

        function initForm() {
            document.getElementById('form-register')
                .addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const nisn = document.getElementById('nisn').value.trim();

                    if (!nisn) {
                        showToast('error', 'Masukkan NISN terlebih dahulu.', 'border-red-500');
                        return;
                    }

                    if (!descriptorData) {
                        showToast('warning', 'Wajah belum terdeteksi. Silakan hadap kamera.', 'border-yellow-500');
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const res = await fetch("{{ route('face.register') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                nisn: nisn,
                                descriptor: descriptorData
                            })
                        });

                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Registrasi Berhasil',
                                text: data.message || 'Wajah berhasil didaftarkan!',
                                position: 'center',
                                confirmButtonText: 'Tutup'
                            });

                            autoSubmitted = false;
                            descriptorData = null;
                            document.getElementById('face-status').innerText = "Menunggu wajah...";
                        } else {
                            showToast('error', data.message || 'Gagal registrasi wajah.', 'border-red-500');
                            autoSubmitted = false;
                        }
                    } catch (err) {
                        console.error(err);
                        showToast('error', 'Kesalahan sistem/koneksi saat mendaftarkan wajah.', 'border-red-500');
                        autoSubmitted = false;
                    }
                });
        }
    </script>
    <style>
        .register-face-guide {
            border-radius: 48% 48% 44% 44% / 38% 38% 62% 62%;
        }

        @media (max-width: 640px) {
            .register-face-guide {
                width: 62%;
            }
        }
    </style>
@endpush
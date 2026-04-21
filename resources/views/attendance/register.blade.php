@extends('layouts.app')

@section('title', 'Registrasi Wajah')

@section('header_title', 'Registrasi Wajah Siswa')

@section('content')
    <div class="min-h-screen bg-[#F3F6FD] p-6">
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white rounded-3xl shadow p-6">
                <form id="form-register" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">NISN</label>
                        <input type="text" id="nisn"
                            class="w-full mt-1 px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="Masukkan NISN">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Nama Siswa</label>
                        <input type="text" id="name" class="w-full mt-1 px-4 py-3 rounded-xl border bg-gray-100" readonly>
                    </div>

                    <div class="flex items-center gap-4 mt-3">
                        <img id="student-photo" src="" class="w-16 h-16 rounded-xl object-cover border hidden">

                        <div id="student-info" class="text-sm text-gray-500 hidden">
                            Data siswa ditemukan
                        </div>
                    </div>

                    <div class="text-sm text-gray-500">
                        Status: <span id="face-status">Menunggu wajah...</span>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition">
                        Simpan Data Wajah
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-3xl shadow p-6">
                <h2 class="text-lg font-bold mb-4">Preview Kamera</h2>

                <div class="bg-black rounded-2xl overflow-hidden relative">
                    <video id="video" autoplay muted playsinline class="w-full h-[320px] object-cover scale-x-[-1]"></video>

                    <div class="absolute top-3 left-3 bg-black/60 px-3 py-1 rounded text-white text-xs">
                        <span id="indicator" class="w-2 h-2 bg-gray-400 inline-block rounded-full mr-2"></span>
                        <span id="status">Offline</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script defer src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        let descriptorData = null;
        let modelsLoaded = false;
        let stream = null;

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

                video.onloadedmetadata = () => {
                    video.play();
                };

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
            setInterval(async () => {

                if (!modelsLoaded) return;

                const video = document.getElementById('video');

                if (!video.srcObject) return;

                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    document.getElementById('face-status').innerText = "Tidak ada wajah";
                    return;
                }

                descriptorData = Array.from(detection.descriptor);

                document.getElementById('face-status').innerText = "Wajah siap disimpan";

            }, 1500);
        }

        function initForm() {
            document.getElementById('form-register')
                .addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const nisn = document.getElementById('nisn').value;

                    if (!nisn || !descriptorData) return;

                    const res = await fetch('/api/v1/face/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nisn: nisn,
                            descriptor: descriptorData
                        })
                    });

                    const data = await res.json();

                    if (data.status === 'success') {
                        showToast('success', data.message, 'border-green-500');

                        autoSubmitted = false;
                        descriptorData = null;
                        document.getElementById('face-status').innerText = "Menunggu wajah...";
                    } else {
                        showToast('error', data.message, 'border-red-500');
                        autoSubmitted = false;
                    }
                });
        }
    </script>
@endpush
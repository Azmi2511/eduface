@extends('layouts.app')

@section('title', 'Presensi Wajah')

@section('header_title', 'Terminal Presensi Wajah')

@section('content')
    <div class="flex flex-col h-screen bg-[#F3F6FD]">
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 p-6">

            <!-- CAMERA -->
            <div class="lg:col-span-7">
                <div class="bg-black rounded-3xl overflow-hidden relative">
                    <video id="video" autoplay muted class="w-full h-[400px] object-cover scale-x-[-1]"></video>

                    <div class="absolute top-4 left-4 bg-black/50 px-3 py-1 rounded text-white text-xs">
                        <span id="indicator" class="w-2 h-2 bg-gray-400 inline-block rounded-full mr-2"></span>
                        <span id="status">Offline</span>
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button onclick="toggleScan()" id="btn-scan"
                        class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold">
                        MULAI SCAN
                    </button>
                </div>
            </div>

            <!-- LOG -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-5 shadow">
                <h3 class="font-bold mb-4">Log Absensi</h3>
                <div id="log" class="space-y-3 max-h-[400px] overflow-y-auto">
                    <p class="text-gray-400 text-sm">Belum ada data</p>
                </div>
            </div>

        </main>
    </div>
@endsection

@push('scripts')
    <script defer src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        let isRunning = false;
        let modelsLoaded = false;
        let lastSpoken = null;
        let voices = [];

        speechSynthesis.onvoiceschanged = () => {
            voices = speechSynthesis.getVoices();
        };


        document.addEventListener("DOMContentLoaded", async () => {
            await initCamera();
            await loadModels();
        });

        // ============================
        // INIT CAMERA
        // ============================
        async function initCamera() {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            document.getElementById('video').srcObject = stream;
        }

        // ============================
        // LOAD MODEL
        // ============================
        async function loadModels() {
            const statusText = document.getElementById('status');

            try {
                statusText.innerText = "⏳ Loading AI Model...";

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                ]);

                modelsLoaded = true;

                statusText.innerText = "Kamera Siap Digunakan";

            } catch (err) {
                statusText.innerText = "❌ Gagal load model";
                console.error(err);
            }
        }
        // ============================
        // GET DESCRIPTOR
        // ============================
        async function getDescriptor() {

            if (!modelsLoaded) {
                console.warn("⚠️ Model belum siap");
                return null;
            }

            const video = document.getElementById('video');

            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) return null;

            return Array.from(detection.descriptor);
        }

        // ============================
        // START / STOP SCAN
        // ============================
        async function toggleScan() {
            const btn = document.getElementById('btn-scan');
            const status = document.getElementById('status');
            const indicator = document.getElementById('indicator');

            if (isRunning) {
                isRunning = false;
                btn.innerText = "MULAI SCAN";
                status.innerText = "Standby";
                indicator.className = "w-2 h-2 bg-gray-400 inline-block rounded-full";
                return;
            }

            if (!modelsLoaded) {
                await loadModels();
            }


            isRunning = true;
            btn.innerText = "STOP";
            status.innerText = "Scanning...";
            indicator.className = "w-2 h-2 bg-green-500 inline-block rounded-full animate-pulse";

            scanLoop();
        }

        // ============================
        // LOOP
        // ============================
        async function scanLoop() {
            if (!isRunning) return;

            const descriptor = await getDescriptor();

            if (descriptor) {
                await sendToServer(descriptor);
            }

            setTimeout(scanLoop, 1500);
        }

        // ============================
        // SEND TO LARAVEL
        // ============================
        async function sendToServer(descriptor) {
            try {
                const res = await fetch('/api/v1/face/predict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ descriptor })
                });

                if (!res.ok) {
                    console.error("❌ HTTP Error:", res.status);
                    return;
                }

                const data = await res.json();

                if (data.status === 'success') {
                    showToast('success', data.data.message, 'border-green-500');
                    addLog(data.data);
                    speak(data.data.name);

                } else if (data.status === 'duplicate') {
                    showToast('warning', data.data.message, 'border-yellow-500');
                    addLog(data.data);

                } else {
                    showToast('error', data.message, 'border-red-500');
                }

            } catch (err) {
                console.error(err);

                showToast(
                    'error',
                    'Server error',
                    'border-red-500'
                );
            }
        }

        // ============================
        // REGISTER
        // ============================
        async function registerFace() {
            if (!modelsLoaded) {
                alert("⏳ Model AI masih loading, tunggu sebentar...");
                return;
            }

            const nisn = prompt("Masukkan NISN:");
            if (!nisn) return;

            const descriptor = await getDescriptor();

            if (!descriptor) {
                alert("❌ Wajah tidak terdeteksi");
                return;
            }


            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const res = await fetch('/api/v1/face/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ nisn, descriptor })
            });

            const data = await res.json();
            alert(data.message);
        }

        // ============================
        // LOG UI
        // ============================
        function addLog(data) {
            const log = document.getElementById('log');

            if (log.querySelector('p')) {
                log.innerHTML = '';
            }

            const exists = Array.from(log.children).some(el =>
                el.dataset.nisn === data.nisn
            );
            if (exists) return;

            // =========================
            // MAPPING STATUS
            // =========================
            const statusMap = {
                'Hadir': {
                    border: 'border-green-400 bg-green-50',
                    badge: 'bg-green-100 text-green-700'
                },
                'Terlambat': {
                    border: 'border-yellow-400 bg-yellow-50',
                    badge: 'bg-yellow-100 text-yellow-700'
                },
                'Alpha': {
                    border: 'border-red-400 bg-red-50',
                    badge: 'bg-red-100 text-red-700'
                },
                'Izin': {
                    border: 'border-blue-400 bg-blue-50',
                    badge: 'bg-blue-100 text-blue-700'
                }
            };

            const style = statusMap[data.status] || statusMap['Hadir'];

            const item = document.createElement('div');
            item.className = `p-4 rounded-xl border ${style.border} shadow-sm animate-fade-in`;
            item.dataset.nisn = data.nisn;

            item.innerHTML = `
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-800">${data.name}</p>
                        <p class="text-xs text-gray-500">${data.nisn}</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded-full ${style.badge}">
                        ${data.status}
                    </span>
                </div>

                <div class="text-xs text-gray-400 mt-2">
                    ⏱ ${data.time}
                </div>
            `;

            log.prepend(item);
        }
        // ============================
        // TEXT TO SPEECH
        // ============================
        function speak(name) {
            const text = `${name} berhasil absen`;

            // =========================
            // ANTI SPAM (DEBOUNCE)
            // =========================
            if (lastSpoken === text) return;
            lastSpoken = text;

            // Reset setelah 5 detik agar bisa bicara lagi
            setTimeout(() => {
                lastSpoken = null;
            }, 5000);

            // =========================
            // CLEAR QUEUE (PENTING)
            // =========================
            speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);

            // =========================
            // SET BAHASA
            // =========================
            utterance.lang = "id-ID";

            // =========================
            // PILIH VOICE INDONESIA
            // =========================
            const indoVoice = voices.find(v =>
                v.lang === "id-ID" || v.lang === "id_ID"
            );

            if (indoVoice) {
                utterance.voice = indoVoice;
            }

            // =========================
            // TUNING SUARA
            // =========================
            utterance.rate = 0.9;   // lebih natural
            utterance.pitch = 1;    // standar
            utterance.volume = 1;   // maksimal

            // =========================
            // ERROR HANDLING
            // =========================
            utterance.onerror = (e) => {
                console.error("TTS Error:", e);
            };

            speechSynthesis.speak(utterance);
        }
    </script>
@endpush
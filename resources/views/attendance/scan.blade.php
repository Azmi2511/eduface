@extends('layouts.app')

@section('title', 'Presensi Wajah')

@section('header_title', 'Terminal Presensi Wajah')

@section('content')
    <div class="flex flex-col h-screen bg-[#F3F6FD]">
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 p-6">

            <!-- CAMERA -->
            <div class="lg:col-span-7">
                <div class="bg-black rounded-3xl overflow-hidden relative">
                    <video id="video" autoplay muted playsinline class="w-full h-[400px] object-cover scale-x-[-1]"></video>

                    <div id="face-guide" class="face-guide pointer-events-none absolute left-1/2 top-[48%] h-[78%] w-[44%] -translate-x-1/2 -translate-y-1/2 border-4 border-white/70 shadow-[0_0_0_9999px_rgba(0,0,0,0.25)] transition-colors duration-200">
                        <div id="face-instruction" class="absolute -bottom-12 left-1/2 w-max max-w-[90vw] -translate-x-1/2 rounded-full bg-black/70 px-4 py-2 text-center text-xs font-semibold text-white">
                            Posisikan wajah di dalam frame
                        </div>
                    </div>

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
    <script src="{{ asset('script/face-api.min.js') }}"></script>
    <script>
        if (typeof faceapi === 'undefined') {
            document.write('<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"><\/script>');
        }
    </script>

    <script>
        let isRunning = false;
        let modelsLoaded = false;
        let isProcessing = false;
        let scanTimer = null;
        let lastDescriptor = null;
        let smileDetected = false;
        let smileBaseline = 0;
        let smileBaselineSamples = 0;
        let faceReady = false;
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
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });
            const video = document.getElementById('video');
            video.srcObject = stream;
            await video.play();
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
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 224,
                    scoreThreshold: 0.5
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                updateFaceGuide(null);
                faceReady = false;
                return null;
            }

            faceReady = updateFaceGuide(detection);
            if (!faceReady) {
                return {
                    descriptor: Array.from(detection.descriptor),
                    smileDetected: false
                };
            }

            const smileScore = getSmileScore(detection.landmarks, detection.detection.box);
            if (smileBaselineSamples < 12 && smileScore > 0.18) {
                smileBaseline = smileBaselineSamples === 0
                    ? smileScore
                    : (smileBaseline * 0.8) + (smileScore * 0.2);
                smileBaselineSamples++;
            }

            const smileThreshold = smileBaselineSamples >= 3
                ? Math.max(0.27, smileBaseline * 1.12)
                : 0.31;
            if (smileScore > smileThreshold) smileDetected = true;

            return {
                descriptor: Array.from(detection.descriptor),
                smileDetected,
                faceReady
            };
        }

        function updateFaceGuide(detection) {
            const guide = document.getElementById('face-guide');
            const instruction = document.getElementById('face-instruction');

            if (!detection) {
                guide.className = guide.className.replace(/border-\S+/g, '') + ' border-white/70';
                instruction.textContent = 'Posisikan wajah di dalam frame';
                return false;
            }

            const box = detection.detection.box;
            const video = document.getElementById('video');
            if (!video.videoWidth || !video.videoHeight) return false;
            const centerX = (box.x + box.width / 2) / video.videoWidth;
            const centerY = (box.y + box.height / 2) / video.videoHeight;
            const faceWidth = box.width / video.videoWidth;
            const centered = centerX > 0.38 && centerX < 0.62 && centerY > 0.35 && centerY < 0.68;
            const correctDistance = faceWidth > 0.28 && faceWidth < 0.62;

            guide.className = guide.className.replace(/border-\S+/g, '') + (centered && correctDistance ? ' border-emerald-400' : ' border-amber-300');

            if (!centered) {
                instruction.textContent = 'Posisikan wajah di tengah frame';
                return false;
            }
            if (faceWidth <= 0.28) {
                instruction.textContent = 'Dekatkan wajah sedikit';
                return false;
            }
            if (faceWidth >= 0.62) {
                instruction.textContent = 'Jauhkan wajah sedikit';
                return false;
            }

            instruction.textContent = smileDetected ? 'Wajah siap diproses' : 'Jarak sesuai, senyum untuk mulai';
            return true;
        }

        function getSmileScore(landmarks, faceBox) {
            const mouth = landmarks.getMouth();
            const mouthWidth = distance(mouth[0], mouth[6]);
            const mouthOpening = distance(mouth[3], mouth[9]);
            return (mouthWidth / faceBox.width) + (mouthOpening / faceBox.width) * 0.15;
        }

        function distance(first, second) {
            return Math.hypot(first.x - second.x, first.y - second.y);
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
                clearTimeout(scanTimer);
                lastDescriptor = null;
                smileDetected = false;
                smileBaseline = 0;
                smileBaselineSamples = 0;
                btn.innerText = "MULAI SCAN";
                status.innerText = "Standby";
                indicator.className = "w-2 h-2 bg-gray-400 inline-block rounded-full";
                return;
            }

            if (!modelsLoaded) {
                await loadModels();
            }

            lastDescriptor = null;
            smileDetected = false;
            smileBaseline = 0;
            smileBaselineSamples = 0;
            faceReady = false;
            isRunning = true;
            btn.innerText = "STOP";
            status.innerText = "Senyum untuk mulai...";
            indicator.className = "w-2 h-2 bg-green-500 inline-block rounded-full animate-pulse";

            scanLoop();
        }

        // ============================
        // LOOP
        // ============================
        async function scanLoop() {
            if (!isRunning || isProcessing) return;

            isProcessing = true;
            try {
                const detection = await getDescriptor();

                if (detection && detection.smileDetected && !lastDescriptor) {
                    lastDescriptor = detection.descriptor;
                    await sendToServer(detection.descriptor);
                } else if (detection && !detection.smileDetected) {
                    document.getElementById('status').innerText = detection.faceReady
                        ? "Senyum untuk mulai..."
                        : "Atur posisi wajah sesuai frame...";
                }
            } finally {
                isProcessing = false;
            }

            if (isRunning) {
                scanTimer = setTimeout(scanLoop, 350);
            }
        }

        // ============================
        // SEND TO LARAVEL
        // ============================
        async function sendToServer(descriptor) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('face.predict') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ descriptor })
                });

                const data = await res.json();

                if (data.status === 'success' && data.data) {
                    showToast('success', data.data.message || 'Presensi berhasil dicatat!', 'border-green-500');
                    addLog(data.data);
                    speak(data.data.name);
                    isRunning = false;

                } else if (data.status === 'duplicate' && data.data) {
                    showToast('warning', data.data.message || 'Sudah absen hari ini', 'border-yellow-500');
                    addLog(data.data);
                    isRunning = false;

                } else {
                    showToast('error', data.message || 'Wajah tidak dikenali', 'border-red-500');
                    lastDescriptor = null;
                }

            } catch (err) {
                console.error(err);
                lastDescriptor = null;

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

            const detection = await getDescriptor();
            const descriptor = detection?.descriptor;

            if (!descriptor) {
                alert("❌ Wajah tidak terdeteksi");
                return;
            }


            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const res = await fetch("{{ route('face.register') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
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
    <style>
        .face-guide {
            border-radius: 48% 48% 44% 44% / 38% 38% 62% 62%;
        }

        @media (max-width: 640px) {
            .face-guide {
                width: 62%;
            }
        }
    </style>
@endpush
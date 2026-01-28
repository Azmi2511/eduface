@extends('layouts.app')

@section('title', 'Terminal Presensi Wajah')

@section('content')
<div class="flex flex-col h-screen overflow-hidden bg-[#F3F6FD]">
    <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('attendance.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all border border-gray-100">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-gray-800 tracking-tight">Terminal Scan Eduface</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sistem Pengenalan Wajah Aktif</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="text-right hidden md:block">
                <p id="live-clock" class="text-lg font-black text-gray-800 leading-none">00:00:00</p>
                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-tighter mt-1">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="h-10 w-[1px] bg-gray-100"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 p-6 md:p-10 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-full">
            <div class="lg:col-span-7 flex flex-col gap-6">
                <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-indigo-500">
                            <i class="fas fa-video text-sm"></i>
                        </div>
                        <select id="cameraSelect" class="w-full bg-transparent text-slate-700 text-sm font-bold focus:ring-0 border-none block pl-10 py-2.5 cursor-pointer">
                            <option value="">Inisiasi Sumber Kamera...</option>
                        </select>
                    </div>
                    <button id="btn-refresh" class="p-2.5 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="relative flex-1 bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-indigo-900/10 border-[6px] border-white">
                    <video id="video" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1] opacity-90"></video>
                    <canvas id="canvas" class="hidden"></canvas>
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                    <div id="scan-line" class="absolute top-0 w-full h-1 bg-indigo-400 shadow-[0_0_20px_rgba(79,70,229,0.8)] animate-scan opacity-0 pointer-events-none"></div>

                    <div class="absolute top-6 left-6 flex items-center gap-3">
                        <div class="backdrop-blur-md bg-black/30 px-4 py-2 rounded-full border border-white/10 flex items-center gap-2.5 shadow-lg">
                            <span class="relative flex h-2.5 w-2.5">
                                <span id="status-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75 hidden"></span>
                                <span id="status-indicator" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-slate-400"></span>
                            </span>
                            <span id="status-text" class="text-[10px] font-black text-white tracking-widest uppercase">Offline</span>
                        </div>
                    </div>

                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 pointer-events-none">
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-indigo-400 rounded-tl-2xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-indigo-400 rounded-tr-2xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-indigo-400 rounded-bl-2xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-indigo-400 rounded-br-2xl"></div>
                    </div>
                </div>

                <button id="btn-cctv" onclick="toggleCCTV()" class="w-full group relative overflow-hidden rounded-2xl bg-indigo-600 px-8 py-5 transition-all duration-300 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/30 active:scale-[0.99]">
                    <div class="relative flex items-center justify-center gap-3 text-white font-bold tracking-widest">
                        <i class="fas fa-power-off text-sm group-hover:rotate-90 transition-transform"></i>
                        <span>MULAI PEMINDAIAN</span>
                    </div>
                </button>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Scan Berhasil Hari Ini</p>
                        <h3 id="detection-count-main" class="text-3xl font-black text-gray-800">0</h3>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-inner">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>

                <div class="flex-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-white flex justify-between items-center">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider">Aktivitas Terbaru</h4>
                        <div id="detection-count-badge" class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full uppercase">0 Siswa</div>
                    </div>
                    
                    <div id="logContainer" class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-slate-50/50 relative">
                        <div id="empty-log-state" class="absolute inset-0 flex flex-col items-center justify-center text-slate-300">
                            <div class="w-20 h-20 bg-white rounded-full shadow-sm flex items-center justify-center mb-4 border border-gray-50">
                                <i class="fas fa-user-clock text-2xl"></i>
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-widest">Menunggu Data Masuk...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    @keyframes scan {
        0% { top: 0; }
        100% { top: 100%; }
    }
    .animate-scan { animation: scan 3s linear infinite; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .log-entry { animation: slideIn 0.3s ease-out forwards; }
</style>
@endsection

@push('scripts')
<script>
    const API_URL = "http://localhost:8001";
    
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
@endpush
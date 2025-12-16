<!DOCTYPE html>
<html>
<head>
    <title>CCTV Absensi Cerdas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-black h-screen flex flex-col items-center justify-center text-white">

    <h1 class="text-2xl mb-4 font-mono">ATLAS - Automatic Attendance</h1>

    <div class="relative border-4 border-green-500 rounded-lg overflow-hidden w-[640px] h-[480px]">
        <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
        <canvas id="canvas" class="hidden"></canvas>
        
        <div id="resultOverlay" class="absolute bottom-0 w-full bg-black/70 p-4 text-center hidden">
            <h2 id="studentName" class="text-xl font-bold text-green-400">Nama Siswa</h2>
            <p id="scanTime" class="text-sm">Waktu: -</p>
        </div>
    </div>
    
    <div class="mt-4 text-gray-400 text-sm">Status: <span id="statusText">Menunggu Kamera...</span></div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const overlay = document.getElementById('resultOverlay');
        const nameEl = document.getElementById('studentName');
        const timeEl = document.getElementById('scanTime');
        const statusText = document.getElementById('statusText');

        let isProcessing = false; // Agar tidak spam request kalau server lagi mikir

        // 1. Nyalakan Kamera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { 
                video.srcObject = stream; 
                statusText.innerText = "Sistem Siap. Memindai...";
                
                // Mulai Loop Otomatis setiap 2.5 Detik
                setInterval(scanFace, 2500);
            })
            .catch(err => alert("Kamera Error: " + err));

        // 2. Fungsi Scan Otomatis
        function scanFace() {
            if (isProcessing) return; // Jangan kirim kalau yang lama belum selesai

            isProcessing = true;
            statusText.innerText = "Mengirim data...";

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            canvas.toBlob(blob => {
                const formData = new FormData();
                formData.append('file', blob, 'scan.jpg');

                fetch("{{ route('face.predict') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        // Tampilkan Hasil di Layar
                        nameEl.innerText = data.data.name;
                        timeEl.innerText = "Absen: " + data.data.time + " (" + data.data.status + ")";
                        overlay.classList.remove('hidden');
                        
                        // Bunyikan suara 'beep' (opsional)
                        new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg').play();
                        
                        // Sembunyikan lagi setelah 3 detik
                        setTimeout(() => { overlay.classList.add('hidden'); }, 3000);
                    } else if (data.status === 'ignored') {
                         statusText.innerText = "Info: " + data.student + " sudah absen (Cooldown).";
                    } else {
                        statusText.innerText = "Wajah tidak dikenal / Belum terdaftar.";
                    }
                })
                .catch(err => console.error(err))
                .finally(() => {
                    isProcessing = false; // Buka kunci proses
                    if(!overlay.classList.contains('hidden')) statusText.innerText = "Berhasil Absen!";
                    else statusText.innerText = "Memindai...";
                });
            }, 'image/jpeg');
        }
    </script>
</body>
</html>
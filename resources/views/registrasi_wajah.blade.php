<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Wajah Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Daftar Wajah Siswa</h2>

        <div class="mb-4">
            <label class="block text-sm font-bold mb-2">NISN Siswa</label>
            <input type="text" id="nisn" class="w-full p-2 border rounded" placeholder="Contoh: 12345">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-bold mb-2">Pose Wajah</label>
            <select id="pose" class="w-full p-2 border rounded">
                <option value="depan">Wajah Depan (Lurus)</option>
                <option value="kiri">Serong Kiri</option>
                <option value="kanan">Serong Kanan</option>
            </select>
        </div>

        <div class="relative w-full h-64 bg-black rounded overflow-hidden mb-4">
            <video id="video" class="absolute w-full h-full object-cover" autoplay playsinline></video>
            <canvas id="canvas" class="hidden"></canvas>
        </div>

        <button onclick="takeSnapshot()" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
            Ambil Foto & Simpan
        </button>

        <div id="status" class="mt-4 text-center text-sm font-bold"></div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const status = document.getElementById('status');

        // 1. Nyalakan Kamera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => { alert("Kamera error: " + err); });

        // 2. Fungsi Ambil Foto
        function takeSnapshot() {
            const nisn = document.getElementById('nisn').value;
            const pose = document.getElementById('pose').value;
            
            if(!nisn) return alert("Isi NISN dulu!");

            status.innerText = "Mengirim...";
            
            // Gambar di Video disalin ke Canvas
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            // Ubah Canvas jadi File (Blob)
            canvas.toBlob(blob => {
                const formData = new FormData();
                formData.append('file', blob, 'register.jpg');
                formData.append('nisn', nisn);
                formData.append('pose', pose);

                // Kirim ke Laravel
                fetch("{{ route('face.register') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        status.innerText = "✅ " + data.message;
                        status.className = "mt-4 text-center text-green-600 font-bold";
                    } else {
                        status.innerText = "❌ " + data.message;
                        status.className = "mt-4 text-center text-red-600 font-bold";
                    }
                })
                .catch(err => {
                    console.error(err);
                    status.innerText = "Error Sistem";
                });
            }, 'image/jpeg');
        }
    </script>
</body>
</html>
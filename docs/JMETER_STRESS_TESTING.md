# Skenario Stress Test Eduface dengan Apache JMeter

Dokumen ini merancang uji beban untuk aplikasi Eduface berdasarkan rute web dan API yang tersedia di `routes/web.php` dan `routes/api.php`. Ini adalah rancangan pengujian, bukan hasil eksekusi atau klaim kapasitas.

## 1. Tujuan dan batas aman

- Mengukur waktu respons, throughput, tingkat error, dan perilaku aplikasi/database saat beban meningkat.
- Mencakup fitur UI berbasis sesi dan API `/api/v1` berbasis Sanctum.
- Jalankan hanya pada staging atau lingkungan uji yang disetujui. Jangan menjalankan stress test terhadap produksi atau layanan pihak ketiga tanpa persetujuan eksplisit.
- Gunakan data uji, akun khusus, serta batas Virtual Users (VUs) yang disepakati pemilik lingkungan. Hentikan tes jika error meningkat tajam, layanan tidak stabil, atau sumber daya melewati ambang operasional.
- Uji akurasi pengenalan wajah dan rendering/perilaku browser bukan tujuan JMeter. JMeter menguji HTTP request dan bukan pengganti pengujian browser.

## 2. Persiapan JMeter

### Elemen Test Plan yang disarankan

1. **User Defined Variables**: `protocol`, `host`, `port`, `basePath`, kredensial uji dan ID data uji. Default lokal dapat menggunakan `http`, `127.0.0.1`, `8000`, dan base path kosong; ganti sesuai staging.
2. **HTTP Request Defaults**: gunakan variabel target; jangan mengunci alamat server di tiap sampler.
3. **HTTP Cookie Manager** untuk mengelola cookie sesi Laravel per virtual user.
4. **HTTP Cache Manager** untuk mensimulasikan cache browser bila relevan. Untuk halaman web, bandingkan request dokumen saja dengan varian **Retrieve All Embedded Resources** agar beban CSS/gambar/asset ikut terukur. Pisahkan cache hit dan cache miss; JMeter bukan browser penuh dan tidak mengeksekusi JavaScript.
5. **CSV Data Set Config** untuk akun dan data unik. Gunakan mode sharing yang mencegah beberapa thread memakai akun/baris yang sama secara tidak sengaja.
6. **HTTP Header Manager** untuk `Accept`, `Content-Type`, dan header autentikasi API.
7. **Ekstraktor dan assertion**:
   - Web: ambil token CSRF dari halaman form login atau form terkait, lalu kirim sebagai `_token`/header yang diminta. Pertahankan cookie dari respons sebelumnya.
   - API: ambil token dari respons login dengan JSON Extractor dan simpan per thread; kirim sebagai nilai bearer pada header `Authorization`.
   - Periksa kode status yang diharapkan, format/content-type, serta penanda konten penting. Jangan hanya menghitung respons HTTP 200 sebagai lulus.
8. **Transaction Controller** untuk mencatat durasi alur bisnis lengkap, bukan hanya tiap request.
9. **Constant Throughput Timer** atau **Throughput Shaping Timer** untuk mengatur laju; gunakan ramp-up, bukan mengirim seluruh thread sekaligus kecuali pada skenario spike.

### Variabel dan data uji

Simpan variabel sensitif di properti lokal/CLI atau secret manager lingkungan CI; jangan commit password, OTP, token, cookie, atau data pribadi ke CSV/JMX.

| Dataset | Kolom yang disarankan | Catatan |
|---|---|---|
| Akun web | `username,password,role` | Akun uji unik per thread; siapkan admin, teacher, parent. |
| Akun API | `username,password,role` | Akun/token API terpisah; sertakan student bila role tersebut diaktifkan. |
| Data tulis | `run_id,record_suffix,nisn,email` | Gunakan suffix unik, misalnya ID run + nomor thread, dan bersihkan setelah tes. |
| Form OTP | `email,otp_source` | Gunakan mail sandbox/fake atau mekanisme staging yang disetujui. Jangan hard-code OTP produksi. |
| File upload/wajah | `file_path` | Gunakan file sintetis kecil yang valid. Ukuran besar dan prediksi wajah diuji terpisah. |

Siapkan relasi data yang diperlukan sebelum tes: kelas, siswa, guru, mata pelajaran, jadwal, perangkat, izin, pengumuman, notifikasi, dan absensi. Ambil identifier awal dari fixture/seed khusus; jangan bergantung pada data yang berubah selama eksekusi.

## 3. Skenario alur bisnis dan cakupan fitur

Jalankan setiap skenario sebagai **Transaction Controller** dengan sampler bertahap. Sesuaikan assertion terhadap respons aktual aplikasi dan data fixture staging. Untuk skenario tulis, gunakan identifier unik dan lakukan pembersihan setelah run; jangan mengirim operasi hapus atau backup secara berulang sebagai bagian dari beban umum.

| ID | Skenario dan alur JMeter | Permukaan/rute yang dicakup | Persona / prasyarat |
|---|---|---|---|
| JM-01 | **Pengunjung dan halaman publik**: GET halaman login, register, forgot password, halaman scan/registrasi wajah, data registrasi, daftar/detail destinations, lookup siswa berdasarkan NISN. Ukur respons HTML/JSON; jangan memanggil aksi OTP pada tes navigasi. | Web `GET /login`, `/auth/login`, `/register`, `/password/forgot`, `/scan`, `/registrasi-wajah`; API `GET /api/v1/register-data`, `/api/v1/destinations`, `/api/v1/destinations/{destination}`, `/api/v1/student/{nisn}`. | Tanpa login; data lookup tersedia. |
| JM-02 | **Login web, dashboard, navigasi, logout**: GET form login → ekstrak CSRF → POST kredensial valid → GET dashboard → GET halaman inti yang diizinkan → POST logout → GET halaman terproteksi dan pastikan sesi tidak lagi aktif. Tambahkan percobaan kredensial salah sebagai transaksi terpisah. | Web `POST /login`, `GET /`, `POST /logout`; halaman terlindungi di JM-03 s.d. JM-09. | Akun admin/teacher/parent terpisah; cookie manager per thread. |
| JM-03 | **Master data admin**: daftar → detail → buat record uji → baca kembali → update → hapus record uji. Buat entitas dengan urutan relasi yang benar dan gunakan data unik. Pisahkan resource agar metrik dapat dibandingkan. | Web resource `users`, `students`, `teachers`, `parents`, `classes`, `announcements`; halaman pengaturan pada JM-10. | Admin; teacher untuk operasi kelas/siswa sesuai middleware. |
| JM-04 | **Jadwal dan mata pelajaran**: buka daftar jadwal → lihat detail → buat/update jadwal uji → baca jadwal hari ini/list → hapus hanya record run tersebut. Uji operasi bentrok sebagai tes fungsional ber-laju rendah, bukan flood. | Web resource `schedules`; API `/api/v1/schedules` dan `/api/v1/my-schedules/today`; API CRUD `/api/v1/subjects`; `/api/v1/available-teachers`. | Web user berwenang; API token valid. |
| JM-05 | **Absensi web/API**: buka daftar/scan/register → catat kehadiran dengan siswa/jadwal fixture → baca daftar/detail → ekspor sekali per transaksi terkontrol. Jalankan input perangkat sebagai skenario API terpisah. | Web `GET /attendance`, `/attendance/scan`, `/attendance/register`, resource `/attendance`, `POST /attendance/store`, `POST /attendance/export`; API `GET/POST /api/v1/attendance`, `POST /api/v1/attendance/device-input`. | Akun web berwenang; token API; data siswa/jadwal aktif. |
| JM-06 | **Pengajuan dan persetujuan izin**: baca daftar/detail izin → buat pengajuan lewat API → baca kembali → ubah status oleh admin/guru → verifikasi status → cek akses parent yang hanya boleh melihat. | Web `GET /permissions`, `/permissions/{id}`, `POST /permissions/{id}/status`; API `/api/v1/permissions` (GET/POST), `PATCH /api/v1/permissions/{permission}/status`. | Admin/teacher/parent sesuai batas role; fixture izin. |
| JM-07 | **Pengumuman**: admin membaca daftar/detail → buat pengumuman unik → baca ulang → update → hapus record uji; user biasa membaca pengumuman yang tersedia. | Web `/announcements` dan `/announcements/{id}`; API CRUD `/api/v1/announcements`. | Admin untuk tulis; akun biasa untuk baca sesuai aplikasi. |
| JM-08 | **Notifikasi**: baca daftar dan jumlah belum dibaca → tandai satu dibaca → tandai semua dibaca → verifikasi jumlah → hapus item uji dan, hanya pada fixture terisolasi, hapus semua. | Web `/notifications`, `/notifications/{id}`, `/notifications/{id}/read`, `/notifications/mark-all-read`, `/notifications/destroy-all`; API `/api/v1/notifications`, `/unread-count`, `PATCH /{id}/read`, `/mark-all-read`. | Akun dengan notifikasi fixture sendiri. |
| JM-09 | **Profil, kata sandi, preferensi, bantuan**: GET profil → update profil dengan nilai uji → baca ulang → update preferensi → baca ulang. Uji update password/logout/login dengan akun khusus pada jumlah kecil. Buka halaman bantuan/support. | Web `/profile` (`PATCH`), `/profile/password` (`PUT`), `/settings/preferences` (`GET/POST`), `/help-support`; API `/api/v1/me`, `/me/update`, `/me/password`, `/user/update-fcm`. | Akun web/API terisolasi; jangan berbagi akun saat mengganti password. |
| JM-10 | **Pengaturan dan administrasi**: admin membaca pengaturan → update general/attendance/notification/security dengan nilai staging yang dapat dipulihkan → baca ulang. Jalankan backup database paling banyak satu kali secara manual/terkontrol, catat durasi/ukuran, lalu pastikan file/artefak uji dibersihkan. | Web `/settings`, `/settings/general`, `/settings/attendance`, `/settings/notification`, `/settings/security`, `/settings/backup`; API `/api/v1/settings`, `/settings/general`, `/settings/attendance`, `/settings/notification`, `/settings/backup`. | Admin; salinan konfigurasi awal dan ruang disk cukup. |
| JM-11 | **Registrasi, OTP, dan pemulihan akun**: registrasi: ambil data form → validasi data → kirim OTP → verifikasi dan buat satu akun unik. Password reset: kirim kode → verifikasi → reset → login dengan kredensial baru. Gunakan laju rendah dan email sandbox. | Web `/auth/validate`, `/auth/check-otp`, `/auth/final`, `/register/send-otp`, `/register/verify-create`, `/password/send-code`, `/password/verify-code`, `/password/reset`; API `/api/v1/send-otp`, `/api/v1/verify-register`. | Email uji unik; mail sandbox/fake; OTP diperoleh dari kanal uji, tidak di-hard-code. |
| JM-12 | **Pengenalan wajah dan upload**: unggah satu file sintetis valid → verifikasi respons dan cleanup; panggil face register/predict dengan payload/file valid pada concurrency kecil lalu naik bertahap. Pantau CPU, memori, ukuran request dan dependensi eksternal. | Web `/face/register`, `/face/predict`; API `/api/v1/face/register`, `/api/v1/face/predict`, `/api/v1/upload-file`. | Siswa uji dan file valid; pastikan batas ukuran/kuota; jalankan terpisah dari profil beban umum. |
| JM-13 | **API autentikasi dan dashboard**: POST login → ekstrak token → GET dashboard/home/me → update profil/FCM → POST logout → panggil endpoint terlindungi dengan kredensial API lama dan periksa penolakan. Jalankan login gagal dan request tanpa autentikasi sebagai transaksi terpisah ber-laju rendah. | API `/api/v1/login`, `/dashboard`, `/home`, `/me`, `/me/update`, `/user/update-fcm`, `/logout`. | Akun dan kredensial API per thread; nilai autentikasi tidak dicatat ke hasil JMeter. |
| JM-14 | **API data sekolah dan perangkat**: baca/tulis data resource dengan siklus create/read/update/delete pada record uji unik; panggil daftar kelas dan statistik siswa. | API CRUD `/api/v1/users`, `/teachers`, `/parents`, `/students`, `/classes`, `/subjects`, `/schedules`, `/devices`; `GET /api/v1/class-list`, `/stats/students`, `/available-teachers`; destinations tulis untuk admin. | Token ber-role sesuai; fixture relasi dan cleanup terkontrol. |

### Matriks kelengkapan endpoint

| Kelompok fitur | Rute yang dicakup |
|---|---|
| Autentikasi web | Login, logout, registrasi, validasi/OTP registrasi, lupa password/verifikasi/reset |
| Dashboard | Web `/`; API `/dashboard`, `/home` |
| Pengguna & profil | Web resource users, `/profile`, ubah password; API users CRUD, `/me`, update profil/password, FCM |
| Siswa, guru, orang tua, kelas | Web CRUD sesuai akses role; API resource students/teachers/parents/classes, lookup NISN, class-list, statistik siswa, guru tersedia |
| Jadwal & mata pelajaran | Web schedules; API schedules, jadwal hari ini, subjects |
| Absensi & perangkat | Web attendance/scan/register/store/export; API attendance/device-input, devices CRUD |
| Izin | Web daftar/detail/status; API permissions list/create/status |
| Pengumuman & notifikasi | Web announcement CRUD/detail; notification list/detail/read/delete/mark-all/delete-all; API announcements CRUD dan notifikasi |
| Preferensi, bantuan & pengaturan | Web preferences/help/support dan pengaturan umum/absensi/notifikasi/keamanan/backup; API settings CRUD/subset dan backup |
| Wajah, berkas, direktori tujuan | Web face register/predict; API face register/predict, upload-file, destinations read/write |

Halaman UI tambahan: cek semua halaman di atas pada beban HTTP. Interaksi yang membutuhkan eksekusi JavaScript (misalnya tampilan kamera, prediksi wajah di browser, dan rendering dinamis) harus dilengkapi dengan uji browser terpisah.

## 4. Model beban

Nilai berikut adalah **profil awal untuk staging kecil**, bukan kapasitas yang dijamin. Pemilik aplikasi harus menyetujui `TARGET_VUS`, laju request, durasi, dan batas berhenti sebelum run. Mulailah dari bawah dan jangan melampaui batas sumber daya staging.

| Profil | Pengaturan awal | Tujuan |
|---|---|---|
| Smoke | 1-3 VU, 1-2 menit, 1 iterasi tiap alur | Memastikan autentikasi, assertion, data, dan cleanup benar. |
| Baseline | 5-10 VU, ramp-up 1 menit, 5 menit stabil | Mengukur latensi tanpa antrean beban berarti. |
| Load | 10 → 25 → 50 VU; ramp-up 2-5 menit per tahap, tahan 10 menit/tahap | Mengukur target operasional yang disepakati. Berhenti jika melewati ambang. |
| Stress | Mulai pada target load; tambah 10-20% VU per tahap 5 menit sampai batas yang disetujui | Menemukan titik degradasi; bukan tes untuk dijalankan terus tanpa batas. |
| Spike | Baseline → target load dalam 30-60 detik; tahan 3-5 menit; turunkan kembali | Mengamati pemulihan setelah lonjakan. |
| Soak | 60-75% target load selama 1-2 jam | Mencari kebocoran resource, antrean menumpuk, dan degradasi latensi. |

Contoh profil trafik awal untuk campuran HTTP (atur ulang berdasarkan log/analitik nyata):

| Jenis transaksi | Porsi awal |
|---|---:|
| Dashboard, daftar, detail, lookup, jadwal | 45% |
| Absensi baca dan pencatatan normal | 20% |
| Data master/API CRUD dengan data uji | 15% |
| Notifikasi, izin, profil, preferensi | 10% |
| Halaman anonim/autentikasi non-OTP | 10% |

OTP, email, upload, face recognition, backup, ekspor besar, hapus massal, dan perubahan password **tidak** masuk ke campuran intensif secara default. Jalankan sebagai profil terpisah dengan laju rendah dan batas concurrency yang disetujui.

## 5. Pengaturan HTTP dan assertion

- Untuk web, mulai dari GET halaman/form yang membentuk sesi; ekstrak CSRF dan pertahankan cookie. Respons `419` biasanya menunjukkan token/cookie atau sesi tidak dipertahankan, bukan bottleneck performa.
- API: kirim `Accept: application/json`; gunakan bearer token per thread. Catat dan periksa respons autentikasi/otorisasi tanpa menyimpan kredensial/token di listener.
- Tambahkan assertion atas status yang dikontrak, content-type, bentuk JSON/HTML, serta perubahan data yang penting. Pisahkan respons bisnis yang diharapkan (mis. validasi) dari kegagalan server.
- Gunakan think time acak 1-3 detik untuk alur pengguna; sediakan juga skenario throughput konstan terkontrol untuk menguji kapasitas server.
- Gunakan **non-GUI mode** untuk tes beban. Listener `View Results Tree` hanya untuk debugging dengan 1-2 user; untuk run gunakan `Summary Report`, `Aggregate Report`/backend listener dan JTL.
- Simpan hasil per run dengan nama yang mencantumkan build, profil, tanggal, dan target lingkungan. Jangan aktifkan pencatatan body respons berisi data pribadi pada tes beban.

## 6. Kriteria evaluasi dan penghentian

Ambang harus disetujui sebelum run; angka di bawah hanya contoh target awal yang perlu disesuaikan dengan SLA dan baseline:

- Error aplikasi (HTTP 5xx, timeout, assertion gagal) < 1% pada profil load yang disetujui; respons 4xx yang memang diharapkan pada skenario negatif tidak dihitung sebagai error server.
- p95 respons transaksi baca ≤ 2 detik dan p95 transaksi tulis ≤ 3 detik.
- Tidak ada kehilangan/duplikasi data di luar aturan bisnis; operasi baca sesudah tulis menunjukkan hasil yang konsisten.
- CPU, memori, koneksi DB, disk, queue, dan worker tidak melewati batas operasional yang telah disetujui; aplikasi pulih setelah spike.
- Soak tidak menunjukkan tren pertumbuhan resource/latensi yang tidak terkendali.

Hentikan tes segera jika 5xx/timeout naik tajam, error persisten, layanan tak dapat dipakai, antrean/koneksi DB habis, disk backup/log mendekati penuh, atau resource mencapai batas stop yang ditetapkan operator.

## 7. Menjalankan dan melaporkan

1. Catat build/commit, konfigurasi lingkungan tanpa rahasia, dataset, profil, durasi, target VU/RPS, dan versi JMeter.
2. Jalankan JM-01 s.d. JM-14 satu per satu pada tahap smoke untuk menguji ekstraksi CSRF/token dan assertion. Jalankan alur tulis hanya dengan akun dan data uji.
3. Jalankan profil load terlebih dahulu pada campuran yang disetujui. Stress/spike/soak dijalankan terpisah setelah baseline stabil.
4. Hasilkan JTL serta ringkasan Aggregate Report; korelasikan timestamp dengan log aplikasi, database, web server, queue, dan metrik host.
5. Setelah selesai, verifikasi cleanup data/berkas uji, sesi/token, mail sandbox, serta pengaturan yang diubah telah dipulihkan.

Template hasil:

| Run ID | Build/lingkungan | Profil/VU/RPS | Durasi | Throughput | p50/p90/p95/p99 | Error (%) | CPU/Memori/DB | Kesimpulan/temuan |
|---|---|---|---|---|---|---|---|---|
|  |  |  |  |  |  |  |  |  |

## 8. Referensi cakupan

- `routes/web.php` — rute halaman, formulir, autentikasi sesi, dan middleware role.
- `routes/api.php` — API v1, autentikasi Sanctum, resource CRUD, serta batas akses per role.
- `skenario_pengujian_systematis_eduface.md` — kasus uji fungsional/integrasi yang sudah ada; dokumen ini melengkapinya dengan strategi beban.

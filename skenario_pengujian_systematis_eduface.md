# Skenario Pengujian Sistematis — Eduface

Dokumen ini berisi skenario pengujian fungsional dan integrasi untuk aplikasi Eduface. Skenario disusun dari alur dan batas akses yang didefinisikan di `routes/web.php` dan `routes/api.php`, serta melengkapi pengujian partisi ekuivalen/nilai batas yang sudah dicatat di `kasus_uji_eduface.md` dan pengujian acak di `kasus_uji_random_testing.md`.

> **Status:** rancangan skenario, belum merupakan bukti bahwa semua kasus telah dieksekusi atau lulus. Sesuaikan respons/status HTTP dengan kontrak aktual aplikasi saat eksekusi.

## 1. Tujuan dan cakupan

Memeriksa alur penting Eduface dari sisi pengguna, integrasi data, autentikasi, dan otorisasi:

- Autentikasi web berbasis sesi dan API berbasis token Sanctum.
- Registrasi dan verifikasi OTP, serta pemulihan kata sandi.
- Akses sesuai peran admin, guru, dan orang tua.
- Manajemen siswa, kelas, dan jadwal.
- Pencatatan, peninjauan, dan ekspor absensi.
- Profil, izin, notifikasi, pengaturan, dan endpoint API.

Pengujian performa beban, pemindaian keamanan menyeluruh, dan verifikasi akurasi model pengenalan wajah tidak termasuk dalam cakupan dokumen ini.

## 2. Metode desain kasus uji

| Metode | Penerapan |
|---|---|
| Alur/use-case | Menjalankan alur lengkap seperti login, registrasi, pencatatan absensi, dan perubahan status izin. |
| Partisi ekuivalen dan nilai batas | Menguji kelas input valid/tidak valid serta batas panjang/format. Rincian awal tersedia di `kasus_uji_eduface.md`. |
| Tabel keputusan | Memeriksa kombinasi peran dan hak akses pada rute yang dilindungi. |
| Transisi status | Memeriksa perubahan status OTP, izin, notifikasi, dan sesi autentikasi. |
| Integrasi dan persistensi | Memastikan perubahan berhasil disimpan dan tetap terbaca setelah halaman/request berikutnya. |
| Regresi | Menjalankan ulang kasus inti setelah perubahan kode, migrasi skema, atau konfigurasi. |

## 3. Persiapan lingkungan dan data uji

1. Gunakan lingkungan **testing/staging**, bukan data produksi. Konfigurasi pengujian repositori saat ini menggunakan SQLite in-memory, cache array, mailer array, dan session array melalui `phpunit.xml`.
2. Siapkan akun terpisah untuk `admin`, `teacher`, dan `parent`, serta data kelas, siswa, jadwal, dan izin yang saling terkait.
3. Gunakan alamat email dan identitas siswa unik pada setiap eksekusi agar pengujian dapat diulang.
4. Untuk email OTP, gunakan mailer testing/fake dan ambil kode dari hasil pengiriman yang ditangkap; jangan bergantung pada email sungguhan.
5. Catat versi aplikasi, konfigurasi database, akun/peran yang digunakan (tanpa kata sandi), waktu eksekusi, hasil aktual, dan bukti yang tidak memuat data sensitif.
6. Bersihkan data uji setelah eksekusi. Untuk rangkaian PHPUnit, manfaatkan transaksi/`RefreshDatabase` yang sudah dipakai oleh beberapa test.

## 4. Matriks skenario

### 4.1 Autentikasi, registrasi, dan pemulihan akun

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| AUTH-01 | P1 | Akun aktif tersedia | Buka halaman login, masukkan kredensial valid, lalu buka dashboard dan logout. | Login berhasil; sesi memberikan akses ke dashboard; logout mengakhiri sesi dan halaman terlindungi tidak lagi dapat dibuka tanpa login. |
| AUTH-02 | P1 | Akun aktif tersedia | Coba login dengan kata sandi salah, lalu dengan username yang tidak terdaftar. | Kedua percobaan ditolak dengan pesan yang tidak mengungkap apakah username terdaftar; sesi autentikasi tidak terbentuk. |
| AUTH-03 | P1 | Belum login | Akses langsung dashboard, profil, dan daftar absensi melalui URL. | Pengguna diarahkan ke login atau akses ditolak; tidak ada data terlindungi yang ditampilkan. |
| AUTH-04 | P1 | Email baru dan data registrasi valid | Minta OTP registrasi, masukkan OTP yang diterima, lalu selesaikan pembuatan akun. | OTP dapat digunakan sesuai masa berlakunya; akun dibuat satu kali dengan data/peran yang dipilih dan pengguna menerima hasil yang jelas. |
| AUTH-05 | P1 | Email sudah terdaftar | Kirim permintaan OTP registrasi dengan email yang sudah digunakan. | Registrasi ditolak; akun lama tidak berubah dan OTP tidak membuat akun duplikat. |
| AUTH-06 | P1 | OTP valid tersedia | Verifikasi menggunakan OTP salah, OTP kedaluwarsa, lalu OTP valid. | OTP salah/kedaluwarsa ditolak; OTP valid diterima; respons tidak membocorkan kode OTP. |
| AUTH-07 | P1 | Akun dan akses ke email uji tersedia | Jalankan alur lupa kata sandi: minta kode, verifikasi kode, ubah kata sandi, login dengan kata sandi baru. | Kata sandi baru tersimpan; login baru berhasil; kata sandi lama tidak lagi dapat dipakai. |
| AUTH-08 | P2 | Sesi aktif | Kirim permintaan perubahan data berbasis sesi tanpa token CSRF yang sah. | Permintaan ditolak oleh perlindungan CSRF dan data tidak berubah. |

### 4.2 Hak akses berbasis peran

Gunakan akun masing-masing peran. Jalankan tiap kasus dengan request web yang telah login; ulangi sebagai pengguna tanpa sesi untuk memeriksa batas autentikasi.

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| ROLE-01 | P1 | Akun admin aktif | Buka menu pengguna, guru, orang tua, kelas, siswa, pengumuman, dan pengaturan. | Admin dapat mengakses fungsi yang dilindungi khusus admin sesuai rute. |
| ROLE-02 | P1 | Akun guru aktif | Akses daftar/manajemen kelas dan siswa serta halaman izin. Coba buka rute khusus admin seperti manajemen pengguna dan pengaturan sistem. | Guru mendapat akses hanya pada fungsi yang diizinkan untuk admin/guru; fungsi khusus admin ditolak tanpa perubahan data. |
| ROLE-03 | P1 | Akun orang tua aktif | Akses halaman izin dan daftar orang tua; coba buka manajemen siswa/kelas dan fungsi admin. | Akses sesuai izin orang tua pada rute; akses yang tidak diizinkan ditolak. |
| ROLE-04 | P1 | Akun guru dan orang tua aktif; izin uji tersedia | Sebagai orang tua, buka detail izin dan coba mengubah status. Ulangi perubahan status sebagai guru. | Orang tua dapat melihat izin tetapi tidak mengubah status; perubahan oleh guru yang berwenang tersimpan. |
| ROLE-05 | P1 | Akun non-admin API aktif dengan token Sanctum | Panggil endpoint pengaturan/backup yang dibatasi untuk admin. | API menolak akses non-admin; tidak ada pengaturan atau berkas backup yang bocor. |

### 4.3 Data sekolah dan jadwal

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| DATA-01 | P1 | Admin/guru login; data siswa belum ada | Buat siswa dengan data valid, buka kembali detail/daftar, perbarui satu field, lalu hapus data uji. | Operasi CRUD berhasil; perubahan terbaca kembali; setelah penghapusan, data tidak muncul lagi sesuai perilaku aplikasi. |
| DATA-02 | P1 | Siswa dengan NISN tertentu telah tersimpan | Coba membuat siswa kedua dengan NISN yang sama. | Input duplikat ditolak dan record siswa awal tetap utuh. |
| DATA-03 | P1 | Admin/guru login | Buat kelas baru, kemudian pilih kelas tersebut pada data siswa; periksa relasi dari tampilan yang tersedia. | Kelas tersimpan dan relasi kelas-siswa menunjuk pada kelas yang benar. |
| DATA-04 | P1 | Admin/guru login; guru, kelas, dan mata pelajaran tersedia | Buat jadwal dengan hari dan waktu valid, lalu buka daftar jadwal. | Jadwal tersimpan dengan kelas, guru, mata pelajaran, hari, dan waktu yang sesuai input. |
| DATA-05 | P1 | Sudah ada jadwal untuk guru/kelas pada waktu tertentu | Buat jadwal kedua yang waktunya bertumpang tindih pada hari dan sumber daya yang sama. | Sistem mendeteksi konflik sesuai aturan jadwal dan tidak menyimpan jadwal bentrok. |
| DATA-06 | P2 | Admin/guru login | Kirim jadwal dengan hari tidak dikenal, waktu tidak valid, atau waktu selesai sebelum waktu mulai. | Validasi menolak data; pesan terkait ditampilkan; tidak ada jadwal parsial yang tersimpan. |

### 4.4 Absensi dan izin

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| ATT-01 | P1 | Siswa dan jadwal aktif tersedia; pengguna berwenang login | Catat absensi siswa melalui alur absensi yang tersedia, kemudian buka daftar/detail absensi. | Absensi tercatat untuk siswa dan jadwal yang benar serta dapat dibaca kembali. |
| ATT-02 | P1 | Absensi untuk siswa dan jadwal pada tanggal uji sudah ada | Kirim pencatatan kedua untuk kombinasi yang sama, termasuk dua request berdekatan bila dapat dilakukan. | Tidak muncul record ganda yang melanggar aturan unik aplikasi; hasil pembaruan/penolakan mengikuti kebijakan absensi. |
| ATT-03 | P1 | Beberapa record absensi dengan tanggal/status berbeda tersedia | Terapkan filter yang tersedia pada daftar absensi, lalu bandingkan hasilnya dengan data uji. | Hanya record yang sesuai filter ditampilkan; tidak ada data di luar rentang/kriteria yang diminta. |
| ATT-04 | P2 | Pengguna dengan izin ekspor tersedia | Ekspor absensi dengan filter tertentu, lalu cocokkan isi berkas dengan hasil daftar untuk filter yang sama. | Berkas dapat dibuka dan berisi data yang konsisten dengan filter; tidak memuat record di luar cakupan. |
| ATT-05 | P1 | Akun orang tua dan izin uji tersedia | Buka daftar/detail izin terkait, ubah status sebagai guru/admin sesuai kewenangan, lalu baca kembali. | Status berubah hanya melalui peran berwenang dan status baru konsisten pada tampilan berikutnya. |

### 4.5 Profil, notifikasi, dan preferensi

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| USER-01 | P1 | Pengguna login | Ubah nama/nomor telepon dengan data valid, simpan, muat ulang halaman profil. | Perubahan tersimpan dan tetap tampil setelah halaman dimuat ulang. |
| USER-02 | P1 | Dua akun dengan email berbeda tersedia | Coba mengganti email akun pertama menjadi email akun kedua. | Perubahan ditolak sebagai email duplikat dan profil akun pertama tidak berubah. |
| USER-03 | P1 | Pengguna login dengan kata sandi lama yang diketahui | Ubah kata sandi menggunakan kata sandi lama yang benar; coba ulangi dengan kata sandi lama salah. | Perubahan valid berhasil; kata sandi lama salah ditolak; kegagalan tidak mengubah kredensial. |
| USER-04 | P2 | Pengguna memiliki notifikasi belum dibaca | Tandai satu notifikasi telah dibaca, lalu gunakan fungsi tandai semua dibaca. | Status baca berubah sesuai tindakan dan tetap konsisten setelah halaman dimuat ulang. |
| USER-05 | P2 | Pengguna login | Ubah preferensi pengguna, muat ulang halaman preferensi. | Preferensi valid tersimpan untuk pengguna yang sedang login dan tidak mengubah preferensi akun lain. |

### 4.6 API dan integrasi

| ID | Prioritas | Pra-kondisi | Langkah uji | Hasil yang diharapkan |
|---|---|---|---|---|
| API-01 | P1 | Tidak memiliki token API | Panggil endpoint terproteksi seperti `/api/v1/me` atau dashboard. | API mengembalikan respons unauthenticated (umumnya HTTP 401) dan tidak menyertakan data privat. |
| API-02 | P1 | Token Sanctum valid | Panggil endpoint profil/me, lalu ubah profil dengan payload valid dan baca ulang. | API menerima token; perubahan tersimpan dan respons baca ulang menunjukkan data yang diperbarui. |
| API-03 | P1 | Token Sanctum valid | Logout melalui API, lalu gunakan kembali token yang sama untuk endpoint terproteksi. | Logout berhasil dan token tidak lagi dapat digunakan untuk mengakses endpoint terproteksi. |
| API-04 | P2 | Token valid dan data uji tersedia | Panggil endpoint daftar dan detail salah satu resource API, lalu cocokkan identitas record. | Respons mengikuti format JSON endpoint dan menampilkan record yang tepat; request tanpa izin/parameter tidak valid ditolak dengan respons terkendali. |

## 5. Pengujian non-fungsional dasar

| ID | Aspek | Langkah uji | Kriteria lulus |
|---|---|---|---|
| NF-01 | Ketahanan input | Jalankan kasus input tidak valid, karakter khusus, dan batas panjang yang sudah tercantum pada dokumen partisi ekuivalen/nilai batas dan random testing. | Aplikasi memberi validasi/penolakan yang terkendali; tidak terjadi HTTP 500, data rusak, atau eksekusi konten input. |
| NF-02 | Privasi | Periksa respons login, registrasi, OTP, dan API ketika gagal. | Tidak ada kata sandi, token, OTP, atau data privat yang tidak diperlukan terekspos pada pesan/log/response. |
| NF-03 | Integritas data | Setelah create/update/delete, periksa kembali melalui halaman/API terkait dan database testing. | Data konsisten dengan tindakan; tidak ada record duplikat/parsial yang tidak diharapkan. |
| NF-04 | Kompatibilitas alur | Jalankan alur login, akses dashboard, navigasi modul, dan logout pada browser yang didukung proyek. | Tidak ada error tampilan atau navigasi yang menghalangi penyelesaian alur utama. |

## 6. Kriteria mulai dan selesai

### Mulai

- Aplikasi dapat berjalan pada lingkungan testing/staging.
- Skema database telah dimigrasikan dan data uji tersedia.
- Email/OTP, penyimpanan berkas, dan layanan eksternal diarahkan ke fake/mock atau sandbox.
- Versi kode yang diuji dan daftar skenario sudah dicatat.

### Selesai

- Semua kasus P1 telah dieksekusi; kasus gagal memiliki bukti dan laporan defect.
- Tidak ada defect kritis/tinggi yang belum diselesaikan atau diterima secara eksplisit.
- Kasus gagal tidak ditandai lulus hanya karena respons server berhasil; persistensi dan otorisasi juga diverifikasi.
- Skenario yang tidak dapat diuji diberi status **Blocked/Not Run** dengan alasan dan dampaknya.

## 7. Format pencatatan hasil

Gunakan satu baris untuk setiap eksekusi kasus:

| ID | Versi/build | Hasil aktual | Status (Pass/Fail/Blocked/Not Run) | Bukti/defect ID | Penguji | Tanggal |
|---|---|---|---|---|---|---|
| AUTH-01 |  |  |  |  |  |  |

Untuk kasus **Fail**, sertakan langkah reproduksi, data uji yang tidak sensitif, hasil yang diharapkan, hasil aktual, dan bukti yang relevan. Jangan menaruh kata sandi, OTP aktif, token, atau data pribadi nyata di bukti pengujian.

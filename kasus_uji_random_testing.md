# Kasus Uji — Random Testing (Monkey Testing / Fuzzing)
## Aplikasi Eduface (Sistem Absensi Sekolah)

Random Testing adalah teknik pengujian di mana input data acak, tidak terduga, atau tidak terformat diberikan ke dalam aplikasi. Tujuannya adalah untuk menemukan bug tersembunyi, kebocoran memori, crash aplikasi, atau celah keamanan (seperti SQL Injection atau XSS) yang mungkin terlewat oleh pengujian terstruktur.

---

## 1. Pengujian Form Login (`POST /login`)

| ID | Skenario Random / Fuzzing | Input `username` | Input `password` | Hasil yang Diharapkan |
|----|---------------------------|-------------------|-------------------|-----------------------|
| RT-L01 | Input karakter khusus / simbol | `!@#$%^&*()` | `!@#$%^&*()` | Ditolak dengan pesan "Credentials not valid". Tidak ada error database / HTTP 500. |
| RT-L02 | String sangat panjang (Buffer Overflow attempt) | 5000 karakter huruf `a` | 5000 karakter huruf `b` | Ditolak. Sistem membatasi panjang input atau mengembalikan error validasi, bukan crash. |
| RT-L03 | SQL Injection sederhana | `' OR 1=1 --` | `' OR 1=1 --` | Ditolak (Laravel ORM sudah melindungi ini secara default). Pesan "Credentials not valid". |
| RT-L04 | Payload Cross-Site Scripting (XSS) | `<script>alert('XSS')</script>` | `password123` | Ditolak. Script tidak dieksekusi saat input dikembalikan ke view (Blade melakukann *escaping*). |
| RT-L05 | Spasi kosong berlapis | `     ` | `     ` | Validasi gagal (dianggap kosong jika di-trim) atau ditolak karena tidak cocok. |
| RT-L06 | Input tipe data tak terduga (misal JSON array via API) | `["admin"]` | `{"pass":"word"}` | API mengembalikan error validasi tipe data (HTTP 422) atau "Credentials not valid". |

---

## 2. Pengujian Registrasi Pengguna (`POST /register/send-otp`)

| ID | Skenario Random / Fuzzing | Input | Hasil yang Diharapkan |
|----|---------------------------|-------|-----------------------|
| RT-R01 | Email tidak lazim tapi formatnya lolos regex tertentu | `email@domain..com`, `email@-domain.com`, `user@123.123.123.123` | Bergantung pada ketatnya validasi `email` Laravel. Seharusnya ditolak (HTTP 422). |
| RT-R02 | Nama dengan emoji atau karakter Unicode ekstrim | `name = 👨‍👩‍👦‍👦 ẓ̵̭͖̘̹͍̀̿̄` | Data tersimpan dengan benar jika database mendukung utf8mb4, atau ditolak dengan anggun (bukan crash). |
| RT-R03 | Input role di luar enum (Manipulasi Request) | `role = DROP TABLE users;` | Ditolak dengan pesan "The selected role is invalid". |
| RT-R04 | Ukuran file foto profil acak / ekstensi palsu | Upload file `.exe` yang di-rename menjadi `foto.jpg` | Ditolak oleh validasi `image|mimes:jpeg,png,jpg`. |
| RT-R05 | No HP berupa huruf dan simbol | `phone = +ABC-(DEF)-GH!!` | Validasi gagal, atau jika lolos, tidak menyebabkan error saat disimpan ke database (tipe string). |

---

## 3. Pengujian Endpoint Verifikasi OTP (`POST /password/verify-code`)

| ID | Skenario Random / Fuzzing | Input | Hasil yang Diharapkan |
|----|---------------------------|-------|-----------------------|
| RT-O01 | OTP berisi huruf (bukan angka) | `code = ABCDEF` | Validasi gagal (HTTP 422) karena aturan `digits:6`. |
| RT-O02 | OTP bernilai negatif atau float | `code = -12345` atau `123.45` | Validasi gagal (HTTP 422). |
| RT-O03 | Flood request secara konstan (Brute-force) | Kirim 100 request/detik dengan kode OTP acak | Rate limiting aktif, mengembalikan HTTP 429 atau 423 (Akun terkunci sementara). |

---

## 4. Pengujian Update Profil (`PATCH /profile`)

| ID | Skenario Random / Fuzzing | Input | Hasil yang Diharapkan |
|----|---------------------------|-------|-----------------------|
| RT-P01 | Injeksi path file pada input nama (LFI attempt) | `full_name = ../../../etc/passwd` | Tersimpan sebagai teks biasa, tidak mengeksekusi apa pun. |
| RT-P02 | Tag HTML pada input nomor telepon | `phone = <b>0812</b>` | Tersimpan sebagai teks biasa (jika lolos validasi), saat ditampilkan di Blade di-escape (tidak tebal). |
| RT-P03 | Mengirim request PATCH tanpa CSRF Token | Headers tanpa `X-CSRF-TOKEN` | Ditolak dengan pesan "419 Page Expired". |

---

## 5. Pengujian Pencarian & Filter (Misal: Pencarian Siswa)

| ID | Skenario Random / Fuzzing | Input Parameter URL | Hasil yang Diharapkan |
|----|---------------------------|---------------------|-----------------------|
| RT-S01 | Parameter pencarian panjang sekali | `?search=[5000 karakter acak]` | Halaman tetap dimuat normal (mungkin kosong/tidak ada hasil), atau dibatasi oleh server web (HTTP 414 URI Too Long). |
| RT-S02 | Karakter wildcard SQL | `?search=%` atau `?search=_` | Menampilkan seluruh data atau lolos dengan aman karena Laravel query builder menggunakan parameterized queries. |
| RT-S03 | Filter status invalid tipe data | `?is_active=bukan_angka` | Query tetap berjalan tanpa error HTTP 500, data kosong atau mengabaikan filter. |

---

## 6. Pengujian Modul Kehadiran / Scan API (`POST /attendance/storeAjax`)

| ID | Skenario Random / Fuzzing | Input | Hasil yang Diharapkan |
|----|---------------------------|-------|-----------------------|
| RT-A01 | NISN tidak valid / acak | `nisn = !@#$999` | Validasi gagal (HTTP 422) dengan pesan "The selected nisn is invalid". |
| RT-A02 | Request beruntun (Double click/ Race condition) | Kirim 5 request bersamaan (millisecond) untuk NISN yang sama | Data kehadiran tercatat 1 kali berkat `updateOrCreate` atau lock. Tidak boleh ada duplikasi row untuk hari & jadwal yang sama. |
| RT-A03 | Manipulasi format waktu pada device | Request dikirim dari client dengan tanggal sistem '2099-01-01' | Server menggunakan `Carbon::now()` backend, jadi waktu client diabaikan. Kehadiran tercatat menggunakan waktu server. |

---

## Kesimpulan Random Testing

Pengujian ini fokus pada ketahanan sistem (robustness) terhadap input yang tidak masuk akal atau mencoba mengeksploitasi sistem. Karena Eduface dibangun menggunakan Laravel, banyak perlindungan bawaan yang sudah ada:
1. **SQL Injection:** Dicegah oleh Eloquent ORM.
2. **XSS:** Dicegah oleh Blade template engine (escaping otomatis `{{ }}`).
3. **CSRF:** Dicegah oleh middleware VerifyCsrfToken.
4. **Validasi:** Controller mem-filter tipe data yang tidak sesuai.

Fokus utama bug biasanya ditemukan pada **race condition** (seperti klik submit berkali-kali) atau **penggunaan karakter Unicode ekstrim** yang mungkin tidak didukung oleh set karakter database (meskipun utf8mb4 sudah standar di Laravel).

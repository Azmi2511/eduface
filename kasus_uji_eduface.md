# Kasus Uji — Partisi Ekuivalen & Analisis Nilai Batas
## Aplikasi Eduface (Sistem Absensi Sekolah)

---

## 1. Login (`POST /login`)

**Field yang diuji:** `username` (required), `password` (required)

### Partisi Ekuivalen

| ID | Partisi | Input Username | Input Password | Kelas | Hasil yang Diharapkan |
|----|---------|---------------|----------------|-------|----------------------|
| L-EP-01 | Username & password valid | `admin` (ada di DB) | `password` (cocok) | **Valid** | Redirect ke dashboard |
| L-EP-02 | Username valid, password salah | `admin` (ada di DB) | `wrongpass` | **Invalid** | Pesan error "Credentials not valid" |
| L-EP-03 | Username tidak terdaftar | `tidakada123` | `password` | **Invalid** | Pesan error "Credentials not valid" |
| L-EP-04 | Username kosong | _(kosong)_ | `password` | **Invalid** | Validasi gagal: "username is required" |
| L-EP-05 | Password kosong | `admin` | _(kosong)_ | **Invalid** | Validasi gagal: "password is required" |

### Analisis Nilai Batas

| ID | Kondisi Batas | Input Username | Input Password | Hasil yang Diharapkan |
|----|--------------|----------------|----------------|----------------------|
| L-BV-01 | Username 1 karakter (batas bawah) | `a` | `password` | Validasi lolos, cek DB → tidak ditemukan → error |
| L-BV-02 | Password 1 karakter (batas bawah) | `admin` | `p` | Validasi lolos, password tidak cocok → error |

---

## 2. Registrasi Pengguna (`POST /register/send-otp`)

**Field yang diuji:** `name` (required, max:255), `email` (required, email, unique), `password` (required, min:8, confirmed), `role` (required, in:student,teacher,parent), `gender` (required, in:L,P)

### Partisi Ekuivalen

| ID | Partisi | Data Input | Kelas | Hasil yang Diharapkan |
|----|---------|-----------|-------|----------------------|
| R-EP-01 | Semua field valid | name="Budi Santoso", email="budi@mail.com", password="Password123" (confirmed), role="student", gender="L", class_id=1 | **Valid** | OTP terkirim ke email |
| R-EP-02 | Email format tidak valid | email="budi-mail" | **Invalid** | Error: "email tidak valid" |
| R-EP-03 | Email sudah terdaftar | email=(email yg sudah ada di DB) | **Invalid** | Error: "email sudah digunakan" |
| R-EP-04 | Role di luar pilihan | role="superadmin" | **Invalid** | Error: validasi role gagal |
| R-EP-05 | Gender di luar pilihan | gender="X" | **Invalid** | Error: validasi gender gagal |

### Analisis Nilai Batas — Field `password` (min:8)

| ID | Kondisi Batas | Panjang Password | Contoh Input | Hasil yang Diharapkan |
|----|--------------|-----------------|-------------|----------------------|
| R-BV-01 | Di bawah batas minimum | 7 karakter | `Pass123` | Error: "password minimal 8 karakter" |
| R-BV-02 | Tepat di batas minimum | 8 karakter | `Pass1234` | **Valid** — validasi lolos |
| R-BV-03 | Di atas batas minimum | 9 karakter | `Pass12345` | **Valid** — validasi lolos |

### Analisis Nilai Batas — Field `name` (max:255)

| ID | Kondisi Batas | Panjang Name | Hasil yang Diharapkan |
|----|--------------|-------------|----------------------|
| R-BV-04 | Tepat di batas maksimum | 255 karakter | **Valid** — validasi lolos |
| R-BV-05 | Di atas batas maksimum | 256 karakter | Error: "name maksimal 255 karakter" |

---

## 3. Verifikasi OTP — Lupa Password (`POST /password/verify-code`)

**Field yang diuji:** `email` (required, email, exists), `code` (required, digits:6)

### Partisi Ekuivalen

| ID | Partisi | Input Email | Input Code | Kelas | Hasil yang Diharapkan |
|----|---------|------------|-----------|-------|----------------------|
| O-EP-01 | Email & OTP valid | `user@mail.com` (terdaftar) | `123456` (cocok cache) | **Valid** | "OTP terverifikasi" |
| O-EP-02 | OTP salah | `user@mail.com` | `000000` (tidak cocok) | **Invalid** | "Kode OTP salah atau kedaluwarsa" |
| O-EP-03 | OTP kedaluwarsa | `user@mail.com` | `123456` (cache expired) | **Invalid** | "Kode OTP salah atau kedaluwarsa" |
| O-EP-04 | Email tidak terdaftar | `ghost@mail.com` | `123456` | **Invalid** | Validasi gagal: email tidak ditemukan |
| O-EP-05 | Terlalu banyak percobaan (≥3×) | `user@mail.com` | salah 3× berturut | **Invalid** | "Terlalu banyak percobaan. Akun terkunci sementara." (HTTP 423) |

### Analisis Nilai Batas — Field `code` (digits:6)

| ID | Kondisi Batas | Panjang Code | Contoh Input | Hasil yang Diharapkan |
|----|--------------|-------------|-------------|----------------------|
| O-BV-01 | Di bawah batas (5 digit) | 5 | `12345` | Error: "code harus 6 digit" |
| O-BV-02 | Tepat di batas (6 digit) | 6 | `123456` | **Valid** — validasi format lolos |
| O-BV-03 | Di atas batas (7 digit) | 7 | `1234567` | Error: "code harus 6 digit" |
| O-BV-04 | Batas bawah nilai 6 digit | 6 | `100000` | **Valid** — nilai minimum 6 digit |
| O-BV-05 | Batas atas nilai 6 digit | 6 | `999999` | **Valid** — nilai maksimum 6 digit |

---

## 4. Update Profil (`PATCH /profile`)

**Field yang diuji:** `full_name` (required, max:255), `email` (required, email, unique kecuali milik sendiri), `phone` (nullable, max:20)

### Partisi Ekuivalen

| ID | Partisi | Data Input | Kelas | Hasil yang Diharapkan |
|----|---------|-----------|-------|----------------------|
| P-EP-01 | Semua field valid | full_name="Siti Rahayu", email="siti@mail.com", phone="08123456789" | **Valid** | "Profil berhasil diperbarui" |
| P-EP-02 | Nama kosong | full_name="" | **Invalid** | Error: "full_name wajib diisi" |
| P-EP-03 | Email milik user lain | email=(email user lain di DB) | **Invalid** | Error: "email sudah digunakan" |
| P-EP-04 | Phone kosong (nullable) | phone=null | **Valid** | Profil diperbarui tanpa nomor telepon |

### Analisis Nilai Batas — Field `phone` (max:20)

| ID | Kondisi Batas | Panjang Phone | Contoh Input | Hasil yang Diharapkan |
|----|--------------|--------------|-------------|----------------------|
| P-BV-01 | Di bawah batas maks | 19 karakter | `0812345678901234567` | **Valid** |
| P-BV-02 | Tepat di batas maks | 20 karakter | `08123456789012345678` | **Valid** |
| P-BV-03 | Di atas batas maks | 21 karakter | `081234567890123456789` | Error: "phone maksimal 20 karakter" |

---

## 5. Reset Password (`POST /password/reset`)

**Field yang diuji:** `email` (required, email, exists), `password` (required, min:8)

### Partisi Ekuivalen

| ID | Partisi | Data Input | Kelas | Hasil yang Diharapkan |
|----|---------|-----------|-------|----------------------|
| RP-EP-01 | Data valid, OTP terverifikasi | email="user@mail.com", password="NewPass88" | **Valid** | "Password berhasil direset" |
| RP-EP-02 | Tanpa verifikasi OTP sebelumnya | email="user@mail.com", password="NewPass88" | **Invalid** | "Permintaan tidak sah atau sudah kedaluwarsa" (HTTP 403) |
| RP-EP-03 | Email tidak terdaftar | email="noone@mail.com", password="NewPass88" | **Invalid** | Error validasi: email tidak ditemukan |

### Analisis Nilai Batas — Field `password` (min:8)

| ID | Kondisi Batas | Panjang Password | Contoh Input | Hasil yang Diharapkan |
|----|--------------|-----------------|-------------|----------------------|
| RP-BV-01 | Di bawah batas minimum | 7 karakter | `NewPas7` | Error: "password minimal 8 karakter" |
| RP-BV-02 | Tepat di batas minimum | 8 karakter | `NewPass8` | **Valid** — password direset |
| RP-BV-03 | Di atas batas minimum | 9 karakter | `NewPass88` | **Valid** — password direset |

---

## 6. Manajemen Jadwal (`POST /schedules`)

**Field yang diuji:** `day_of_week` (required, in:Monday–Sunday), `start_time` (required, format H:i), `end_time` (required, format H:i, after start_time)

### Partisi Ekuivalen

| ID | Partisi | Data Input | Kelas | Hasil yang Diharapkan |
|----|---------|-----------|-------|----------------------|
| S-EP-01 | Semua field valid | class_id=1, subject_id=1, teacher_id=1, day="Monday", start="08:00", end="09:00" | **Valid** | "Jadwal berhasil ditambahkan" |
| S-EP-02 | Hari di luar pilihan | day="Holiday" | **Invalid** | Error validasi: day_of_week tidak valid |
| S-EP-03 | end_time ≤ start_time | start="10:00", end="09:00" | **Invalid** | Error: "end_time harus setelah start_time" |
| S-EP-04 | start_time = end_time | start="10:00", end="10:00" | **Invalid** | Error: "end_time harus setelah start_time" |
| S-EP-05 | Jadwal bentrok (guru/kelas sama, jam overlap) | Data sama dengan jadwal yang sudah ada | **Invalid** | Error: "Jadwal bentrok!" |

### Analisis Nilai Batas — Field `start_time` & `end_time` (format H:i)

| ID | Kondisi Batas | Input start_time | Input end_time | Hasil yang Diharapkan |
|----|--------------|-----------------|----------------|----------------------|
| S-BV-01 | Batas awal hari | `00:00` | `01:00` | **Valid** — jadwal tersimpan |
| S-BV-02 | Batas akhir hari | `23:00` | `23:59` | **Valid** — jadwal tersimpan |
| S-BV-03 | Selisih 1 menit (batas minimum durasi) | `08:00` | `08:01` | **Valid** — durasi terpendek yang diizinkan |
| S-BV-04 | Format tidak valid | `25:00` | `09:00` | Error: format waktu tidak valid |

---

## Ringkasan Kasus Uji

| No | Fitur | Jumlah EP | Jumlah BVA | Total |
|----|-------|-----------|------------|-------|
| 1 | Login | 5 | 2 | 7 |
| 2 | Registrasi | 5 | 5 | 10 |
| 3 | Verifikasi OTP | 5 | 5 | 10 |
| 4 | Update Profil | 4 | 3 | 7 |
| 5 | Reset Password | 3 | 3 | 6 |
| 6 | Manajemen Jadwal | 5 | 4 | 9 |
| | **Total** | **27** | **22** | **49** |

> [!NOTE]
> Setiap kasus uji dirancang berdasarkan aturan validasi yang ada di source code controller Laravel (validation rules). Partisi Ekuivalen membagi input ke kelas valid dan invalid, sedangkan Analisis Nilai Batas menguji titik-titik kritis pada panjang/nilai minimum dan maksimum field.

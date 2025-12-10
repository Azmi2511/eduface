<!-- Project: Eduface -->
<p align="center">
  <a href="./" target="_blank">
    <!-- Prefer project logo if available -->
    <img src="public/assets/logo.png" alt="Eduface" width="160" style="border-radius:8px;" />
  </a>
</p>

# Eduface — Sistem Absensi Sekolah (Laravel)

Eduface adalah aplikasi manajemen absensi dan informasi sekolah berbasis Laravel. Aplikasi ini menyediakan fungsionalitas inti seperti manajemen pengguna (admin, guru, orang tua), absensi, jadwal, pengumuman, notifikasi, dan lainnya.

Project ini adalah backend + frontend (Blade) berbasis Laravel dan dirancang untuk dijalankan di lingkungan pengembangan lokal maupun produksi.

---

## Fitur Utama
- Manajemen pengguna: `users`, `students`, `teachers`, `parents`.
- Absensi: pencatatan dan laporan kehadiran.
- Pengumuman dan notifikasi untuk user terkait.
- Kontrol akses role-based (`admin`, `teacher`, `parent`).
- Autentikasi sederhana berbasis sesi (session-based auth).

---

## Persyaratan
- PHP 8.1+ (proyek ini diuji pada PHP 8.2)
- Composer
- MySQL atau database yang didukung Laravel
- Node.js & npm (opsional, untuk asset tooling)

---

## Quick Start (Local Development)
Panduan singkat untuk menjalankan proyek ini di Windows PowerShell.

1. Clone repo

```powershell
git clone <repo-url> eduface-project
cd eduface-project
```

2. Install dependencies

```powershell
composer install
npm install    # jika mau build asset (opsional)
```

3. Konfigurasi environment

Salin file `.env.example` (atau `.env` sudah ada) dan sesuaikan pengaturan DB.

```powershell
copy .env.example .env
php artisan key:generate
```

Edit `.env` dan atur koneksi database:

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

4. Session driver (penting)

Untuk pengembangan lokal kami merekomendasikan `SESSION_DRIVER=file` (default disimpan di `storage/framework/sessions`). Jika Anda menggunakan `database` pastikan tabel sesi ada dengan menjalankan:

```powershell
php artisan session:table
php artisan migrate
```

Jika Anda mengalami error CSRF / 419 setelah login, pastikan session driver berfungsi (lihat bagian Troubleshooting).

5. Migrasi & seed (jika diperlukan)

```powershell
php artisan migrate
php artisan db:seed --class=ClassesTableSeeder   # contoh seeder
```

6. Jalankan server

```powershell
php artisan serve
# buka http://127.0.0.1:8000
```

7. (Opsional) Buat akun test cepat

Anda dapat membuat user melalui Tinker atau skrip `scripts/create_test_user.php` (jika tersedia):

```powershell
php artisan tinker
>>> \App\Models\User::create(['username'=>'admin','password'=>\Hash::make('password'),'full_name'=>'Admin','role'=>'admin']);
```

Atau jalankan skrip helper:

```powershell
php scripts/create_test_user.php
```

---

## Struktur Direktori (singkat)
- `app/` - model, controller, middleware
- `routes/web.php` - rute web
- `resources/views` - Blade templates (frontend)
- `public/assets` - logo, favicon, static assets
- `database/migrations` - skema database

---

## Troubleshooting umum

- 419 Page Expired setelah login:
  - Pastikan `SESSION_DRIVER` di `.env` benar (untuk lokal gunakan `file`).
  - Pastikan `APP_URL` sesuai (mis. `http://127.0.0.1:8000`), dan hapus cookie browser atau gunakan incognito saat mengetes.
  - Jika memakai `database` untuk session, jalankan `php artisan session:table` dan `php artisan migrate`.

- Redirect loop antara `/` dan `/login`:
  - Periksa middleware `app/Http/Middleware/SessionAuth.php` dan pastikan login route diizinkan untuk bypass.
  - Cek apakah ada proses eksternal atau script yang mem-poll root secara terus-menerus (lihat `storage/logs/laravel.log` untuk `SessionAuth check`).

- Link sidebar tidak berpindah/404:
  - Pastikan view `resources/views/partials/sidebar.blade.php` menggunakan `route('...')` helpers dan nama rute sesuai `php artisan route:list`.

---

## Testing

Jalankan test unit/feature yang tersedia:

```powershell
php artisan test
```

---

## Deployment (singkat)

- Buat `APP_ENV=production` dan set `APP_DEBUG=false` di `.env`.
- Setup web server (Nginx/Apache) pointing ke folder `public/`.
- Gunakan process manager (Supervisor) untuk queue workers jika diperlukan.

---

## Contributing

1. Fork repo
2. Buat branch feature/descriptive
3. Buka PR dengan deskripsi perubahan dan langkah reproduksi

Terima PR yang rapi, small, dan disertai penjelasan dan tests jika perlu.

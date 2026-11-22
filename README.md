# PEMIRA FASILKOM — Sistem Pemilihan Raya Mahasiswa

Aplikasi e-voting PEMIRA Fakultas Ilmu Komputer berbasis **Laravel 11**, **Inertia.js**, **Vue 3**, **TypeScript**, dan **Tailwind CSS**.

## Kebutuhan Sistem
- PHP 8.2 – 8.4 (disarankan; ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `ctype`, `json`)
  *(Jika di laptop memakai PHP 8.5+, tambahkan flag `--ignore-platform-req=php` saat menjalankan `composer install`.)*
- Composer
- Node.js >= 18 & npm
- MySQL / MariaDB

## Instalasi Awal (Clone di Laptop Baru / Tim)
1. Clone repository lalu masuk ke folder project:
   ```bash
   git clone https://github.com/ferditri/pemira-2027.git
   cd pemira-2027
   ```
2. Install dependency backend & frontend:
   ```bash
   composer install
   npm install
   ```
3. Salin file environment dan generate `APP_KEY` baru:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Di PowerShell Windows gunakan `Copy-Item .env.example .env`.)*
4. Buat database kosong di MySQL (misal `pemira`), lalu atur konfigurasi `.env`:
   - Database (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Google OAuth (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI=http://localhost:8000/auth/callback`)
   - **Super Admin**: isi email pengelola tertinggi pada `PEMIRA_ADMIN_EMAILS` (pisahkan dengan koma jika lebih dari satu), contoh:
     ```env
     PEMIRA_ADMIN_EMAILS="25082010135@student.upnjatim.ac.id"
     ```
   - *(Opsional khusus testing lokal)* `AUTH_ALLOW_ANY_EMAIL=true` hanya aktif saat `APP_ENV=local` dan otomatis terkunci mati di `production`.
5. Jalankan migrasi database dan buat symlink storage:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
6. Build aset frontend (atau jalankan `npm run dev` saat ngoding):
   ```bash
   npm run build
   ```

## Menjalankan di Lokal (Development)
Jalankan 2 terminal di folder root project:
```bash
php artisan serve --host=localhost --port=8000
npm run dev
```
*(Untuk menjalankan automated test keamanan & alur voting: `php artisan test`.)*

## Catatan Production
Lihat panduan lengkap & checklist keamanan deploy di [`HOSTING_CHECKLIST.md`](./HOSTING_CHECKLIST.md). Ringkasnya:
- Pastikan di `.env` production:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_KEY` wajib digenerate baru di server (`php artisan key:generate --force`)
  - `APP_URL=https://domain-pemira-anda.ac.id`
  - `AUTH_ALLOW_ANY_EMAIL=false`
  - `TELESCOPE_ENABLED=false`
  - `SESSION_SECURE_COOKIE=true` & `SESSION_ENCRYPT=true`
  - `TRUSTED_PROXIES` diisi IP/CIDR proxy server (misal `127.0.0.1`)
- Pastikan hanya akun terpercaya yang terdaftar di `PEMIRA_ADMIN_EMAILS`. Akun di daftar ini berstatus Super Admin dan tidak dapat diubah/dihapus oleh admin lain dari dashboard.
- Saat melakukan **Reset Pemilihan** pada organisasi, surat suara lama diarsipkan dengan *soft delete* (`deleted_at`) dan ringkasan rekap agregat anonim otomatis disimpan di `storage/app/private/reset-backups/`.

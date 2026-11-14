# PEMIRA FASILKOM — Sistem Pemilihan Raya Mahasiswa

Aplikasi e-voting PEMIRA Fakultas Ilmu Komputer berbasis **Laravel 11**, **Inertia.js**, **Vue 3**, **TypeScript**, dan **Tailwind CSS**.

## Kebutuhan Sistem
- PHP >= 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `ctype`, `json`)
- Composer
- Node.js >= 18 & npm
- MySQL / MariaDB

## Instalasi Awal
1. Masuk ke folder aplikasi:
   ```bash
   cd pemira26-main
   ```
2. Install dependency backend & frontend:
   ```bash
   composer install
   npm install
   ```
3. Salin file environment dan generate app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Atur konfigurasi `.env`:
   - Database (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Google OAuth (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`)
   - **Super Admin**: isi email pengelola tertinggi pada `PEMIRA_ADMIN_EMAILS` (pisahkan dengan koma jika lebih dari satu), contoh:
     ```env
     PEMIRA_ADMIN_EMAILS="25082010135@student.upnjatim.ac.id"
     ```
5. Jalankan migrasi database dan buat symlink storage:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
6. Build aset frontend:
   ```bash
   npm run build
   ```

## Menjalankan di Lokal (Development)
```bash
php artisan serve
npm run dev
```

## Catatan Production
- Pastikan di `.env` production:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://domain-pemira-anda.ac.id`
- Pastikan hanya akun terpercaya yang terdaftar di `PEMIRA_ADMIN_EMAILS`. Akun di daftar ini berstatus Super Admin dan tidak dapat diubah/dihapus oleh admin lain dari dashboard.
- Saat melakukan **Reset Pemilihan** pada organisasi, surat suara lama diarsipkan dengan *soft delete* (`deleted_at`) dan backup JSON otomatis disimpan di `storage/app/private/reset-backups/`.

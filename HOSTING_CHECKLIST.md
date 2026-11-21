# Panduan Lengkap Deploy, Hosting & Siap Hari-H — PEMIRA 2026

Panduan ringkas, urut, dan *best practice* keamanan dari nol sampai siap dipakai saat hari pemilihan.

---

## 1. Persiapan Server, Domain & SSL (HTTPS)

1. **Arahkan Domain ke Server**
   - Di panel DNS domain (Cloudflare / registrar), buat **A Record** mengarah ke IP server/hosting (`@` dan `www` jika dipakai).
2. **Arahkan DocumentRoot ke Folder `public/`**
   - **WAJIB**: Root web server (Apache/Nginx/cPanel) harus menunjuk ke `.../pemira26-main/public`, **bukan** root project (agar `.env` dan file sistem tidak bisa diakses publik).
3. **Aktifkan SSL / HTTPS**
   - Pasang sertifikat SSL (Let's Encrypt / Cloudflare SSL Full).
   - **Wajib HTTPS** karena:
     - Google OAuth menolak domain publik HTTP biasa.
     - Fitur **Buka Kamera** di halaman verifikasi KTM & selfie hanya diizinkan browser pada `https://` (atau `localhost`).
4. **Hardening PHP (`php.ini`)**
   - Pastikan `register_argc_argv = Off` di `php.ini` server production (mencegah manipulasi argumen CLI melalui query string HTTP).
   - Pastikan `expose_php = Off` dan `display_errors = Off`.

---

## 2. Upload Kode, Update Security Patch & Build Aset

1. **Upload / Pull Kode** ke server (via `git pull` atau upload zip/SFTP).
   - **Saat update kode di kemudian hari**: **JANGAN** hapus atau timpa folder `storage/app/` dan file `.env`.
2. **Pastikan Dependency Composer Sudah Ter-patch Sebelum Hari-H**
   - Sebelum deploy production, pastikan `laravel/framework` (>= `11.31+` / terbaru 11.x untuk tambal **CVE-2024-52301**) dan `symfony/http-foundation` (terbaru 7.x untuk tambal **CVE-2026-108082** ReDoS) sudah diperbarui dan dicek lewat `composer audit`.
3. **Install Dependency Production & Build Frontend**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```
   *(Jika server shared hosting tidak punya Node.js, jalankan `npm run build` di PC lokal lalu upload folder `public/build/` ke server.)*
4. **Izin Folder (Permission)**
   - Pastikan folder `storage/` dan `bootstrap/cache/` bisa ditulis (*writable*, umumnya `chmod -R 775 storage bootstrap/cache` di Linux).

---

## 3. Pengaturan `.env` Production (WAJIB TELITI)

Salin `.env.example` menjadi `.env` di server, lalu sesuaikan:

### Aplikasi, Session & Keamanan
```env
APP_NAME="PEMIRA 2026"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainkamu.com

# Wajib true di production HTTPS
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

# Wajib false di production (Telescope hanya untuk lokal)
TELESCOPE_ENABLED=false

# IP/CIDR reverse proxy tepercaya (misal 127.0.0.1 untuk Nginx/Apache lokal)
TRUSTED_PROXIES=127.0.0.1
```

### ⚠️ 2 Hal Keamanan WAJIB Dicek Saat Deploy ke Server (Jangan Sampai Terlewat!)

1. **`APP_KEY` WAJIB Generate Baru di Server Production**
   - Di `.env.example` lama sempat ada `APP_KEY` terisi (kini sudah dikosongkan).
   - Saat setup server production, **WAJIB** jalankan `php artisan key:generate --force` (cukup sekali saat setup awal) agar server punya kunci enkripsi unik dan tidak pernah memakai key dari git/repo lain.
   - **Kenapa kritis?** Jika `APP_KEY` bocor/sama dengan repo, penyerang bisa memalsukan cookie terenkripsi dan signed URL. Sebagai perlindungan berlapis (*defense-in-depth*), `config/filesystems.php` juga sudah diset `'serve' => false` pada disk `local` agar file KTM privat hanya dilayani lewat controller terautentikasi.

2. **Konfigurasi `TRUSTED_PROXIES` (`trustProxies`)**
   - Jangan gunakan `TRUSTED_PROXIES=*` kecuali port aplikasi backend tertutup rapat oleh firewall sehingga **hanya** bisa diakses melalui 1 reverse proxy tepercaya (Nginx / Cloudflare) yang menimpa header `X-Forwarded-Proto`/`X-Forwarded-Host` dan menambahkan IP asli klien ke `X-Forwarded-For`.
   - **Rekomendasi aman:** Isi `TRUSTED_PROXIES` dengan IP/CIDR proxy spesifik server kamu (contoh `127.0.0.1` bila Nginx/Apache berada di mesin yang sama, atau daftar CIDR proxy yang dipakai) agar rate limit login (`/auth/redirect`, `/auth/callback`) dan deteksi HTTPS (HSTS) tidak bisa dipalsukan klien.

### Database MySQL Hosting
Isi sesuai database yang dibuat di panel hosting:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db_hosting
DB_USERNAME=user_db_hosting
DB_PASSWORD=password_db_yang_kuat
```

### Google OAuth
```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://domainkamu.com/auth/callback
```

### Konfigurasi PEMIRA
```env
# Email admin (otomatis akses dashboard /admin), pisah koma
PEMIRA_ADMIN_EMAILS="admin1@student.upnjatim.ac.id,admin2@gmail.com"

# Domain email pemilih yang diizinkan (bisa lebih dari 1, pisah koma)
PEMIRA_ALLOWED_EMAIL_DOMAIN="student.upnjatim.ac.id"

# WAJIB false di production
AUTH_ALLOW_ANY_EMAIL=false

# Kontak & link landing page
PEMIRA_SUPPORT_WHATSAPP=6285xxxxxxxxx
PEMIRA_TUTORIAL_VIDEO_ID=_Zpm7Rbca1A
PEMIRA_INSTAGRAM_URL=https://www.instagram.com/pemirafasilkom/
PEMIRA_YOUTUBE_URL=https://www.youtube.com/@pemirafasilkomupnvjt
```

> Variabel `MEMCACHED_*`, `REDIS_*`, `MAIL_*` (`MAIL_MAILER=log`), dan `AWS_*` biarkan default jika tidak memakai layanan tersebut.

---

## 4. Google Cloud Console (OAuth)

Buka **Google Auth Platform → Clients → Web application**:
1. **Authorized JavaScript origins**: tambahkan `https://domainkamu.com`
2. **Authorized redirect URIs**: tambahkan `https://domainkamu.com/auth/callback`
3. **Audience / Publishing status**: ubah status ke **In production** (jangan *Testing*), supaya seluruh mahasiswa bisa login tanpa harus didaftarkan satu-satu sebagai *Test user*.

---

## 5. Setup Database MySQL & Storage Foto

1. **Buat database kosong + user MySQL** di panel hosting.
2. **Migrasi struktur tabel** (TANPA `--seed` agar data dummy testing tidak ikut masuk):
   ```bash
   php artisan migrate --force
   ```
3. **Hubungkan folder storage foto**:
   ```bash
   php artisan storage:link
   ```
   - Cek ruang disk hosting cukup untuk foto KTM, selfie, dan kandidat (siapkan minimal 2–5 GB).
   - Pastikan *directory listing* dimatikan di web server (`Options -Indexes` sudah ada di `public/.htaccess`) agar isi folder `storage/` tidak bisa diintip.
4. **Optimasi Cache Laravel** (jalankan setiap selesai ubah `.env` atau update kode):
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 6. Isi Data Resmi Sebelum Hari-H (Lewat Dashboard `/admin`)

1. Login pakai akun email yang terdaftar di `PEMIRA_ADMIN_EMAILS`.
2. Buka **Whitelists** → tempel daftar seluruh NPM mahasiswa yang berhak memilih di *Mass Whitelists*.
3. Buka **Calon & Tipe** → buat/cek Organisasi, Tipe Pemilihan (misal BEM, BLM Informatika `10`, BLM Sistem Informasi `20`, dll), dan isi data Calon (nama, foto, visi, misi).
4. Buka **Verifikasi KTM** → klik **Custom** pada panel *Contoh* jika ingin mengunggah foto contoh peragaan asli.
5. Buka **Users** → set role `committee` untuk akun panitia jika ada (pastikan email & NPM akun panitia sesuai dengan akun Google aslinya).

---

## 7. Checklist Tes H-1 & Hari-H

- [ ] Buka `https://domainkamu.com` → tampil normal, SSL gembok aktif, header keamanan (`X-Frame-Options`, `Strict-Transport-Security`, dll) muncul.
- [ ] Pastikan `APP_KEY` sudah digenerate baru di server (bukan kosong & bukan dari `.env.example`).
- [ ] Pastikan `TRUSTED_PROXIES` sudah disesuaikan dengan IP proxy server (bukan `*` terbuka ke internet) dan `register_argc_argv = Off`.
- [ ] Pastikan `TELESCOPE_ENABLED=false`, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `AUTH_ALLOW_ANY_EMAIL=false`.
- [ ] Coba buka `https://domainkamu.com/dev/login/123` → harus **404**.
- [ ] Coba buka `https://domainkamu.com/telescope` → harus **404** (tidak terdaftar di production).
- [ ] Coba login pakai email non-kampus (yang bukan admin) → harus **ditolak**.
- [ ] Coba login pakai email kampus yang **tidak** ada di Whitelist → harus **ditolak**.
- [ ] Coba login pakai akun tes yang ada di Whitelist → tes **Buka Kamera**, upload KTM & selfie, pastikan foto muncul di `/admin/verifications` dan tidak bisa dibuka akun pemilih lain (`403`).
- [ ] Tes flow vote 1x (pastikan tidak bisa vote 2x), lalu bersihkan data tes sebelum jam pemilihan resmi dibuka.
- [ ] Saat jam mulai: buka **Kontrol Pemilihan** → klik **Mulai / Buka Vote**.
- [ ] Selama hari-H: pantau **Verifikasi KTM** (Sah / Minta Re-take / Tidak Sah) dan pastikan backup database + `storage/app/` rutin berjalan.

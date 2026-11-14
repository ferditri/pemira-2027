# Panduan Lengkap Deploy, Hosting & Siap Hari-H — PEMIRA 2026

Panduan ringkas, urut, dan *best practice* dari nol sampai siap dipakai saat hari pemilihan.

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

---

## 2. Upload Kode & Build Aset

1. **Upload / Pull Kode** ke server (via `git pull` atau upload zip/SFTP).
   - **Saat update kode di kemudian hari**: **JANGAN** hapus atau timpa folder `storage/app/public/` dan file `.env`.
2. **Install Dependency & Build Frontend**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```
   *(Jika server shared hosting tidak punya Node.js, jalankan `npm run build` di PC lokal lalu upload folder `public/build/` ke server.)*
3. **Izin Folder (Permission)**
   - Pastikan folder `storage/` dan `bootstrap/cache/` bisa ditulis (*writable*, umumnya `chmod -R 775 storage bootstrap/cache` di Linux).

---

## 3. Pengaturan `.env` Production (WAJIB TELITI)

Salin `.env.example` menjadi `.env` di server, lalu sesuaikan:

### Aplikasi & Keamanan
```env
APP_NAME="PEMIRA 2026"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainkamu.com
```
- **`APP_ENV=production`**: Mematikan route bypass `/dev/login/{npm}` dan mengunci `AUTH_ALLOW_ANY_EMAIL`.
- **`APP_DEBUG=false`**: Mencegah detail error/password tampil ke user jika terjadi error.
- **`APP_KEY`**: Jalankan `php artisan key:generate` di server (cukup sekali saat setup awal).

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
# Email admin (otomatis akses dashboard /admin & Telescope), pisah koma
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
   - Pastikan *directory listing* dimatikan di web server agar isi folder `storage/` tidak bisa diintip.
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
5. Buka **Users** → set role `committee` untuk akun panitia jika ada.

---

## 7. Auto-Backup Database & Foto (Sangat Disarankan untuk Hari-H)

Untuk acara resmi fakultas, **jangan hanya mengandalkan satu salinan data**. Backup harus mencakup **2 hal**:
1. **Database MySQL** (`users`, `whitelists`, `ballots`, `ballot_details`, `user_verifications`, dll)
2. **Folder foto upload** (`storage/app/public/` — berisi KTM, selfie, foto kandidat, logo)

### Contoh Auto-Backup via Cron Job (VPS / cPanel Linux)
Simpan script misal di `/home/user/backup_pemira.sh` (di luar folder `public_html` / project):
```bash
#!/bin/bash
DATE=$(date +"%Y-%m-%d_%H-%M")
BACKUP_DIR="/home/user/backups_pemira"
mkdir -p "$BACKUP_DIR"

# 1. Backup Database MySQL
mysqldump -u user_db_hosting -p'password_db' nama_db_hosting | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# 2. Backup Foto Storage
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" -C /path/ke/pemira26-main/storage/app public

# 3. Hapus backup yang lebih lama dari 7 hari agar disk tidak penuh
find "$BACKUP_DIR" -type f -mtime +7 -delete
```
Jadwalkan di `crontab -e` (misal jalan otomatis **setiap 1 jam** selama hari-H):
```cron
0 * * * * /bin/bash /home/user/backup_pemira.sh
```

### Opsional: Kirim Otomatis ke Bot Telegram (Off-Site Backup)
Agar file backup langsung terkirim ke Telegram (keluar dari server), buat bot di `@BotFather` (`BOT_TOKEN`) dan ambil `CHAT_ID` tujuan (chat pribadi / grup privat panitia inti, **jangan grup terbuka** karena berisi data & foto KTM). Tambahkan di `backup_pemira.sh` sebelum baris `find`:

```bash
BOT_TOKEN="ISI_TOKEN_BOT_KAMU"
CHAT_ID="ISI_CHAT_ID_KAMU"

# Kirim DB
curl -s -F document=@"$BACKUP_DIR/db_$DATE.sql.gz" \
     -F caption="Backup DB PEMIRA ($DATE)" \
     "https://api.telegram.org/bot$BOT_TOKEN/sendDocument?chat_id=$CHAT_ID" > /dev/null

# Kirim Storage (Telegram limit 50 MB: jika lebih, otomatis di-split per 45 MB)
if [ "$(stat -c%s "$BACKUP_DIR/storage_$DATE.tar.gz")" -le 51380224 ]; then
  curl -s -F document=@"$BACKUP_DIR/storage_$DATE.tar.gz" \
       -F caption="Backup Storage ($DATE)" \
       "https://api.telegram.org/bot$BOT_TOKEN/sendDocument?chat_id=$CHAT_ID" > /dev/null
else
  split -b 45M "$BACKUP_DIR/storage_$DATE.tar.gz" "$BACKUP_DIR/storage_$DATE.tar.gz.part_"
  for part in "$BACKUP_DIR/storage_$DATE.tar.gz.part_"*; do
    curl -s -F document=@"$part" \
         -F caption="Backup Storage ($DATE) - $(basename "$part")" \
         "https://api.telegram.org/bot$BOT_TOKEN/sendDocument?chat_id=$CHAT_ID" > /dev/null
  done
  rm -f "$BACKUP_DIR/storage_$DATE.tar.gz.part_"*
fi
```
*(Untuk menggabungkan file `.part_*` jika terpecah: `cat storage_*.tar.gz.part_* > storage.tar.gz`.)*

*(Tips tambahan: H-0 setelah pemilihan ditutup, pastikan salinan `db_*.sql.gz` dan `storage_*.tar.gz` sudah aman tersimpan di laptop/Google Drive panitia.)*

---

## 8. Checklist Tes H-1 & Hari-H

- [ ] Buka `https://domainkamu.com` → tampil normal, SSL gembok aktif.
- [ ] Coba buka `https://domainkamu.com/dev/login/123` → harus **404 / tidak bisa login**.
- [ ] Coba login pakai email non-kampus (yang bukan admin) → harus **ditolak**.
- [ ] Coba login pakai email kampus yang **tidak** ada di Whitelist → harus **ditolak**.
- [ ] Coba login pakai akun tes yang ada di Whitelist → tes **Buka Kamera**, upload KTM & selfie, pastikan foto muncul di `/admin/verifications`.
- [ ] Tes flow vote 1x (pastikan tidak bisa vote 2x), lalu bersihkan data tes sebelum jam pemilihan resmi dibuka.
- [ ] Saat jam mulai: buka **Kontrol Pemilihan** → klik **Mulai / Buka Vote**.
- [ ] Selama hari-H: pantau **Verifikasi KTM** (Sah / Minta Re-take / Tidak Sah) dan pastikan auto-backup berjalan.

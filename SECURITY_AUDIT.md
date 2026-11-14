# Backend Security Audit — PEMIRA 2026

Audit dilakukan secara *source-first* mengikuti workflow `cloudflare/security-audit` terhadap `routes/web.php`, `app/Http/Middleware/`, `app/Http/Controllers/`, dan `app/Providers/TelescopeServiceProvider.php`.

---

## Ringkasan Eksekutif

| ID | Severity | Judul Temuan | Lokasi |
|---|---|---|---|
| SEC-01 | **High** | Tidak ada batas maksimum jumlah kandidat pada `VoteController::storeGroup` (pemilih bisa memilih lebih dari kuota/banyak kandidat sekaligus) | `app/Http/Controllers/VoteController.php:141-176` |
| SEC-02 | **Medium** | Endpoint mutasi state (`recap/reset` & `logout`) menggunakan HTTP `GET` (rentan CSRF via link/image tag) | `routes/web.php:62,113` |
| SEC-03 | **Medium** | Foto sensitif KTM & selfie disimpan di disk `public` (`storage/app/public/`), dapat diakses langsung tanpa sesi login jika URL diketahui | `app/Http/Controllers/UserVerificationController.php:42-46` |
| SEC-04 | **Low** | Validasi `npm` pada `UserController::update` memakai `max:11` (bisa < 6 karakter saat `substr`) dan belum memaksa digit | `app/Http/Controllers/UserController.php:93-101` |
| SEC-05 | **Low** | Pesan exception mentah Google OAuth ditampilkan ke user pada flash message | `app/Http/Controllers/AuthController.php:37-39` |
| INFO-01 | **Info** | `EnsureUserIsVerified` mengizinkan status `pending` masuk bilik suara (sesuai alur verifikasi manual panitia saat rekap) | `app/Http/Middleware/EnsureUserIsVerified.php:21` |

---

## Detail Temuan & Rekomendasi Perbaikan

### SEC-01 — [HIGH] Tidak Ada Batas Maksimum `candidate_ids` Saat Voting (`VoteController::storeGroup`)
- **Trust Boundary:** Pemilih terautentikasi (`voter`) → integritas surat suara (`ballot_details`).
- **Bukti Source:** `pemira26-main/app/Http/Controllers/VoteController.php` baris 122–149 dan 169–176.
  - Controller hanya memeriksa batas bawah (`count($request->candidate_ids) < $minCandidates`), tetapi **tidak memeriksa batas atas** (`count($request->candidate_ids) > $minCandidates`).
  - Akibatnya, pemilih yang mengirim payload POST `candidate_ids: [1, 2, 3]` pada grup dengan `min_candidates = 1` akan tercatat memilih **semua** kandidat sekaligus dalam satu grup.
- **Perbaikan Terkecil (Smallest Effective Fix):**
  Tambahkan pengecekan batas maksimum tepat setelah pengecekan batas minimum di `VoteController::storeGroup`:
  ```php
  if (count($request->candidate_ids) > $minCandidates) {
      return redirect()
          ->back()
          ->withErrors([
              "candidate_ids" => "Maksimal pilih {$minCandidates} kandidat",
          ])
          ->withInput();
  }
  ```

---

### SEC-02 — [MEDIUM] Mutasi State Melalui HTTP `GET` (`recap/reset` dan `logout`)
- **Trust Boundary:** Sesi panitia/admin/pemilih → aksi perubahan state via permintaan lintas-situs (`GET` tidak dilindungi CSRF token Laravel).
- **Bukti Source:** `pemira26-main/routes/web.php`:
  - Baris 113: `Route::get("/reset", [RecapController::class, "reset"])->name("organizations.recap.reset");` — mereset seluruh status `is_verified` & `verified_at` pada `ballots` organisasi.
  - Baris 62: `Route::get("/logout", [AuthController::class, "logout"])->name("logout");` — memutus sesi user via `GET`.
- **Perbaikan Terkecil:**
  Ubah kedua route menjadi `Route::post(...)` dan perbarui pemanggil di frontend (`AdminLayout.vue` dan halaman recap) menggunakan `router.post(...)` / `<Link method="post">`.

---

### SEC-03 — [MEDIUM] Foto KTM & Selfie Disimpan di Disk `public` Tanpa Proteksi Akses
- **Trust Boundary:** Publik / non-admin → dokumen identitas mahasiswa (`ktms/`, `verifications/`).
- **Bukti Source:** `pemira26-main/app/Http/Controllers/UserVerificationController.php` baris 42–46:
  ```php
  $ktm = Storage::disk("public")->put("ktms", $request->file("ktm"));
  $verification = Storage::disk("public")->put("verifications", $request->file("verification"));
  ```
  Karena berada di `storage/app/public/` yang ter-symlink ke `public/storage/`, file dapat diakses langsung via URL statis tanpa melewati middleware autentikasi/otorisasi admin. (Nama file acak 40 karakter mengurangi risiko tebak URL, namun tetap mengekspos file jika URL bocor atau directory listing aktif).
- **Perbaikan Terkecil:**
  1. Jangka pendek (hosting): pastikan `Options -Indexes` / directory listing mati di web server untuk `/storage/`.
  2. Jangka menengah (arsitektur): simpan `ktms` & `verifications` di disk `local` (`storage/app/private/`) dan sajikan melalui endpoint controller khusus yang dilindungi `AdminMiddleware` / `ComitteeMiddleware` (atau pemilik akun sendiri).

---

### SEC-04 — [LOW] Validasi `npm` Pada `UserController::update`
- **Trust Boundary:** Admin input → konsistensi `year` & `major` akun.
- **Bukti Source:** `pemira26-main/app/Http/Controllers/UserController.php` baris 93, 100–101:
  - Aturan validasi `npm` pada `update` adalah `"required|string|max:11|unique:users,npm," . $user->id` (berbeda dengan `store` yang memakai `size:11`). Jika admin mengisi NPM < 6 karakter, `substr($npm, 4, 2)` menghasilkan string pendek/kosong.
- **Perbaikan Terkecil:**
  Samakan aturan pada `update` menjadi `"required|string|size:11|regex:/^[0-9]{11}$/|unique:users,npm," . $user->id`.

---

### SEC-05 — [LOW] Kebocoran Detail Exception pada Google OAuth Callback
- **Trust Boundary:** Error internal OAuth → pesan UI pengguna.
- **Bukti Source:** `pemira26-main/app/Http/Controllers/AuthController.php` baris 37–39:
  `return redirect(route("login"))->with("flash.message", "Gagal autentikasi Google: " . $th->getMessage())`
- **Perbaikan Terkecil:**
  Log detail error lewat `Log::error(...)` (sudah ada di baris 36), lalu tampilkan pesan generik ke user: `"Gagal autentikasi Google. Silakan coba lagi."`.

---

### INFO-01 — Catatan Alur Verifikasi Manual (`EnsureUserIsVerified`)
- `pemira26-main/app/Http/Middleware/EnsureUserIsVerified.php` baris 21 hanya memblokir status `retake` dan `rejected`, sehingga pemilih berstatus `pending` tetap bisa masuk memilih dan validasinya dilakukan panitia saat rekap (`RecapController::result` hanya menghitung `ballots.is_verified = true`).
- **Catatan operasional:** Jika panitia menandai verifikasi pemilih sebagai `rejected` / `retake` di `/admin/verifications` setelah pemilih terlanjur mengirim suara, pastikan panitia juga menandai ballot pemilih tersebut tidak sah di menu rekap (atau sinkronkan otomatis saat `review`).

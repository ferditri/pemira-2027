<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Privacy Policy - PEMIRA FASILKOM 2027</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/nav_logo_transparent.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    @vite(['resources/css/app.css'])
    <link href="{{ asset('assets/css/landing.css') }}" rel="stylesheet" />
  </head>

  <body class="bg-[#071124] text-slate-100 min-h-screen flex flex-col justify-between">
    <!-- Navbar -->
    <nav class="navbar-royal sticky top-0 z-50">
      <div class="max-w-5xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
          <img src="{{ asset('assets/images/nav_logo_transparent.png') }}" alt="Logo" class="h-10 w-auto" />
          <span class="font-cinzel font-bold text-amber-200 tracking-wider">PEMIRA FASILKOM</span>
        </a>
        <a href="{{ route('landing') }}" class="btn-royal-blue px-6 py-2 text-xs uppercase">
          &larr; Kembali ke Beranda
        </a>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-6 py-16 flex-1 w-full">
      <div class="bg-[#faf4e6] border-2 border-[#bba070] rounded-2xl p-8 md:p-12 text-[#3b2a15] shadow-2xl">
        <div class="border-b border-[#bba070]/40 pb-6 mb-8 text-center">
          <span class="font-cinzel text-xs tracking-widest text-amber-900 uppercase font-bold">Kebijakan Privasi</span>
          <h1 class="font-cinzel text-3xl md:text-4xl font-extrabold text-[#6e1324] mt-2">
            PRIVACY POLICY
          </h1>
          <p class="text-xs text-[#705531] mt-2">Terakhir diperbarui: November 2026</p>
        </div>

        <div class="space-y-6 text-sm md:text-base leading-relaxed">
          <section>
            <h2 class="font-cinzel font-bold text-xl text-[#0d1b38] mb-2">1. Pengantar</h2>
            <p>
              Kebijakan Privasi ini menjelaskan bagaimana Komisi Pemilihan Raya Mahasiswa (PEMIRA) Fakultas Ilmu Komputer mengumpulkan, menggunakan, dan melindungi data pribadi pemilih yang menggunakan sistem e-voting ini.
            </p>
          </section>

          <section>
            <h2 class="font-cinzel font-bold text-xl text-[#0d1b38] mb-2">2. Data yang Dikumpulkan</h2>
            <p>Data yang kami gunakan untuk otentikasi dan validasi pemilih meliputi:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2 text-[#4a361e]">
              <li>Nomor Pokok Mahasiswa (NPM)</li>
              <li>Nama Lengkap & Program Studi</li>
              <li>Akun Email Mahasiswa (SSO Google Workspace)</li>
              <li>Foto Kartu Tanda Mahasiswa (KTM) untuk verifikasi identitas</li>
            </ul>
          </section>

          <section>
            <h2 class="font-cinzel font-bold text-xl text-[#0d1b38] mb-2">3. Kerahasiaan Suara (Luber Jurdil)</h2>
            <p>
              Pilihan surat suara Anda dienkripsi secara asimetris di server dan <strong>TIDAK DAPAT DIKAITKAN KEMBALI</strong> dengan identitas atau NPM Anda. Sistem hanya mencatat status token bahwa pemilih telah menggunakan hak suaranya demi mencegah pemilihan ganda.
            </p>
          </section>

          <section>
            <h2 class="font-cinzel font-bold text-xl text-[#0d1b38] mb-2">4. Keamanan Data</h2>
            <p>
              Seluruh transmisi data dilindungi oleh enkripsi SSL/TLS 256-bit standar industri. Data verifikasi hanya dapat diakses oleh Panitia Pengawas Pemira yang berwenang dan disumpah untuk menjaga kerahasiaan.
            </p>
          </section>

          <section>
            <h2 class="font-cinzel font-bold text-xl text-[#0d1b38] mb-2">5. Hubungi Kami</h2>
            <p>
              Jika ada pertanyaan mengenai kebijakan privasi atau kendala akun pemilih, silakan hubungi tim IT Pemira melalui Instagram resmi di 
              <a href="{{ config('pemira.instagram_url') }}" target="_blank" class="text-amber-800 font-bold underline hover:text-[#6e1324]">@pemirafasilkom</a>.
            </p>
          </section>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="footer-royal py-6 text-center text-xs text-amber-200/50">
      &copy; 2026 PEMIRA Fasilkom 2027. Hak cipta dilindungi.
    </footer>
  </body>
</html>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PEMIRA FASILKOM 2027 - Pemilihan Raya Mahasiswa</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/nav_logo_transparent.png') }}" />

    <!-- Google Fonts (Cinzel, MedievalSharp, Montserrat) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800;900&family=Cinzel+Decorative:wght@700;900&family=MedievalSharp&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
      rel="stylesheet"
    />

    <!-- Tailwind CSS (CDN for instant standalone rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('assets/css/landing.css') }}" rel="stylesheet" />
  </head>

  <body class="bg-[#071124] text-slate-100 overflow-x-hidden selection:bg-amber-500 selection:text-slate-900">

    <!-- ================= NAVBAR ================= -->
    <nav id="main-navbar" class="navbar-royal fixed top-0 left-0 w-full z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4 lg:gap-8">
          
          <!-- Brand Logo -->
          <a href="#hero" class="flex items-center gap-3 group shrink-0">
            <img 
              src="{{ asset('assets/images/nav_logo_transparent.png') }}" 
              alt="Logo PEMIRA" 
              class="h-11 w-auto drop-shadow-[0_2px_8px_rgba(223,158,56,0.5)] group-hover:scale-105 transition duration-300" 
            />
            <div class="flex flex-col">
              <span class="font-cinzel font-bold text-lg text-amber-200 tracking-wider">PEMIRA</span>
              <span class="font-cinzel text-xs text-amber-400/80 -mt-1 tracking-widest">FASILKOM</span>
            </div>
          </a>

          <!-- Desktop Menu Navigation -->
          <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
            <a href="#hero" class="nav-link">Beranda</a>
            <a href="#tentang" class="nav-link">Tentang</a>
            <a href="#jadwal" class="nav-link">Jadwal</a>
            <a href="#kandidat" class="nav-link">Kandidat</a>
            <a href="#voting" class="nav-link">Cara Memilih</a>
          </div>

          <!-- Action Button -->
          <div class="hidden md:flex items-center shrink-0">
            @auth
              <a href="{{ route('index') }}" class="btn-gold px-7 py-2.5 text-sm uppercase">
                MASUK KE VOTING
              </a>
            @else
              <a href="{{ route('login') }}" class="btn-gold px-7 py-2.5 text-sm uppercase">
                MEMILIH
              </a>
            @endauth
          </div>

          <!-- Mobile Hamburger Toggle -->
          <div class="md:hidden flex items-center">
            <button id="mobile-menu-btn" type="button" class="text-amber-300 hover:text-white p-2 focus:outline-none" aria-label="Toggle Menu">
              <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div id="mobile-menu" class="hidden md:hidden bg-[#071328]/95 border-b border-amber-500/30 px-6 pt-2 pb-6 space-y-3">
        <a href="#hero" class="mobile-nav-link block py-2 text-slate-200 font-cinzel font-semibold hover:text-amber-400">Beranda</a>
        <a href="#tentang" class="mobile-nav-link block py-2 text-slate-200 font-cinzel font-semibold hover:text-amber-400">Tentang</a>
        <a href="#jadwal" class="mobile-nav-link block py-2 text-slate-200 font-cinzel font-semibold hover:text-amber-400">Jadwal</a>
        <a href="#kandidat" class="mobile-nav-link block py-2 text-slate-200 font-cinzel font-semibold hover:text-amber-400">Kandidat</a>
        <a href="#voting" class="mobile-nav-link block py-2 text-slate-200 font-cinzel font-semibold hover:text-amber-400">Cara Memilih</a>
        <div class="pt-3">
          @auth
            <a href="{{ route('index') }}" class="btn-gold w-full py-3 text-center text-sm uppercase">
              MASUK KE VOTING
            </a>
          @else
            <a href="{{ route('login') }}" class="btn-gold w-full py-3 text-center text-sm uppercase">
              MEMILIH
            </a>
          @endauth
        </div>
      </div>
    </nav>

    <!-- ================= HERO SECTION (TRUE LAYERED ARTWORK) ================= -->
    <header id="hero" class="relative bg-[#071124]">
      <div class="hero-layered-container">
        <!-- Pristine 2K Background: Knights, Moon, Castle, Marble Floor (No baked navbar/text) -->
        <img 
          src="{{ asset('assets/images/me/bg-hero-section.webp') }}" 
          alt="Kastil PEMIRA FASILKOM 2027" 
          class="hero-bg-img"
        />

        <!-- Interactive Layered Content -->
        <div class="hero-layered-overlay">
          <img 
            src="{{ asset('assets/images/me/teks-pemira-hero-trimmed.webp') }}" 
            alt="PEMIRA FASILKOM 2027" 
            class="hero-title-graphic"
          />
          <p class="hero-tagline-text">
            Satu suara, satu takdir kerajaan kampus. Tentukan pemimpin yang akan mengibarkan kemenangan
          </p>
          <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6">
            @auth
              <a href="{{ route('index') }}" class="btn-gold px-7 py-3 md:px-9 md:py-3.5 text-xs md:text-sm uppercase shadow-2xl">
                MASUK KE VOTING
              </a>
            @else
              <a href="{{ route('login') }}" class="btn-gold px-7 py-3 md:px-9 md:py-3.5 text-xs md:text-sm uppercase shadow-2xl">
                MULAI MEMILIH
              </a>
            @endauth
            <a href="#kandidat" class="btn-royal-blue px-7 py-3 md:px-9 md:py-3.5 text-xs md:text-sm uppercase shadow-2xl">
              LIHAT KANDIDAT
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- ================= SECTION: TENTANG PEMIRA ================= -->
    <section id="tentang" class="section-parchment pt-16 pb-24 px-4 sm:px-6 lg:px-8">
      
      <!-- Sparkle stars -->
      <svg class="sparkle-star top-16 left-12 w-8 h-8 text-amber-500/50" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 0 C12 6 6 12 0 12 C6 12 12 18 12 24 C12 18 18 12 24 12 C18 12 12 6 12 0 Z"/>
      </svg>
      <svg class="sparkle-star top-48 right-16 w-10 h-10 text-amber-500/50" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 0 C12 6 6 12 0 12 C6 12 12 18 12 24 C12 18 18 12 24 12 C18 12 12 6 12 0 Z"/>
      </svg>

      <div class="max-w-4xl mx-auto text-center">
        <!-- Sub-divider line -->
        <div class="parchment-divider">
          <div class="parchment-divider-line"></div>
          <span class="parchment-divider-text">TENTANG</span>
          <div class="parchment-divider-line"></div>
        </div>

        <!-- Main Title: PEMIRA -->
        <h2 class="section-heading-dark">
          PEMIRA
        </h2>

        <!-- Description Paragraphs -->
        <div class="space-y-6 text-[#4a3b25] font-medium leading-relaxed max-w-2xl mx-auto">
          <p class="font-cinzel font-semibold text-lg text-[#2e2213]">
            Satu suara, satu takdir kerajaan kampus. Tentukan pemimpin yang akan mengibarkan kemenangan.
          </p>
          <p class="text-sm md:text-base leading-relaxed text-[#5c472d]">
            PEMIRA FASILKOM 2027 merupakan pesta demokrasi akbar tahunan bagi seluruh Sivitas Akademika Fakultas Ilmu Komputer. Seluruh mahasiswa diundang untuk menggunakan hak suaranya, mengenal visi & misi calon pemimpin, serta turut menentukan arah kemajuan peradaban kampus tercinta.
          </p>
        </div>
      </div>
    </section>

    <!-- ================= SECTION: CALON KETUA & WAKIL BEM ================= -->
    <section id="kandidat" class="section-parchment pt-12 pb-24 px-4 sm:px-6 lg:px-8 border-t border-[#dfba78]/30">
      <div class="max-w-6xl mx-auto candidate-frame-box">
        
        <!-- Golden L-Bracket Ornaments (4 Corners) -->
        <svg class="corner-ornament corner-tl text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
          <polygon points="90,15 85,12 85,18" fill="currentColor" />
          <polygon points="15,90 12,85 18,85" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-tr text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
          <polygon points="90,15 85,12 85,18" fill="currentColor" />
          <polygon points="15,90 12,85 18,85" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-bl text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
          <polygon points="90,15 85,12 85,18" fill="currentColor" />
          <polygon points="15,90 12,85 18,85" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-br text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
          <polygon points="90,15 85,12 85,18" fill="currentColor" />
          <polygon points="15,90 12,85 18,85" fill="currentColor" />
        </svg>

        <!-- Section Title matching BAHAN/LandingPage.png -->
        <h2 class="section-heading-dark mb-16">
          CALON KETUA DAN WAKIL BEM
        </h2>

        <!-- Candidate Cards Grid (Using Exact Artworks with Interactive Hotspot) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-14 max-w-4xl mx-auto">
          
          <!-- Card 1: Calon Ketua -->
          <div class="candidate-card-wrapper">
            <img 
              src="{{ asset('assets/images/card1_ketua.webp') }}" 
              alt="Calon Ketua" 
              class="w-full h-auto drop-shadow-xl"
            />
            <button 
              type="button" 
              onclick="openCandidateModal('ketua')"
              class="candidate-hotspot-btn"
              title="Lihat Visi & Misi Calon Ketua"
              aria-label="Lihat Visi & Misi Calon Ketua"
            ></button>
          </div>

          <!-- Card 2: Calon Wakil -->
          <div class="candidate-card-wrapper">
            <img 
              src="{{ asset('assets/images/card2_wakil.webp') }}" 
              alt="Calon Wakil" 
              class="w-full h-auto drop-shadow-xl"
            />
            <button 
              type="button" 
              onclick="openCandidateModal('wakil')"
              class="candidate-hotspot-btn"
              title="Lihat Visi & Misi Calon Wakil"
              aria-label="Lihat Visi & Misi Calon Wakil"
            ></button>
          </div>

        </div>
      </div>
    </section>

    <!-- ================= SECTION: CALON BLM ================= -->
    <section class="section-parchment pt-12 pb-24 px-4 sm:px-6 lg:px-8 border-t border-[#dfba78]/30">
      <div class="max-w-6xl mx-auto candidate-frame-box">
        
        <!-- Golden Corner Ornaments -->
        <svg class="corner-ornament corner-tl text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-tr text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-bl text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
        </svg>
        <svg class="corner-ornament corner-br text-amber-700/60" viewBox="0 0 100 100" fill="none" stroke="currentColor">
          <path d="M15,90 L15,15 L90,15" stroke-width="2.5" />
          <path d="M25,85 L25,25 L85,25" stroke-width="1.5" />
          <circle cx="15" cy="15" r="4" fill="currentColor" />
        </svg>

        <!-- Heading matching BAHAN/LandingPage.png -->
        <h2 class="section-heading-dark mb-14">
          CALON BLM
        </h2>

        <!-- Calon BLM Cards (5 Kandidat BLM dari Project Pemira) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
          
          <!-- BLM 1 -->
          <div class="blm-card">
            <div class="w-36 h-36 mx-auto mb-4 rounded-full overflow-hidden border-2 border-amber-600/50 shadow-md">
              <img src="{{ asset('assets/images/blm1.webp') }}" alt="Mohammad Bintang Adrian" class="w-full h-full object-cover" />
            </div>
            <h3 class="font-cinzel font-bold text-amber-900 text-base mb-1">NO. URUT 1</h3>
            <p class="font-cinzel font-semibold text-slate-800 text-sm mb-4">Mohammad Bintang Adrian</p>
            <button type="button" onclick="openCandidateModal('blm1')" class="btn-crimson px-6 py-2 text-xs uppercase">
              VISI & MISI
            </button>
          </div>

          <!-- BLM 2 -->
          <div class="blm-card">
            <div class="w-36 h-36 mx-auto mb-4 rounded-full overflow-hidden border-2 border-amber-600/50 shadow-md">
              <img src="{{ asset('assets/images/blm2.webp') }}" alt="M. Naufal Baihaqi" class="w-full h-full object-cover" />
            </div>
            <h3 class="font-cinzel font-bold text-amber-900 text-base mb-1">NO. URUT 2</h3>
            <p class="font-cinzel font-semibold text-slate-800 text-sm mb-4">M. Naufal Baihaqi</p>
            <button type="button" onclick="openCandidateModal('blm2')" class="btn-crimson px-6 py-2 text-xs uppercase">
              VISI & MISI
            </button>
          </div>

          <!-- BLM 3 -->
          <div class="blm-card">
            <div class="w-36 h-36 mx-auto mb-4 rounded-full overflow-hidden border-2 border-amber-600/50 shadow-md">
              <img src="{{ asset('assets/images/blm3.webp') }}" alt="Ananda Putri" class="w-full h-full object-cover" />
            </div>
            <h3 class="font-cinzel font-bold text-amber-900 text-base mb-1">NO. URUT 3</h3>
            <p class="font-cinzel font-semibold text-slate-800 text-sm mb-4">Ananda Putri</p>
            <button type="button" onclick="openCandidateModal('blm3')" class="btn-crimson px-6 py-2 text-xs uppercase">
              VISI & MISI
            </button>
          </div>

          <!-- BLM 4 -->
          <div class="blm-card sm:col-span-1 lg:col-start-1 lg:col-span-1">
            <div class="w-36 h-36 mx-auto mb-4 rounded-full overflow-hidden border-2 border-amber-600/50 shadow-md">
              <img src="{{ asset('assets/images/blm4.webp') }}" alt="Bunga Citra Amelya" class="w-full h-full object-cover" />
            </div>
            <h3 class="font-cinzel font-bold text-amber-900 text-base mb-1">NO. URUT 4</h3>
            <p class="font-cinzel font-semibold text-slate-800 text-sm mb-4">Bunga Citra Amelya</p>
            <button type="button" onclick="openCandidateModal('blm4')" class="btn-crimson px-6 py-2 text-xs uppercase">
              VISI & MISI
            </button>
          </div>

          <!-- BLM 5 -->
          <div class="blm-card sm:col-span-1 lg:col-span-1">
            <div class="w-36 h-36 mx-auto mb-4 rounded-full overflow-hidden border-2 border-amber-600/50 shadow-md">
              <img src="{{ asset('assets/images/blm5.webp') }}" alt="Muhammad Ammar" class="w-full h-full object-cover" />
            </div>
            <h3 class="font-cinzel font-bold text-amber-900 text-base mb-1">NO. URUT 5</h3>
            <p class="font-cinzel font-semibold text-slate-800 text-sm mb-4">Muhammad Ammar</p>
            <button type="button" onclick="openCandidateModal('blm5')" class="btn-crimson px-6 py-2 text-xs uppercase">
              VISI & MISI
            </button>
          </div>

        </div>
      </div>
    </section>

    <!-- ================= SECTION: TUTORIAL VOTING ================= -->
    <section id="voting" class="section-tutorial">
      <div class="max-w-6xl mx-auto px-4 text-center">
        
        <!-- Header -->
        <div class="parchment-divider mb-2">
          <div class="parchment-divider-line !bg-amber-400/50"></div>
          <h2 class="font-cinzel text-3xl md:text-5xl font-extrabold text-amber-100 tracking-wider">
            TUTORIAL VOTING
          </h2>
          <div class="parchment-divider-line !bg-amber-400/50"></div>
        </div>
        <p class="text-amber-200/70 font-cinzel text-sm tracking-widest uppercase mb-12">
          Panduan langkah demi langkah tata cara pemungutan suara
        </p>

        <!-- Ornate Golden Video Frame Container -->
        <div class="video-ornate-container">
          <div class="relative w-full aspect-video group">
            
            <!-- Video Thumbnail strictly fitted inside the golden frame inner window -->
            <div class="video-inner-window">
              <img 
                src="https://img.youtube.com/vi/_Zpm7Rbca1A/maxresdefault.jpg" 
                alt="Thumbnail Tutorial Voting" 
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
              />
              <!-- Subtle Dark Overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-[#0a1835]/80 via-black/20 to-black/15"></div>
            </div>

            <!-- Carved Golden Picture Frame Overlay (True RGBA Transparent Asset) -->
            <img 
              src="{{ asset('assets/images/me/kotak-tutorialvoting.webp') }}" 
              alt="Bingkai Emas Ornamen" 
              class="absolute inset-0 w-full h-full object-fill pointer-events-none z-10"
            />

            <!-- Single Play Video Button positioned cleanly at bottom border (matching Figma) -->
            <div class="absolute -bottom-5 sm:-bottom-6 inset-x-0 z-20 flex items-center justify-center">
              <button 
                type="button" 
                onclick="openVideoModal()" 
                class="btn-gold px-10 py-3.5 text-sm uppercase rounded-full shadow-2xl flex items-center gap-3 transform hover:scale-105 active:scale-95 transition"
              >
                <span>TONTON VIDEO</span>
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M8 5v14l11-7z"/>
                </svg>
              </button>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- ================= SECTION: JADWAL PELAKSANAAN (TRUE LAYERED WITH bg-jadwalpelaksanaan.webp) ================= -->
    <section id="jadwal" class="relative bg-[#0d1c3f] overflow-hidden">
      
      <!-- Desktop Timeline Display (Clean 2K Background + Sharp Vector Typography & Cards) -->
      <div class="hidden md:block timeline-layered-container">
        <img 
          src="{{ asset('assets/images/me/bg-jadwalpelaksanaan.webp') }}" 
          alt="Latar Jadwal Pelaksanaan" 
          class="timeline-bg-img"
        />

        <div class="timeline-overlay-content">
          <!-- Title -->
          <div class="flex items-center justify-center gap-4 mb-10">
            <div class="h-[1.5px] w-20 bg-gradient-to-r from-transparent to-amber-200/70"></div>
            <h2 class="font-cinzel text-3xl lg:text-4xl font-extrabold text-white tracking-wider drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]">
              JADWAL PELAKSANAAN
            </h2>
            <div class="h-[1.5px] w-20 bg-gradient-to-l from-transparent to-amber-200/70"></div>
          </div>

          <!-- Horizontal Timeline Line + 4 Cards Grid -->
          <div class="relative w-full max-w-5xl mx-auto">
            <!-- Connecting Horizontal Line behind badges -->
            <div class="absolute top-7 left-[10%] right-[10%] h-[2px] bg-gradient-to-r from-transparent via-amber-200/80 to-transparent z-0"></div>

            <div class="grid grid-cols-4 gap-5 lg:gap-7 relative z-10">
              <!-- Tahap 1 -->
              <div class="flex flex-col items-center">
                <div class="timeline-badge-circle mb-5">I</div>
                <div class="timeline-scroll-card w-full h-full">
                  <span class="font-cinzel font-bold text-amber-900 text-[10px] lg:text-xs tracking-wider uppercase block mb-1">Tahap 1</span>
                  <h4 class="font-cinzel font-extrabold text-[#5c1624] text-xs lg:text-sm mb-2 leading-tight">Pendaftaran & Berkas</h4>
                  <p class="text-[10px] lg:text-xs text-[#543f25] leading-relaxed">
                    Sosialisasi pemilu, pendaftaran calon ketua & wakil BEM, serta verifikasi berkas calon anggota BLM.
                  </p>
                </div>
              </div>

              <!-- Tahap 2 -->
              <div class="flex flex-col items-center">
                <div class="timeline-badge-circle mb-5">II</div>
                <div class="timeline-scroll-card w-full h-full">
                  <span class="font-cinzel font-bold text-amber-900 text-[10px] lg:text-xs tracking-wider uppercase block mb-1">Tahap 2</span>
                  <h4 class="font-cinzel font-extrabold text-[#5c1624] text-xs lg:text-sm mb-2 leading-tight">Uji Kelayakan</h4>
                  <p class="text-[10px] lg:text-xs text-[#543f25] leading-relaxed">
                    Pemeriksaan berkas, fit & proper test, wawancara panelis independen, dan pengundian nomor urut.
                  </p>
                </div>
              </div>

              <!-- Tahap 3 -->
              <div class="flex flex-col items-center">
                <div class="timeline-badge-circle mb-5">III</div>
                <div class="timeline-scroll-card w-full h-full">
                  <span class="font-cinzel font-bold text-amber-900 text-[10px] lg:text-xs tracking-wider uppercase block mb-1">Tahap 3</span>
                  <h4 class="font-cinzel font-extrabold text-[#5c1624] text-xs lg:text-sm mb-2 leading-tight">Masa Kampanye</h4>
                  <p class="text-[10px] lg:text-xs text-[#543f25] leading-relaxed">
                    Penyampaian visi & misi akbar, pameran program kerja, uji publik terbuka, dan debat terbuka.
                  </p>
                </div>
              </div>

              <!-- Tahap 4 -->
              <div class="flex flex-col items-center">
                <div class="timeline-badge-circle mb-5">IV</div>
                <div class="timeline-scroll-card w-full h-full">
                  <span class="font-cinzel font-bold text-amber-900 text-[10px] lg:text-xs tracking-wider uppercase block mb-1">Tahap 4</span>
                  <h4 class="font-cinzel font-extrabold text-[#5c1624] text-xs lg:text-sm mb-2 leading-tight">Pemungutan Suara</h4>
                  <p class="text-[10px] lg:text-xs text-[#543f25] leading-relaxed">
                    Pelaksanaan E-Voting serentak sivitas akademika Fasilkom dan pengumuman rekapitulasi suara.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Timeline Display (Vertical Stack for Phone Screens) -->
      <div class="md:hidden py-16 px-4 text-center">
        <h2 class="font-cinzel text-2xl font-extrabold text-amber-100 tracking-wider mb-8">
          JADWAL PELAKSANAAN
        </h2>
        <div class="space-y-6 max-w-sm mx-auto">
          <div class="timeline-scroll-card">
            <span class="inline-block w-10 h-10 rounded-full bg-amber-400 text-slate-900 font-cinzel font-bold text-base leading-10 mb-2">I</span>
            <h4 class="font-cinzel font-bold text-[#5c1624] text-lg mb-1">Tahap 1: Pendaftaran & Berkas</h4>
            <p class="text-xs text-[#543f25]">Sosialisasi dan pendaftaran calon ketua & wakil BEM serta anggota BLM.</p>
          </div>
          <div class="timeline-scroll-card">
            <span class="inline-block w-10 h-10 rounded-full bg-amber-400 text-slate-900 font-cinzel font-bold text-base leading-10 mb-2">II</span>
            <h4 class="font-cinzel font-bold text-[#5c1624] text-lg mb-1">Tahap 2: Uji Kelayakan</h4>
            <p class="text-xs text-[#543f25]">Verifikasi berkas, fit & proper test, dan penetapan nomor urut calon.</p>
          </div>
          <div class="timeline-scroll-card">
            <span class="inline-block w-10 h-10 rounded-full bg-amber-400 text-slate-900 font-cinzel font-bold text-base leading-10 mb-2">III</span>
            <h4 class="font-cinzel font-bold text-[#5c1624] text-lg mb-1">Tahap 3: Masa Kampanye</h4>
            <p class="text-xs text-[#543f25]">Penyampaian visi & misi akbar, pameran program, dan debat kandidat.</p>
          </div>
          <div class="timeline-scroll-card">
            <span class="inline-block w-10 h-10 rounded-full bg-amber-400 text-slate-900 font-cinzel font-bold text-base leading-10 mb-2">IV</span>
            <h4 class="font-cinzel font-bold text-[#5c1624] text-lg mb-1">Tahap 4: Pemungutan Suara</h4>
            <p class="text-xs text-[#543f25]">E-Voting serentak sivitas akademika Fasilkom dan pengumuman pemenang.</p>
          </div>
        </div>
      </div>

    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="footer-royal">

      <div class="max-w-4xl mx-auto px-4">
        
        <!-- Crisp Transparent Logo centered -->
        <div class="flex justify-center mb-4">
          <img 
            src="{{ asset('assets/images/me/teks-pemira-footer-trimmed.webp') }}" 
            alt="PEMIRA FASILKOM 2027" 
            class="h-14 md:h-16 w-auto drop-shadow-[0_4px_15px_rgba(223,158,56,0.55)]"
          />
        </div>

        <!-- Navigation Links matching BAHAN/LandingPage.png -->
        <div class="flex flex-wrap justify-center gap-6 md:gap-10 mb-5">
          <a href="#tentang" class="footer-link">TENTANG</a>
          <a href="#kandidat" class="footer-link">KANDIDAT</a>
          <a href="#voting" class="footer-link">TUTORIAL</a>
          <a href="#jadwal" class="footer-link">JADWAL</a>
          <a href="{{ route('privacy-policy') }}" class="footer-link">PRIVACY POLICY</a>
        </div>

        <!-- Social Icons matching BAHAN/LandingPage.png -->
        <div class="flex justify-center gap-4 mb-5">
          <!-- Instagram -->
          <a 
            href="https://www.instagram.com/pemirafasilkom/" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="social-circle-btn" 
            aria-label="Instagram"
          >
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
          <!-- YouTube -->
          <a 
            href="https://www.youtube.com/@pemirafasilkomupnvjt" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="social-circle-btn" 
            aria-label="YouTube"
          >
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
          </a>
        </div>

        <!-- Copyright matching BAHAN/LandingPage.png -->
        <p class="text-xs text-amber-200/50 font-montserrat">
          &copy; 2026 PEMIRA Fasilkom 2027. Hak cipta dilindungi.
        </p>

      </div>
    </footer>

    <!-- ================= MODAL: VISI & MISI ================= -->
    <div id="candidate-modal" class="royal-modal-backdrop" onclick="closeCandidateModal(event)">
      <div class="royal-modal-content" onclick="event.stopPropagation()">
        <button type="button" class="royal-modal-close" onclick="closeCandidateModal()" aria-label="Tutup">&times;</button>
        <div id="modal-body"></div>
      </div>
    </div>

    <!-- ================= MODAL: TUTORIAL VIDEO ================= -->
    <div id="video-modal" class="royal-modal-backdrop" onclick="closeVideoModal(event)">
      <div class="relative w-full max-w-4xl bg-black rounded-xl overflow-hidden shadow-2xl p-2" onclick="event.stopPropagation()">
        <button type="button" class="royal-modal-close !top-3 !right-3 z-30" onclick="closeVideoModal()" aria-label="Tutup">&times;</button>
        <div class="relative w-full aspect-video">
          <iframe 
            id="youtube-player" 
            class="w-full h-full rounded-lg"
            src="" 
            title="Tutorial Voting Pemira" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
          ></iframe>
        </div>
      </div>
    </div>

    <!-- ================= JAVASCRIPT ================= -->
    <script src="{{ asset('assets/js/landing.js') }}"></script>
  </body>
</html>

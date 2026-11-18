/* ==========================================================================
   PEMIRA FASILKOM 2027 - MAIN JAVASCRIPT
   ========================================================================== */

// Always start at the Hero section on refresh unless a specific hash is requested
if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}
window.addEventListener('DOMContentLoaded', () => {
  if (!window.location.hash) {
    window.scrollTo(0, 0);
  }
});

// Data Kandidat (Visi & Misi)
const candidateData = {
  ketua: {
    title: 'CALON KETUA BEM',
    name: 'NAMA CALON PRESIDEN',
    image: '/assets/images/ketua_portrait.png',
    visi: 'Mewujudkan BEM Fasilkom sebagai episentrum pergerakan mahasiswa yang progresif, inklusif, kolaboratif, serta berdaya saing global dengan berlandaskan integritas dan kepemimpinan berwawasan masa depan.',
    misi: [
      'Memperkuat advokasi kesejahteraan dan aspirasi mahasiswa secara tanggap dan transparan.',
      'Membangun iklim akademik dan inovasi teknologi yang mendorong prestasi mahasiswa di kancah nasional maupun internasional.',
      'Mengoptimalkan sinergi antar himpunan, lembaga otonom, dan sivitas akademika Fakultas Ilmu Komputer.',
      'Menanamkan budaya kepemimpinan yang berintegritas, melayani, dan berkelanjutan.'
    ]
  },
  wakil: {
    title: 'CALON WAKIL KETUA BEM',
    name: 'NAMA CALON WAKIL PRESIDEN',
    image: '/assets/images/wakil_portrait.png',
    visi: 'Membangun internal organisasi yang adaptif, solid, dan harmonis guna mendukung percepatan akselerasi program kerja BEM Fasilkom yang berdampak nyata.',
    misi: [
      'Menata tata kelola birokrasi dan administrasi organisasi yang efisien, responsif, dan akuntabel.',
      'Mempererat hubungan kekeluargaan serta soliditas seluruh fungsionaris dan lembaga mahasiswa.',
      'Mengembangkan program pemberdayaan sumber daya mahasiswa berbasis teknologi informasi.',
      'Mendampingi Ketua BEM dalam pengawalan aspirasi dan pergerakan strategis kampus.'
    ]
  },
  blm1: {
    title: 'CALON BLM NO. URUT 1',
    name: 'Mohammad Bintang Adrian',
    image: '/assets/images/blm1.webp',
    visi: 'Menjadikan BLM sebagai wadah yang aktif, terbuka, dan dekat dengan mahasiswa dalam menyalurkan aspirasi serta mendorong perubahan positif di lingkungan kampus.',
    misi: [
      'Mendengarkan dan menampung aspirasi mahasiswa dengan terbuka dan jujur.',
      'Menjalin komunikasi yang harmonis antara mahasiswa dan pimpinan fakultas.',
      'Berkontribusi aktif dalam kegiatan pengawasan yang konstruktif.'
    ]
  },
  blm2: {
    title: 'CALON BLM NO. URUT 2',
    name: 'M. Naufal Baihaqi',
    image: '/assets/images/blm2.webp',
    visi: 'Mewujudkan BLM yang berintegritas, aspiratif, dan responsif sebagai representasi seluruh mahasiswa Fakultas Ilmu Komputer.',
    misi: [
      'Memaksimalkan fungsi legislasi dan pengawasan kebijakan organisasi mahasiswa.',
      'Menciptakan kanal aspirasi yang transparan dan mudah diakses oleh seluruh mahasiswa.',
      'Menjaga harmonisasi hubungan kerja antar lembaga mahasiswa Fasilkom.'
    ]
  },
  blm3: {
    title: 'CALON BLM NO. URUT 3',
    name: 'Ananda Putri',
    image: '/assets/images/blm3.webp',
    visi: 'Membawa BLM Fasilkom menjadi lembaga perwakilan mahasiswa yang inklusif, kritis, dan berorientasi pada solusi nyata.',
    misi: [
      'Menjaring aspirasi secara merata tanpa membedakan latar belakang mahasiswa.',
      'Mengawal transparansi anggaran dan pelaksanaan program kerja ormawa.',
      'Memberikan edukasi legislatif bagi mahasiswa Fasilkom.'
    ]
  },
  blm4: {
    title: 'CALON BLM NO. URUT 4',
    name: 'Bunga Citra Amelya',
    image: '/assets/images/blm4.webp',
    visi: 'Menjadi pribadi yang terus bertumbuh, berani mengambil tanggung jawab, dan mampu memberi dampak positif bagi lingkungan sekitar.',
    misi: [
      'Menggunakan setiap kesempatan untuk mendengarkan keluhan dan masukan mahasiswa.',
      'Menegakkan nilai-nilai kejujuran, kerja keras, dan ketekunan dalam proses legislasi.',
      'Mendorong kolaborasi lintas angkatan dan program studi.'
    ]
  },
  blm5: {
    title: 'CALON BLM NO. URUT 5',
    name: 'Muhammad Ammar',
    image: '/assets/images/blm5.webp',
    visi: 'Optimalisasi peran legislatif mahasiswa dengan standar mutu dan efektivitas kerja tertinggi.',
    misi: [
      'Fokus pada output kerja yang berkualitas dan terukur bagi mahasiswa.',
      'Mewujudkan pengawasan yang tegas namun tetap solutif dan membangun.',
      'Mengakselerasi penyaluran advokasi fasilitas pembelajaran kampus.'
    ]
  }
};

// 1. Navbar Scroll Effect (passive + guarded to avoid scroll jank)
const navbar = document.getElementById('main-navbar');
if (navbar) {
  let isScrolled = false;
  const syncNavbar = () => {
    const nextScrolled = window.scrollY > 40;
    if (nextScrolled !== isScrolled) {
      isScrolled = nextScrolled;
      navbar.classList.toggle('scrolled', isScrolled);
    }
  };
  syncNavbar();
  window.addEventListener('scroll', syncNavbar, { passive: true });
}

// 2. Mobile Menu Toggle
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
const hamburgerIcon = document.getElementById('hamburger-icon');
const closeIcon = document.getElementById('close-icon');

if (mobileMenuBtn && mobileMenu) {
  mobileMenuBtn.addEventListener('click', () => {
    const isHidden = mobileMenu.classList.contains('hidden');
    if (isHidden) {
      mobileMenu.classList.remove('hidden');
      hamburgerIcon.classList.add('hidden');
      closeIcon.classList.remove('hidden');
    } else {
      mobileMenu.classList.add('hidden');
      hamburgerIcon.classList.remove('hidden');
      closeIcon.classList.add('hidden');
    }
  });

  // Close menu when clicking nav links
  document.querySelectorAll('.mobile-nav-link').forEach(link => {
    link.addEventListener('click', () => {
      mobileMenu.classList.add('hidden');
      hamburgerIcon.classList.remove('hidden');
      closeIcon.classList.add('hidden');
    });
  });
}

// 3. Scroll Reveal Observer
const revealElements = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
    }
  });
}, {
  threshold: 0.1,
  rootMargin: '0px 0px -40px 0px'
});

revealElements.forEach(el => revealObserver.observe(el));

// 4. Candidate Modal Logic
const candidateModal = document.getElementById('candidate-modal');
const modalBody = document.getElementById('modal-body');

function openCandidateModal(id) {
  const data = (window.__LANDING_CANDIDATES__ && window.__LANDING_CANDIDATES__[id]) || (typeof candidateData !== 'undefined' ? candidateData[id] : null);
  if (!data) return;

  const initial = (data.name || 'C').trim().charAt(0).toUpperCase();
  const visiText = data.visi ? data.visi : '-';
  const misiList = Array.isArray(data.misi) && data.misi.length > 0 ? data.misi : [];

  modalBody.innerHTML = `
    <div class="text-center">
      <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden border-4 border-amber-600/60 shadow-lg bg-amber-100 flex items-center justify-center relative">
        <img src="${data.image}" alt="${data.name}" class="w-full h-full object-cover" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
        <div class="w-full h-full hidden items-center justify-center bg-amber-900/10 text-amber-950 font-cinzel font-bold text-3xl select-none">
          ${initial}
        </div>
      </div>
      <span class="inline-block px-3.5 py-1 bg-amber-900/15 border border-amber-700/30 rounded-full text-xs font-cinzel font-bold text-amber-950 uppercase tracking-widest mb-1.5 shadow-sm">
        ${data.title}
      </span>
      <h3 class="font-cinzel font-extrabold text-2xl text-[#6e1324] mb-6 tracking-wide drop-shadow-sm">
        ${data.name}
      </h3>
    </div>

    <!-- Visi Box -->
    <div class="mb-5 bg-white/85 border border-amber-800/20 rounded-xl p-5 shadow-sm backdrop-blur-[2px]">
      <div class="flex items-center gap-2.5 mb-2.5">
        <div class="w-3 h-3 rounded-full bg-amber-600 shadow-sm flex-shrink-0"></div>
        <h4 class="font-cinzel font-bold text-amber-950 tracking-wider uppercase text-base">Visi</h4>
      </div>
      <p class="modal-visi-text text-[#2b1810] text-sm md:text-base leading-relaxed pl-5 font-montserrat font-medium" style="color: #2b1810 !important;">
        "${visiText}"
      </p>
    </div>

    <!-- Misi Box -->
    <div class="bg-white/85 border border-amber-800/20 rounded-xl p-5 shadow-sm backdrop-blur-[2px]">
      <div class="flex items-center gap-2.5 mb-3">
        <div class="w-3 h-3 rounded-full bg-[#6e1324] shadow-sm flex-shrink-0"></div>
        <h4 class="font-cinzel font-bold text-[#6e1324] tracking-wider uppercase text-base">Misi</h4>
      </div>
      <ul class="modal-misi-list space-y-2.5 text-[#2b1810] text-sm md:text-base pl-2 font-montserrat font-normal" style="color: #2b1810 !important;">
        ${misiList.length > 0
          ? misiList.map(m => `
            <li class="flex items-start gap-2.5 text-[#2b1810]" style="color: #2b1810 !important;">
              <span class="text-amber-800 font-bold mt-0.5 select-none flex-shrink-0" style="color: #92400e !important;">❖</span>
              <span class="leading-relaxed text-[#2b1810]" style="color: #2b1810 !important;">${m}</span>
            </li>
          `).join('')
          : `<li class="text-sm italic text-[#5c4033] pl-4" style="color: #5c4033 !important;">Belum ada rincian misi.</li>`
        }
      </ul>
    </div>
  `;

  candidateModal.classList.add('active');
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';
}

function closeCandidateModal(e) {
  if (e && e.target !== candidateModal && !e.target.classList.contains('royal-modal-close')) {
    return;
  }
  candidateModal.classList.remove('active');
  document.documentElement.style.overflow = '';
  document.body.style.overflow = '';
}

// 5. Video Tutorial Modal Logic
const videoModal = document.getElementById('video-modal');
const ytPlayer = document.getElementById('youtube-player');
const youtubeUrl = 'https://www.youtube.com/embed/_Zpm7Rbca1A?autoplay=1';

function openVideoModal() {
  ytPlayer.src = youtubeUrl;
  videoModal.classList.add('active');
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';
}

function closeVideoModal(e) {
  if (e && e.target !== videoModal && !e.target.classList.contains('royal-modal-close')) {
    return;
  }
  ytPlayer.src = '';
  videoModal.classList.remove('active');
  document.documentElement.style.overflow = '';
  document.body.style.overflow = '';
}

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeCandidateModal();
    closeVideoModal();
  }
});

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_candidates', function (Blueprint $table) {
            $table->id();
            $table->enum('section', ['bem', 'blm'])->index();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->string('role_label');
            $table->string('name');
            $table->string('photo')->nullable();
            $table->text('vision');
            $table->json('mission');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('landing_candidates')->insert([
            [
                'section' => 'bem',
                'candidate_id' => null,
                'role_label' => 'CALON KETUA BEM',
                'name' => 'Nama Calon Ketua BEM',
                'photo' => '/assets/images/ketua_portrait.png',
                'vision' => 'Mewujudkan BEM Fasilkom sebagai episentrum pergerakan mahasiswa yang progresif, inklusif, kolaboratif, serta berdaya saing global dengan berlandaskan integritas dan kepemimpinan berwawasan masa depan.',
                'mission' => json_encode([
                    'Memperkuat advokasi kesejahteraan dan aspirasi mahasiswa secara tanggap dan transparan.',
                    'Membangun iklim akademik dan inovasi teknologi yang mendorong prestasi mahasiswa di kancah nasional maupun internasional.',
                    'Mengoptimalkan sinergi antar himpunan, lembaga otonom, dan sivitas akademika Fakultas Ilmu Komputer.',
                    'Menanamkan budaya kepemimpinan yang berintegritas, melayani, dan berkelanjutan.',
                ]),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'bem',
                'candidate_id' => null,
                'role_label' => 'CALON WAKIL KETUA BEM',
                'name' => 'Nama Calon Wakil Ketua BEM',
                'photo' => '/assets/images/wakil_portrait.png',
                'vision' => 'Membangun internal organisasi yang adaptif, solid, dan harmonis guna mendukung percepatan akselerasi program kerja BEM Fasilkom yang berdampak nyata.',
                'mission' => json_encode([
                    'Menata tata kelola birokrasi dan administrasi organisasi yang efisien, responsif, dan akuntabel.',
                    'Mempererat hubungan kekeluargaan serta soliditas seluruh fungsionaris dan lembaga mahasiswa.',
                    'Mengembangkan program pemberdayaan sumber daya mahasiswa berbasis teknologi informasi.',
                    'Mendampingi Ketua BEM dalam pengawalan aspirasi dan pergerakan strategis kampus.',
                ]),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'blm',
                'candidate_id' => null,
                'role_label' => 'NO. URUT 1',
                'name' => 'Mohammad Bintang Adrian',
                'photo' => '/assets/images/blm1.webp',
                'vision' => 'Menjadikan BLM sebagai wadah yang aktif, terbuka, dan dekat dengan mahasiswa dalam menyalurkan aspirasi serta mendorong perubahan positif di lingkungan kampus.',
                'mission' => json_encode([
                    'Mendengarkan dan menampung aspirasi mahasiswa dengan terbuka dan jujur.',
                    'Menjalin komunikasi yang harmonis antara mahasiswa dan pimpinan fakultas.',
                    'Berkontribusi aktif dalam kegiatan pengawasan yang konstruktif.',
                ]),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'blm',
                'candidate_id' => null,
                'role_label' => 'NO. URUT 2',
                'name' => 'M. Naufal Baihaqi',
                'photo' => '/assets/images/blm2.webp',
                'vision' => 'Mewujudkan BLM yang berintegritas, aspiratif, dan responsif sebagai representasi seluruh mahasiswa Fakultas Ilmu Komputer.',
                'mission' => json_encode([
                    'Memaksimalkan fungsi legislasi dan pengawasan kebijakan organisasi mahasiswa.',
                    'Menciptakan kanal aspirasi yang transparan dan mudah diakses oleh seluruh mahasiswa.',
                    'Menjaga harmonisasi hubungan kerja antar lembaga mahasiswa Fasilkom.',
                ]),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'blm',
                'candidate_id' => null,
                'role_label' => 'NO. URUT 3',
                'name' => 'Ananda Putri',
                'photo' => '/assets/images/blm3.webp',
                'vision' => 'Membawa BLM Fasilkom menjadi lembaga perwakilan mahasiswa yang inklusif, kritis, dan berorientasi pada solusi nyata.',
                'mission' => json_encode([
                    'Menjaring aspirasi secara merata tanpa membedakan latar belakang mahasiswa.',
                    'Mengawal transparansi anggaran dan pelaksanaan program kerja ormawa.',
                    'Memberikan edukasi legislatif bagi mahasiswa Fasilkom.',
                ]),
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'blm',
                'candidate_id' => null,
                'role_label' => 'NO. URUT 4',
                'name' => 'Bunga Citra Amelya',
                'photo' => '/assets/images/blm4.webp',
                'vision' => 'Menjadi pribadi yang terus bertumbuh, berani mengambil tanggung jawab, dan mampu memberi dampak positif bagi lingkungan sekitar.',
                'mission' => json_encode([
                    'Menggunakan setiap kesempatan untuk mendengarkan keluhan dan masukan mahasiswa.',
                    'Menegakkan nilai-nilai kejujuran, kerja keras, dan ketekunan dalam proses legislasi.',
                    'Mendorong kolaborasi lintas angkatan dan program studi.',
                ]),
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'blm',
                'candidate_id' => null,
                'role_label' => 'NO. URUT 5',
                'name' => 'Muhammad Ammar',
                'photo' => '/assets/images/blm5.webp',
                'vision' => 'Optimalisasi peran legislatif mahasiswa dengan standar mutu dan efektivitas kerja tertinggi.',
                'mission' => json_encode([
                    'Fokus pada output kerja yang berkualitas dan terukur bagi mahasiswa.',
                    'Mewujudkan pengawasan yang tegas namun tetap solutif dan membangun.',
                    'Mengakselerasi penyaluran advokasi fasilitas pembelajaran kampus.',
                ]),
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_candidates');
    }
};

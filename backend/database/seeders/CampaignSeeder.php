<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\CampaignTier;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    // Unsplash image collections by category for variety
    private array $imageUrls = [
        'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80',
        'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=600&q=80',
        'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=80',
        'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600&q=80',
        'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=600&q=80',
        'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=600&q=80',
        'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80',
        'https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&q=80',
        'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&q=80',
        'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&q=80',
        'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80',
        'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&q=80',
        'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&q=80',
        'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&q=80',
        'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&q=80',
    ];

    // Realistic campaign topics in Indonesian context
    private array $campaigns = [
        [
            'title' => 'Aplikasi Belajar Coding untuk Anak Desa',
            'category_id' => 1, // Teknologi
            'description' => 'Kami ingin mengembangkan aplikasi pembelajaran coding interaktif yang dapat diakses oleh anak-anak di daerah pedesaan. Dengan aplikasi ini, anak-anak dapat belajar programming dasar melalui game dan tantangan seru tanpa perlu koneksi internet cepat. Target kami adalah 1.000 pengguna aktif di 10 desa dalam 6 bulan pertama. Dana akan digunakan untuk pengembangan aplikasi, pembuatan konten pembelajaran, dan pelatihan guru pendamping.',
            'target_amount' => 85000000,
            'collected_amount' => 52300000,
            'deadline_offset' => '+45 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Bantuan Air Bersih untuk Desa Sukamaju',
            'category_id' => 2, // Kemanusiaan
            'description' => 'Desa Sukamaju di Nusa Tenggara Timur mengalami kekeringan setiap musim kemarau. Kami berencana membangun sistem pipanisasi air bersih dari sumber mata air terdekat sejauh 3 km. Proyek ini akan melayani 500 kepala keluarga. Dana akan digunakan untuk pembelian pipa, pembangunan bak penampung, dan biaya tenaga kerja lokal. Kami sudah mendapatkan izin dari pemerintah desa dan dukungan dari warga setempat.',
            'target_amount' => 120000000,
            'collected_amount' => 120000000,
            'deadline_offset' => '-10 days',
            'status' => CampaignStatus::SUCCESS,
        ],
        [
            'title' => 'Beasiswa Pendidikan untuk 50 Anak Kurang Mampu',
            'category_id' => 3, // Pendidikan
            'description' => 'Program beasiswa ini bertujuan membiayai pendidikan 50 anak dari keluarga kurang mampu di Jakarta dan sekitarnya. Setiap anak akan mendapatkan bantuan biaya sekolah, seragam, buku, dan uang saku selama satu tahun ajaran. Kami bekerja sama dengan 10 sekolah mitra untuk memastikan dana tepat sasaran. Donasi Anda akan mengubah masa depan anak-anak ini melalui pendidikan.',
            'target_amount' => 250000000,
            'collected_amount' => 187500000,
            'deadline_offset' => '+60 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Festival Musik Tradisional Nusantara',
            'category_id' => 4, // Kreatif
            'description' => 'Festival Musik Tradisional Nusantara adalah acara tahunan yang menampilkan puluhan seniman dan grup musik tradisional dari berbagai daerah di Indonesia. Tahun ini kami ingin mengadakan festival yang lebih besar dengan panggung utama, workshop alat musik tradisional, dan pasar kuliner Nusantara. Dana akan digunakan untuk sewa tempat, honor seniman, dan produksi acara.',
            'target_amount' => 175000000,
            'collected_amount' => 45000000,
            'deadline_offset' => '+90 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Pengembangan Aplikasi Pertanian Cerdas',
            'category_id' => 1, // Teknologi
            'description' => 'Aplikasi pertanian cerdas berbasis AI yang membantu petani mendeteksi hama, memantau cuaca, dan mengoptimalkan jadwal tanam. Dengan teknologi machine learning, aplikasi ini dapat memberikan rekomendasi pupuk dan pestisida yang tepat. Kami telah melakukan uji coba dengan 50 petani di Jawa Barat dan hasilnya meningkatkan hasil panen hingga 30%.',
            'target_amount' => 150000000,
            'collected_amount' => 89000000,
            'deadline_offset' => '+30 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Rumah Singgah untuk Pasien Kanker',
            'category_id' => 2, // Kemanusiaan
            'description' => 'Membangun rumah singgah bagi pasien kanker dari luar kota yang berobat di Jakarta. Rumah singgah ini akan menyediakan tempat tinggal sementara yang nyaman, dapur umum, dan layanan konseling gratis. Target kami adalah menampung 30 pasien dan keluarganya setiap bulan. Kami sudah memiliki lokasi strategis dekat Rumah Sakit Kanker Dharmais.',
            'target_amount' => 500000000,
            'collected_amount' => 500000000,
            'deadline_offset' => '-5 days',
            'status' => CampaignStatus::SUCCESS,
        ],
        [
            'title' => 'Perpustakaan Keliling untuk Papua',
            'category_id' => 3, // Pendidikan
            'description' => 'Menyediakan perpustakaan keliling berupa mobil yang dilengkapi buku-buku bacaan, tablet edukasi, dan akses internet untuk anak-anak di wilayah pedalaman Papua. Mobil akan berkeliling ke 20 desa setiap bulannya. Setiap kunjungan akan diisi dengan kegiatan membaca bersama, story telling, dan pengenalan teknologi dasar.',
            'target_amount' => 320000000,
            'collected_amount' => 0,
            'deadline_offset' => '+120 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Koleksi Fashion Batik Milenial',
            'category_id' => 4, // Kreatif
            'description' => 'Koleksi fashion batik kontemporer yang menggabungkan motif tradisional dengan desain modern untuk anak muda. Kami bekerja sama dengan pengrajin batik dari Solo, Yogyakarta, dan Pekalongan untuk menciptakan 50 desain eksklusif. Dana akan digunakan untuk produksi massal, pemasaran digital, dan partisipasi di pameran fashion internasional.',
            'target_amount' => 95000000,
            'collected_amount' => 95000000,
            'deadline_offset' => '-20 days',
            'status' => CampaignStatus::SUCCESS,
        ],
        [
            'title' => 'Startup EdTech: Platform Bimbel Online',
            'category_id' => 1, // Teknologi
            'description' => 'Platform bimbingan belajar online yang menghubungkan siswa dengan tutor berkualitas secara real-time. Fitur unggulan meliputi papan tulis interaktif, rekaman sesi belajar, dan analisis perkembangan siswa. Target pasar kami adalah siswa SMP dan SMA yang mempersiapkan ujian nasional dan masuk perguruan tinggi.',
            'target_amount' => 200000000,
            'collected_amount' => 34000000,
            'deadline_offset' => '+75 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Dapur Umum untuk Korban Bencana',
            'category_id' => 2, // Kemanusiaan
            'description' => 'Mendirikan dapur umum mobile yang siap diterjunkan ke lokasi bencana alam di seluruh Indonesia. Dilengkapi dengan peralatan masak industri, genset, dan persediaan bahan makanan untuk 1.000 orang per hari. Tim kami sudah berpengalaman merespon bencana di Lombok, Palu, dan Cianjur.',
            'target_amount' => 280000000,
            'collected_amount' => 156000000,
            'deadline_offset' => '+40 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Kursus Koding Gratis untuk Perempuan',
            'category_id' => 3, // Pendidikan
            'description' => 'Program kursus coding gratis selama 6 bulan untuk 100 perempuan muda dari latar belakang ekonomi kurang mampu. Peserta akan belajar HTML, CSS, JavaScript, dan React.js. Di akhir program, peserta akan mendapatkan sertifikat dan bantuan penempatan kerja di perusahaan mitra. Batch pertama telah meluluskan 45 peserta dengan 80% terserap di industri teknologi.',
            'target_amount' => 180000000,
            'collected_amount' => 180000000,
            'deadline_offset' => '-3 days',
            'status' => CampaignStatus::SUCCESS,
        ],
        [
            'title' => 'Film Dokumenter Laut Indonesia',
            'category_id' => 4, // Kreatif
            'description' => 'Film dokumenter yang mengangkat keindahan bawah laut Indonesia sekaligus kampanye pelestarian terumbu karang. Tim produksi akan menyelam di 15 lokasi terbaik di Indonesia, dari Raja Ampat hingga Wakatobi. Film ini akan ditayangkan di festival film internasional dan platform streaming. Sebagian hasil penjualan tiket akan disumbangkan untuk konservasi laut.',
            'target_amount' => 400000000,
            'collected_amount' => 72000000,
            'deadline_offset' => '+150 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Pengembangan Game Edukasi Sejarah',
            'category_id' => 1, // Teknologi
            'description' => 'Game petualangan berbasis sejarah Indonesia yang interaktif dan edukatif. Pemain akan menjelajahi berbagai era sejarah, dari kerajaan Majapahit hingga masa kemerdekaan, sambil menyelesaikan misi dan teka-teki. Game ini ditujukan untuk siswa SD dan SMP sebagai media pembelajaran alternatif yang menyenangkan.',
            'target_amount' => 130000000,
            'collected_amount' => 130000000,
            'deadline_offset' => '-15 days',
            'status' => CampaignStatus::SUCCESS,
        ],
        [
            'title' => 'Bank Sampah Digital untuk Komunitas',
            'category_id' => 2, // Kemanusiaan
            'description' => 'Platform digital yang menghubungkan masyarakat dengan bank sampah terdekat. Setor sampah anorganik, dapatkan poin yang bisa ditukar dengan sembako atau pulsa. Target kami adalah menggerakkan 5.000 rumah tangga untuk memilah sampah dan mengurangi volume sampah ke TPA sebesar 40%. Sudah beroperasi di 3 kelurahan di Bandung.',
            'target_amount' => 75000000,
            'collected_amount' => 28000000,
            'deadline_offset' => '+55 days',
            'status' => CampaignStatus::ACTIVE,
        ],
        [
            'title' => 'Pameran Seni Rupa Digital Indonesia',
            'category_id' => 4, // Kreatif
            'description' => 'Pameran seni rupa digital yang menampilkan karya 30 seniman digital Indonesia berbakat. Pameran akan diselenggarakan secara hybrid (offline di galeri dan online di metaverse). Pengunjung dapat membeli karya seni dalam bentuk NFT dan fisik. Acara ini juga akan menjadi ajang networking antara seniman, kolektor, dan galeri.',
            'target_amount' => 220000000,
            'collected_amount' => 0,
            'deadline_offset' => '+100 days',
            'status' => CampaignStatus::FAILED,
        ],
    ];

    // Tier templates for variety
    private array $tierTemplates = [
        [
            ['name' => 'Support Pemula', 'min_amount' => 25000, 'quota' => 0, 'reward_description' => 'Ucapan terima kasih di halaman website kami.'],
            ['name' => 'Donatur Aktif', 'min_amount' => 100000, 'quota' => 0, 'reward_description' => 'Stiker eksklusif + ucapan terima kasih di website.'],
            ['name' => 'Donatur Utama', 'min_amount' => 500000, 'quota' => 50, 'reward_description' => 'Merchandise eksklusif (kaos/tote bag) + stiker + ucapan terima kasih.'],
            ['name' => 'Donatur Premium', 'min_amount' => 1000000, 'quota' => 20, 'reward_description' => 'Semua reward sebelumnya + sesi konsultasi eksklusif 30 menit + laporan dampak bulanan.'],
        ],
        [
            ['name' => 'Dukung Kami', 'min_amount' => 50000, 'quota' => 0, 'reward_description' => 'Terima kasih di media sosial kami.'],
            ['name' => 'Sahabat Komunitas', 'min_amount' => 200000, 'quota' => 100, 'reward_description' => 'Merch official + ucapan di media sosial.'],
            ['name' => 'Mitra Program', 'min_amount' => 1000000, 'quota' => 30, 'reward_description' => 'Merch premium + laporan dampak triwulan + undangan acara tahunan.'],
            ['name' => 'Founding Partner', 'min_amount' => 5000000, 'quota' => 5, 'reward_description' => 'Semua reward sebelumnya + nama tercantum sebagai founding partner di materi promosi + private dinner bersama founder.'],
        ],
        [
            ['name' => 'Early Bird', 'min_amount' => 100000, 'quota' => 200, 'reward_description' => 'Akses early bird ke produk/layanan + ucapan terima kasih.'],
            ['name' => 'Backer Setia', 'min_amount' => 250000, 'quota' => 100, 'reward_description' => 'Produk edisi terbatas + stiker + akses ke grup komunitas eksklusif.'],
            ['name' => 'VIP Backer', 'min_amount' => 1000000, 'quota' => 20, 'reward_description' => 'Produk premium + nama di credits + akses ke sesi behind the scene.'],
        ],
    ];

    public function run(): void
    {
        $imageIndex = 0;

        foreach ($this->campaigns as $index => $data) {
            $creatorId = ($index % 3) + 2; // Distribute among 3 creators (ids: 2, 3, 4)

            $campaign = Campaign::create([
                'user_id' => $creatorId,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => str()->slug($data['title']) . '-' . uniqid(),
                'description' => $data['description'],
                'target_amount' => $data['target_amount'],
                'collected_amount' => $data['collected_amount'],
                'deadline' => now()->add($data['deadline_offset']),
                'status' => $data['status'],
            ]);

            // Create campaign image (use Unsplash)
            CampaignImage::create([
                'campaign_id' => $campaign->id,
                'title' => 'Campaign Image',
                'image_url' => $this->imageUrls[$imageIndex % count($this->imageUrls)],
                'is_primary' => true,
            ]);
            $imageIndex++;

            // Add a second image for some campaigns
            if ($index % 3 === 0) {
                CampaignImage::create([
                    'campaign_id' => $campaign->id,
                    'title' => 'Campaign Image 2',
                    'image_url' => $this->imageUrls[($imageIndex + 3) % count($this->imageUrls)],
                    'is_primary' => false,
                ]);
            }

            // Create tiers
            $tierSet = $this->tierTemplates[$index % count($this->tierTemplates)];
            foreach ($tierSet as $tierData) {
                CampaignTier::create([
                    'campaign_id' => $campaign->id,
                    'name' => $tierData['name'],
                    'min_amount' => $tierData['min_amount'],
                    'quota' => $tierData['quota'],
                    'remaining_quota' => $tierData['quota'] > 0 ? max(0, $tierData['quota'] - rand(3, 15)) : 0,
                    'reward_description' => $tierData['reward_description'],
                ]);
            }
        }
    }
}

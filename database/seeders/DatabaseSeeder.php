<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\Person;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Procurement;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\User;
use App\Models\WorkArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter content. Numbers, people and articles are placeholders meant to be
 * replaced from the admin portal before going live.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@plnservices.test'], [
            'name' => 'Administrator',
            'password' => 'PlnServices@2026',
            'role' => 'superadmin',
        ]);

        $this->settings();
        $this->milestones();
        $this->services();
        $this->statistics();
        $this->people();
        $this->posts();
        $this->portfolios();
        $this->procurements();
        $this->documents();
        $this->regions();
        $this->feedbacks();
    }

    private function settings(): void
    {
        Setting::put([
            'hero_eyebrow' => 'Bagian dari PLN Group',
            'hero_title' => 'PT PLN Services',
            'hero_subtitle' => 'Perusahaan penyedia jasa ketenagalistrikan terintegrasi yang andal, aman, dan efisien di seluruh Indonesia serta tumbuh berkelanjutan.',
            'overview_title' => 'Penyedia jasa ketenagalistrikan terintegrasi di bawah PLN Group',
            'overview_text' => 'Lingkup layanan kami meliputi pengoperasian dan pemeliharaan pembangkit, transmisi, dan distribusi, pelayanan teknik dan pelanggan, konstruksi kelistrikan, hingga solusi energi baru terbarukan dan digital.',
            'vision' => 'Menjadi perusahaan jasa ketenagalistrikan terintegrasi terdepan di Asia Tenggara yang menjadi pilihan utama pelanggan untuk solusi energi bersih.',
            'mission' => "Menyediakan layanan ketenagalistrikan terintegrasi yang andal, aman, dan efisien.\nMendukung transisi energi melalui solusi energi baru terbarukan dan digital.\nMembangun talenta unggul dengan budaya kerja AKHLAK.\nMemberikan nilai tambah berkelanjutan bagi pemegang saham, pelanggan, dan masyarakat.",
            'values' => "Amanah\nKompeten\nHarmonis\nLoyal\nAdaptif\nKolaboratif",
            'about_title' => 'Tentang PLN Services',
            'about_intro' => 'PT PLN Services adalah anak perusahaan PT PLN (Persero) yang menyediakan jasa ketenagalistrikan terintegrasi — dari pembangkit, transmisi, dan distribusi hingga pelayanan pelanggan — dengan jaringan kantor dan unit layanan di seluruh Indonesia.',
            'about_body' => 'Didukung tenaga kerja profesional bersertifikat dan sistem manajemen mutu, keselamatan, serta lingkungan, PLN Services berkomitmen menjaga keandalan pasokan listrik dan menghadirkan solusi energi yang efisien bagi PLN Group, industri, dan masyarakat.',
            'about_image' => null,
            'about_commitment' => 'Kami terus berinovasi melalui layanan digital, energi baru terbarukan, dan peningkatan kompetensi insan PLN Services untuk mendukung transisi energi Indonesia.',
            'company_name' => 'PT PLN Services',
            'address' => 'Jakarta, Indonesia',
            'phone' => '(021) 000 0000',
            'email' => 'info@plnservices.test',
            'whatsapp' => '',
            'call_center' => 'PLN 123',
            'instagram' => '#',
            'linkedin' => '#',
            'youtube' => '#',
            'facebook' => '#',
            'footer_note' => 'Perusahaan penyedia jasa ketenagalistrikan terintegrasi, bagian dari PLN Group.',
            'ga_measurement_id' => '',
            'ga_dashboard_url' => '',
        ]);
    }

    private function milestones(): void
    {
        $rows = [
            ['1945', 'Hari Listrik Nasional', 'Pada 27 Oktober 1945 lahir Jawatan Listrik dan Gas — titik awal perjalanan kelistrikan Indonesia dan PLN Group.'],
            ['2026', 'PLN Services resmi beroperasi', 'PLN Services hadir sebagai penyedia jasa ketenagalistrikan terintegrasi PLN Group dengan satu standar layanan di seluruh wilayah kerja.'],
            ['2027', 'Target: layanan digital terintegrasi', 'Penerapan sistem monitoring aset dan layanan pelanggan berbasis data di seluruh unit.'],
            ['2030', 'Target: mitra utama transisi energi', 'Menjadi mitra utama transisi energi Indonesia melalui layanan energi baru terbarukan dan operasi berstandar kelas dunia.'],
        ];

        foreach ($rows as $i => [$year, $title, $description]) {
            Milestone::create(compact('year', 'title', 'description') + ['entity' => 'pln_services', 'sort_order' => $i + 1]);
        }
    }

    private function services(): void
    {
        $rows = [
            ['Operasi & Pemeliharaan Pembangkit', 'factory', 'Pengoperasian dan pemeliharaan pembangkit listrik secara andal, aman, dan efisien dengan standar keselamatan tertinggi.'],
            ['Jasa Transmisi & Distribusi', 'utility-pole', 'Pemeliharaan jaringan tegangan tinggi hingga tegangan rendah, termasuk pekerjaan dalam keadaan bertegangan (PDKB).'],
            ['Pelayanan Teknik & Pelanggan', 'plug-zap', 'Layanan penyambungan, pelayanan gangguan, pembacaan meter, dan pelayanan pelanggan terpadu 24/7.'],
            ['EPC & Konstruksi Kelistrikan', 'hard-hat', 'Rekayasa, pengadaan, dan konstruksi gardu induk, jaringan, dan instalasi kelistrikan skala industri.'],
            ['Energi Baru Terbarukan', 'sun', 'Solusi PLTS atap, hybrid, dan energi hijau untuk industri, kawasan, dan daerah terpencil.'],
            ['Kelistrikan Pelabuhan', 'anchor', 'Penyediaan listrik darat (shore power) dan infrastruktur energi untuk kawasan pelabuhan.'],
            ['Digital & Smart Energy', 'cpu', 'Smart meter, sistem monitoring berbasis IoT, dan analitik data untuk manajemen energi yang lebih cerdas.'],
            ['Konsultansi & Sertifikasi', 'shield-check', 'Kajian teknis, audit energi, inspeksi, dan pendampingan sertifikasi instalasi kelistrikan.'],
        ];

        foreach ($rows as $i => [$title, $icon, $summary]) {
            Service::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'icon' => $icon,
                'summary' => $summary,
                'description' => "<p>{$summary}</p><p>Didukung tenaga ahli bersertifikat dan jaringan unit layanan di seluruh Indonesia, kami memastikan setiap pekerjaan memenuhi standar mutu, keselamatan, dan lingkungan PLN Group.</p>",
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function statistics(): void
    {
        $rows = [
            ['Unit Layanan', 120, null, '+', 'map-pin'],
            ['Tenaga Kerja Profesional', 15000, null, '+', 'users'],
            ['Pelanggan Terlayani', 25, null, ' Jt', 'smile'],
            ['Kapasitas Dikelola', 3500, null, ' MW', 'zap'],
        ];

        foreach ($rows as $i => [$label, $value, $prefix, $suffix, $icon]) {
            Statistic::create(compact('label', 'value', 'prefix', 'suffix', 'icon') + ['sort_order' => $i + 1]);
        }
    }

    private function people(): void
    {
        $direksi = ['Direktur Utama', 'Direktur Operasi', 'Direktur Keuangan & Manajemen Risiko', 'Direktur SDM & Umum'];
        foreach ($direksi as $i => $position) {
            Person::create(['type' => 'direksi', 'name' => 'Nama '.$position, 'position' => $position, 'bio' => 'Profil singkat akan diperbarui melalui portal admin.', 'sort_order' => $i + 1]);
        }

        $komisaris = ['Komisaris Utama', 'Komisaris', 'Komisaris Independen'];
        foreach ($komisaris as $i => $position) {
            Person::create(['type' => 'komisaris', 'name' => 'Nama '.$position, 'position' => $position, 'bio' => 'Profil singkat akan diperbarui melalui portal admin.', 'sort_order' => $i + 1]);
        }
    }

    private function posts(): void
    {
        $rows = [
            ['Selamat Datang di Website Resmi PLN Services', 'Siaran Pers', 'Website resmi PLN Services hadir sebagai pusat informasi layanan, tata kelola, pengadaan, dan kabar terbaru perusahaan.'],
            ['Peringati Hari Listrik Nasional, Insan PLN Services Gaungkan Satu Energi', 'Kegiatan', 'Momentum Hari Listrik Nasional menjadi pengingat semangat melistriki negeri bagi seluruh insan PLN Services.'],
            ['Siaga Kelistrikan Akhir Tahun, Ribuan Personel Disiagakan', 'Berita', 'PLN Services menyiagakan personel dan posko di seluruh wilayah kerja untuk menjaga keandalan pasokan listrik.'],
            ['Program Listrik Desa: Menerangi Pelosok Nusantara', 'CSR', 'Melalui program tanggung jawab sosial, PLN Services menghadirkan akses listrik dan pelatihan bagi masyarakat desa.'],
            ['Inovasi Smart Monitoring Gardu Distribusi Berbasis IoT', 'Inovasi', 'Sistem monitoring real-time membantu tim lapangan mendeteksi gangguan lebih cepat dan menekan durasi padam.'],
            ['PLN Services Raih Penghargaan Keselamatan Kerja', 'Prestasi', 'Komitmen budaya K3 di seluruh unit kembali mendapatkan apresiasi.'],
            ['Pelatihan PDKB untuk Talenta Muda Kelistrikan', 'Kegiatan', 'Program peningkatan kompetensi pekerjaan dalam keadaan bertegangan bagi personel baru.'],
            ['Shore Power Pelabuhan Dorong Transportasi Laut Rendah Emisi', 'Berita', 'Penyediaan listrik darat bagi kapal sandar mengurangi penggunaan mesin diesel di pelabuhan.'],
        ];

        foreach ($rows as $i => [$title, $category, $excerpt]) {
            Post::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'category' => $category,
                'excerpt' => $excerpt,
                'content' => "<p>{$excerpt}</p><h2>Membangun layanan yang terintegrasi</h2><p>Integrasi organisasi memungkinkan standar layanan yang seragam, proses yang lebih ringkas, dan pemanfaatan sumber daya yang lebih optimal di seluruh wilayah kerja.</p><blockquote>“Satu energi, satu semangat, untuk Indonesia yang lebih terang.”</blockquote><p>Ke depan, PLN Services akan terus memperkuat kolaborasi dengan seluruh pemangku kepentingan untuk menghadirkan layanan ketenagalistrikan yang andal dan berkelanjutan.</p><p><em>Konten contoh — silakan ganti melalui portal admin.</em></p>",
                'status' => 'published',
                'published_at' => now()->subDays($i * 4 + 1),
                'views' => random_int(80, 1500),
            ]);
        }
    }

    private function portfolios(): void
    {
        $rows = [
            ['O&M PLTU Sistem Kalimantan', 'Pembangkitan', 'Kalimantan', '2025', true],
            ['Pemeliharaan Jaringan 20 kV Jawa Timur', 'Distribusi', 'Jawa Timur', '2025', true],
            ['PLTS Hybrid Pulau Terluar', 'Energi Terbarukan', 'Maluku', '2024', true],
            ['Shore Power Pelabuhan Nasional', 'Pemeliharaan', 'Jawa Tengah', '2025', false],
            ['Smart Meter & AMI Kawasan Industri', 'Digital & Smart Grid', 'Batam', '2026', true],
            ['Konstruksi Gardu Induk 150 kV', 'Transmisi', 'Sulawesi Selatan', '2024', false],
        ];

        foreach ($rows as [$title, $category, $location, $year, $featured]) {
            Portfolio::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'client' => 'PT PLN (Persero)',
                'category' => $category,
                'location' => $location,
                'year' => $year,
                'summary' => "Proyek {$category} di {$location} yang dikerjakan dengan standar keselamatan dan mutu PLN Group.",
                'description' => '<p>Konten contoh — silakan ganti melalui portal admin.</p>',
                'is_featured' => $featured,
            ]);
        }
    }

    private function procurements(): void
    {
        $rows = [
            ['Pengadaan Material Jaringan Distribusi TM/TR', 'Tender Terbuka', 'Dibuka'],
            ['Jasa Pemeliharaan Kendaraan Operasional', 'Seleksi Umum', 'Dibuka'],
            ['Pengadaan APD dan Perlengkapan K3', 'Tender Terbuka', 'Evaluasi'],
            ['Jasa Konsultansi Sistem Informasi Terintegrasi', 'Seleksi Umum', 'Pengumuman Pemenang'],
            ['Pengadaan Smart Meter Fase 1', 'E-Purchasing', 'Selesai'],
            ['Sewa Gedung Kantor Region', 'Penunjukan Langsung', 'Selesai'],
        ];

        foreach ($rows as $i => [$title, $method, $status]) {
            Procurement::create([
                'number' => sprintf('PLNS/PGD/%03d/%s', $i + 1, now()->year),
                'title' => $title,
                'method' => $method,
                'status' => $status,
                'start_date' => now()->subDays(30 - $i * 3),
                'end_date' => now()->addDays(14 - $i * 6),
                'description' => 'Informasi lengkap tersedia pada dokumen pengadaan.',
            ]);
        }
    }

    private function documents(): void
    {
        $rows = [
            ['rups', 'Risalah RUPS Tahunan', '2026'],
            ['rups', 'Risalah RUPS Luar Biasa', '2026'],
            ['anggaran_dasar', 'Akta Anggaran Dasar Perusahaan', '2026'],
            ['esg', 'Kebijakan Lingkungan, Sosial, dan Tata Kelola', '2026'],
            ['esg', 'Kebijakan Keselamatan dan Kesehatan Kerja', '2026'],
        ];

        foreach ($rows as [$type, $title, $year]) {
            Document::create(compact('type', 'title', 'year') + ['description' => 'Dokumen akan diunggah melalui portal admin.']);
        }
    }

    private function regions(): void
    {
        $areas = [
            ['Sumatera', 'Aceh, Sumatera Utara, Sumatera Barat, Riau, Kepulauan Riau, Jambi, Sumatera Selatan, Bengkulu, Lampung, Bangka Belitung', 28],
            ['Jawa, Madura & Bali', 'Banten, DKI Jakarta, Jawa Barat, Jawa Tengah, DI Yogyakarta, Jawa Timur, Bali', 42],
            ['Kalimantan', 'Kalimantan Barat, Tengah, Selatan, Timur, dan Utara', 22],
            ['Sulawesi & Nusa Tenggara', 'Sulawesi, Nusa Tenggara Barat, Nusa Tenggara Timur', 18],
            ['Maluku & Papua', 'Maluku, Maluku Utara, Papua dan provinsi pemekarannya', 10],
        ];

        foreach ($areas as $i => [$name, $provinces, $units]) {
            WorkArea::create(['name' => $name, 'provinces' => $provinces, 'units' => $units, 'sort_order' => $i + 1, 'description' => "Unit layanan PLN Services di wilayah {$name}."]);
        }

        $offices = [
            ['Kantor Pusat PLN Services', 'Kantor Pusat', 'Jakarta Selatan, DKI Jakarta', -6.2297, 106.8261],
            ['Kantor Region Sumatera', 'Kantor Region', 'Kota Medan, Sumatera Utara', 3.5952, 98.6722],
            ['Kantor Region Kepulauan Riau', 'Kantor Region', 'Kota Batam, Kepulauan Riau', 1.0456, 104.0305],
            ['Kantor Region Jawa Timur & Bali', 'Kantor Region', 'Kota Surabaya, Jawa Timur', -7.2575, 112.7521],
            ['Kantor Region Kalimantan', 'Kantor Region', 'Kota Tarakan, Kalimantan Utara', 3.3000, 117.6333],
            ['Kantor Region Sulawesi', 'Kantor Region', 'Kota Makassar, Sulawesi Selatan', -5.1477, 119.4327],
            ['Kantor Region Nusa Tenggara', 'Kantor Region', 'Kota Kupang, Nusa Tenggara Timur', -10.1772, 123.6070],
            ['Kantor Region Papua', 'Kantor Region', 'Kota Jayapura, Papua', -2.5337, 140.7181],
        ];

        foreach ($offices as $i => [$name, $type, $address, $lat, $lng]) {
            Office::create(['name' => $name, 'type' => $type, 'address' => $address, 'latitude' => $lat, 'longitude' => $lng, 'sort_order' => $i + 1]);
        }
    }

    private function feedbacks(): void
    {
        $rows = [
            ['Rina', 'Pengadaan & Vendor', 'Informasi kemitraan vendor', 'Bagaimana cara mendaftar sebagai vendor PLN Services?'],
            ['Budi', 'Karier & Rekrutmen', 'Lowongan kerja', 'Apakah ada informasi rekrutmen tahun ini?'],
            ['Sari', 'Kemitraan & Bisnis', 'Pertanyaan layanan PLTS', 'Saya tertarik dengan layanan PLTS atap untuk pabrik kami.'],
        ];

        foreach ($rows as $i => [$name, $category, $subject, $message]) {
            Feedback::create(['name' => $name, 'category' => $category, 'email' => Str::lower($name).'@example.com', 'subject' => $subject, 'message' => $message, 'created_at' => now()->subDays($i)]);
        }
    }
}

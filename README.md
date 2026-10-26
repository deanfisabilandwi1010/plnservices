# PLN Services — Company Profile Portal

Website company profile **PLN Services** bergaya situs korporat PLN (putih, biru #3498db, Raleway + Open Sans) dengan animasi halus dan portal admin untuk mengelola seluruh konten.

**Stack:** Laravel 13 · Blade · Tailwind CSS 4 · GSAP 3 (ScrollTrigger + SplitText) · Lenis smooth scroll · Leaflet · Quill 2 · Lucide icons

## Menjalankan secara lokal

```bash
composer install
npm install
cp .env.example .env        # lalu sesuaikan APP_NAME, DB_*, dll.
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build               # atau `npm run dev` saat development
php artisan serve
```

- Website: <http://localhost:8000>
- Portal admin: <http://localhost:8000/admin>
  Akun awal (dari seeder, **ganti segera**): `admin@plnservices.test` / `PlnServices@2026`

Default database adalah SQLite (`database/database.sqlite`). Untuk MySQL (XAMPP), ubah `DB_CONNECTION=mysql` beserta `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`, lalu jalankan `php artisan migrate --seed`.

## Fitur website publik

| Fitur | Lokasi |
| --- | --- |
| Hero beranda: dua lingkaran (biru & kuning PLN) yang irisannya menampilkan foto proyek; foto, label, dan latar blur berganti otomatis (lensa menutup–membuka), progres per foto, jeda saat disorot, bergerak mengikuti kursor. Foto di `public/images/hero`, daftar slide di `site/home.blade.php` | `resources/js/site/hero.js`, `components/hero-venn` |
| Scrollytelling "Perjalanan Energi" (Tentang Kami): titik energi di canvas berpindah formasi mengikuti scroll — tersebar → terbelah konvensional \| terbarukan → mengalir di kabel transmisi → bola lampu menyala; tooltip jenis pembangkit, legenda filter, titik menghindari kursor | `resources/js/site/energy-story.js`, `site/partials/energy-story` |
| Kursor pengikut (desktop): membesar di atas tautan, berlabel di atas elemen `data-cursor-label` | `resources/js/site/cursor.js` |
| Loader kunjungan pertama: logo + garis progres tipis, lalu memudar (±1 detik) | `resources/js/site/transition.js` |
| Navigasi antarhalaman: dua panel biru datar menyapu area konten (header tetap) dari arah menu tujuan dengan nama halaman, lalu tersapu keluar di halaman baru; garis menu aktif meluncur, bar progres tipis di atas; batas waktu pengaman agar navigasi tak pernah tertahan | `resources/js/site/transition.js` |
| Carousel Layanan & Berita (panah, titik, autoplay) | `resources/js/site/carousel.js` |
| Scrollytelling "Jaringan dalam Angka": 1 kotak = 1 unit layanan, bergerak mengikuti scroll (campur → kelompok → peta pulau barat–timur → batang), tooltip & legenda filter (data: menu Wilayah Kerja) | `resources/js/site/unit-story.js`, `resources/views/site/partials/unit-story.blade.php` |
| Linimasa interaktif "Jejak Proyek & Kabar": proyek vs berita per tahun, milestone di sumbu, tooltip & filter kategori | `resources/js/site/timeline-viz.js`, `resources/views/site/partials/timeline.blade.php` |
| Reveal fade, judul per baris, garis judul section, counter | `resources/js/site/reveal.js` |
| Filter portofolio, penghitung karakter, progres baca | `resources/js/site/interactions.js` |
| Peta kantor region (Leaflet, basemap terang Esri) | `resources/js/site/map.js` |
| Header sticky & menu mobile | `resources/js/site/header.js` |
| Ilustrasi isometrik bergerak (melayang, asap, kincir angin, pulsa listrik, aliran sungai) | `resources/views/components/iso-scene.blade.php` |
| Halaman dalam bergaya editorial: judul dua kolom besar, ilustrasi garis "menghubungkan titik" yang tergambar mengikuti scroll, garis pemandu vertikal dengan titik yang menyala, daftar tautan interaktif, akordeon, penanda kalimat, pratinjau layanan saat hover, sub-navigasi sticky dengan scroll-spy | `resources/js/site/editorial.js`, `components/line-art`, `split-heading`, `section-nav`, `cta-band` |
| Menu mobile dua panel (menu masuk dari kiri dengan sedikit miring, kontak dari kanan) | `resources/js/site/header.js` |


Semua animasi menghormati pengaturan `prefers-reduced-motion`.

Halaman: Beranda, Tentang Kami (cerita & nilai), Manajemen, Tata Kelola (RUPS / Anggaran Dasar / ESG), Layanan (+detail), Portofolio (+detail), Wilayah Kerja, Berita (+detail, pencarian, kategori), Pengadaan, Kontak (form → Feedback).

Logo Danantara Indonesia di header diambil dari `public/images/logos/danantara.png` (sumber: plnnusadaya.co.id); header menyembunyikannya bila file itu dihapus.

## Portal admin

Seluruh layar CRUD dibangkitkan dari **`config/cms.php`** oleh `App\Http\Controllers\Admin\ResourceController`. Untuk menambah modul baru: buat model + migrasi, lalu daftarkan kolom tabel dan field form-nya di `config/cms.php` (tipe field: text, email, textarea, editor, image, file, date, datetime, number, select, toggle, password, icon).

Modul yang tersedia: Berita, Portfolio, Feedback, Google Analytics, Data Admin, Data Pengadaan, Data RUPS, Anggaran Dasar, Kebijakan ESG, Company Overview, Milestone, Profil Direksi, Profil Komisaris, About Us, Statistics, Services, Wilayah Kerja, Kantor Region, Footer Settings.

Pengaturan halaman tunggal (Company Overview, About Us, Footer, Google Analytics) disimpan di tabel `settings` dan dibaca lewat helper `setting('key')`.

## Konten awal

Seeder mengisi konten contoh agar situs langsung tampil. **Angka statistik, nama Direksi/Komisaris, alamat/kontak, berita, portofolio, dan data pengadaan adalah placeholder** — ganti melalui portal admin sebelum dipublikasikan. Gambar yang belum diunggah otomatis diganti artwork "energi" yang digenerasi.

## Deploy

Panduan deploy ke Vercel (database online, Cloudflare R2, Environment Variables): lihat [DEPLOY-VERCEL.md](DEPLOY-VERCEL.md).

## Pengujian

```bash
php artisan test
```

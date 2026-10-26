<?php

use App\Models\Document;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\Person;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Procurement;
use App\Models\Service;
use App\Models\Statistic;
use App\Models\User;
use App\Models\WorkArea;

/*
|--------------------------------------------------------------------------
| CMS modules
|--------------------------------------------------------------------------
|
| Every admin CRUD screen is generated from these definitions by
| App\Http\Controllers\Admin\ResourceController. To add a module, add a
| model + migration and describe its list columns and form fields here.
|
| Field types: text, email, textarea, editor, image, file, date, datetime,
| number, select, toggle, password, icon.
| Column types: text, image, badge, status, date, bool.
|
*/

$documentFields = [
    'title' => ['label' => 'Judul Dokumen', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
    'year' => ['label' => 'Tahun', 'type' => 'text', 'rules' => 'nullable|digits:4'],
    'file' => ['label' => 'File (PDF/DOC)', 'type' => 'file', 'rules' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480'],
    'description' => ['label' => 'Keterangan', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
];

$documentColumns = [
    'title' => ['label' => 'Judul', 'type' => 'text'],
    'year' => ['label' => 'Tahun', 'type' => 'badge'],
    'file' => ['label' => 'File', 'type' => 'file'],
];

$personFields = [
    'name' => ['label' => 'Nama Lengkap', 'type' => 'text', 'rules' => 'required|max:255'],
    'position' => ['label' => 'Jabatan', 'type' => 'text', 'rules' => 'required|max:255'],
    'photo' => ['label' => 'Foto', 'type' => 'image', 'rules' => 'nullable|image|max:4096'],
    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
    'bio' => ['label' => 'Profil Singkat', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
];

$personColumns = [
    'photo' => ['label' => 'Foto', 'type' => 'image'],
    'name' => ['label' => 'Nama', 'type' => 'text'],
    'position' => ['label' => 'Jabatan', 'type' => 'text'],
    'sort_order' => ['label' => 'Urutan', 'type' => 'text'],
];

return [

    /*
    | Disk for uploaded images/documents: "public" locally, "s3" (Cloudflare R2
    | or AWS S3) on hosts without a persistent filesystem such as Vercel.
    */
    'media_disk' => env('MEDIA_DISK', 'public'),

    'modules' => [

        'posts' => [
            'label' => 'Berita',
            'icon' => 'newspaper',
            'model' => Post::class,
            'order' => ['published_at', 'desc'],
            'search' => ['title', 'category'],
            'slug_from' => 'title',
            'columns' => [
                'image' => ['label' => 'Cover', 'type' => 'image'],
                'title' => ['label' => 'Judul', 'type' => 'text'],
                'category' => ['label' => 'Kategori', 'type' => 'badge'],
                'published_at' => ['label' => 'Tanggal', 'type' => 'date'],
                'status' => ['label' => 'Status', 'type' => 'status'],
            ],
            'fields' => [
                'title' => ['label' => 'Judul Berita', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
                'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Berita', 'Siaran Pers', 'CSR', 'Prestasi', 'Inovasi', 'Kegiatan'], 'rules' => 'required'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'published' => 'Published'], 'rules' => 'required|in:draft,published'],
                'published_at' => ['label' => 'Tanggal Publikasi', 'type' => 'datetime', 'rules' => 'nullable|date'],
                'image' => ['label' => 'Gambar Cover', 'type' => 'image', 'rules' => 'nullable|image|max:4096'],
                'excerpt' => ['label' => 'Ringkasan', 'type' => 'textarea', 'rules' => 'nullable|max:500', 'width' => 'full'],
                'content' => ['label' => 'Isi Berita', 'type' => 'editor', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'portfolios' => [
            'label' => 'Portfolio',
            'icon' => 'briefcase',
            'model' => Portfolio::class,
            'order' => ['year', 'desc'],
            'search' => ['title', 'client', 'location'],
            'slug_from' => 'title',
            'columns' => [
                'image' => ['label' => 'Gambar', 'type' => 'image'],
                'title' => ['label' => 'Proyek', 'type' => 'text'],
                'client' => ['label' => 'Klien', 'type' => 'text'],
                'category' => ['label' => 'Kategori', 'type' => 'badge'],
                'year' => ['label' => 'Tahun', 'type' => 'text'],
                'is_featured' => ['label' => 'Unggulan', 'type' => 'bool'],
            ],
            'fields' => [
                'title' => ['label' => 'Nama Proyek', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
                'client' => ['label' => 'Klien', 'type' => 'text', 'rules' => 'nullable|max:255'],
                'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Pembangkitan', 'Transmisi', 'Distribusi', 'Energi Terbarukan', 'Digital & Smart Grid', 'Pemeliharaan'], 'rules' => 'nullable'],
                'location' => ['label' => 'Lokasi', 'type' => 'text', 'rules' => 'nullable|max:255'],
                'year' => ['label' => 'Tahun', 'type' => 'text', 'rules' => 'nullable|digits:4'],
                'image' => ['label' => 'Gambar', 'type' => 'image', 'rules' => 'nullable|image|max:4096'],
                'is_featured' => ['label' => 'Tampilkan sebagai unggulan', 'type' => 'toggle'],
                'summary' => ['label' => 'Ringkasan', 'type' => 'textarea', 'rules' => 'nullable|max:500', 'width' => 'full'],
                'description' => ['label' => 'Deskripsi', 'type' => 'editor', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'feedbacks' => [
            'label' => 'Feedback',
            'icon' => 'message-square',
            'model' => Feedback::class,
            'readonly' => true,
            'order' => ['created_at', 'desc'],
            'search' => ['name', 'email', 'subject', 'company', 'category'],
            'columns' => [
                'name' => ['label' => 'Nama', 'type' => 'text'],
                'category' => ['label' => 'Kategori', 'type' => 'badge'],
                'subject' => ['label' => 'Subject', 'type' => 'text'],
                'created_at' => ['label' => 'Tanggal', 'type' => 'date'],
                'is_read' => ['label' => 'Dibaca', 'type' => 'bool'],
            ],
            'fields' => [
                'name' => ['label' => 'Nama', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'email'],
                'phone' => ['label' => 'Telepon', 'type' => 'text'],
                'company' => ['label' => 'Instansi / Perusahaan', 'type' => 'text'],
                'category' => ['label' => 'Kategori', 'type' => 'text'],
                'subject' => ['label' => 'Subject', 'type' => 'text'],
                'message' => ['label' => 'Pesan', 'type' => 'textarea', 'width' => 'full'],
            ],
        ],

        'users' => [
            'label' => 'Data Admin',
            'icon' => 'users',
            'model' => User::class,
            'order' => ['name', 'asc'],
            'search' => ['name', 'email'],
            'columns' => [
                'name' => ['label' => 'Nama', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'text'],
                'role' => ['label' => 'Role', 'type' => 'badge'],
                'created_at' => ['label' => 'Terdaftar', 'type' => 'date'],
            ],
            'fields' => [
                'name' => ['label' => 'Nama / Username', 'type' => 'text', 'rules' => 'required|max:255'],
                'email' => ['label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:255|unique:users,email,{id}'],
                'role' => ['label' => 'Role', 'type' => 'select', 'options' => ['superadmin' => 'Super Admin', 'admin' => 'Admin', 'editor' => 'Editor'], 'rules' => 'required'],
                'password' => ['label' => 'Password', 'type' => 'password', 'rules' => 'nullable|min:8', 'help' => 'Kosongkan jika tidak ingin mengubah password.'],
            ],
        ],

        'procurements' => [
            'label' => 'Data Pengadaan',
            'icon' => 'gavel',
            'model' => Procurement::class,
            'order' => ['start_date', 'desc'],
            'search' => ['title', 'number', 'method'],
            'columns' => [
                'number' => ['label' => 'Nomor', 'type' => 'text'],
                'title' => ['label' => 'Paket Pengadaan', 'type' => 'text'],
                'method' => ['label' => 'Metode', 'type' => 'badge'],
                'end_date' => ['label' => 'Batas', 'type' => 'date'],
                'status' => ['label' => 'Status', 'type' => 'status'],
            ],
            'fields' => [
                'title' => ['label' => 'Nama Paket', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
                'number' => ['label' => 'Nomor Pengadaan', 'type' => 'text', 'rules' => 'nullable|max:100'],
                'method' => ['label' => 'Metode', 'type' => 'select', 'options' => ['Tender Terbuka', 'Seleksi Umum', 'Penunjukan Langsung', 'Pengadaan Langsung', 'E-Purchasing'], 'rules' => 'nullable'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Dibuka', 'Evaluasi', 'Pengumuman Pemenang', 'Selesai', 'Dibatalkan'], 'rules' => 'required'],
                'start_date' => ['label' => 'Tanggal Mulai', 'type' => 'date', 'rules' => 'nullable|date'],
                'end_date' => ['label' => 'Tanggal Berakhir', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:start_date'],
                'document' => ['label' => 'Dokumen Pengadaan', 'type' => 'file', 'rules' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480'],
                'description' => ['label' => 'Keterangan', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'rups' => [
            'label' => 'Data RUPS',
            'icon' => 'landmark',
            'model' => Document::class,
            'scope' => ['type' => 'rups'],
            'order' => ['year', 'desc'],
            'search' => ['title', 'year'],
            'columns' => $documentColumns,
            'fields' => $documentFields,
        ],

        'anggaran-dasar' => [
            'label' => 'Anggaran Dasar',
            'icon' => 'scale',
            'model' => Document::class,
            'scope' => ['type' => 'anggaran_dasar'],
            'order' => ['year', 'desc'],
            'search' => ['title', 'year'],
            'columns' => $documentColumns,
            'fields' => $documentFields,
        ],

        'esg' => [
            'label' => 'Kebijakan ESG',
            'icon' => 'leaf',
            'model' => Document::class,
            'scope' => ['type' => 'esg'],
            'order' => ['year', 'desc'],
            'search' => ['title', 'year'],
            'columns' => $documentColumns,
            'fields' => $documentFields,
        ],

        'direksi' => [
            'label' => 'Profil Direksi',
            'icon' => 'user-round-check',
            'model' => Person::class,
            'scope' => ['type' => 'direksi'],
            'order' => ['sort_order', 'asc'],
            'search' => ['name', 'position'],
            'columns' => $personColumns,
            'fields' => $personFields,
        ],

        'komisaris' => [
            'label' => 'Profil Komisaris',
            'icon' => 'id-card',
            'model' => Person::class,
            'scope' => ['type' => 'komisaris'],
            'order' => ['sort_order', 'asc'],
            'search' => ['name', 'position'],
            'columns' => $personColumns,
            'fields' => $personFields,
        ],

        'milestones' => [
            'label' => 'Milestone',
            'icon' => 'history',
            'model' => Milestone::class,
            'order' => ['sort_order', 'asc'],
            'search' => ['title', 'year'],
            'columns' => [
                'year' => ['label' => 'Tahun', 'type' => 'badge'],
                'title' => ['label' => 'Judul', 'type' => 'text'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'text'],
            ],
            'fields' => [
                'year' => ['label' => 'Tahun / Periode', 'type' => 'text', 'rules' => 'required|max:10'],
                'title' => ['label' => 'Judul', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'image' => ['label' => 'Gambar (opsional)', 'type' => 'image', 'rules' => 'nullable|image|max:4096'],
                'description' => ['label' => 'Cerita', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'statistics' => [
            'label' => 'Statistics',
            'icon' => 'chart-column',
            'model' => Statistic::class,
            'order' => ['sort_order', 'asc'],
            'search' => ['label'],
            'columns' => [
                'icon' => ['label' => 'Ikon', 'type' => 'icon'],
                'label' => ['label' => 'Label', 'type' => 'text'],
                'value' => ['label' => 'Nilai', 'type' => 'text'],
                'suffix' => ['label' => 'Satuan', 'type' => 'text'],
            ],
            'fields' => [
                'label' => ['label' => 'Label', 'type' => 'text', 'rules' => 'required|max:255', 'width' => 'full'],
                'prefix' => ['label' => 'Prefix (mis. Rp)', 'type' => 'text', 'rules' => 'nullable|max:20'],
                'value' => ['label' => 'Nilai', 'type' => 'number', 'rules' => 'required|numeric', 'step' => 'any'],
                'suffix' => ['label' => 'Satuan (mis. MW, +)', 'type' => 'text', 'rules' => 'nullable|max:20'],
                'icon' => ['label' => 'Ikon', 'type' => 'icon', 'rules' => 'nullable|max:50'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
            ],
        ],

        'services' => [
            'label' => 'Services',
            'icon' => 'settings',
            'model' => Service::class,
            'order' => ['sort_order', 'asc'],
            'search' => ['title'],
            'slug_from' => 'title',
            'columns' => [
                'icon' => ['label' => 'Ikon', 'type' => 'icon'],
                'title' => ['label' => 'Layanan', 'type' => 'text'],
                'summary' => ['label' => 'Ringkasan', 'type' => 'text'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'text'],
            ],
            'fields' => [
                'title' => ['label' => 'Nama Layanan', 'type' => 'text', 'rules' => 'required|max:255'],
                'icon' => ['label' => 'Ikon', 'type' => 'icon', 'rules' => 'nullable|max:50'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'image' => ['label' => 'Gambar', 'type' => 'image', 'rules' => 'nullable|image|max:4096'],
                'summary' => ['label' => 'Ringkasan', 'type' => 'textarea', 'rules' => 'nullable|max:500', 'width' => 'full'],
                'description' => ['label' => 'Deskripsi Lengkap', 'type' => 'editor', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'work-areas' => [
            'label' => 'Wilayah Kerja',
            'icon' => 'map',
            'model' => WorkArea::class,
            'order' => ['sort_order', 'asc'],
            'search' => ['name', 'provinces'],
            'columns' => [
                'name' => ['label' => 'Wilayah', 'type' => 'text'],
                'provinces' => ['label' => 'Cakupan Provinsi', 'type' => 'text'],
                'units' => ['label' => 'Unit', 'type' => 'badge'],
            ],
            'fields' => [
                'name' => ['label' => 'Nama Wilayah', 'type' => 'text', 'rules' => 'required|max:255'],
                'units' => ['label' => 'Jumlah Unit Layanan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'provinces' => ['label' => 'Cakupan Provinsi', 'type' => 'text', 'rules' => 'nullable|max:255', 'width' => 'full'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],

        'offices' => [
            'label' => 'Kantor Region',
            'icon' => 'building-2',
            'model' => Office::class,
            'order' => ['sort_order', 'asc'],
            'search' => ['name', 'address'],
            'columns' => [
                'name' => ['label' => 'Kantor', 'type' => 'text'],
                'type' => ['label' => 'Tipe', 'type' => 'badge'],
                'phone' => ['label' => 'Telepon', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'text'],
            ],
            'fields' => [
                'name' => ['label' => 'Nama Kantor', 'type' => 'text', 'rules' => 'required|max:255'],
                'type' => ['label' => 'Tipe', 'type' => 'select', 'options' => ['Kantor Pusat', 'Kantor Region', 'Kantor Area'], 'rules' => 'required'],
                'phone' => ['label' => 'Telepon', 'type' => 'text', 'rules' => 'nullable|max:50'],
                'email' => ['label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'rules' => 'nullable|numeric|between:-90,90', 'step' => 'any'],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'rules' => 'nullable|numeric|between:-180,180', 'step' => 'any'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'address' => ['label' => 'Alamat', 'type' => 'textarea', 'rules' => 'nullable', 'width' => 'full'],
            ],
        ],
    ],

    /*
    | Single-page settings screens, stored as key/value rows in `settings`.
    */
    'settings' => [

        'company-overview' => [
            'label' => 'Company Overview',
            'icon' => 'building',
            'fields' => [
                'hero_eyebrow' => ['label' => 'Hero — Label Kecil', 'type' => 'text'],
                'hero_title' => ['label' => 'Hero — Judul Utama', 'type' => 'text', 'width' => 'full'],
                'hero_subtitle' => ['label' => 'Hero — Sub Judul', 'type' => 'textarea', 'width' => 'full'],
                'overview_title' => ['label' => 'Judul Overview', 'type' => 'text', 'width' => 'full'],
                'overview_text' => ['label' => 'Teks Overview', 'type' => 'textarea', 'width' => 'full'],
                'vision' => ['label' => 'Visi', 'type' => 'textarea', 'width' => 'full'],
                'mission' => ['label' => 'Misi (satu baris per poin)', 'type' => 'textarea', 'width' => 'full'],
                'values' => ['label' => 'Nilai Perusahaan (satu baris per poin)', 'type' => 'textarea', 'width' => 'full'],
            ],
        ],

        'about-us' => [
            'label' => 'About Us',
            'icon' => 'info',
            'fields' => [
                'about_title' => ['label' => 'Judul Halaman', 'type' => 'text', 'width' => 'full'],
                'about_intro' => ['label' => 'Paragraf Pembuka', 'type' => 'textarea', 'width' => 'full'],
                'about_image' => ['label' => 'Gambar Utama', 'type' => 'image'],
                'about_body' => ['label' => 'Paragraf Kedua', 'type' => 'textarea', 'width' => 'full'],
                'about_commitment' => ['label' => 'Komitmen Perusahaan', 'type' => 'textarea', 'width' => 'full'],
                'about_segments_intro' => ['label' => 'Pengantar Segmen Pelanggan', 'type' => 'textarea', 'width' => 'full'],
                'about_segments' => ['label' => 'Segmen Pelanggan', 'type' => 'textarea', 'width' => 'full', 'help' => 'Satu segmen per baris.'],
                'about_highlights' => ['label' => 'Sorotan', 'type' => 'textarea', 'width' => 'full', 'help' => 'Satu kalimat per baris. Apit kata dengan *bintang* untuk diberi penanda.'],
            ],
        ],

        'footer' => [
            'label' => 'Footer Settings',
            'icon' => 'panel-bottom',
            'fields' => [
                'company_name' => ['label' => 'Nama Perusahaan', 'type' => 'text', 'width' => 'full'],
                'address' => ['label' => 'Alamat Kantor Pusat', 'type' => 'textarea', 'width' => 'full'],
                'phone' => ['label' => 'Telepon', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'email'],
                'whatsapp' => ['label' => 'WhatsApp', 'type' => 'text'],
                'call_center' => ['label' => 'Call Center', 'type' => 'text'],
                'instagram' => ['label' => 'URL Instagram', 'type' => 'text'],
                'linkedin' => ['label' => 'URL LinkedIn', 'type' => 'text'],
                'youtube' => ['label' => 'URL YouTube', 'type' => 'text'],
                'facebook' => ['label' => 'URL Facebook', 'type' => 'text'],
                'footer_note' => ['label' => 'Teks Footer', 'type' => 'textarea', 'width' => 'full'],
            ],
        ],

        'analytics' => [
            'label' => 'Google Analytics',
            'icon' => 'chart-line',
            'fields' => [
                'ga_measurement_id' => ['label' => 'Measurement ID (G-XXXXXXX)', 'type' => 'text', 'help' => 'Jika diisi, tag Google Analytics 4 otomatis dipasang di semua halaman publik.'],
                'ga_dashboard_url' => ['label' => 'URL Dashboard / Looker Studio', 'type' => 'text', 'width' => 'full', 'help' => 'Opsional: tautan laporan yang akan ditampilkan di halaman ini.'],
            ],
        ],
    ],

    /*
    | Sidebar layout (keys refer to modules / settings above).
    */
    'menu' => [
        ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'admin.dashboard'],
        ['type' => 'module', 'key' => 'posts'],
        ['type' => 'module', 'key' => 'portfolios'],
        ['type' => 'module', 'key' => 'feedbacks'],
        ['type' => 'setting', 'key' => 'analytics'],
        ['type' => 'link', 'label' => 'Lihat Web', 'icon' => 'globe', 'route' => 'home', 'external' => true],
        ['type' => 'heading', 'label' => 'Master Data'],
        ['type' => 'module', 'key' => 'users'],
        ['type' => 'module', 'key' => 'procurements'],
        ['type' => 'module', 'key' => 'rups'],
        ['type' => 'module', 'key' => 'anggaran-dasar'],
        ['type' => 'module', 'key' => 'esg'],
        ['type' => 'heading', 'label' => 'Setup Konten'],
        ['type' => 'setting', 'key' => 'company-overview'],
        ['type' => 'module', 'key' => 'milestones'],
        ['type' => 'module', 'key' => 'direksi'],
        ['type' => 'module', 'key' => 'komisaris'],
        ['type' => 'setting', 'key' => 'about-us'],
        ['type' => 'module', 'key' => 'statistics'],
        ['type' => 'module', 'key' => 'services'],
        ['type' => 'module', 'key' => 'work-areas'],
        ['type' => 'module', 'key' => 'offices'],
        ['type' => 'setting', 'key' => 'footer'],
    ],
];

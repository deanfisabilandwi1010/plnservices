# Deploy ke cPanel (Rumahweb) — plnservices.co.id

Situs produksi berjalan di hosting cPanel Rumahweb (server `kampar.iixcp.rumahweb.net`, akun `plnr4935`).

## Susunan di server

| Lokasi | Isi |
| --- | --- |
| `~/plnservices` | Aplikasi Laravel (kode, `vendor`, `storage`, `.env`) |
| `~/public_html` → `~/plnservices/public` | *Symlink*: document root domain menunjuk ke folder `public` Laravel |
| `~/public_html_rumahweb_backup` | Isi `public_html` bawaan Rumahweb (halaman "under construction"), disimpan untuk jaga-jaga |
| `~/bin/composer` | Composer |
| `~/plnservices/storage/app/public` | Salinan foto dari masa transisi (upload kini ke MinIO) |

- **PHP 8.4** (dipilih lewat *Select PHP Version* / CloudLinux), web server **LiteSpeed**.
- **Database:** MariaDB `plnr4935_web` (user `plnr4935_web`, host `localhost`); password hanya ada di `~/plnservices/.env`.
- **SSL:** AutoSSL cPanel; `public/.htaccess` mengalihkan semua HTTP ke HTTPS (kecuali `/.well-known/` untuk perpanjangan sertifikat).
- **Batas upload:** 25 MB, diatur lewat `php_value` di `public/.htaccess` (blok `IfModule Litespeed`).
- **SSH:** port `2223`. Aktifkan di cPanel → *SSH Access* → *Manage SSH Keys* (impor/authorize kunci Anda).

## Penyimpanan file: MinIO perusahaan

Upload admin disimpan di bucket **`plnservices`** pada MinIO perusahaan, lewat endpoint HTTPS
**`https://minio-storage.nusadaya.net`** (di belakang Cloudflare). Bucket tetap privat; situs menyajikan file lewat
`https://plnservices.co.id/media/<path>` (`MEDIA_DISK=s3`, `MEDIA_PROXY=true` di `.env`).

- Endpoint lama `http://182.253.97.52:9000` **tidak bisa dipakai** dari server ini: Rumahweb memblokir koneksi keluar ke port 9000.
- `storage/app/public/` masih menyimpan salinan foto dari masa transisi; tidak dipakai situs, boleh dihapus.
- Cadangan `.env` sebelum peralihan: `~/plnservices/.env.backup-local-storage`. Untuk kembali ke penyimpanan lokal,
  set `MEDIA_DISK=public` dan `MEDIA_PROXY=false`, lalu `php artisan config:cache`.

## Memperbarui situs

Dari komputer pengembang (folder proyek):

```bash
npm run build          # aset CSS/JS ke public/build
tar --force-local -czf plns.tar.gz \
    --exclude=./.git --exclude=./node_modules --exclude=./vendor --exclude='./.env' --exclude='./.env.*' \
    --exclude=./tests --exclude=./public/Assets --exclude=./public/storage --exclude=./public/hot \
    --exclude='./database/*.sqlite' --exclude='./storage/*' --exclude='./bootstrap/cache/*.php' .
scp -P 2223 plns.tar.gz plnr4935@kampar.iixcp.rumahweb.net:~/
```

Lalu di server (`ssh -p 2223 plnr4935@kampar.iixcp.rumahweb.net`):

```bash
cd ~/plnservices
rm -rf public/build                      # aset lama (nama file ber-hash) diganti seluruhnya
tar -xzf ~/plns.tar.gz && rm ~/plns.tar.gz
rm -f bootstrap/cache/*.php              # jaga-jaga: cache paket lokal memuat paket dev
~/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
php artisan package:discover
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Catatan:
- `--no-scripts` + `package:discover` manual: server mematikan `proc_open`, jadi skrip Composer gagal kalau dijalankan otomatis.
- Paket **jangan** menyertakan `storage/` — isinya (upload, log, sesi) milik server — maupun `bootstrap/cache/*.php`
  (cache lokal mendaftarkan paket dev seperti Pail, sehingga `package:discover` gagal di server).
- `.env` di server tidak pernah ditimpa oleh paket.

## Mode Coming Soon

Selama aset perusahaan belum lengkap, situs berjalan dengan `SOFT_LAUNCH=true` di `~/plnservices/.env`:

- `/` menampilkan halaman Coming Soon; tombol **Tentang Kami** membuka beranda di `/beranda`.
- Hanya **Home** dan **Profil** (Tentang Kami dan Manajemen) yang terbuka. Menu lain tampil "Coming Soon",
  dan kunjungan langsung ke halamannya dialihkan ke `/`. Daftar halaman yang ditutup: `closed_paths` di `config/cms.php`.
- Formulir pesan di beranda tetap berfungsi dan masuk ke admin.

Untuk membuka situs penuh, ubah ke `SOFT_LAUNCH=false`, lalu jalankan
`php artisan config:cache && php artisan route:cache` (keduanya wajib, karena route `/` ikut berubah).

## Data

Data awal disalin dari database Neon (Vercel) pada saat migrasi. Sejak itu **database cPanel adalah data utama** —
kelola konten lewat `https://plnservices.co.id/admin`, bukan lewat admin di Vercel.

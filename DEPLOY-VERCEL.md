# Deploy ke Vercel

Vercel menjalankan Laravel sebagai *serverless function* lewat runtime komunitas
[`vercel-php`](https://github.com/vercel-community/php). Konsekuensinya, Vercel **tidak menyimpan
file secara permanen**, sehingga:

- database SQLite lokal (`database/database.sqlite`) **tidak bisa dipakai** → butuh database online;
- file upload admin (foto berita, foto direksi, dokumen RUPS, dsb.) **disimpan di MinIO perusahaan** (kompatibel S3).

Yang sudah disiapkan di repo:

| File | Fungsi |
| --- | --- |
| `vercel.json` | Runtime PHP 8.4 (`vercel-php@0.8.0`), file statis dari `public/`, semua request lain ke Laravel; path cache Laravel diarahkan ke `/tmp` |
| `api/index.php` | Pintu masuk Vercel → `public/index.php` |
| `.vercelignore` | `vendor`, `node_modules`, `tests`, `.env` tidak diunggah |
| `public/build/` | Hasil `npm run build` **ikut di-commit**, karena disajikan langsung sebagai file statis |
| `MEDIA_DISK` / `MEDIA_PROXY` | Upload ke disk `s3` (MinIO) dan disajikan lewat `/media/...` (lihat bagian 2) |

---

## 1. Siapkan database online

Pilih salah satu (keduanya didukung runtime):

- **PostgreSQL – Neon** (paling mudah): di dashboard Vercel buka **Storage → Create Database → Neon**.
  Salin *connection string*-nya ke `DB_URL`, atau isi `DB_HOST`/`DB_DATABASE`/… satu per satu.
- **MySQL** – mis. TiDB Cloud Serverless atau Aiven for MySQL (ada paket gratis).

## 2. Penyimpanan file: MinIO perusahaan

File upload admin disimpan di bucket **`plnservices`** pada MinIO perusahaan (console: <https://minio.nusadaya.net/>).

- **API S3** ada di `http://182.253.97.52:9000` (IP publik). `minio.nusadaya.net` hanya console web, bukan API.
  Alamat internal `http://172.16.1.12:9000` tidak bisa dijangkau dari Vercel.
- Bucket **privat** dan API-nya **hanya HTTP**, sehingga gambar tidak bisa ditautkan langsung dari situs HTTPS.
  Karena itu `MEDIA_PROXY=true`: gambar disajikan oleh Laravel di `https://<domain>/media/<path>` (bucket tetap privat),
  dengan `Cache-Control` satu tahun sehingga CDN Vercel yang melayani permintaan berikutnya.
- Jika IT menyediakan endpoint API ber-HTTPS, cukup ganti `AWS_ENDPOINT`.

**Batas ukuran di Vercel:** request dan response fungsi Vercel maksimal ±4,5 MB. Gambar (maks. 4 MB) aman;
dokumen PDF/DOC di atas ±4,5 MB tidak bisa diunggah maupun diunduh lewat situs di Vercel — kecilkan filenya,
atau unggah langsung lewat console MinIO.

## 3. Migrasi tabel & isi konten awal (sekali saja, dari komputer Anda)

Buat file `.env.production` di folder proyek (file ini sudah di-*ignore* git, jadi tidak ikut ter-upload):

```dotenv
APP_ENV=production
APP_KEY=<sama dengan APP_KEY di .env lokal>
DB_CONNECTION=pgsql        # atau mysql
DB_HOST=...
DB_PORT=5432               # MySQL: 3306 / sesuai provider
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
DB_SSLMODE=require         # untuk PostgreSQL/Neon
```

Lalu jalankan:

```bash
php artisan migrate --force --env=production
php artisan db:seed --force --env=production
```

Di Windows/XAMPP, ekstensi PostgreSQL untuk PDO belum aktif secara default. Tanpa mengubah `php.ini`,
aktifkan khusus untuk perintah tersebut: `php -d extension=pdo_pgsql artisan migrate --force --env=production`
(begitu pula untuk `db:seed`). Untuk migrasi pakai host **unpooled** Neon; untuk Vercel pakai host **pooled**.

`db:seed` membuat akun admin (`admin@plnservices.test` / `PlnServices@2026`) dan konten contoh.
**Konten yang Anda isi di komputer lokal tidak ikut pindah** — isi ulang lewat `/admin` di website
online, lalu segera ganti password admin.

## 4. Import di Vercel

1. **Application Preset:** pilih **Other**.
2. **Root Directory:** `./`
3. **Build and Output Settings:** biarkan default (diabaikan, karena `vercel.json` memakai `builds`).
4. **Environment Variables:** isi daftar di bawah, lalu klik **Deploy**.

### Environment Variables yang harus diisi

| Nama | Contoh / keterangan |
| --- | --- |
| `APP_NAME` | `PLN Services` |
| `APP_KEY` | Salin dari `.env` lokal (diawali `base64:`). **Rahasia.** |
| `APP_URL` | `https://plnservices.vercel.app` (atau domain Anda) |
| `APP_LOCALE` | `id` |
| `DB_CONNECTION` | `pgsql` (Neon) atau `mysql` |
| `DB_URL` | *Atau* connection string lengkap dari Neon — jika diisi, `DB_HOST` dst. boleh dikosongkan |
| `DB_HOST` | Host database |
| `DB_PORT` | `5432` (PostgreSQL) / `3306` atau port dari provider (MySQL) |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | User database |
| `DB_PASSWORD` | Password database. **Rahasia.** |
| `DB_SSLMODE` | `require` — khusus PostgreSQL/Neon |
| `MYSQL_ATTR_SSL_CA` | Khusus MySQL yang mewajibkan SSL: path CA sesuai petunjuk provider (untuk Vercel umumnya `/etc/pki/tls/certs/ca-bundle.crt`) |
| `AWS_ACCESS_KEY_ID` | Access key MinIO. **Rahasia.** |
| `AWS_SECRET_ACCESS_KEY` | Secret key MinIO. **Rahasia.** |
| `AWS_DEFAULT_REGION` | `us-east-1` |
| `AWS_BUCKET` | `plnservices` |
| `AWS_ENDPOINT` | `http://182.253.97.52:9000` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` |

Sudah diatur otomatis oleh `vercel.json` (tidak perlu diisi): `APP_ENV=production`, `APP_DEBUG=false`,
`SESSION_DRIVER=cookie`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `LOG_CHANNEL=stderr`,
`MEDIA_DISK=s3`, `MEDIA_PROXY=true`, dan path cache di `/tmp`.

**Region:** `vercel.json` menjalankan fungsi PHP di `sin1` (Singapura), sama dengan region database Neon
(`ap-southeast-1`). Jika database Anda di region lain, ubah `regions` agar keduanya berdekatan — setiap query
yang menyeberang benua menambah ±200 ms, dan satu halaman menjalankan beberapa query.

## 5. Setelah deploy

- Buka `https://<domain>/admin`, login, ganti password, lalu isi konten & unggah gambar.
- Error saat runtime bisa dilihat di Vercel → project → **Logs**.

## Setiap kali mengubah tampilan (CSS/JS)

`public/build` disajikan apa adanya, jadi build ulang dan commit sebelum push:

```bash
npm run build
git add public/build
git commit -m "Build assets"
git push
```

## Catatan

- Runtime PHP di Vercel bukan dukungan resmi Vercel; halaman pertama setelah lama sepi bisa terasa lambat (*cold start*).
- Untuk situs dengan admin & upload file, hosting PHP biasa (VPS, cPanel, Laravel Cloud) umumnya lebih sederhana:
  cukup MySQL + `php artisan storage:link`, tanpa object storage.

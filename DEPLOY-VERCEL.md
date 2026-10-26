# Deploy ke Vercel

Vercel menjalankan Laravel sebagai *serverless function* lewat runtime komunitas
[`vercel-php`](https://github.com/vercel-community/php). Konsekuensinya, Vercel **tidak menyimpan
file secara permanen**, sehingga:

- database SQLite lokal (`database/database.sqlite`) **tidak bisa dipakai** → butuh database online;
- file upload admin (foto berita, foto direksi, dokumen RUPS, dsb.) **harus disimpan di Cloudflare R2 / AWS S3**.

Yang sudah disiapkan di repo:

| File | Fungsi |
| --- | --- |
| `vercel.json` | Runtime PHP 8.4 (`vercel-php@0.8.0`), file statis dari `public/`, semua request lain ke Laravel; path cache Laravel diarahkan ke `/tmp` |
| `api/index.php` | Pintu masuk Vercel → `public/index.php` |
| `.vercelignore` | `vendor`, `node_modules`, `tests`, `.env` tidak diunggah |
| `public/build/` | Hasil `npm run build` **ikut di-commit**, karena disajikan langsung sebagai file statis |
| `MEDIA_DISK` | Upload otomatis ke disk `s3` di Vercel (`public` di komputer lokal) |

---

## 1. Siapkan database online

Pilih salah satu (keduanya didukung runtime):

- **PostgreSQL – Neon** (paling mudah): di dashboard Vercel buka **Storage → Create Database → Neon**.
  Salin *connection string*-nya ke `DB_URL`, atau isi `DB_HOST`/`DB_DATABASE`/… satu per satu.
- **MySQL** – mis. TiDB Cloud Serverless atau Aiven for MySQL (ada paket gratis).

## 2. Siapkan Cloudflare R2 (penyimpanan file)

1. Cloudflare Dashboard → **R2** → **Create bucket**, mis. `plnservices-media`.
2. Di bucket → **Settings → Public access**: aktifkan **r2.dev subdomain** (atau hubungkan domain sendiri).
   Catat URL publiknya, mis. `https://pub-xxxxxxxx.r2.dev`.
3. R2 → **Manage R2 API Tokens → Create API Token**, izin **Object Read & Write** untuk bucket tadi.
   Catat **Access Key ID**, **Secret Access Key**, dan endpoint `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`.

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
| `AWS_ACCESS_KEY_ID` | Access Key ID token R2. **Rahasia.** |
| `AWS_SECRET_ACCESS_KEY` | Secret Access Key token R2. **Rahasia.** |
| `AWS_DEFAULT_REGION` | `auto` |
| `AWS_BUCKET` | `plnservices-media` |
| `AWS_ENDPOINT` | `https://<ACCOUNT_ID>.r2.cloudflarestorage.com` |
| `AWS_URL` | URL publik bucket, mis. `https://pub-xxxxxxxx.r2.dev` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` |

Sudah diatur otomatis oleh `vercel.json` (tidak perlu diisi): `APP_ENV=production`, `APP_DEBUG=false`,
`SESSION_DRIVER=cookie`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `LOG_CHANNEL=stderr`,
`MEDIA_DISK=s3`, dan path cache di `/tmp`.

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
  cukup MySQL + `php artisan storage:link`, tanpa R2.

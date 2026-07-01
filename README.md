# Website RSUD Lubuk Basung

Portal informasi publik dan panel admin Rumah Sakit Umum Daerah Lubuk Basung.

Stack: **Laravel 11**, **Blade + Livewire 3**, **Filament 3**, **MySQL 8** (dev mendukung **SQLite** + **file cache** sebagai fallback), **Redis** (opsional di production).

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Setup Awal](#setup-awal)
- [Menjalankan di Lokal (Laragon)](#menjalankan-di-lokal-laragon)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Database & Seeder](#database--seeder)
- [Pengujian](#pengujian)
- [Scheduler & Queue](#scheduler--queue)
- [Sitemap & SEO](#sitemap--seo)
- [Backup](#backup)
- [Catatan Lingkungan Windows / Composer](#catatan-lingkungan-windows--composer)
- [Deployment Production](#deployment-production)
- [Struktur Direktori Penting](#struktur-direktori-penting)

## Fitur Utama

### Frontend Publik (`/`)
Beranda, Profil RSUD (Sejarah, Visi-Misi, Struktur Organisasi, Sambutan Direktur), Layanan, Jadwal Dokter (filter Livewire), Berita & Pengumuman, Galeri, Tarif, Pendaftaran, Karir / Lowongan, PPID (4 kategori UU 14/2008), Pengaduan (form + tracking via tiket), Kontak, FAQ, Sitemap.xml.

### Panel Admin (`/admin`)
- Manajemen Polyclinic, Service, Doctor, DoctorSchedule (validasi overlap server-side), News (rich editor + publish action terotorisasi), Gallery, Tariff, JobVacancy, PPID Category & Document (disk routing privat untuk DIKECUALIKAN), Complaint (PII protection + transition guard), Page, FAQ, User & Role.
- Dashboard widget statistik (StatsOverview) + tabel Pengaduan Terbaru (role-aware).
- SiteSettingPage custom dengan section Identitas / Kontak / Lokasi / Sosial Media.
- RBAC via `spatie/laravel-permission` dengan 6 role: `super-admin`, `admin`, `editor`, `humas`, `petugas-pengaduan`, `viewer`.
- Audit log via `spatie/laravel-activitylog` dengan PII redactor (`AuditFilter`).

### Cross-cutting
- Scheduler: `news:publish-scheduled` (everyMinute), `vacancy:auto-close` (00:05), `sitemap:generate` (01:00), `backup:clean` (01:30) + `backup:run` (02:00).
- Queue jobs: `NotifyAdminComplaintJob` (3x retry exponential backoff), `GenerateImageVariantsJob` (thumbnail 400px / main 1200px via Intervention Image).
- Middleware: `SecurityHeaders` (HSTS, CSP, X-Frame-Options, Referrer-Policy), `ForcePublicCacheHeader` (Cache-Control: public, max-age=300), `RecaptchaV3`.
- Cache invalidation observers: News, Doctor, DoctorSchedule, Polyclinic, Service, Tariff, SiteSetting, Page.
- Rate limiter: login 5/menit/IP, complaint 3/jam/IP.
- Force HTTPS pada lingkungan `production`.

## Persyaratan Sistem

| Komponen          | Versi minimum            |
|-------------------|--------------------------|
| PHP               | 8.2 (disarankan 8.3+)    |
| Composer          | 2.5+                     |
| Node.js           | 20 (untuk asset build)   |
| MySQL / MariaDB   | 8.0 / 10.6+              |
| Redis (opsional)  | 6+ untuk queue & cache   |
| Ekstensi PHP      | pdo_mysql, mbstring, openssl, intl, gd, fileinfo, tokenizer |

## Setup Awal

```bash
git clone <repo-url> website-rsud-lubas
cd website-rsud-lubas
cp .env.example .env
composer install
npm install
npm run build
php artisan key:generate
php artisan storage:link
```

## Menjalankan di Lokal (Laragon)

1. Buat database MySQL (mis. `rsud_lubas`) dan sesuaikan kredensial di `.env`.
2. Jalankan migrasi + seed role + (opsional) data demo:
   ```bash
   php artisan migrate:fresh --seed
   php artisan db:seed --class=DemoSeeder
   ```
3. Jalankan dev server:
   ```bash
   php artisan serve
   npm run dev
   ```
4. Akses:
   - Publik: <http://localhost:8000>
   - Admin: <http://localhost:8000/admin>
     - Email: `admin@rsud.local`
     - Password: `password`

## Konfigurasi Environment

Variabel kunci di `.env`:

| Variable                | Default dev   | Catatan                                                     |
|-------------------------|---------------|-------------------------------------------------------------|
| `APP_ENV`               | `local`       | `production` di server live                                 |
| `APP_TIMEZONE`          | `Asia/Jakarta`| Selaras `config/app.php`                                    |
| `DB_CONNECTION`         | `mysql`       | Ubah ke `sqlite` jika tidak punya MySQL                     |
| `CACHE_STORE`           | `file`        | Production direkomendasikan `redis`                         |
| `QUEUE_CONNECTION`      | `database`    | Production direkomendasikan `redis`                         |
| `SESSION_DRIVER`        | `file`        | Production: `redis` atau `database`                         |
| `MAIL_MAILER`           | `smtp`        | `array` di testing                                          |
| `RECAPTCHAV3_SITEKEY` / `RECAPTCHAV3_SECRET` | _kosong_ | Isi untuk mengaktifkan verifikasi reCAPTCHA pada `/pengaduan` |

`.env.testing` sudah disiapkan untuk PHPUnit / Pest (SQLite in-memory + array drivers).

## Database & Seeder

```bash
php artisan migrate            # apply migrations
php artisan migrate:fresh      # drop all + re-run
php artisan db:seed            # seed RoleSeeder
php artisan db:seed --class=DemoSeeder  # demo data + super-admin user
```

Seeder yang tersedia:
- `RoleSeeder` — 6 role + permission matrix (idempoten).
- `DemoSeeder` — data demo (poliklinik, dokter, jadwal, berita, FAQ, dll) + super-admin `admin@rsud.local`.

## Pengujian

```bash
composer test            # seluruh suite (Pest)
composer test:unit       # suite Unit saja
composer test:feature    # suite Feature saja
composer test:property   # suite Property (Eris)
composer analyse         # PHPStan / Larastan
composer format          # Pint (PSR-12)
```

Lingkungan testing memakai SQLite in-memory + array cache + sync queue. Lihat `tests/TestCase.php` untuk override config dan `tests/stubs/RedisStub.php` untuk stub kelas Redis (lingkungan tanpa ekstensi `phpredis`).

## Scheduler & Queue

```bash
php artisan schedule:list       # tampilkan semua schedule
php artisan schedule:run        # jalankan satu siklus (untuk debug)
php artisan queue:work redis    # production queue worker
```

Cron production minimal (tiap menit):
```cron
* * * * * cd /var/www/rsud && php artisan schedule:run >> /dev/null 2>&1
```

Supervisord example untuk queue worker:
```ini
[program:rsud-queue]
command=php /var/www/rsud/artisan queue:work redis --tries=3 --backoff=10
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/rsud-queue.log
```

## Sitemap & SEO

- `sitemap.xml` di-generate oleh `php artisan sitemap:generate` (otomatis daily 01:00).
- Bila file belum ada, route `/sitemap.xml` mengembalikan fallback berisi halaman statis.
- Meta tags + Open Graph dipasang oleh `partials/_meta-tags.blade.php` berbasis `SiteSettingService`.

## Backup

`spatie/laravel-backup` sudah terpasang. Konfigurasi default backup ke storage lokal; ubah `config/backup.php` untuk target S3-compatible.

```bash
php artisan backup:run     # eksekusi manual
php artisan backup:list    # lihat backup yang tersedia
```

## Catatan Lingkungan Windows / Composer

Saat development di Windows (Laragon), Composer 2.4.1 dapat mengalami crash `STATUS_STACK_BUFFER_OVERRUN` saat `composer dump-autoload`. Untuk itu disediakan skrip alternatif:

```bash
php regen-autoload.php
```

Skrip ini membaca `composer.lock` + `vendor/composer/installed.json` lalu menulis ulang `vendor/composer/autoload_static.php` dan `autoload_files.php` (PSR-4 + classmap PHPUnit + files including `RedisStub.php`).

Jika ekstensi `phpredis` tidak terpasang, `RedisStub.php` mencegah error _"Class Redis not found"_ pada package yang sudah meng-cache instance RedisStore di constructor (mis. Spatie Permission). Production tetap memakai ekstensi `phpredis` atau paket `predis/predis`.

## Deployment Production

1. Set `APP_ENV=production`, `APP_DEBUG=false`, dan ubah driver cache/queue/session ke `redis`.
2. Pasang ekstensi `phpredis` atau `composer require predis/predis`.
3. Setup MySQL dengan kredensial yang aman; jalankan `php artisan migrate --force`.
4. `php artisan optimize` (config:cache, route:cache, view:cache).
5. `npm run build` untuk asset Vite.
6. Pastikan cron `schedule:run` aktif setiap menit.
7. Setup queue worker via Supervisord.
8. Siapkan reverse proxy (Nginx) dengan TLS; aplikasi memforce HTTPS otomatis di production.
9. Konfigurasi backup target (S3-compatible) di `config/backup.php`.

## Struktur Direktori Penting

```
app/
├── Console/Commands/             # PublishScheduledNews, AutoCloseVacancy, GenerateSitemap
├── Filament/
│   ├── Resources/                # 13 resource Filament (Polyclinic, Service, Doctor, dll.)
│   ├── Pages/SiteSettingPage.php
│   └── Widgets/                  # StatsOverview, LatestComplaints
├── Http/
│   ├── Controllers/Public/       # 13 controller publik
│   ├── Middleware/               # SecurityHeaders, ForcePublicCacheHeader, RecaptchaV3
│   └── Requests/                 # ScheduleIndexRequest, StoreComplaintRequest
├── Jobs/                         # NotifyAdminComplaintJob, GenerateImageVariantsJob
├── Models/                       # 18 model Eloquent
├── Notifications/                # NewComplaintNotification (PII-aware)
├── Observers/                    # 8 observer (cache invalidation)
├── Policies/                     # 13 policy mapping ke permission Spatie
├── Repositories/                 # DoctorScheduleRepository
├── Services/                     # SiteSettingService, NewsService, ComplaintService, dll.
└── Support/
    ├── AuditFilter.php           # PII redactor untuk audit log
    ├── CacheKeys.php             # Konstanta key cache
    ├── Enums/                    # Day, NewsStatus, ComplaintStatus, dll.
    ├── ValueObjects/             # ScheduleFilter, ComplaintData
    └── ViewModels/               # DoctorScheduleVM
```

Lihat juga `.kiro/specs/rsud-website/` untuk dokumen `requirements.md`, `design.md`, dan `tasks.md` lengkap.

## Lisensi

Internal RSUD Lubuk Basung. Hak cipta dilindungi.

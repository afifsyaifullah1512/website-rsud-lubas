# Implementation Plan: Website RSUD

## Overview

Konversi desain teknis Website RSUD menjadi serangkaian tugas pengkodean inkremental yang dapat dieksekusi oleh agen kode. Implementasi mengikuti stack: **Laravel 11 + Blade + Livewire 3 + Filament 3 + MySQL 8 + Redis**. Setiap tugas dibangun di atas tugas sebelumnya dan diakhiri dengan perakitan komponen sehingga tidak ada kode yang menggantung.

Strategi pengujian: **Pest 2** untuk unit/feature test, **Eris** (atau Pest plugin PBT) untuk property-based test atas 12 correctness properties (P1–P12) yang didefinisikan di `design.md`. Pengujian property dijadikan sub-tugas opsional yang ditandai dengan `*` dan ditempatkan dekat dengan implementasi terkait agar error tertangkap lebih dini.

Konvensi:
- Sub-tugas dengan `*` adalah opsional (test) — `MUST NOT` diimplementasi otomatis kecuali user meminta.
- Setiap sub-tugas mereferensikan klausul granular dari `requirements.md` dengan format `_Requirements: X.Y_`.
- Setiap sub-tugas property test mereferensikan property dari design (`P1`..`P12`).

## Tasks

- [x] 1. Bootstrap proyek Laravel dan konfigurasi inti
  - [x] 1.1 Inisialisasi proyek Laravel 11 dan dependensi inti
    - Buat proyek `laravel/laravel` ^11, set PHP 8.2+, konfigurasi `.env` (APP, DB MySQL 8, REDIS, MAIL, RECAPTCHA)
    - Pasang composer packages: `livewire/livewire ^3`, `filament/filament ^3`, `spatie/laravel-permission ^6`, `spatie/laravel-activitylog ^4`, `spatie/laravel-backup ^9`, `spatie/laravel-sitemap ^7`, `intervention/image ^3`, `mews/purifier ^3`, `josiasmontag/laravel-recaptchav3`
    - Pasang dev packages: `pestphp/pest ^2`, `pestphp/pest-plugin-laravel`, `giorgiosironi/eris`, `larastan/larastan`, `laravel/pint`
    - Konfigurasi `config/cache.php`, `config/session.php`, `config/queue.php` untuk driver `redis`
    - Catatan runtime: `composer.json` mengunci `platform.ext-pcntl = 8.2` dan `php = ^8.2`; CLI PHP 8.3 tetap kompatibel saat memanggil `vendor/bin/*` lokal — pastikan CI/produksi memakai biner PHP yang di-bind ke 8.2 minimal
    - Catatan Redis: lingkungan dev/test boleh tanpa ekstensi `phpredis` karena `tests/stubs/RedisStub.php` (yang ditambahkan ke `autoload.files`) mencegah error _"Class Redis not found"_ untuk paket yang men-cache instance `RedisStore` (mis. Spatie Permission). Produksi WAJIB memasang ekstensi `phpredis` ATAU `composer require predis/predis`
    - _Requirements: 14.4, 29.5, 30.5, 34.1_

  - [x] 1.2 Konfigurasi Vite + Tailwind + asset frontend publik
    - Pasang `tailwindcss`, `@tailwindcss/typography`, `@tailwindcss/forms`, `alpinejs`, `swiper`
    - Setup `tailwind.config.js`, `vite.config.js`, dan entrypoint `resources/css/app.css` + `resources/js/app.js`
    - Tambah Blade directive Vite di layout root untuk asset versioning
    - _Requirements: 29.3, 29.4_

  - [x] 1.3 Konfigurasi Pest, Larastan, Pint, dan struktur folder testing
    - Inisialisasi Pest (`./vendor/bin/pest --init`), buat `tests/Pest.php`, `tests/Unit`, `tests/Feature`, `tests/Property`
    - Konfigurasi `phpstan.neon` (level 6) dan `pint.json` (PSR-12)
    - Tambah skrip composer: `test`, `test:unit`, `test:feature`, `test:property`, `analyse`, `format`
    - _Requirements: 30.7_

- [x] 2. Skema database, enum, value object, dan policy dasar
  - [x] 2.1 Buat enum dan value object inti
    - Buat `app/Support/Enums/Day.php` (SENIN..MINGGU dengan `dayIndex()` dan `optionsId()`)
    - Buat `app/Support/Enums/NewsStatus.php` (DRAFT, PUBLISHED, ARCHIVED)
    - Buat `app/Support/Enums/ServiceType.php`, `TariffClass.php`, `ComplaintStatus.php`, `JobVacancyStatus.php`, `PpidCategoryType.php`
    - Buat value object `app/Support/ValueObjects/ScheduleFilter.php` (immutable DTO `polyclinicId`, `day`, `search`)
    - Buat `app/Support/ValueObjects/ComplaintData.php`
    - _Requirements: 4.7, 5.2, 8.4, 11.3, 17.2, 19.1, 21.1, 22.1, 23.1_

  - [ ]* 2.2 Property test untuk enum `Day` dan `ScheduleFilter` round-trip
    - **Property: P10 — Idempotency** (round-trip serialize/deserialize ScheduleFilter)
    - **Validates: Requirements 4.8_
    - Gunakan Eris untuk generate filter acak; assert `ScheduleFilter::fromArray(filter->toArray())` ekuivalen
    - _Requirements: 4.8_

  - [x] 2.3 Migrations: tabel inti users, roles, pages, site_settings
    - Migrasi `users` (default Laravel + tambahan kolom audit jika perlu), `pages` (slug unique, body longtext), `site_settings` (key PK, value JSON)
    - Jalankan publish + migrate `spatie/laravel-permission` (roles, permissions, model_has_*)
    - _Requirements: 2.2, 14.1, 16.2, 25.1, 26.1_

  - [x] 2.4 Migrations: tabel polyclinics, doctors, doctor_schedules, services, tariffs
    - `polyclinics`: slug unique, `is_active`, `sort_order`, soft deletes
    - `doctors`: `polyclinic_id` FK, slug unique, `is_active`, soft deletes
    - `doctor_schedules`: `doctor_id` FK, `polyclinic_id` FK, enum `day`, `start_time` time, `end_time` time, `is_active`, index `(doctor_id, day, is_active)`
    - `services`: slug unique, enum `type`, `polyclinic_id` nullable FK, soft deletes
    - `tariffs`: `service_id` FK, `price` decimal(12,2) unsigned, `class` nullable
    - _Requirements: 3.4, 4.3, 8.4, 17.1, 17.2, 17.4, 18.1, 18.2, 21.1, 21.2, 33.1_

  - [x] 2.5 Migrations: tabel news, news_categories, galleries, media (polymorphic), faqs
    - `news_categories`: slug unique
    - `news`: slug unique, `category_id` FK, `author_id` FK, enum `status`, `published_at`, `views` unsigned default 0, index `(status, published_at)`, index `(category_id, published_at)`, soft deletes
    - `galleries`: slug unique, enum `type`
    - `media`: morphs `mediable_*`, `disk`, `path`, `mime`, `size`, `caption`, `sort_order`
    - `faqs`: `question`, `answer`, `sort_order`, `is_active`
    - _Requirements: 5.2, 5.3, 7.1, 7.2, 13.1, 19.1, 19.5, 20.1, 20.2_

  - [x] 2.6 Migrations: job_vacancies, ppid_categories, ppid_documents, complaints, complaint_logs
    - `job_vacancies`: slug unique, `open_at` date, `close_at` date, enum `status`
    - `ppid_categories`: enum `type`
    - `ppid_documents`: `category_id` FK, `file_path`, `year`, `published_at` nullable
    - `complaints`: `ticket_number` unique (varchar 32), enum `status`, `ip_address`, index `(ip_address, created_at)`
    - `complaint_logs`: `complaint_id` FK, `user_id` nullable FK, enum `status`, `note`
    - _Requirements: 9.1, 9.3, 10.1, 10.3, 11.3, 11.5, 11.9, 22.1, 22.2, 23.1_

  - [x] 2.7 Eloquent models, relasi, scope, dan casts
    - Implementasi `User`, `Page`, `SiteSetting`, `Polyclinic`, `Doctor`, `DoctorSchedule`, `Service`, `Tariff`, `News`, `NewsCategory`, `Gallery`, `Media`, `Faq`, `JobVacancy`, `PpidCategory`, `PpidDocument`, `Complaint`, `ComplaintLog`
    - Tambah relasi (`belongsTo`, `hasMany`, `morphMany`), scope `active`, `published`, casts enum, `SoftDeletes` pada entitas yang sesuai
    - Pasang trait `LogsActivity` (Spatie) pada model yang dimutasi via Admin
    - _Requirements: 4.3, 5.2, 9.4, 10.3, 11.3, 15.6, 33.1_

  - [ ]* 2.8 Property test untuk model invariants (slug & price)
    - **Property: P3 — Slug Uniqueness & Format** + **P6 — Tariff Non-Negative**
    - **Validates: Requirements 8.4, 17.4, 19.5, 21.2_
    - Eris generate ribuan slug acak; assert hanya yang match `^[a-z0-9-]+$` lolos validation
    - Generate Tariff dengan price acak; assert tabel menolak `price < 0`
    - _Requirements: 8.4, 17.4, 19.5, 21.2_

  - [x] 2.9 Migration `hero_slides` dan model `HeroSlide` (aditif — tabel baru)
    - Buat migrasi tabel baru `hero_slides`: `image_path` (string), `headline` (string nullable), `subheadline` (string nullable), `cta_label` (string nullable), `cta_url` (string nullable), `sort_order` (int default 0), `is_active` (bool default true), `timestamps`
    - Buat model Eloquent `app/Models/HeroSlide.php`: scope `active()` (filter `is_active = true`, urut `sort_order ASC`), casts (`is_active` => boolean, `sort_order` => integer), trait `LogsActivity` (Spatie) dengan `logOnlyDirty`
    - Catatan: ini murni **aditif** — migrasi membuat tabel baru `hero_slides` dan TIDAK mengubah/mereset migrasi inti pada Task 2.3–2.6; tidak ada tugas migrasi sebelumnya yang perlu dibuka ulang
    - _Requirements: 35.2, 36.2, 36.7_

- [x] 3. Checkpoint — pastikan migrasi dan seed dasar berjalan
  - Jalankan `php artisan migrate:fresh --seed`, `php artisan test --testsuite=Unit`, dan ajukan pertanyaan ke user jika ada kendala konfigurasi DB/Redis.

- [x] 4. Konfigurasi RBAC, autentikasi admin, dan audit log
  - [x] 4.1 Seeder roles dan permissions Spatie
    - Buat `RoleSeeder` dengan role: `super-admin`, `admin`, `editor`, `humas`, `petugas-pengaduan`, `viewer`
    - Definisikan permissions per resource: `news.{view,create,update,delete,publish}`, `complaint.{view,respond}`, `doctor.*`, `schedule.*`, `service.*`, `tariff.*`, `gallery.*`, `vacancy.*`, `ppid.*`, `page.*`, `user.*`, `role.*`, `setting.*`
    - Mapping role → permissions sesuai matriks di design
    - _Requirements: 15.1, 15.2, 15.4_

  - [x] 4.2 Policies untuk seluruh resource
    - Buat `app/Policies/` untuk `News`, `Doctor`, `DoctorSchedule`, `Polyclinic`, `Service`, `Tariff`, `Gallery`, `JobVacancy`, `PpidDocument`, `Complaint`, `Page`, `User`, `SiteSetting`
    - Map ke permissions Spatie via `$user->can('news.publish')`
    - Daftarkan di `AuthServiceProvider`
    - _Requirements: 15.2, 15.3, 15.4, 15.5, 19.3, 24.4, 25.3_

  - [x] 4.3 Konfigurasi Filament Panel dan auth admin
    - Generate panel `admin` di `/admin`; konfigurasi `AdminPanelProvider` (sudah dibuat — review brand, color, dan auth middleware)
    - Implementasi `User::canAccessPanel()` agar hanya pengguna ber-role panel yang dapat masuk (sudah dibuat)
    - Otorisasi resource menggunakan Policy + `HasRoles` (Spatie) tanpa plugin tambahan; paket `filament/spatie-laravel-permission-plugin` TIDAK dipasang karena tidak tersedia di Packagist dan integrasi Policy + Gate sudah memadai (lihat catatan kelas `AdminPanelProvider`)
    - Daftarkan named limiter `login` (5 percobaan/menit/IP) via `RateLimiter::for('login', ...)` di `AppServiceProvider::boot()` agar selaras dengan default Filament Login
    - Set session timeout dari `config/session.php` (`SESSION_LIFETIME`)
    - Force HTTPS di production via `URL::forceScheme('https')` pada `AppServiceProvider::boot()` ketika `app()->environment('production')`
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 30.5_

  - [x] 4.4 Konfigurasi Spatie Activitylog untuk audit
    - Migrasi tabel `activity_log` (termasuk kolom `event` dan `batch_uuid`)
    - Konfigurasi `LogsActivity` pada model yang dimutasi via Admin dengan `logOnlyDirty`, exclude field sensitif (`message` Complaint, `password`, `remember_token`)
    - Buat helper `app/Support/AuditFilter.php` (PII redactor stateless: `PII_KEYS`, `REDACTED`, `redactPii()` rekursif, `safeAttributes(Model)`, `forComplaint(Complaint)`)
    - _Requirements: 15.6, 24.5, 30.7, 32.2_

  - [ ]* 4.5 Feature test RBAC dan audit log
    - **Property: P8 — RBAC Authorization**
    - **Validates: Requirements 15.2, 15.3, 15.4, 15.5, 19.3, 24.4, 25.3_
    - Test: setiap role akses resource → assert HTTP 200/403 sesuai matriks; assert `activity_log` ter-insert saat mutasi
    - _Requirements: 15.2, 15.3, 15.4, 15.5, 15.6, 19.3, 24.4, 25.3_

  - [ ]* 4.6 Unit test `AuditFilter` (PII redactor)
    - File: `tests/Unit/AuditFilterTest.php`
    - Test: redact key di `PII_KEYS` (`message`, `email`, `phone`, `ip_address`, `password`, `remember_token`) di top-level menjadi `[REDACTED]`
    - Test: pass-through untuk key non-PII (`name`, `subject`, `status`, `ticket_number`, `id`)
    - Test: rekursi pada struktur nested `old`/`attributes` (format Spatie Activitylog)
    - Test: input array kosong → output array kosong
    - _Requirements: 24.5, 30.7, 32.2_

  - [x] 4.7 Tambah permission hero slide ke `RoleSeeder` (refinement aditif)
    - Tambahkan permissions `slider.{view,create,update,delete}` ke `RoleSeeder`
    - Mapping role: `super-admin`, `admin`, `humas` → seluruh permission slider (manage); `editor` → `slider.view`; `viewer` → `slider.view`
    - Catatan: ini perluasan aditif atas Task 4.1 (yang sudah `[x]`) — TIDAK perlu membuka ulang 4.1; cukup tambahkan entri permission baru pada seeder
    - _Requirements: 36.5_

- [x] 5. Service layer inti dan caching
  - [x] 5.1 Implementasi `SiteSettingService` dengan cache request-scoped
    - `get(string $key, $default)`, `set(...)`, `all()` dengan cache `forever('site_settings')`
    - Invalidasi cache pada `set()` dan via `SiteSettingObserver` (saved/deleted)
    - Bind di service container sebagai singleton per request
    - _Requirements: 12.1, 12.2, 12.3, 26.1, 26.2, 26.3_

  - [x] 5.2 Implementasi `DoctorScheduleService::listFiltered`
    - DTO `ScheduleFilter`, repository `DoctorScheduleRepository::queryActive(filter)` dengan eager load `doctor.polyclinic`
    - `cache.remember("schedules:".md5(serialize), 10m, ...)` dan view-model `DoctorScheduleVM`
    - Sortir deterministik: `polyclinics.name ASC, day_index ASC, start_time ASC`
    - Tambah method `findByDoctor(int $doctorId)` untuk mendukung halaman `/dokter/{slug}` (Requirement 4.5)
    - _Requirements: 4.1, 4.3, 4.4, 4.5, 4.8, 29.2, 29.5_

  - [ ]* 5.3 Property test `DoctorScheduleService::listFiltered` idempotency
    - **Property: P10 — Idempotency Listing**
    - **Validates: Requirements 4.8_
    - Eris generate set jadwal acak + filter acak; panggil `listFiltered` dua kali; assert urutan & isi identik
    - Cek cache hit pada panggilan kedua tidak menyentuh DB (mock)
    - _Requirements: 4.8, 29.5_

  - [x] 5.4 Implementasi `ScheduleService::checkOverlap` (validasi bentrok)
    - Method `checkOverlap(int $doctorId, Day $day, string $start, string $end, ?int $excludeId)`
    - Gunakan kondisi `start < s.end_time AND end > s.start_time` pada interval setengah-terbuka
    - Throw `InvalidArgumentException` jika `start >= end` (precondition Algoritma 2)
    - _Requirements: 18.2, 18.3, 18.4_

  - [ ]* 5.5 Property test schedule non-overlap
    - **Property: P1 — Schedule Non-Overlap** + **P2 — Schedule Validity**
    - **Validates: Requirements 18.3, 18.4_
    - Eris generate banyak interval acak; assert `checkOverlap` mengembalikan true ⟺ ada irisan
    - Generate jadwal `start >= end`; assert validation reject
    - _Requirements: 18.2, 18.3, 18.4_

  - [x] 5.6 Implementasi `NewsService` dan `ComplaintService`
    - `NewsService::paginatePublished(perPage, ?categorySlug)` (1..50), `findBySlug`, `incrementViews`
    - `ComplaintService::submit(ComplaintData, ip)`: rate limit (`RateLimiter::tooMany("complaint:".$ip, 3, 3600)`), generate ticket `RSUD-YYYYMMDD-XXXXXX` dengan retry sampai unique, sanitize message via Purifier, dispatch `NotifyAdminComplaintJob`, log `ComplaintLog`
    - `ComplaintService::changeStatus(...)`: validasi transisi `NEW→IN_REVIEW→RESPONDED→CLOSED` (super-admin bebas ke CLOSED)
    - _Requirements: 5.1, 5.2, 5.3, 5.6, 11.3, 11.4, 11.5, 11.9, 11.10, 24.2, 24.3, 32.1, 32.2, 34.1_

  - [ ]* 5.7 Property test ticket pengaduan dan rate limit
    - **Property: P5 — Complaint Ticket Uniqueness & Format** + **P11 — Rate Limit Pengaduan**
    - **Validates: Requirements 11.3, 11.5, 11.9_
    - Eris generate 10k ComplaintData; assert semua ticket match `^RSUD-\d{8}-[0-9A-Z]{6}$` dan unique
    - Simulasi 4+ submit dengan IP sama dalam 1 jam; assert submit ke-4 throw `TooManyAttemptsException`
    - _Requirements: 11.3, 11.5, 11.9_

  - [x] 5.8 Cache invalidation observer & event listener
    - Buat observer untuk `News`, `DoctorSchedule`, `Doctor`, `Polyclinic`, `Service`, `Tariff`, `SiteSetting`, `Page` yang panggil `Cache::forget` pada key terkait
    - Helper `app/Support/CacheKeys.php` untuk standarisasi key
    - Catatan: Observer (selain `SiteSettingObserver`) di-skip pada environment `testing` lewat `AppServiceProvider::boot` agar test minimal bebas dependensi cache eksternal; `SiteSettingObserver` tetap aktif demi `SiteSettingObserverTest`.
    - _Requirements: 1.6, 6.2, 16.4, 17.3, 18.5, 19.4, 21.3, 26.3_

  - [ ]* 5.9 Property test cache coherence
    - **Property: P9 — Cache Coherence**
    - **Validates: Requirements 1.6, 16.4, 17.3, 18.5, 19.4, 21.3, 26.3_
    - Eris: generate operasi acak (create/update/delete) pada entitas; assert cache key terkait di-flush sebelum response berikutnya
    - _Requirements: 1.6, 16.4, 17.3, 18.5, 19.4, 21.3, 26.3_

- [x] 6. Layer publik: routing, controllers, FormRequest, layout
  - [x] 6.1 Layout publik dan partial
    - `resources/views/layouts/public.blade.php` dengan navbar, footer, dan slot
    - Partial `_meta-tags.blade.php` (title, meta description, OG tags) dari `SiteSetting`
    - Middleware `SecurityHeaders` (HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, CSP)
    - Middleware `ForcePublicCacheHeader` untuk halaman tanpa state user (`Cache-Control: public, max-age=300`)
    - _Requirements: 12.1, 27.3, 29.1, 30.4, 30.5_

  - [x] 6.2 Routing publik (`routes/web.php`)
    - Daftarkan semua rute publik sesuai design (`/`, `/profil/*`, `/layanan*`, `/jadwal-dokter`, `/dokter/{slug}`, `/berita*`, `/galeri`, `/tarif`, `/pendaftaran`, `/karir*`, `/ppid*`, `/pengaduan*`, `/kontak`, `/faq`, `/sitemap.xml`)
    - Apply middleware `throttle:5,60` + `recaptcha` pada `POST /pengaduan`
    - _Requirements: 1.1, 2.1, 3.1, 3.2, 4.1, 5.1, 5.4, 5.5, 7.1, 8.1, 8.3, 9.1, 10.1, 10.2, 11.1, 11.2, 12.1, 13.1, 27.2_

  - [x] 6.3 `HomeController` + Blade beranda
    - Inject `NewsService`, `DoctorScheduleService`, repositori layanan unggulan
    - Cache halaman (`cache.remember("home", 5m)`) dengan invalidation hook
    - View `public/home/index.blade.php`: hero, layanan unggulan, jadwal hari ini, 6 berita terbaru
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

  - [ ]* 6.4 Feature test halaman beranda
    - Assert HTTP 200, hanya tampilkan News PUBLISHED & published_at ≤ now, dokter aktif, max 6 berita
    - Cache hit: assert pemanggilan kedua tidak query DB News (mock query log)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 6.5 `ProfileController` + halaman Page (sejarah, visi-misi, struktur, sambutan)
    - Resolve Page by slug, sanitize body via Purifier, render layout publik
    - 404 jika Page tidak ada
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 28.1_

  - [x] 6.6 `ServiceController` + listing & detail layanan
    - Index: group by `type` enum
    - Show: tampilkan polyclinic terkait + tautan ke jadwal poli
    - 404 jika slug tidak ditemukan; soft-deleted tidak tampil
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 28.1, 33.1, 33.2_

  - [x] 6.7 `DoctorScheduleController` + Livewire `DoctorScheduleFilter`
    - `ScheduleIndexRequest` (FormRequest) validasi `polyclinic_id`, `day` (Enum Day), `q` (min 2)
    - Index controller render Blade + komponen Livewire
    - Komponen Livewire: properties `polyclinicId`, `day`, `q`; method `render()` panggil `DoctorScheduleService::listFiltered`
    - Halaman `/dokter/{slug}` profil dokter + jadwal aktif
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_

  - [ ]* 6.8 Feature test filter jadwal dokter
    - Test filter polyclinic+day+q mengembalikan subset; assert urutan deterministik
    - Test 422 untuk `polyclinic_id` tidak ada dan `day` invalid
    - Test Livewire `wire:model` update tanpa full reload
    - _Requirements: 4.2, 4.4, 4.6, 4.7, 4.8_

  - [x] 6.9 `NewsController` + Livewire `NewsSearch` + detail berita
    - Index pagination 9, kategori `/berita/kategori/{slug}`, detail `/berita/{slug}` dengan `incrementViews`
    - Komponen Livewire untuk search by title/excerpt min 2 char
    - 404 untuk slug tidak ditemukan / belum published
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8_

  - [ ]* 6.10 Property test visibility berita publik
    - **Property: P4 — Published News Visibility** + **P12 — Soft Delete Consistency**
    - **Validates: Requirements 5.2, 33.1, 33.2_
    - Eris: generate banyak News acak (campuran status, published_at masa lalu/depan, soft-deleted); assert listing publik hanya berisi `status=PUBLISHED ∧ published_at ≤ now ∧ deleted_at IS NULL`
    - _Requirements: 5.2, 33.1, 33.2_

  - [x] 6.11 `GalleryController`, `TariffController`, `CareerController`, `FaqController`, `ContactController`
    - Galeri: group by type, urut media `sort_order`, generate thumbnail 400px & main 1200px via Intervention/Image
    - Tarif: format Rupiah `Rp ` + ribuan; `/pendaftaran` render Page
    - Karir: filter `status=OPEN AND close_at >= today`, paginate 10, urut `open_at DESC`; detail attachment
    - FAQ: list aktif sort_order, accordion via Alpine
    - Kontak: tampilkan SiteSetting (alamat, telp, email, jam, koordinat, sosmed) + embed Maps
    - _Requirements: 7.1, 7.2, 7.3, 8.1, 8.2, 8.3, 9.1, 9.2, 12.1, 12.2, 12.3, 13.1, 13.2_

  - [x] 6.12 `PpidController` (publik)
    - `/ppid` group by category type, `/ppid/{type}` filter
    - `DIKECUALIKAN`: hanya metadata + dasar pengecualian, tanpa link unduh
    - Hanya tampilkan dokumen `published_at ≤ now`
    - Stream file dari disk (private untuk DIKECUALIKAN, public untuk lainnya)
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 32.3, 32.4_

  - [x] 6.13 `ComplaintController` (form, store, track) + Livewire `ComplaintForm`
    - GET `/pengaduan`: render Livewire form (name, email, phone, subject, message, recaptcha)
    - POST `/pengaduan`: validate via `StoreComplaintRequest` (rules sesuai design + `recaptcha`), panggil `ComplaintService::submit`, redirect ke halaman terima kasih dengan ticket
    - GET `/pengaduan/cek/{ticket}`: tampilkan status + timeline tanpa PII (kecuali subject)
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7, 11.8, 11.10_

  - [ ]* 6.14 Feature + property test pengaduan end-to-end
    - **Property: P5 + P11**
    - **Validates: Requirements 11.3, 11.5, 11.9_
    - Test: form valid → 200 + ticket; reCAPTCHA gagal → tolak; rate limit > 3/jam → 429
    - Track endpoint tidak bocorkan email/phone
    - _Requirements: 11.3, 11.5, 11.6, 11.7, 11.8, 11.9_

  - [x] 6.15 Halaman error 404, 422, 503 ber-branding RSUD
    - Render `resources/views/errors/{404,422,503}.blade.php` dengan tombol kembali
    - Handler exception untuk `QueryException` → 503 maintenance
    - _Requirements: 28.1, 28.2, 28.3, 28.4_

  - [x] 6.16 Hero slider publik dan modern header (beranda)
    - Buat `resources/views/partials/header.blade.php`: top bar (telepon, jam operasional, kontak IGD dari `SiteSetting`), navigasi dengan active-state highlight, toggle responsif via Alpine untuk viewport `<768px`
    - Buat `resources/views/partials/hero.blade.php`: carousel Swiper dengan auto-play, tombol next/prev, dots/pagination, pause saat hover/focus, navigasi keyboard (panah), swipe gesture, gambar pertama `loading="eager"` sisanya `lazy`, overlay untuk kontras teks
    - Fallback statis dari `SiteSetting` ketika tidak ada slide aktif (area hero tidak pernah kosong)
    - Update `HomeController` untuk memuat `HeroSlide::active()->get()` ke dalam payload cache `home`/beranda dengan fallback ke `SiteSetting`
    - _Requirements: 1.7, 1.8, 1.9, 35.1, 35.2, 35.3, 35.4, 35.5, 35.6, 35.7, 35.8, 35.9, 35.10_

- [x] 7. Checkpoint — pastikan semua tes layer publik lulus
  - Jalankan `composer test`, ajukan pertanyaan ke user jika ada kendala.

- [x] 8. Filament admin: resources, pages, dan widgets
  - [x] 8.1 Resource `PolyclinicResource`, `ServiceResource`
    - Form: name, slug (auto-generate, unique), description, icon, type (Service), polyclinic_id (Service), is_active, sort_order
    - Validation regex slug `^[a-z0-9-]+$`
    - Authorization via Policy
    - _Requirements: 17.1, 17.2, 17.4, 15.2, 15.3, 15.4_

  - [x] 8.2 Resource `DoctorResource`, `DoctorScheduleResource`
    - Doctor: photo upload (image, ≤1MB, disk public), polyclinic_id, slug
    - DoctorSchedule: select doctor, day enum Indonesian, time picker, validasi server-side overlap via `ScheduleService::checkOverlap` di mutateBeforeCreate/Update
    - Trigger cache invalidation event
    - _Requirements: 18.1, 18.2, 18.3, 18.4, 18.5, 15.2, 15.6_

  - [ ]* 8.3 Property test admin overlap rejection
    - **Property: P1 — Schedule Non-Overlap**
    - **Validates: Requirements 18.3, 18.4_
    - Eris: generate pasangan jadwal acak; submit lewat resource; assert overlap selalu di-reject dan non-overlap selalu accepted
    - _Requirements: 18.3, 18.4_

  - [x] 8.4 Resource `NewsResource`, `NewsCategoryResource`
    - Form: title, slug, excerpt, body (rich editor + Purifier on save), cover_image (jpg/jpeg/png/webp, ≤2MB), category_id, status, published_at
    - Action `publish` dilindungi permission `news.publish` (Policy)
    - Auto-set `author_id = auth user`
    - _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5, 30.2_

  - [x] 8.5 Resource `GalleryResource` + Media manager
    - Form: title, slug, type (PHOTO/VIDEO), repeater Media dengan validasi MIME sesuai type, sort_order
    - Dispatch job `GenerateImageVariantsJob` untuk thumbnail/cover
    - _Requirements: 20.1, 20.2, 20.3_

  - [x] 8.6 Resource `TariffResource`, `JobVacancyResource`
    - Tariff: service_id, item_name, price (numeric ≥ 0), class enum
    - JobVacancy: title, slug, description, open_at ≤ close_at, status, attachment (pdf)
    - Auto-derive status `CLOSED` via mutator + scheduler harian (lihat 9.4)
    - _Requirements: 21.1, 21.2, 21.3, 22.1, 22.2, 22.3_

  - [ ]* 8.7 Property test domain rules tariff & vacancy
    - **Property: P6 — Tariff Non-Negative** + **P7 — Vacancy Date Order**
    - **Validates: Requirements 21.2, 22.2_
    - Eris: generate Tariff/JobVacancy acak; assert table-level constraint menolak `price<0` dan `open_at > close_at`
    - _Requirements: 21.2, 22.2_

  - [x] 8.8 Resource `PpidCategoryResource`, `PpidDocumentResource`
    - Form: category, title, file (pdf/docx/xlsx, validasi MIME + size), year (≥2000), published_at
    - Kategori `DIKECUALIKAN` simpan ke disk privat (`local`); kategori lain ke disk publik
    - _Requirements: 23.1, 23.2, 23.3, 23.4, 32.3_

  - [x] 8.9 Resource `ComplaintResource` + action `changeStatus`
    - Table: ticket_number, name, subject, status, created_at desc
    - Detail: tampilkan body hanya untuk role `petugas-pengaduan` & `super-admin` (Policy `view-body`)
    - Action `changeStatus` dengan modal note → panggil `ComplaintService::changeStatus` (validasi transisi)
    - Pastikan logger middleware tidak menulis `message` ke log
    - _Requirements: 15.5, 24.1, 24.2, 24.3, 24.4, 24.5, 32.1, 32.2_

  - [ ]* 8.10 Feature test transisi status pengaduan & PII protection
    - Test transisi valid: NEW→IN_REVIEW→RESPONDED→CLOSED; transisi invalid ditolak
    - Test super-admin bisa langsung CLOSED
    - Test role `editor` dapatkan 403 saat akses Complaint
    - Test log application tidak mengandung body message (assert `Log::shouldReceive`)
    - _Requirements: 24.3, 24.4, 24.5, 32.1, 32.2_

  - [x] 8.11 Resource `PageResource`, `FaqResource`, `UserResource`, `RoleResource`
    - Page: slug regex unique, body Purifier
    - Faq: question, answer, sort_order, is_active
    - User: hanya super-admin; password ≥ 8, email unique, role assign
    - Role: hanya super-admin; tampilkan permissions
    - _Requirements: 13.1, 16.1, 16.2, 16.3, 16.4, 25.1, 25.2, 25.3_

  - [x] 8.12 `SiteSettingPage` (Filament Page kustom)
    - Form key-by-key: nama RS, logo (upload), alamat, telp, email, jam operasional, lat/lng, social media URLs
    - Validasi tipe per key; trigger cache invalidation
    - _Requirements: 26.1, 26.2, 26.3_

  - [x] 8.13 Dashboard Widgets
    - Widget statistik: jumlah News, Complaint NEW, Job_Vacancy OPEN, total Doctor aktif
    - Widget tabel "Pengaduan terbaru" (untuk role petugas-pengaduan)
    - _Requirements: 24.1_

  - [x] 8.14 Resource `HeroSlideResource` (admin)
    - Form: upload `image_path` ke disk `public` (mimes jpg/jpeg/png/webp, ukuran ≤2MB), `headline`, `subheadline`, `cta_label`, `cta_url`, toggle `is_active`
    - Tabel reorderable berdasarkan `sort_order`; toggle `is_active` di tabel
    - Validasi CTA berpasangan: `cta_label` dan `cta_url` saling `requiredWith` (dua arah) sehingga keduanya terisi atau keduanya kosong; bila terisi, `cta_url` harus lolos rule `url`
    - Authorization via Policy `slider.*` (view/create/update/delete); audit log via `LogsActivity`
    - **Cache invalidation**: pada save/delete/reorder slide, `Cache::forget` key beranda (`home`) — hook ini dilipat ke dalam resource ini sehingga Task 5.8 (sudah `[x]`) tidak perlu dibuka ulang
    - _Requirements: 36.1, 36.2, 36.3, 36.4, 36.5, 36.6, 36.7_

  - [ ]* 8.15 Property test hero slide (P13 & P14)
    - **Property: P13 — Hero Slide Visibility & Ordering** — Eris/dataset acak: generate banyak slide (campuran `is_active`, `sort_order` acak); assert `HeroSlide::active()->get()` hanya berisi `is_active=true` dan terurut menaik `sort_order`; assert fallback `SiteSetting` dipakai saat tidak ada slide aktif (count = 0)
    - **Property: P14 — Hero Slide Paired CTA Invariant** — generate slide acak; assert validasi menolak kombinasi `cta_label`/`cta_url` yang hanya salah satu terisi, dan menerima keduanya-terisi (dengan `cta_url` valid) atau keduanya-kosong
    - **Validates: Requirements 35.2, 35.3, 35.4, 36.3_
    - _Requirements: 35.2, 35.3, 35.4, 36.3_

- [x] 9. Cross-cutting: scheduler, queue jobs, sitemap, backup, security headers, recaptcha
  - [x] 9.1 Job `NotifyAdminComplaintJob` + Notification
    - Implement `Notification` ke role `petugas-pengaduan`, retry 3x exponential backoff, tulis ke `failed_jobs`
    - Konfigurasi `supervisord` example untuk `queue:work redis --tries=3`
    - _Requirements: 11.4, 34.1, 34.2, 34.3_

  - [x] 9.2 Job `GenerateImageVariantsJob`
    - Resize gambar Media menjadi thumbnail 400px + main 1200px (Intervention/Image), simpan WebP fallback
    - Dispatch on Media create/update
    - _Requirements: 7.3, 20.3, 29.3_

  - [x] 9.3 Command `news:publish-scheduled` (dijalankan tiap menit)
    - Implement `App\Console\Commands\PublishScheduledNews` mengikuti Algoritma 4 (lockForUpdate, status DRAFT → PUBLISHED, invalidate cache)
    - Daftar di `Kernel::schedule()` `everyMinute()->withoutOverlapping()`
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [ ]* 9.4 Property test publikasi terjadwal
    - **Property: P4 — Published News Visibility**
    - **Validates: Requirements 6.1, 6.2_
    - Generate News acak (status, published_at); jalankan command; assert hanya yang `DRAFT ∧ published_at ≤ now` berubah jadi PUBLISHED
    - Test concurrency: jalankan 2 worker pseudo-parallel; assert tidak ada double-publish
    - _Requirements: 6.1, 6.2, 6.3_

  - [x] 9.5 Command `vacancy:auto-close` (harian)
    - Update Job_Vacancy `status=CLOSED` jika `today > close_at`
    - Schedule `dailyAt('00:05')`
    - _Requirements: 9.4, 22.3_

  - [x] 9.6 Command `sitemap:generate` + route `/sitemap.xml`
    - Gunakan `spatie/laravel-sitemap` untuk membangun urls (statis + News published + Doctor aktif + Polyclinic aktif + JobVacancy OPEN)
    - Tulis `public/sitemap.xml`; route serve dengan `Content-Type: application/xml`
    - Schedule `dailyAt('01:00')`
    - _Requirements: 27.1, 27.2_

  - [x] 9.7 Konfigurasi `spatie/laravel-backup`
    - Backup DB + storage harian ke S3-compatible (atau local), notifikasi gagal via mail/log
    - Schedule `backup:run` + `backup:clean`
    - _Requirements: 31.1, 31.2_

  - [x] 9.8 Middleware `RecaptchaV3` dan `SecurityHeaders`
    - `RecaptchaV3`: verify token ke API Google, tolak jika score < 0.5
    - `SecurityHeaders`: tambahkan HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, CSP (`default-src 'self'`, izinkan domain Maps)
    - Daftarkan di `Kernel`
    - _Requirements: 11.6, 30.4, 30.5_

  - [ ]* 9.9 Feature test security headers, CSRF, dan upload validation
    - Test: setiap response publik mengandung headers wajib
    - Test: POST tanpa CSRF token ditolak 419
    - Test: upload file MIME tidak valid → 422; ukuran > limit → 422
    - _Requirements: 30.1, 30.2, 30.4, 30.6, 28.4_

  - [ ]* 9.10 Test kepatuhan parameter binding (cegah SQL injection)
    - Static-analysis/grep test: assert seluruh akses DB aplikasi memakai Query Builder/Eloquent dengan parameter binding; tidak ada `whereRaw`/`DB::raw`/`DB::statement` yang menginterpolasi input mentah tanpa binding
    - _Requirements: 30.3_

- [x] 10. Performance: index, eager loading, dan HTTP cache
  - [x] 10.1 Tambahkan database index sesuai design
    - Index `news(status, published_at)`, `news(category_id, published_at)`, `doctor_schedules(doctor_id, day, is_active)`, `doctors(polyclinic_id, is_active)`, unique pada semua slug & `complaints.ticket_number`
    - Migrasi terpisah `*_add_performance_indexes`
    - _Requirements: 29.2, 29.5_

  - [x] 10.2 Pastikan eager loading di repository (anti N+1)
    - Refactor query repositori agar selalu `with(['polyclinic','schedules'])`, `with('category','author')` pada listing News
    - Tambah test guard `assertQueryCountLessThan(N)` di feature test listing
    - _Requirements: 29.2_

  - [ ]* 10.3 Test N+1 dan HTTP cache header
    - Pasang `beyondcode/laravel-query-detector` di env testing
    - Assert query count untuk `/jadwal-dokter` dan `/berita` di bawah threshold
    - Assert response halaman publik tanpa user state mengandung `Cache-Control: public, max-age=300`
    - _Requirements: 29.1, 29.2, 29.5_

- [x] 11. Integrasi: wiring komponen, seed data demo, dan smoke test
  - [x] 11.1 Seeder demo (Polyclinic, Doctor, Schedule, Service, Tariff, News, Faq, SiteSetting, Page, PpidCategory)
    - Seeder idempotent untuk dev/demo, tidak digunakan di production
    - _Requirements: 1.2, 3.1, 4.1, 5.1, 8.1, 13.1, 12.1_

  - [x] 11.2 Wire navigasi publik dan footer dari SiteSetting
    - Footer menampilkan nama RS, alamat, kontak, sosial media live dari SiteSetting
    - Navbar highlight active route
    - _Requirements: 12.1, 12.3, 26.2_

  - [x] 11.3 Wire Filament shield untuk navigasi admin
    - `Gate::before` super-admin auto-allow sudah dibind di `AuthServiceProvider` — pastikan tetap aktif setelah resource baru ditambahkan
    - Konfigurasi `shouldRegisterNavigation()` per Resource agar sidebar Filament hanya menampilkan resource yang user-nya punya permission
    - _Requirements: 15.2, 15.3, 15.4_

  - [ ]* 11.4 Smoke test integrasi end-to-end (Pest Feature)
    - Visitor: home → klik berita → detail → kembali → jadwal dokter → filter → submit pengaduan dengan recaptcha mock → terima ticket
    - Admin: login → buat News draft → publish → cache home invalidated → tampil di /
    - Admin: ubah status Complaint → log audit ter-insert
    - _Requirements: 1.1, 1.6, 4.1, 5.5, 11.3, 11.7, 19.4, 24.2, 15.6_

- [x] 12. Final checkpoint — pastikan semua tes lulus dan dokumentasi siap
  - Jalankan `composer test` (unit + feature + property), `composer analyse`, `composer format --test`
  - Verifikasi `php artisan route:list` lengkap, `php artisan schedule:list` benar
  - Update `README.md` proyek dengan instruksi setup, run, dan deploy
  - Ajukan pertanyaan ke user jika ada item yang gagal atau memerlukan keputusan.

## Notes

- Sub-tugas yang diawali `*` adalah opsional (test) dan dapat dilewati untuk MVP cepat. Tugas implementasi inti **tidak boleh** dilewati.
- Setiap sub-tugas merujuk klausul granular dari `requirements.md`. Property test mereferensikan property `P1`–`P14` dari `design.md` (P13–P14 untuk hero slider).
- Checkpoint (`3`, `7`, `12`) digunakan untuk validasi inkremental.
- Pengujian property menggunakan Eris (PHP) yang dipanggil dari Pest; jika di lingkungan target Eris tidak tersedia, gantikan dengan Pest dataset acak generatif.
- Semua file Blade harus auto-escape; konten kaya melewati Purifier sebelum disimpan (lihat `5.6`, `8.4`, `8.11`).
- File migration urutan: 2.3 → 2.4 → 2.5 → 2.6 → 10.1 (index tambahan).
- `AuditFilter` (Task 4.4) dipakai oleh layer audit/activitylog dan dilengkapi unit test opsional di Task 4.6 (`tests/Unit/AuditFilterTest.php`); test ini stateless dan tidak mem-boot Laravel sehingga aman dijalankan di wave 7.
- Runtime: `composer.json` mengunci `php = ^8.2` dan `platform.ext-pcntl = 8.2`. Saat dieksekusi via PHP 8.3 CLI lokal, biner harus tetap kompatibel dengan platform requirement; gunakan `php -d ignore_platform_req=php` hanya jika benar-benar perlu, dan pastikan production memakai PHP ≥ 8.2 dengan ekstensi `phpredis` (atau `predis/predis`) terpasang. Lingkungan testing memakai SQLite in-memory + array cache + `RedisStub` (lihat `tests/stubs/RedisStub.php`).

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "1.3", "2.1"] },
    { "id": 2, "tasks": ["2.2", "2.3"] },
    { "id": 3, "tasks": ["2.4", "2.5", "2.6"] },
    { "id": 4, "tasks": ["2.7"] },
    { "id": 5, "tasks": ["2.8", "2.9", "4.1", "10.1"] },
    { "id": 6, "tasks": ["4.2", "4.3", "4.4", "4.7", "5.1", "5.2", "5.4", "5.6", "5.8"] },
    { "id": 7, "tasks": ["4.5", "4.6", "5.3", "5.5", "5.7", "5.9", "6.1"] },
    { "id": 8, "tasks": ["6.2", "9.1", "9.2", "9.3", "9.5", "9.6", "9.7", "9.8"] },
    { "id": 9, "tasks": ["6.3", "6.5", "6.6", "6.7", "6.9", "6.11", "6.12", "6.13", "6.15", "9.4", "9.9", "9.10", "10.2", "11.1"] },
    { "id": 10, "tasks": ["6.4", "6.8", "6.10", "6.14", "6.16", "8.1", "8.4", "8.5", "8.6", "8.8", "8.9", "8.11", "8.12", "8.13", "8.14", "10.3", "11.2"] },
    { "id": 11, "tasks": ["8.2", "8.3", "8.7", "8.10", "8.15", "11.3"] },
    { "id": 12, "tasks": ["11.4"] }
  ]
}
```

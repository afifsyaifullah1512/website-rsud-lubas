# CHECKPOINT — Website RSUD (rsud-website)

> Handoff untuk lanjut di chat baru. Update terakhir: sesi UI overhaul + editable content + audit.

## Ringkasan Progres (UPDATE TERBARU)

Tahap implementasi inti (Section 1–12) SELESAI. Sesi terakhir fokus **polish front-end publik, editable content via admin, sistem ikon, dan audit fungsi/keamanan**. Status sekarang: **66 test passed**, Pint PASS, fitur publik+admin terverifikasi.

### Yang dikerjakan sesi UI (penting untuk lanjutan)
- **Design system**: warna tema = `sky` (biru muda) via CSS var `--brand-*` (dipilih di admin theme_color). Tombol `.btn-primary/.btn-white/.btn-outline/.btn-outline-brand/.btn-ghost` (solid, shadow lembut). Font Inter + Plus Jakarta Sans via Bunny (`<link>` di `layouts/public.blade.php`).
- **Ikon**: SEMUA pakai **Iconify Phosphor duotone** (`ph:*-duotone`) via `<iconify-icon>` (script CDN di head; CSP sudah izinkan `code.iconify.design` + `api.iconify.design`). Registry: `app/Support/UiIcons.php` (key→`ph:` map + `options()` untuk Select admin). `ServiceType::iconName()` & `fallbackImage()` untuk kartu layanan.
- **Beranda** (`resources/views/public/home/index.blade.php`) urutan: hero → stats → quick-actions(toggle) → trust(toggle) → about → services → facilities → schedule-today → gallery → news → cta-pengaduan → contact-cta. Semua partial di `public/home/partials/`.
- **Editable penuh dari Admin → Pengaturan Situs** (`app/Filament/Pages/SiteSettingPage.php`, default di `app/Support/SiteContent.php`, fallback di blade). Mencakup: identitas/kontak/IGD/registration_url/theme_color/sosmed; teks tiap section beranda (services/schedule/news/complaint/about/facilities/gallery/contact — eyebrow/heading/subheading); repeater `home_quick_actions`, `home_trust_badges` (bisa logo upload), `home_facilities`, `home_about_highlights`; gambar about; toggle `igd_active`, `ppid_active`, `home_show_quick_actions`, `home_show_trust`; `header_subtitle` (default kosong=sembunyi), `footer_tagline`.
- **PENTING — seeder anti-timpa**: `DemoSeeder::seedSiteSettings()` pakai `firstOrCreate` (BUKAN updateOrCreate) supaya reseed TIDAK menimpa konfigurasi admin. Jangan balikin ke updateOrCreate.
- **Kartu layanan** `public/service/_card.blade.php`: pure foto + overlay (tanpa ikon), dipakai di home services (carousel) & `/layanan` (carousel per kategori). Service punya kolom `image` (migration `2026_06_26_000001_add_image_to_services_table`).
- **Jadwal**: `/jadwal-dokter` (livewire `doctor-schedule-filter`) = kartu dokter dikelompokkan per dokter. Home `schedule-today` = flex-wrap kartu (BUKAN carousel — fix bug 1 kartu di hari sepi).
- Halaman **Berita** & **PPID** sudah diseragamkan (pill rounded-full, kartu hover premium, ikon Phosphor).

### Fitur DIHAPUS dari publik (model/tabel disimpan, non-destruktif)
- **Tarif**: route/controller `index`/view/nav/sitemap dihapus; `/pendaftaran` tetap (TariffController@registration). Tabel `tariffs` + admin resource dihapus.
- **Karir/Lowongan**: route/CareerController/view/nav/quick-action/sitemap dihapus. Model `JobVacancy` + **admin JobVacancyResource MASIH ADA** (user minta dibiarkan). `/karir` = 404.

### BUG yang diperbaiki sesi ini (jangan terulang)
- **storage symlink** rusak → `php artisan storage:link` (gambar 404/“nama file”).
- **disk `public` url** → diubah ke **relatif `/storage`** (config/filesystems.php) biar gambar jalan di host apa pun.
- **PageResource attachment chrome**: strip `<span>nama-file/ukuran</span>` dari gambar RichEditor (`PageResource::stripAttachmentChrome`).
- **emergency_phone** field di admin: hapus `->tel()` (regex tolak "(IGD)").
- **NavItem filter `roots_only`**: closure pakai `$q` → ganti `Builder $query` (Filament EvaluatesClosures error). 
- **NewsSearch** (livewire): render pakai data eksplisit di `render()` (v3-proper), bukan magic `getXxxProperty`.
- **PageResource mutateFormDataUsing**: array-callable → Closure.
- **GenerateImageVariantsJob**: driver Intervention dipilih dinamis (imagick/gd).

### Audit & test (sesi ini)
- Audit 39 route GET (publik+admin) → semua < 500.
- `tests/Feature/AdminFilterSmokeTest.php` (BARU): 14 tes Livewire untuk SEMUA filter admin + widget dashboard.
- `tests/Feature/NewsSearchTest.php`: +2 tes render dropdown (assertSee).
- Security: header CSP/HSTS/X-Frame/nosniff/Referrer SET; `/admin` →302 login; tak ada raw SQL; rate-limit pengaduan; Purifier untuk konten kaya.

### SISA untuk PRODUCTION (config, bukan bug — BELUM dikerjakan)
1. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` (user minta TUNDA hardening).
2. reCAPTCHA `RECAPTCHAV3_SITEKEY/SECRET` (gratis) + MAIL SMTP (Gmail/Brevo gratis).
3. Cron `* * * * * php artisan schedule:run` + queue worker + backup target.
4. Ganti data demo → data asli.
5. (Opsional) seragamkan halaman sisa: Berita detail, PPID type, Profil, FAQ, Kontak (info rows).
6. (Opsional) bereskan 20 error baseline PHPStan (false-positive Filament + return-type migrasi Spatie).

## Ringkasan Progres (lama)

- **SEMUA task implementasi inti SELESAI** (Section 1–12). Sisa hanya sub-task property/feature test opsional (diawali `*`).
- Section 8 (admin) lengkap: 8.8 PPID, 8.9 Complaint+changeStatus, 8.11 Page/Faq/User/Role, 8.12 SiteSettingPage — semua diverifikasi.
- Section 9 (cross-cutting) lengkap: jobs, scheduler (5 command terjadwal), sitemap, backup (config dipublish), RecaptchaV3 + SecurityHeaders.
- Section 10 (index + eager loading), Section 11 (DemoSeeder + HeroSlide, footer/nav SiteSetting, shield nav), Section 12 (final checkpoint) — beres.

### Verifikasi sesi ini (bukti)
- `vendor/bin/pest`: **53 passed (125 assertions)**.
- `vendor/bin/pint --test`: **210 files PASS** (PSR-12).
- `vendor/bin/phpstan analyse` (memory_limit=1G): **18 error baseline pra-eksisting** di file yang TIDAK disentuh sesi ini (Filament dynamic `$form`/`$results` false-positive, ternary info-level, return-type migrasi Spatie activity_log, factory nullsafe). Analyse memang belum pernah hijau (checkpoint lama hanya klaim *test* hijau). **2 bug nyata diperbaiki**: `PageResource`/`ManagePages` `mutateFormDataUsing` array-callable → Closure (Purifier Req 13.1/16.1).
- `artisan schedule:list`: 5 command (publish-scheduled everyMinute, vacancy:auto-close 00:05, sitemap:generate 01:00, backup:clean 01:30, backup:run 02:00).
- `artisan route:list`: OK (route `/sitemap.xml` terdaftar).
- DemoSeeder dijalankan: HeroSlide `count=5, active=5`.

### Perubahan kode sesi ini
- `config/backup.php` dipublish (Task 9.7).
- `app/Jobs/GenerateImageVariantsJob.php`: driver Intervention dipilih dinamis (`imagick` bila ada, else `gd`) — sebelumnya hardcode `imagick()` yang gagal di env hanya-`gd`.
- `database/seeders/DemoSeeder.php`: tambah `seedHeroSlides()` (Task 11.1).
- `app/Filament/Resources/PageResource.php` + `.../Pages/ManagePages.php`: fix Closure Purifier.

## Sisa (semua OPSIONAL — test `*`)
2.2, 2.8, 4.5, 4.6, 5.3, 5.5, 5.7, 5.9, 6.4, 6.8, 6.10, 6.14, 8.3, 8.7, 8.10, 8.15, 9.4, 9.9, 9.10, 10.3, 11.4

## Environment / Caveats (WAJIB dibaca sebelum lanjut)

1. **PHP**: pakai biner eksplisit `C:\laragon\bin\php\php-8.3.31-Win32-vs16-x64\php.exe`.
   PHP default di PATH Laragon = 8.1.10 (gagal platform check Laravel 11). 
2. **Redis ext belum terpasang** di PHP CLI. Untuk SEMUA perintah `php artisan` yang menyentuh cache, prepend:
   `$env:CACHE_STORE='array'` (PowerShell). Jangan ubah `.env` permanen.
3. **Database**: `rsud_lubas` di MySQL Laragon (sudah ter-migrate).
4. **Testing**: `phpunit.xml` + `.env.testing` pakai sqlite `:memory:` (force=true). `composer test` hijau.
5. **npm**: PowerShell blok `npm.ps1` → jalankan via `cmd /c "npm run build"`.
6. **reCAPTCHA**: `RECAPTCHAV3_SITEKEY`/`RECAPTCHAV3_SECRET` di `.env` masih KOSONG — isi sebelum tes form pengaduan live (middleware reCAPTCHA skip di env testing).
7. **Quirk task tracker**: task BARU yang ditambahkan ke bawah parent yang sudah `[x]` (mis. 2.9, 4.7, 8.14) TIDAK bisa di-`taskUpdate` ("does not exist"). Workaround: flip checkbox `[ ]`→`[x]` langsung di `tasks.md` via edit teks. `taskUpdate` juga sempat unavailable sesaat — kalau gitu, edit checkbox manual.
8. **Shell PowerShell sempat korup** (karakter `$_` kehapus, echo dobel). Kalau kejadian, pakai grep_search/read_file tool, bukan shell, untuk inspeksi.

## Yang SUDAH selesai

- **Section 1** (bootstrap Laravel 11, Vite/Tailwind/Alpine/Swiper, Pest/Larastan/Pint) ✅
- **Section 2** (enum, value object, SEMUA migrasi, Eloquent models) ✅ + **2.9** (tabel `hero_slides` + model `HeroSlide`) ✅
- **Section 3** checkpoint migrasi ✅
- **Section 4**: 4.1 RoleSeeder, 4.2 Policies, 4.3 Filament panel+auth, 4.4 Activitylog+AuditFilter, **4.7** permission `slider.*` ✅ (4.5/4.6 = test opsional, belum)
- **Section 5**: 5.1 SiteSettingService, 5.2 DoctorScheduleService, 5.4 ScheduleService::checkOverlap, 5.6 News/ComplaintService, 5.8 cache observers ✅ (5.3/5.5/5.7/5.9 = PBT opsional, belum)
- **Section 6** (SEMUA layer publik) ✅ — controllers, Livewire, views, error pages, **6.16 hero slider + header modern**
- **Section 7** checkpoint publik ✅ (**53 test passed, 0 failed**)
- **Section 8 (admin) — sebagian**: 8.1 Polyclinic/Service, 8.2 Doctor/DoctorSchedule (+overlap), 8.4 News/NewsCategory (+publish gate), 8.5 Gallery+Media, 8.6 Tariff/JobVacancy, **8.13 Widgets**, **8.14 HeroSlideResource** ✅
- **10.1** performance index ✅

### Bug yang sudah diperbaiki sesi ini
- **Homepage 500**: Blade salah parse placeholder Swiper `{{index}}` → diganti `@{{index}}` di `resources/views/public/home/partials/hero.blade.php`.
- **PPID enum→string**: compiled view basi; sudah ada regression test `tests/Feature/PpidPublicPageTest.php`.
- **Galeri**: route name `galeri.index` → `galeri`; label enum GalleryType.
- **ServiceResource**: page `ManageServices` yang hilang (resource gagal load) → dibuat.
- **DoctorScheduleResource**: overlap exclude-id pakai `$record?->getKey()` (sebelumnya selalu "bentrok dengan diri sendiri" saat edit).
- **Purifier autoloader** di-regenerate (`composer dump-autoload -o`).

## Yang BELUM selesai (urutan dispatch berikutnya)

### Section 8 sisa (RETRY — tadi kena network error, BELUM dikerjakan):
- **8.8** `PpidCategoryResource`, `PpidDocumentResource` — file pdf/docx/xlsx, year≥2000, DIKECUALIKAN → disk privat `local`, lainnya `public`. _Req 23.1–23.4, 32.3_
- **8.9** `ComplaintResource` + action `changeStatus` — table tanpa PII, body hanya role petugas-pengaduan/super-admin, action changeStatus pakai `ComplaintService::changeStatus` + authorize `complaint.respond`. _Req 15.5, 24.1–24.5, 32.1, 32.2_
- **8.11** `PageResource`, `FaqResource`, `UserResource`, `RoleResource` — Page slug+Purifier; Faq; User/Role super-admin only (password≥8, email unik, role assign). _Req 13.1, 16.x, 25.x_
- **8.12** `SiteSettingPage` (Filament Page kustom) — key-by-key (rs_name, logo, address, phone, emergency_phone, email, operational_hours, lat/lng, social_*, hero_default_*), validasi tipe, save via `SiteSettingService::set` (auto-invalidate cache), gate admin/super-admin. _Req 26.1–26.3_

### Section 9 (cross-cutting) — semua belum:
- **9.1** Job `NotifyAdminComplaintJob` + Notification (CATATAN: job + `NewComplaintNotification` sebenarnya SUDAH ADA dari task 5.6 — tinggal verifikasi/ lengkapi retry/supervisord doc). _Req 11.4, 34.x_
- **9.2** Job `GenerateImageVariantsJob` (SUDAH ADA & dipakai 8.5 — verifikasi). _Req 7.3, 20.3, 29.3_
- **9.3** Command `news:publish-scheduled` (everyMinute, lockForUpdate). _Req 6.x_
- **9.5** Command `vacancy:auto-close` (SUDAH ADA `AutoCloseVacancy` — verifikasi + jadwalkan dailyAt). _Req 9.4, 22.3_
- **9.6** Command `sitemap:generate` + route `/sitemap.xml` (route + SitemapController SUDAH ADA — verifikasi generate). _Req 27.1, 27.2_
- **9.7** Konfigurasi `spatie/laravel-backup`. _Req 31.1, 31.2_
- **9.8** Middleware `RecaptchaV3` + `SecurityHeaders` (SecurityHeaders SUDAH dipasang di bootstrap/app.php task 6.1; RecaptchaV3 alias SUDAH ada — verifikasi skor<0.5 reject). _Req 11.6, 30.4, 30.5_

### Section 10:
- **10.2** Eager loading anti-N+1 di repository + query-count guard. _Req 29.2_

### Section 11 (integrasi):
- **11.1** Seeder demo (Polyclinic, Doctor, Schedule, Service, Tariff, News, Faq, SiteSetting, Page, PpidCategory, + HeroSlide). _Req-banyak_
- **11.2** Wire footer/nav dari SiteSetting (sebagian sudah di `_navbar.blade.php`). _Req 12.1, 12.3, 26.2_
- **11.3** Filament shield: `Gate::before` super-admin (sudah di AuthServiceProvider) + `shouldRegisterNavigation()` per resource. _Req 15.x_

### Section 12:
- **12** Final checkpoint: `composer test` + `composer analyse` + `composer format --test`, `route:list`, `schedule:list`, update README.

### Property tests opsional (diawali `*`, boleh di-skip untuk MVP):
2.2, 2.8, 4.5, 4.6, 5.3, 5.5, 5.7, 5.9, 6.4, 6.8, 6.10, 6.14, 8.3, 8.7, 8.10, 8.15, 9.4, 9.9, 10.3, 11.4

## Cara resume di chat baru
1. Baca file ini + `tasks.md` (sumber kebenaran status, checkbox `[x]`).
2. Banyak file SUDAH ADA (user juga garap paralel di kiro-cli) — selalu INSPECT dulu sebelum bikin, jangan overwrite.
3. Lanjutkan dispatch mulai dari **8.8, 8.9, 8.11, 8.12** (retry), lalu Section 9 (banyak yang tinggal verifikasi), 10.2, 11.x, 12.
4. Tiap selesai task: flip checkbox di `tasks.md` (taskUpdate untuk task lama; edit teks untuk task baru/saat tool error).
5. Setelah Section 8 & 9 beres, jalankan suite (`php artisan test` dgn biner PHP 8.3) sebelum final checkpoint.

# Requirements Document

## Introduction

Dokumen ini menetapkan kebutuhan fungsional dan non-fungsional untuk **Website Rumah Sakit Umum Daerah (RSUD)**, sebuah portal informasi publik resmi yang menyajikan profil organisasi, layanan medis, jadwal dokter, berita/pengumuman, tarif, alur pendaftaran, transparansi PPID, lowongan kerja, kanal pengaduan, kontak, dan FAQ. Sistem juga menyediakan panel admin/CMS untuk pengelolaan seluruh konten dinamis oleh staf RSUD dengan kontrol akses berbasis peran (RBAC).

Kebutuhan diturunkan dari dokumen desain teknis yang telah disetujui (Laravel 11, Blade + Livewire 3, Filament 3, MySQL 8, Redis). Setiap kebutuhan disusun dalam format EARS dan ditujukan untuk dapat diuji.

## Glossary

- **System**: Aplikasi Website RSUD secara keseluruhan (frontend publik + admin panel + layanan latar belakang).
- **Public_Site**: Bagian frontend publik yang dapat diakses tanpa autentikasi (URL root `/`).
- **Admin_Panel**: Panel administrasi berbasis Filament di URL `/admin` yang membutuhkan autentikasi.
- **Visitor**: Pengguna anonim yang mengakses Public_Site.
- **Admin_User**: Pengguna terautentikasi yang mengakses Admin_Panel; memiliki satu atau lebih peran (`super-admin`, `admin`, `editor`, `humas`, `petugas-pengaduan`, `viewer`).
- **Polyclinic**: Unit pelayanan medis (poliklinik), mis. Penyakit Dalam, Anak.
- **Doctor**: Dokter yang bertugas pada satu atau lebih Polyclinic.
- **Doctor_Schedule**: Jadwal praktik seorang Doctor pada satu hari kerja dengan rentang waktu mulai dan selesai.
- **News**: Artikel berita/pengumuman dengan status `DRAFT`, `PUBLISHED`, atau `ARCHIVED`.
- **Service**: Layanan rumah sakit (Poli, Rawat Inap, IGD, Penunjang, Unggulan).
- **Tariff**: Item tarif yang melekat pada Service, memiliki harga dan kelas perawatan.
- **Job_Vacancy**: Pengumuman lowongan kerja dengan periode buka/tutup.
- **PPID_Document**: Dokumen informasi publik (Berkala, Serta-merta, Setiap-saat, Dikecualikan) sesuai UU KIP No. 14/2008.
- **Complaint**: Pengaduan masyarakat yang memiliki `ticket_number` unik dan status (`NEW`, `IN_REVIEW`, `RESPONDED`, `CLOSED`).
- **Gallery**: Kumpulan media (foto/video) yang dipublikasikan.
- **Site_Setting**: Pengaturan situs (nama RS, logo, kontak, alamat, koordinat peta, sosial media, hero fallback default, dll).
- **Hero_Slide**: Entitas slide pada hero slider (carousel) beranda; memuat gambar (wajib), `headline` opsional, `subheadline` opsional, pasangan `cta_label`/`cta_url` opsional, `sort_order` integer, dan `is_active` boolean.
- **Role**: Peran RBAC yang menentukan kumpulan permission.
- **Permission**: Hak melakukan aksi tertentu pada resource (mis. `news.create`, `complaint.respond`).
- **Slug**: String URL-friendly yang cocok dengan regex `^[a-z0-9-]+$`.
- **EARS**: Easy Approach to Requirements Syntax.
- **CMS**: Content Management System (di sini diwujudkan oleh Admin_Panel).
- **PPID**: Pejabat Pengelola Informasi dan Dokumentasi.

## Requirements

### Requirement 1: Beranda

**User Story:** Sebagai Visitor, saya ingin membuka beranda website RSUD, sehingga saya dapat memperoleh ringkasan informasi penting (sambutan, layanan unggulan, jadwal, berita terbaru, kontak) dalam satu halaman.

#### Acceptance Criteria

1. WHEN seorang Visitor mengakses URL `/`, THE Public_Site SHALL menampilkan halaman beranda dengan kode HTTP 200.
2. WHEN halaman beranda dirender, THE Public_Site SHALL menampilkan hero slider (carousel) di bagian paling atas sesuai Requirement 35, diikuti daftar layanan unggulan, ringkasan jadwal dokter hari ini, dan daftar berita terbaru maksimal 6 item.
3. WHEN halaman beranda dirender, THE Public_Site SHALL hanya menampilkan News dengan `status = PUBLISHED` dan `published_at <= now()`.
4. WHEN halaman beranda dirender, THE Public_Site SHALL hanya menampilkan Doctor dan Polyclinic dengan `is_active = true`.
5. WHILE konten beranda yang sama tersedia di cache dan belum kedaluwarsa, THE Public_Site SHALL menyajikan halaman dari cache tanpa melakukan kueri ulang ke database untuk daftar berita dan jadwal.
6. WHEN konten beranda yang relevan (berita, jadwal, layanan unggulan, Hero_Slide) berubah melalui Admin_Panel, THE System SHALL meng-invalidasi cache halaman beranda sebelum response berikutnya disajikan.
7. WHEN halaman beranda dirender, THE Public_Site SHALL menampilkan header dengan navigasi utama yang jelas beserta penanda menu aktif berdasarkan rute yang sedang dibuka.
8. WHEN halaman beranda dirender, THE Public_Site SHALL menampilkan top bar berisi informasi kontak ringkas (nomor telepon), jam layanan, dan kontak IGD/gawat darurat yang nilainya berasal dari Site_Setting.
9. WHILE lebar layar berada di bawah 768px, THE Public_Site SHALL menyajikan navigasi header dalam bentuk menu yang dapat dibuka-tutup (toggle) tanpa memuat ulang halaman.

### Requirement 2: Profil RSUD

**User Story:** Sebagai Visitor, saya ingin membaca profil rumah sakit (sejarah, visi-misi, struktur organisasi, sambutan direktur), sehingga saya memahami identitas dan tata kelola RSUD.

#### Acceptance Criteria

1. WHEN seorang Visitor mengakses `/profil/sejarah`, `/profil/visi-misi`, `/profil/struktur-organisasi`, atau `/profil/sambutan-direktur`, THE Public_Site SHALL menampilkan halaman terkait dengan kode HTTP 200.
2. WHEN halaman profil dirender, THE Public_Site SHALL mengambil isi konten dari entitas Page yang ber-`slug` sesuai (`sejarah`, `visi-misi`, `struktur-organisasi`, `sambutan-direktur`).
3. IF entitas Page yang diminta tidak tersedia, THEN THE Public_Site SHALL mengembalikan kode HTTP 404 dan merender halaman error 404 ber-branding RSUD.
4. WHEN halaman profil dirender, THE Public_Site SHALL menampilkan konten yang telah dilewatkan sanitizer HTML untuk mencegah XSS.

### Requirement 3: Layanan

**User Story:** Sebagai Visitor, saya ingin melihat daftar layanan RSUD beserta detailnya (poliklinik, rawat inap, IGD, penunjang, unggulan), sehingga saya tahu fasilitas yang tersedia.

#### Acceptance Criteria

1. WHEN seorang Visitor mengakses `/layanan`, THE Public_Site SHALL menampilkan daftar Service yang dikelompokkan berdasarkan `type` (`POLI`, `RAWAT_INAP`, `IGD`, `PENUNJANG`, `UNGGULAN`).
2. WHEN seorang Visitor mengakses `/layanan/{slug}`, THE Public_Site SHALL menampilkan detail Service dengan slug tersebut.
3. IF Service dengan slug yang diminta tidak ditemukan, THEN THE Public_Site SHALL mengembalikan kode HTTP 404 dan merender halaman error 404.
4. THE Public_Site SHALL hanya menampilkan Service yang belum dihapus secara soft-delete (`deleted_at IS NULL`).
5. WHEN halaman detail Service dirender dan Service tersebut memiliki Polyclinic terkait, THE Public_Site SHALL menampilkan informasi Polyclinic terkait beserta tautan ke jadwal dokter pada Polyclinic tersebut.

### Requirement 4: Jadwal Dokter

**User Story:** Sebagai Visitor, saya ingin mencari jadwal praktik dokter berdasarkan poliklinik, hari, dan/atau nama dokter, sehingga saya bisa merencanakan kunjungan.

#### Acceptance Criteria

1. WHEN seorang Visitor mengakses `/jadwal-dokter`, THE Public_Site SHALL menampilkan daftar Doctor_Schedule aktif dengan filter poliklinik, hari, dan kata kunci.
2. WHEN Visitor memilih filter poliklinik, hari, atau mengetik kata kunci minimal 2 karakter, THE Public_Site SHALL memperbarui daftar jadwal tanpa memuat ulang seluruh halaman.
3. THE Public_Site SHALL hanya menampilkan Doctor_Schedule dengan `is_active = true` dan Doctor terkait yang `is_active = true`.
4. THE Public_Site SHALL menampilkan Doctor_Schedule terurut berdasarkan nama Polyclinic ascending, kemudian indeks hari ascending (Senin=1 sampai Minggu=7), kemudian `start_time` ascending.
5. WHEN Visitor mengakses `/dokter/{slug}`, THE Public_Site SHALL menampilkan profil dokter beserta seluruh jadwal aktif dokter tersebut.
6. IF parameter `polyclinic_id` yang dikirim tidak ada di tabel Polyclinic, THEN THE Public_Site SHALL mengembalikan respons validasi 422 dengan pesan kesalahan.
7. IF parameter `day` yang dikirim bukan salah satu dari `SENIN`, `SELASA`, `RABU`, `KAMIS`, `JUMAT`, `SABTU`, `MINGGU`, THEN THE Public_Site SHALL mengembalikan respons validasi 422.
8. WHEN dua pemanggilan filter dilakukan berturut-turut dengan parameter identik dan data sumber tidak berubah, THE Public_Site SHALL mengembalikan urutan dan isi daftar yang identik.

### Requirement 5: Berita dan Pengumuman

**User Story:** Sebagai Visitor, saya ingin membaca berita dan pengumuman RSUD, sehingga saya selalu mendapatkan informasi terbaru.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/berita`, THE Public_Site SHALL menampilkan daftar News yang sudah dipublikasikan dengan paginasi 9 item per halaman.
2. THE Public_Site SHALL hanya menampilkan News dengan `status = PUBLISHED` dan `published_at <= now()`.
3. THE Public_Site SHALL mengurutkan daftar News berdasarkan `published_at` descending.
4. WHEN Visitor mengakses `/berita/kategori/{slug}`, THE Public_Site SHALL menampilkan daftar News yang termasuk dalam kategori tersebut dengan paginasi yang sama.
5. WHEN Visitor mengakses `/berita/{slug}`, THE Public_Site SHALL menampilkan detail News dengan slug tersebut.
6. WHEN halaman detail News berhasil dirender untuk Visitor, THE System SHALL menambah `views` News tersebut sebesar 1.
7. IF News dengan slug yang diminta tidak ditemukan atau belum dipublikasikan, THEN THE Public_Site SHALL mengembalikan kode HTTP 404.
8. WHEN Visitor melakukan pencarian dengan kata kunci minimal 2 karakter, THE Public_Site SHALL menampilkan News yang `title` atau `excerpt`-nya mengandung kata kunci tersebut.

### Requirement 6: Penjadwalan Publikasi Berita

**User Story:** Sebagai editor RSUD, saya ingin menjadwalkan berita untuk dipublikasikan otomatis pada waktu tertentu, sehingga publikasi konten dapat diatur tanpa intervensi manual.

#### Acceptance Criteria

1. WHEN scheduler latar belakang berjalan, THE System SHALL mengambil seluruh News dengan `status = DRAFT`, `published_at` tidak null, dan `published_at <= now()`.
2. WHEN scheduler memproses News yang memenuhi syarat di atas, THE System SHALL mengubah `status` menjadi `PUBLISHED` dan meng-invalidasi cache halaman beranda dan cache slug News terkait.
3. WHILE proses penjadwalan berjalan terhadap satu baris, THE System SHALL menggunakan kunci tingkat baris (`lockForUpdate`) untuk mencegah double-publish jika scheduler dipicu secara paralel.
4. IF terjadi kegagalan saat memutakhirkan satu baris News, THEN THE System SHALL mencatat error di log dan melanjutkan proses untuk baris berikutnya.

### Requirement 7: Galeri

**User Story:** Sebagai Visitor, saya ingin melihat galeri foto dan video kegiatan RSUD, sehingga saya mendapat gambaran kegiatan dan fasilitas.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/galeri`, THE Public_Site SHALL menampilkan daftar Gallery yang dikelompokkan berdasarkan `type` (`PHOTO`, `VIDEO`).
2. WHEN sebuah Gallery dirender, THE Public_Site SHALL menampilkan seluruh Media yang melekat padanya terurut berdasarkan `sort_order` ascending.
3. WHEN Visitor membuka gambar di Gallery, THE Public_Site SHALL menyajikan versi yang dioptimalkan (thumbnail 400px dan/atau tampilan utama maksimal 1200px).

### Requirement 8: Tarif dan Pendaftaran

**User Story:** Sebagai Visitor, saya ingin mengetahui tarif layanan dan alur pendaftaran pasien, sehingga saya dapat mempersiapkan kunjungan dengan baik.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/tarif`, THE Public_Site SHALL menampilkan daftar Tariff yang dikelompokkan per Service dan kelas perawatan.
2. THE Public_Site SHALL menampilkan harga Tariff dalam format mata uang Rupiah (`Rp` + ribuan).
3. WHEN Visitor mengakses `/pendaftaran`, THE Public_Site SHALL menampilkan halaman alur pendaftaran (rawat jalan, rawat inap, IGD, BPJS, umum) yang isinya dikelola dari Page atau Site_Setting.
4. THE System SHALL menyimpan harga Tariff sebagai nilai non-negatif (`price >= 0`).

### Requirement 9: Karir / Lowongan Kerja

**User Story:** Sebagai Visitor, saya ingin melihat lowongan kerja yang masih dibuka di RSUD, sehingga saya bisa melamar pada posisi yang sesuai.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/karir`, THE Public_Site SHALL menampilkan daftar Job_Vacancy dengan `status = OPEN` dan `close_at >= today`, terurut `open_at` descending dengan paginasi 10 item.
2. WHEN Visitor mengakses `/karir/{slug}`, THE Public_Site SHALL menampilkan detail Job_Vacancy beserta lampiran dokumen jika tersedia.
3. THE System SHALL menjamin bahwa setiap Job_Vacancy memenuhi `open_at <= close_at`.
4. WHEN scheduler atau kueri publik dijalankan dan `today > close_at`, THE System SHALL memperlakukan Job_Vacancy tersebut sebagai `CLOSED` (tidak ditampilkan di daftar lowongan terbuka).

### Requirement 10: PPID (Transparansi Informasi Publik)

**User Story:** Sebagai Visitor / pemohon informasi, saya ingin mengakses dokumen informasi publik RSUD, sehingga hak atas informasi sesuai UU KIP No. 14/2008 terpenuhi.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/ppid`, THE Public_Site SHALL menampilkan daftar PPID_Document dikelompokkan berdasarkan kategori (`BERKALA`, `SERTA_MERTA`, `SETIAP_SAAT`, `DIKECUALIKAN`).
2. WHEN Visitor mengakses `/ppid/{type}` dengan `type` ∈ {`berkala`, `serta-merta`, `setiap-saat`, `dikecualikan`}, THE Public_Site SHALL menampilkan dokumen dari kategori tersebut.
3. THE Public_Site SHALL hanya menampilkan PPID_Document yang `published_at` tidak null dan `published_at <= now()`.
4. THE Public_Site SHALL TIDAK menampilkan dokumen kategori `DIKECUALIKAN` secara langsung; halaman terkait hanya menampilkan metadata dan keterangan klasifikasi.
5. WHEN Visitor mengunduh dokumen kategori publik (`BERKALA`, `SERTA_MERTA`, `SETIAP_SAAT`), THE System SHALL melayani file dari disk dengan kontrol MIME dan ukuran sesuai aturan upload.

### Requirement 11: Pengaduan Masyarakat

**User Story:** Sebagai Visitor, saya ingin mengirim pengaduan dan melacaknya melalui nomor tiket, sehingga saya yakin keluhan saya tercatat dan ditindaklanjuti.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/pengaduan`, THE Public_Site SHALL menampilkan formulir pengaduan dengan field `name`, `email`, `phone`, `subject`, `message`, dan token reCAPTCHA.
2. WHEN Visitor mengirim formulir pengaduan, THE Public_Site SHALL memvalidasi: `name` 3–120 karakter, `email` format email, `phone` opsional cocok regex `^[0-9+\-() ]{8,20}$`, `subject` ≤ 200 karakter, `message` 20–5000 karakter, dan token reCAPTCHA v3 dengan skor ≥ 0.5.
3. WHEN seluruh validasi terpenuhi, THE System SHALL membuat Complaint baru dengan `status = NEW`, `ticket_number` unik bertugas regex `^RSUD-\d{8}-[0-9A-Z]{6}$`, dan `ip_address` request.
4. WHEN Complaint berhasil dibuat, THE System SHALL membuat satu Complaint_Log dengan `status = NEW` dan note "Pengaduan masuk", lalu mengirim notifikasi email kepada Admin_User berperan `petugas-pengaduan` melalui antrian (queue).
5. IF Visitor mengirim lebih dari 3 pengaduan dengan `ip_address` yang sama dalam jendela 1 jam, THEN THE System SHALL menolak permintaan dengan kode HTTP 429 dan pesan retry-after.
6. IF token reCAPTCHA tidak valid atau skor < 0.5, THEN THE System SHALL menolak submit dengan pesan "Verifikasi gagal, silakan coba lagi." dan tanpa membuat Complaint.
7. WHEN pengaduan tersimpan, THE Public_Site SHALL menampilkan halaman konfirmasi yang memuat `ticket_number` kepada Visitor.
8. WHEN Visitor mengakses `/pengaduan/cek/{ticket}`, THE Public_Site SHALL menampilkan status terkini Complaint dengan `ticket_number` tersebut tanpa menampilkan data PII selain `subject`, `status`, dan timeline status.
9. THE System SHALL menjamin bahwa untuk setiap Complaint, `ticket_number` adalah unik di seluruh tabel `complaints`.
10. THE System SHALL menyimpan `message` Complaint setelah melewati sanitizer untuk mencegah XSS.

### Requirement 12: Kontak

**User Story:** Sebagai Visitor, saya ingin melihat informasi kontak dan lokasi RSUD, sehingga saya dapat menghubungi atau mengunjungi rumah sakit.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/kontak`, THE Public_Site SHALL menampilkan alamat, nomor telepon, email, jam operasional, dan peta lokasi yang nilainya berasal dari Site_Setting.
2. THE Public_Site SHALL menampilkan peta lokasi sebagai embed Google Maps atau OpenStreetMap berdasarkan koordinat yang dikonfigurasi di Site_Setting.
3. THE Public_Site SHALL menampilkan tautan ke akun media sosial resmi yang dikonfigurasi di Site_Setting bila tersedia.

### Requirement 13: FAQ

**User Story:** Sebagai Visitor, saya ingin membaca daftar pertanyaan yang sering diajukan, sehingga saya dapat memperoleh jawaban cepat tanpa menghubungi RSUD.

#### Acceptance Criteria

1. WHEN Visitor mengakses `/faq`, THE Public_Site SHALL menampilkan daftar FAQ dengan `is_active = true` terurut berdasarkan `sort_order` ascending.
2. WHEN Visitor mengklik pertanyaan, THE Public_Site SHALL menampilkan jawaban tanpa memuat ulang halaman.

### Requirement 14: Autentikasi Admin

**User Story:** Sebagai Admin_User, saya ingin masuk ke Admin_Panel dengan kredensial saya, sehingga saya dapat mengelola konten dengan aman.

#### Acceptance Criteria

1. WHEN seorang pengguna anonim mengakses `/admin`, THE Admin_Panel SHALL meredireksi ke halaman login.
2. WHEN Admin_User mengirim kredensial valid, THE Admin_Panel SHALL membuat sesi dan meredireksi ke dashboard admin.
3. IF Admin_User mengirim kredensial salah lebih dari 5 kali dalam 1 menit dari IP yang sama, THEN THE Admin_Panel SHALL menolak permintaan login berikutnya dengan kode HTTP 429 hingga jendela rate limit berakhir.
4. THE Admin_Panel SHALL menjalankan login melalui koneksi HTTPS di lingkungan produksi.
5. WHEN sesi Admin_User tidak aktif melebihi periode timeout yang dikonfigurasi, THE Admin_Panel SHALL meminta login ulang sebelum melayani aksi mutasi.

### Requirement 15: Otorisasi Berbasis Peran (RBAC)

**User Story:** Sebagai super-admin RSUD, saya ingin membatasi aksi pada Admin_Panel berdasarkan peran, sehingga setiap staf hanya dapat mengubah konten sesuai tanggung jawabnya.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menyediakan peran: `super-admin`, `admin`, `editor`, `humas`, `petugas-pengaduan`, dan `viewer`.
2. WHEN Admin_User memicu aksi mutasi (create, update, delete, publish) pada sebuah resource, THE Admin_Panel SHALL memeriksa permission yang sesuai (mis. `news.create`, `news.publish`, `complaint.respond`) sebelum mengeksekusi aksi.
3. IF Admin_User memicu aksi tanpa permission yang dibutuhkan, THEN THE Admin_Panel SHALL mengembalikan kode HTTP 403 dan tidak melakukan perubahan data.
4. WHERE peran adalah `viewer`, THE Admin_Panel SHALL menyembunyikan tombol aksi mutasi dan menolak permintaan mutasi dari role tersebut.
5. WHEN Admin_User berperan `petugas-pengaduan` mengakses daftar Complaint, THE Admin_Panel SHALL menampilkan body pengaduan; sebaliknya, peran lain di luar `super-admin` dan `petugas-pengaduan` tidak boleh melihat body pengaduan.
6. WHEN aksi mutasi dieksekusi oleh Admin_User pada resource apa pun, THE System SHALL mencatatnya pada audit log (siapa, kapan, resource apa, perubahan apa).

### Requirement 16: Manajemen Konten Profil (Pages)

**User Story:** Sebagai editor humas, saya ingin mengelola halaman profil (sejarah, visi-misi, struktur organisasi, sambutan direktur), sehingga konten profil selalu mutakhir.

#### Acceptance Criteria

1. WHEN Admin_User berperan `editor`, `humas`, `admin`, atau `super-admin` membuka resource Page di Admin_Panel, THE Admin_Panel SHALL menampilkan daftar Page yang dapat diedit.
2. WHEN Admin_User menyimpan Page, THE System SHALL memvalidasi `slug` sesuai regex `^[a-z0-9-]+$` dan unik di tabel `pages`.
3. WHEN body Page disimpan, THE System SHALL melewatkan konten melalui sanitizer HTML sebelum penyimpanan.
4. WHEN Page diperbarui, THE System SHALL meng-invalidasi cache halaman publik terkait.

### Requirement 17: Manajemen Poliklinik dan Layanan

**User Story:** Sebagai editor, saya ingin mengelola Polyclinic dan Service, sehingga daftar layanan publik selalu akurat.

#### Acceptance Criteria

1. WHEN Admin_User membuat atau memperbarui Polyclinic, THE System SHALL memvalidasi: `name` wajib, `slug` unik dan cocok regex `^[a-z0-9-]+$`, `is_active` boolean, `sort_order` integer.
2. WHEN Admin_User membuat atau memperbarui Service, THE System SHALL memvalidasi: `name` wajib, `slug` unik dan cocok regex `^[a-z0-9-]+$`, `type` ∈ {`POLI`, `RAWAT_INAP`, `IGD`, `PENUNJANG`, `UNGGULAN`}, dan `polyclinic_id` (jika diisi) merujuk Polyclinic yang ada.
3. WHEN Polyclinic atau Service dibuat, diperbarui, atau dihapus, THE System SHALL meng-invalidasi cache daftar layanan dan jadwal terkait.
4. THE System SHALL menjamin bahwa setiap nilai `slug` pada Polyclinic dan Service unik dalam tabelnya masing-masing.

### Requirement 18: Manajemen Dokter dan Jadwal

**User Story:** Sebagai admin, saya ingin mengelola data Doctor dan Doctor_Schedule, sehingga jadwal yang dipublikasikan kepada masyarakat akurat dan konsisten.

#### Acceptance Criteria

1. WHEN Admin_User menyimpan Doctor, THE System SHALL memvalidasi: `name` wajib (≤ 120 karakter), `polyclinic_id` merujuk Polyclinic yang ada, `specialization` wajib (≤ 120), `slug` unik, dan `photo` (jika diunggah) bertipe image dengan ukuran ≤ 1MB.
2. WHEN Admin_User menyimpan Doctor_Schedule, THE System SHALL memvalidasi: `doctor_id` merujuk Doctor yang ada, `day` ∈ {`SENIN`..`MINGGU`}, `start_time` dan `end_time` format `H:i`, dan `start_time < end_time`.
3. IF Admin_User mencoba menyimpan Doctor_Schedule yang interval `[start_time, end_time)`-nya beririsan dengan Doctor_Schedule aktif lain milik Doctor yang sama pada `day` yang sama, THEN THE System SHALL menolak penyimpanan dengan validation error pada field `start_time` atau `end_time`.
4. THE System SHALL menjamin bahwa untuk setiap pasangan Doctor_Schedule aktif `s1` dan `s2` dengan `doctor_id` dan `day` yang sama dan `s1.id != s2.id`, irisan interval `[s1.start_time, s1.end_time) ∩ [s2.start_time, s2.end_time)` adalah himpunan kosong.
5. WHEN Doctor atau Doctor_Schedule dibuat, diperbarui, atau dihapus, THE System SHALL meng-invalidasi cache filter jadwal terkait sebelum response dikirim.

### Requirement 19: Manajemen Berita

**User Story:** Sebagai editor, saya ingin mengelola News (kategori, konten, kover, status, jadwal publikasi), sehingga saya dapat memublikasikan informasi RSUD secara terkontrol.

#### Acceptance Criteria

1. WHEN Admin_User menyimpan News, THE System SHALL memvalidasi: `title` ≤ 200, `slug` cocok regex `^[a-z0-9-]+$` dan unik di tabel `news`, `body` ≥ 50 karakter, `status` ∈ {`DRAFT`, `PUBLISHED`, `ARCHIVED`}, dan `cover_image` (jika diunggah) bertipe `jpg`, `jpeg`, `png`, atau `webp` dengan ukuran ≤ 2MB.
2. IF Admin_User mengeset `status = PUBLISHED`, THEN THE System SHALL mewajibkan `published_at` terisi dan `published_at <= now()` pada saat publikasi langsung.
3. WHEN Admin_User berperan tanpa permission `news.publish` mencoba mengubah `status` menjadi `PUBLISHED`, THE Admin_Panel SHALL menolak permintaan dengan kode HTTP 403.
4. WHEN News dibuat, diperbarui, dihapus, atau di-archive, THE System SHALL meng-invalidasi cache halaman beranda dan cache slug News terkait.
5. THE System SHALL menjamin bahwa setiap News memiliki `slug` yang unik di tabel `news`.

### Requirement 20: Manajemen Galeri

**User Story:** Sebagai humas, saya ingin mengelola Gallery dan Media-nya, sehingga foto/video kegiatan dapat dipublikasikan dengan rapi.

#### Acceptance Criteria

1. WHEN Admin_User membuat Gallery, THE System SHALL memvalidasi: `title` wajib, `slug` unik dan cocok regex `^[a-z0-9-]+$`, dan `type` ∈ {`PHOTO`, `VIDEO`}.
2. WHEN Admin_User mengunggah Media untuk Gallery, THE System SHALL memvalidasi MIME (image/* atau video/* sesuai `type` Gallery) dan menerapkan batas ukuran sesuai konfigurasi.
3. WHEN gambar diunggah, THE System SHALL menghasilkan versi yang dioptimalkan (thumbnail 400px dan tampilan utama maksimal 1200px) melalui pemrosesan asinkron.

### Requirement 21: Manajemen Tarif

**User Story:** Sebagai admin keuangan, saya ingin mengelola Tariff per Service dan kelas, sehingga publik mendapat informasi biaya yang akurat.

#### Acceptance Criteria

1. WHEN Admin_User menyimpan Tariff, THE System SHALL memvalidasi: `service_id` merujuk Service yang ada, `item_name` wajib (≤ 200), `price` numerik dan ≥ 0, dan `class` (jika diisi) ∈ {`VIP`, `KELAS_1`, `KELAS_2`, `KELAS_3`, `EKSEKUTIF`, `UMUM`}.
2. THE System SHALL menjamin bahwa setiap Tariff memiliki `price >= 0`.
3. WHEN Tariff dibuat, diperbarui, atau dihapus, THE System SHALL meng-invalidasi cache halaman tarif publik.

### Requirement 22: Manajemen Lowongan

**User Story:** Sebagai admin SDM, saya ingin mengelola Job_Vacancy, sehingga lowongan terpublikasi dengan periode yang jelas.

#### Acceptance Criteria

1. WHEN Admin_User menyimpan Job_Vacancy, THE System SHALL memvalidasi: `title` wajib, `slug` unik dan cocok regex `^[a-z0-9-]+$`, `open_at` dan `close_at` format tanggal, `open_at <= close_at`, dan `status` ∈ {`OPEN`, `CLOSED`}.
2. THE System SHALL menjamin bahwa untuk setiap Job_Vacancy berlaku `open_at <= close_at`.
3. WHEN Admin_User menjalankan auto-derive status atau scheduler harian, THE System SHALL mengubah `status` Job_Vacancy menjadi `CLOSED` jika `today > close_at`.

### Requirement 23: Manajemen Dokumen PPID

**User Story:** Sebagai pengelola PPID, saya ingin mengunggah dan mengkategorikan dokumen informasi publik, sehingga RSUD memenuhi kewajiban transparansi sesuai UU KIP.

#### Acceptance Criteria

1. WHEN Admin_User menyimpan PPID_Document, THE System SHALL memvalidasi: `category_id` merujuk kategori yang ada, `title` wajib, `file_path` mengarah ke file yang berhasil diunggah, `year` integer ≥ 2000, dan `published_at` boleh null untuk draft.
2. WHEN Admin_User mengunggah file PPID_Document, THE System SHALL memvalidasi MIME (`pdf`, `docx`, `xlsx`) dan ukuran sesuai konfigurasi maksimum.
3. WHEN PPID_Document berkategori `DIKECUALIKAN` disimpan, THE System SHALL menyimpan file pada disk privat tanpa URL publik langsung.
4. WHEN PPID_Document dipublikasikan (memiliki `published_at` tidak null dan ≤ now()), THE Public_Site SHALL menampilkannya di kategori sesuai (kecuali `DIKECUALIKAN` yang hanya menampilkan metadata).

### Requirement 24: Inbox Pengaduan dan Tindak Lanjut

**User Story:** Sebagai petugas pengaduan, saya ingin melihat, menanggapi, dan mengubah status Complaint, sehingga setiap pengaduan masyarakat dapat ditindaklanjuti dengan jejak audit.

#### Acceptance Criteria

1. WHEN Admin_User berperan `petugas-pengaduan` atau `super-admin` membuka resource Complaint, THE Admin_Panel SHALL menampilkan daftar Complaint terurut berdasarkan `created_at` descending.
2. WHEN Admin_User mengubah status Complaint, THE System SHALL membuat satu baris Complaint_Log baru yang memuat user pelaku, status baru, catatan opsional, dan timestamp.
3. THE Admin_Panel SHALL hanya mengizinkan perubahan status mengikuti urutan yang sah: `NEW → IN_REVIEW → RESPONDED → CLOSED`, atau dari status apa pun ke `CLOSED` oleh `super-admin`.
4. IF Admin_User tanpa permission `complaint.respond` mencoba mengubah status Complaint, THEN THE Admin_Panel SHALL mengembalikan kode HTTP 403.
5. THE System SHALL TIDAK menulis isi `message` Complaint ke log aplikasi (PII protection); hanya `id` dan `ticket_number` yang boleh dilog.

### Requirement 25: Manajemen Pengguna dan Role

**User Story:** Sebagai super-admin, saya ingin mengelola user dan role pada Admin_Panel, sehingga akses dapat diberikan dan dicabut sesuai kebutuhan.

#### Acceptance Criteria

1. WHEN super-admin membuat user baru, THE System SHALL memvalidasi: `name` wajib, `email` format email dan unik, `password` minimal 8 karakter, dan `roles` adalah subset dari role yang terdefinisi.
2. WHEN super-admin menetapkan atau mencabut role pada user, THE System SHALL memperbarui hak akses user tersebut sebelum sesi berikutnya melakukan aksi mutasi.
3. IF Admin_User selain `super-admin` mencoba mengakses resource User atau Role, THEN THE Admin_Panel SHALL mengembalikan kode HTTP 403.

### Requirement 26: Pengaturan Situs (Site Settings)

**User Story:** Sebagai admin, saya ingin mengubah pengaturan situs (nama RS, logo, alamat, kontak, sosial media, koordinat peta), sehingga identitas dan kontak yang ditampilkan publik selalu mutakhir.

#### Acceptance Criteria

1. WHEN Admin_User berperan `admin` atau `super-admin` menyimpan Site_Setting, THE System SHALL memvalidasi tipe nilai sesuai skema masing-masing key (string, url, json, koordinat lintang/bujur).
2. THE System SHALL memuat seluruh Site_Setting hanya satu kali per request HTTP melalui cache permanen yang di-invalidasi pada pembaruan.
3. WHEN Site_Setting diperbarui, THE System SHALL meng-invalidasi cache Site_Setting dan halaman publik yang bergantung padanya.

### Requirement 27: Sitemap dan SEO

**User Story:** Sebagai pengelola RSUD, saya ingin website memiliki sitemap dan metadata SEO yang baik, sehingga konten ditemukan oleh mesin pencari.

#### Acceptance Criteria

1. WHEN scheduler harian berjalan, THE System SHALL menulis ulang `public/sitemap.xml` yang memuat URL halaman statis, News yang dipublikasikan, Doctor aktif, Polyclinic aktif, dan Job_Vacancy ber-`status = OPEN`.
2. WHEN Visitor mengakses `/sitemap.xml`, THE Public_Site SHALL melayani konten dengan content-type `application/xml`.
3. WHEN halaman publik dirender, THE Public_Site SHALL menyertakan tag `<title>`, `<meta description>`, dan tag Open Graph (`og:title`, `og:description`, `og:image`) yang relevan dengan konten halaman.

### Requirement 28: Halaman Error dan Ketersediaan

**User Story:** Sebagai Visitor, saya ingin pesan error yang ramah dan jelas saat halaman tidak ditemukan atau sistem terganggu, sehingga saya tahu langkah berikutnya.

#### Acceptance Criteria

1. IF rute publik tidak cocok atau resource tidak ditemukan, THEN THE Public_Site SHALL mengembalikan kode HTTP 404 dan menampilkan halaman error 404 ber-branding RSUD dengan tombol kembali ke beranda.
2. IF terjadi `QueryException` atau database tidak tersedia saat melayani halaman publik inti, THEN THE Public_Site SHALL mengembalikan kode HTTP 503 dengan halaman maintenance ramah pengguna.
3. WHEN form validation gagal pada permintaan POST publik, THE Public_Site SHALL meredireksi balik dengan pesan error per field dan input lama (kecuali field sensitif seperti password).
4. IF unggahan file melebihi batas ukuran atau MIME tidak valid, THEN THE System SHALL mengembalikan kode HTTP 422 dengan pesan kesalahan jelas.

### Requirement 29: Performa, Caching, dan Aksesibilitas

**User Story:** Sebagai Visitor, saya ingin halaman dimuat cepat dan dapat diakses dari berbagai perangkat, sehingga pengalaman saya nyaman.

#### Acceptance Criteria

1. THE Public_Site SHALL menyajikan halaman publik tanpa state pengguna dengan header `Cache-Control: public, max-age=300`.
2. THE System SHALL menggunakan eager loading (mis. `with(['polyclinic','schedules'])`) sehingga halaman jadwal tidak menghasilkan kueri N+1.
3. THE System SHALL menyajikan gambar di halaman publik dengan atribut `loading="lazy"` dan ukuran yang dioptimalkan.
4. THE Public_Site SHALL menyajikan halaman yang responsif untuk lebar layar mulai 320px.
5. WHEN cache miss terjadi pada listing terfilter (jadwal, berita), THE System SHALL menyimpan hasil ke cache dengan TTL 10 menit menggunakan kunci yang konsisten terhadap parameter filter.

### Requirement 30: Keamanan Aplikasi

**User Story:** Sebagai pengelola RSUD, saya ingin website dilindungi terhadap ancaman umum, sehingga data dan layanan tetap aman.

#### Acceptance Criteria

1. THE System SHALL menerapkan proteksi CSRF pada seluruh form yang melakukan perubahan state.
2. THE System SHALL menyajikan keluaran Blade dengan auto-escape (`{{ }}`) dan melewatkan konten kaya melalui sanitizer HTML sebelum disimpan.
3. THE System SHALL hanya menggunakan Query Builder atau Eloquent (parameter binding) untuk seluruh akses database aplikasi.
4. THE System SHALL menerapkan header keamanan respons: `Strict-Transport-Security`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`, dan `Content-Security-Policy` yang membatasi sumber script/style ke `self` dan domain peta yang diizinkan.
5. THE System SHALL memaksa skema HTTPS pada lingkungan produksi.
6. WHEN file diunggah melalui Admin_Panel atau form publik, THE System SHALL memvalidasi MIME type berdasarkan whitelist dan ukuran maksimum sesuai konfigurasi.
7. WHEN aksi mutasi dijalankan oleh Admin_User, THE System SHALL mencatat aktivitas tersebut dalam audit log (siapa, kapan, resource, perubahan).

### Requirement 31: Pencadangan Data

**User Story:** Sebagai pengelola IT RSUD, saya ingin pencadangan otomatis berkala, sehingga data dapat dipulihkan jika terjadi insiden.

#### Acceptance Criteria

1. WHEN scheduler harian dijalankan pada waktu yang dikonfigurasi, THE System SHALL menjalankan pencadangan database dan storage menggunakan paket pencadangan ke target yang dikonfigurasi (S3-compatible/local).
2. IF pencadangan gagal, THEN THE System SHALL mencatat kegagalan di log harian dan mengirim notifikasi ke saluran error tracking yang dikonfigurasi.

### Requirement 32: Data Pribadi dan Kepatuhan PPID

**User Story:** Sebagai pemohon informasi dan pengelola RSUD, saya ingin pengelolaan data pribadi pengadu dan dokumen PPID mengikuti regulasi, sehingga RSUD memenuhi UU KIP No. 14/2008 dan praktik perlindungan data pribadi.

#### Acceptance Criteria

1. THE System SHALL membatasi akses data pengaduan (Complaint dan Complaint_Log dengan body `message`) hanya kepada Admin_User berperan `petugas-pengaduan` dan `super-admin`.
2. THE System SHALL TIDAK menulis body `message` Complaint maupun PII (email, telepon) ke log aplikasi.
3. THE System SHALL mengklasifikasikan PPID_Document ke dalam empat kategori: `BERKALA`, `SERTA_MERTA`, `SETIAP_SAAT`, `DIKECUALIKAN`, dan menampilkan/menyembunyikan sesuai aturan UU KIP.
4. WHERE PPID_Document berkategori `DIKECUALIKAN`, THE Public_Site SHALL TIDAK menyajikan tautan unduh langsung dan hanya menampilkan metadata serta dasar pengecualian.

### Requirement 33: Konsistensi Konten yang Dihapus

**User Story:** Sebagai pengelola RSUD, saya ingin konten yang dihapus tidak tampil di halaman publik, sehingga tidak ada informasi usang atau salah yang ditampilkan.

#### Acceptance Criteria

1. WHERE entitas mendukung soft delete (`deleted_at`), THE Public_Site SHALL TIDAK menampilkan baris dengan `deleted_at` tidak null pada listing maupun halaman detail.
2. IF Visitor mengakses URL detail entitas yang sudah di-soft-delete, THEN THE Public_Site SHALL mengembalikan kode HTTP 404.

### Requirement 34: Notifikasi Asinkron

**User Story:** Sebagai petugas pengaduan dan admin, saya ingin notifikasi pengaduan dan tugas berat lain diproses di latar belakang, sehingga waktu respons HTTP tetap cepat.

#### Acceptance Criteria

1. WHEN Complaint berhasil dibuat, THE System SHALL mengirim job notifikasi email ke antrian (queue) tanpa memblokir response HTTP ke Visitor.
2. IF job notifikasi gagal mengirim email, THEN THE System SHALL melakukan retry hingga 3 kali dengan exponential backoff dan akhirnya menyimpan job ke `failed_jobs` jika tetap gagal.
3. THE System SHALL menyediakan mekanisme bagi Admin_User untuk melihat dan memicu ulang job yang gagal dari `failed_jobs`.

### Requirement 35: Hero Slider Beranda (Publik)

**User Story:** Sebagai Visitor, saya ingin melihat hero slider (carousel) gambar di bagian atas beranda, sehingga saya langsung memperoleh sorotan informasi penting RSUD secara modern dan mudah dibaca.

#### Acceptance Criteria

1. WHEN halaman beranda dirender, THE Public_Site SHALL menampilkan hero slider berisi satu atau lebih Hero_Slide di bagian paling atas halaman.
2. WHEN hero slider dirender, THE Public_Site SHALL hanya menampilkan Hero_Slide dengan `is_active = true`, terurut berdasarkan `sort_order` ascending.
3. WHEN sebuah Hero_Slide dirender, THE Public_Site SHALL menampilkan gambar slide tersebut, dan menampilkan `headline`, `subheadline`, serta tombol call-to-action (`cta_label` tertaut ke `cta_url`) hanya untuk field yang terisi.
4. IF tidak terdapat Hero_Slide dengan `is_active = true`, THEN THE Public_Site SHALL menampilkan hero fallback statis dari Site_Setting sehingga area hero beranda tidak pernah kosong.
5. WHILE hero slider memuat lebih dari satu Hero_Slide aktif, THE Public_Site SHALL menjalankan auto-play dan menyediakan kontrol next, prev, serta indikator dot untuk berpindah slide.
6. WHEN Visitor mengarahkan fokus atau penunjuk (hover) ke hero slider, THE Public_Site SHALL menghentikan sementara (pause) auto-play slider.
7. WHEN Visitor menekan tombol panah kiri atau panah kanan pada keyboard selagi hero slider memiliki fokus, THE Public_Site SHALL berpindah ke slide sebelumnya atau berikutnya.
8. WHILE lebar layar berada pada perangkat sentuh, THE Public_Site SHALL mendukung navigasi antar Hero_Slide melalui gestur swipe.
9. WHEN hero slider dirender, THE Public_Site SHALL menyajikan gambar Hero_Slide pertama dengan `loading="eager"` dan gambar Hero_Slide berikutnya dengan `loading="lazy"`, selaras dengan Requirement 29.3.
10. WHEN sebuah Hero_Slide menampilkan teks di atas gambar, THE Public_Site SHALL menerapkan lapisan overlay sehingga rasio kontras teks terhadap latar memenuhi minimal 4.5:1, selaras dengan Requirement 29.4.

### Requirement 36: Manajemen Hero Slider (Admin)

**User Story:** Sebagai Admin_User humas, saya ingin mengelola Hero_Slide dari Admin_Panel, sehingga sorotan gambar pada beranda dapat saya perbarui sendiri tanpa mengubah kode.

#### Acceptance Criteria

1. WHEN Admin_User dengan permission yang sesuai (`slider.*`) membuka resource Hero_Slide di Admin_Panel, THE Admin_Panel SHALL menampilkan daftar Hero_Slide terurut berdasarkan `sort_order` ascending beserta status `is_active`.
2. WHEN Admin_User menyimpan Hero_Slide, THE System SHALL memvalidasi: `image` wajib bertipe `jpg`, `jpeg`, `png`, atau `webp` dengan ukuran ≤ 2MB, `headline` ≤ 150 karakter, `subheadline` ≤ 255 karakter, `sort_order` integer, dan `is_active` boolean.
3. WHEN Admin_User mengisi salah satu dari `cta_label` atau `cta_url`, THE System SHALL mewajibkan keduanya terisi berpasangan dan `cta_url` berformat URL yang valid.
4. WHEN Admin_User membuat, memperbarui, mengubah urutan, mengaktifkan, menonaktifkan, atau menghapus Hero_Slide, THE System SHALL meng-invalidasi cache halaman beranda sebelum response berikutnya disajikan, selaras dengan Requirement 1.6.
5. WHEN Admin_User memicu aksi mutasi pada Hero_Slide, THE Admin_Panel SHALL memeriksa permission yang sesuai sebelum mengeksekusi aksi, selaras dengan Requirement 15.2.
6. IF Admin_User memicu aksi mutasi Hero_Slide tanpa permission yang dibutuhkan, THEN THE Admin_Panel SHALL mengembalikan kode HTTP 403 dan tidak melakukan perubahan data.
7. WHEN aksi mutasi pada Hero_Slide dieksekusi oleh Admin_User, THE System SHALL mencatatnya pada audit log (siapa, kapan, resource, perubahan), selaras dengan Requirement 15.6 dan Requirement 30.7.

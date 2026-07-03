<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\NavItem;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\Polyclinic;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Enums\Day;
use App\Support\Enums\GalleryType;
use App\Support\Enums\NewsStatus;
use App\Support\Enums\PpidCategoryType;
use App\Support\Enums\ServiceType;
use App\Support\SiteContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder demo idempoten untuk lingkungan dev.
 *
 * Data dummy lengkap supaya halaman beranda dan seluruh halaman
 * publik tampak hidup tanpa input manual. Aman dipanggil ulang.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Safety guard: jangan jalankan demo data di production
        if (app()->environment('production')) {
            return;
        }

        $superAdmin = $this->seedSuperAdmin();
        $this->seedSiteSettings();
        $this->seedPages();
        $polyclinics = $this->seedPolyclinics();
        $this->seedServices($polyclinics);
        $this->seedDoctorsAndSchedules($polyclinics);
        $this->seedNewsCategoriesAndNews($superAdmin);
        $this->seedFaqs();
        $this->seedPpid();
        $this->seedGalleries();
        $this->seedHeroSlides();
        $this->seedNavItems();
    }

    private function seedHeroSlides(): void
    {
        $slides = [
            [
                'image_path' => 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=1600&q=80',
                'headline' => 'Pelayanan Kesehatan Terakreditasi Paripurna',
                'subheadline' => 'Komitmen kami memberikan layanan profesional, aman, dan terjangkau bagi masyarakat Kabupaten Agam.',
                'cta_label' => 'Lihat Layanan',
                'cta_url' => '/layanan',
            ],
            [
                'image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=1600&q=80',
                'headline' => 'IGD Siaga 24 Jam',
                'subheadline' => 'Tim medis dan perawat profesional siap menangani kegawatdaruratan kapan saja.',
                'cta_label' => 'Hubungi IGD',
                'cta_url' => '/kontak',
            ],
            [
                'image_path' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1600&q=80',
                'headline' => 'Jadwal Dokter Spesialis',
                'subheadline' => 'Rencanakan kunjungan Anda dengan mudah melalui jadwal dokter online.',
                'cta_label' => 'Cek Jadwal',
                'cta_url' => '/jadwal-dokter',
            ],
        ];

        foreach ($slides as $i => $slide) {
            HeroSlide::query()->updateOrCreate(
                ['image_path' => $slide['image_path']],
                [
                    'headline' => $slide['headline'],
                    'subheadline' => $slide['subheadline'],
                    'cta_label' => $slide['cta_label'],
                    'cta_url' => $slide['cta_url'],
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedSuperAdmin(): User
    {
        /** @var User $u */
        $u = User::query()->updateOrCreate(
            ['email' => 'admin@rsud.local'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')],
        );
        if (! $u->hasRole('super-admin')) {
            $u->assignRole('super-admin');
        }
        return $u;
    }

    private function seedSiteSettings(): void
    {
        $defaults = [
            'rs_name' => 'RSUD Lubuk Basung',
            'rs_description' => 'Rumah Sakit Umum Daerah Lubuk Basung — Pelayanan kesehatan profesional terakreditasi Paripurna untuk masyarakat Kabupaten Agam dan sekitarnya.',
            'address' => 'Jl. Jenderal Sudirman No. 1, Lubuk Basung, Kabupaten Agam, Sumatera Barat 26415',
            'phone' => '0752-1234567',
            'emergency_phone' => '0752-1234567 (IGD)',
            'igd_active' => true,
            'ppid_active' => true,
            'karir_active' => true,
            'home_show_quick_actions' => true,
            'home_show_trust' => false,
            'header_subtitle' => '',
            'email' => 'info@rsud-lubas.go.id',
            'operational_hours' => 'Senin–Sabtu 08:00–16:00',
            'theme_color' => 'teal',
            'registration_url' => 'https://rsud.agamkab.go.id/apps/RegOnline/',
            'latitude' => -0.291,
            'longitude' => 100.040,
            'social_facebook' => 'https://facebook.com/rsudlubukbasung',
            'social_instagram' => 'https://instagram.com/rsudlubukbasung',
            'social_youtube' => 'https://youtube.com/@rsudlubukbasung',
            'og_image' => 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=1200&q=80',
        ];
        // firstOrCreate: JANGAN menimpa nilai yang sudah diatur admin.
        // Demo defaults hanya berlaku pada key yang belum ada.
        foreach ($defaults as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Konten beranda & branding editable (teks + repeater) — juga firstOrCreate.
        foreach (SiteContent::TEXTS as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
        SiteSetting::query()->firstOrCreate(['key' => 'home_quick_actions'], ['value' => SiteContent::quickActions()]);
        SiteSetting::query()->firstOrCreate(['key' => 'home_trust_badges'], ['value' => SiteContent::trustBadges()]);
    }

    private function seedPages(): void
    {
        $pages = [
            'sejarah' => [
                'title' => 'Sejarah RSUD Lubuk Basung',
                'body' => '<p>RSUD Lubuk Basung berdiri sejak tahun 1985 sebagai puskesmas rawat inap, kemudian berkembang menjadi rumah sakit umum daerah pada tahun 1995 untuk melayani kebutuhan kesehatan masyarakat Kabupaten Agam dan sekitarnya.</p><p>Sepanjang perjalanannya, rumah sakit ini telah berkembang dari fasilitas terbatas menjadi RS Tipe C dengan 200 tempat tidur, 6 poliklinik spesialis, IGD 24 jam, dan layanan penunjang medis modern.</p><p>Pada tahun 2020, RSUD Lubuk Basung berhasil meraih Akreditasi <strong>Paripurna</strong> dari Komisi Akreditasi Rumah Sakit (KARS), sebuah pencapaian yang menegaskan komitmen kami terhadap mutu pelayanan dan keselamatan pasien.</p>',
            ],            'visi-misi' => [
                'title' => 'Visi & Misi',
                'body' => '<h2>Visi</h2><p>Menjadi rumah sakit pilihan utama masyarakat Kabupaten Agam dengan pelayanan kesehatan yang profesional, bermutu, dan terjangkau pada tahun 2030.</p><h2>Misi</h2><ol><li>Memberikan pelayanan kesehatan paripurna yang berkualitas, aman, dan terjangkau bagi seluruh lapisan masyarakat.</li><li>Mengembangkan sumber daya manusia yang profesional, kompeten, dan berempati.</li><li>Menyediakan sarana, prasarana, dan teknologi medis yang modern sesuai standar nasional.</li><li>Membangun budaya kerja yang berorientasi pada keselamatan pasien dan kepuasan pelanggan.</li><li>Menjalin kemitraan strategis dengan institusi pendidikan dan jejaring rujukan.</li></ol><h2>Motto</h2><blockquote><strong>"Kesehatan Anda Prioritas Kami"</strong></blockquote>',
            ],
            'struktur-organisasi' => [
                'title' => 'Struktur Organisasi',
                'body' => '<p>Struktur organisasi RSUD Lubuk Basung mengikuti Peraturan Daerah Kabupaten Agam tentang Tata Kerja Rumah Sakit Umum Daerah, yang dipimpin oleh seorang Direktur dan didukung oleh:</p><ul><li><strong>Bagian Tata Usaha</strong> — Kepegawaian, Umum, Keuangan</li><li><strong>Bidang Pelayanan Medis</strong> — Rawat Jalan, Rawat Inap, IGD</li><li><strong>Bidang Keperawatan</strong> — Asuhan Keperawatan, Etika & Mutu Keperawatan</li><li><strong>Bidang Penunjang Medis</strong> — Laboratorium, Radiologi, Farmasi, Gizi</li><li><strong>SPI &amp; Komite</strong> — Komite Medik, Komite Keperawatan, Komite PPI, SPI</li></ul><p>Setiap unit dipimpin oleh kepala bidang/bagian yang bertanggung jawab langsung kepada Direktur.</p>',
            ],
            'sambutan-direktur' => [
                'title' => 'Sambutan Direktur',
                'body' => '<p><em>Assalamu\'alaikum Warahmatullahi Wabarakatuh,</em></p><p>Selamat datang di portal resmi RSUD Lubuk Basung. Kami sangat menghargai kepercayaan masyarakat yang telah menjadikan rumah sakit ini sebagai pilihan utama pelayanan kesehatan di Kabupaten Agam.</p><p>Sebagai direktur, saya berkomitmen untuk terus meningkatkan kualitas pelayanan, mengembangkan kompetensi tenaga medis, dan memodernisasi fasilitas kesehatan yang kami miliki. Akreditasi Paripurna yang kami raih bukanlah akhir, melainkan awal dari komitmen jangka panjang untuk memberikan pelayanan terbaik.</p><p>Portal ini hadir agar Anda dapat mengakses informasi layanan, jadwal dokter, tarif, dan menyampaikan pengaduan dengan mudah dan transparan. Suara Anda adalah masukan berharga bagi kami untuk terus berbenah.</p><p>Salam hangat,</p><p><strong>dr. Hendra Pratama, Sp.B(K), MARS</strong><br>Direktur RSUD Lubuk Basung</p>',
            ],
            'pendaftaran' => [
                'title' => 'Alur Pendaftaran',
                'body' => '<h2>Pasien Rawat Jalan</h2><ol><li>Datang ke loket pendaftaran (lantai 1) dengan membawa KTP, kartu BPJS/asuransi, dan rujukan FKTP (jika BPJS).</li><li>Petugas akan mendaftarkan dan memberikan nomor antrian poliklinik.</li><li>Tunggu panggilan di area tunggu poliklinik.</li><li>Setelah konsultasi, ambil resep di apotek (lantai 1).</li></ol><h2>Pasien IGD</h2><p>IGD melayani 24 jam tanpa antrian. Pasien gawat darurat ditangani sesuai prioritas medis (triase). Pendaftaran administrasi dilakukan setelah penanganan awal selesai.</p><h2>Pasien Rawat Inap</h2><ol><li>Surat masuk rawat inap dari dokter poliklinik atau IGD.</li><li>Konfirmasi ketersediaan kamar di loket admisi.</li><li>Lengkapi administrasi (KTP, kartu BPJS/asuransi, surat jaminan jika berlaku).</li><li>Pasien akan diantar ke ruang rawat sesuai kelas yang dipilih.</li></ol><h2>Pendaftaran Online (Coming Soon)</h2><p>Fitur pendaftaran online via WhatsApp Business akan segera tersedia. Ikuti pengumuman di halaman <a href="/berita">Berita</a>.</p>',
            ],
            'fasilitas' => [
                'title' => 'Fasilitas',
                'body' => '<p>RSUD Lubuk Basung menyediakan berbagai fasilitas pendukung untuk kenyamanan pasien dan pengunjung.</p><ul><li>Ruang tunggu ber-AC dengan area khusus lansia & ibu menyusui</li><li>Apotek 24 jam</li><li>Kantin dan minimarket</li><li>Mushola</li><li>Area parkir luas</li><li>ATM Center</li><li>WiFi gratis di area umum</li></ul><p>Halaman ini adalah contoh page custom yang dibuat melalui Admin Panel. Anda dapat menambah atau menyesuaikan konten halaman ini di /admin/pages.</p>',
            ],
        ];
        foreach ($pages as $slug => $data) {
            Page::query()->updateOrCreate(['slug' => $slug], $data);
        }
    }

    /**
     * Seed default menu navigasi publik. Bila admin sudah punya menu
     * sendiri, seeder ini tetap idempoten via `firstOrCreate` per item
     * (tidak overwrite urutan/perubahan admin).
     */
    private function seedNavItems(): void
    {
        // Root items + slug url-nya
        $roots = [
            ['Beranda', '/', 10],
            ['Profil', '#', 20],
            ['Layanan', '/layanan', 30],
            ['Jadwal Dokter', '/jadwal-dokter', 40],
            ['Berita', '/berita', 50],
            ['Galeri', '/galeri', 55],
            ['PPID', '/ppid', 80],
            ['Fasilitas', '/halaman/fasilitas', 90],
            ['Kontak', '/kontak', 100],
        ];
        $rootMap = [];
        foreach ($roots as [$label, $url, $order]) {
            $rootMap[$label] = NavItem::query()->firstOrCreate(
                ['parent_id' => null, 'label' => $label],
                ['url' => $url, 'is_active' => true, 'sort_order' => $order],
            );
        }

        // Children untuk Profil
        $profilParent = $rootMap['Profil'] ?? null;
        if ($profilParent) {
            $children = [
                ['Sejarah', '/profil/sejarah', 10],
                ['Visi & Misi', '/profil/visi-misi', 20],
                ['Struktur Organisasi', '/profil/struktur-organisasi', 30],
                ['Sambutan Direktur', '/profil/sambutan-direktur', 40],
            ];
            foreach ($children as [$label, $url, $order]) {
                NavItem::query()->firstOrCreate(
                    ['parent_id' => $profilParent->id, 'label' => $label],
                    ['url' => $url, 'is_active' => true, 'sort_order' => $order],
                );
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int,Polyclinic> */
    private function seedPolyclinics()
    {
        $list = [
            ['Penyakit Dalam', 'Penanganan penyakit dalam dewasa: hipertensi, diabetes, gangguan pencernaan, dll.'],
            ['Anak', 'Pelayanan kesehatan anak usia 0–18 tahun, imunisasi, dan tumbuh kembang.'],
            ['Bedah', 'Bedah umum dewasa dan anak, bedah minor & mayor.'],
            ['Mata', 'Pemeriksaan visus, katarak, glaukoma, dan kelainan refraksi.'],
            ['Gigi & Mulut', 'Tambal, cabut, scaling, dan perawatan gigi anak.'],
            ['Kandungan & Kebidanan', 'Pemeriksaan kehamilan, USG, KB, dan layanan persalinan.'],
            ['Saraf', 'Stroke, nyeri kepala, gangguan tidur, dan kelainan saraf.'],
            ['THT', 'Telinga, hidung, tenggorokan—anak dan dewasa.'],
        ];
        foreach ($list as $i => [$name, $desc]) {
            Polyclinic::query()->updateOrCreate(
                ['slug' => 'poli-'.Str::slug($name)],
                [
                    'name' => 'Poliklinik '.$name,
                    'description' => $desc,
                    'icon' => null,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
        return Polyclinic::query()->orderBy('sort_order')->get();
    }

    /** @param  \Illuminate\Support\Collection<int,Polyclinic>  $polyclinics */
    private function seedServices($polyclinics): \Illuminate\Support\Collection
    {
        // Layanan unggulan (UNGGULAN type) — tampil di beranda
        $unggulan = [
            ['Bedah Minimal Invasif', 'Operasi dengan sayatan kecil, pemulihan lebih cepat, dan luka minimal.', 'https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&q=80&auto=format&fit=crop'],
            ['Hemodialisa', 'Layanan cuci darah dengan mesin terkini dan tim nefrologi berpengalaman.', 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=800&q=80&auto=format&fit=crop'],
            ['Persalinan Aman & Nyaman', 'Layanan kebidanan profesional dengan dokter spesialis & ruang VIP.', 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&q=80&auto=format&fit=crop'],
            ['Medical Check Up', 'Paket pemeriksaan kesehatan komprehensif dengan hasil akurat.', 'https://images.unsplash.com/photo-1612277795421-9bc7706a4a34?w=800&q=80&auto=format&fit=crop'],
        ];
        foreach ($unggulan as $i => [$name, $desc, $image]) {
            Service::query()->updateOrCreate(
                ['slug' => 'unggulan-'.Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'image' => $image,
                    'type' => ServiceType::UNGGULAN->value,
                    'polyclinic_id' => null,
                ],
            );
        }

        // Layanan IGD & Rawat Inap & Penunjang
        Service::query()->updateOrCreate(
            ['slug' => 'igd-24-jam'],
            ['name' => 'IGD 24 Jam', 'description' => 'Instalasi Gawat Darurat dengan dokter & perawat siaga 24 jam.', 'type' => ServiceType::IGD->value],
        );
        Service::query()->updateOrCreate(
            ['slug' => 'rawat-inap-vip'],
            ['name' => 'Rawat Inap VIP', 'description' => 'Kamar single dengan fasilitas premium untuk kenyamanan maksimal.', 'type' => ServiceType::RAWAT_INAP->value],
        );
        Service::query()->updateOrCreate(
            ['slug' => 'rawat-inap-kelas-1'],
            ['name' => 'Rawat Inap Kelas 1', 'description' => 'Kamar 2 tempat tidur dengan AC, TV, dan kamar mandi dalam.', 'type' => ServiceType::RAWAT_INAP->value],
        );
        Service::query()->updateOrCreate(
            ['slug' => 'laboratorium-klinik'],
            ['name' => 'Laboratorium Klinik', 'description' => 'Pemeriksaan darah, urine, kimia, mikrobiologi, dan PCR.', 'type' => ServiceType::PENUNJANG->value],
        );
        Service::query()->updateOrCreate(
            ['slug' => 'radiologi'],
            ['name' => 'Radiologi', 'description' => 'X-Ray, USG 4D, dan CT Scan untuk diagnosa pencitraan akurat.', 'type' => ServiceType::PENUNJANG->value],
        );

        // Layanan POLI per polyclinic
        foreach ($polyclinics as $poli) {
            Service::query()->updateOrCreate(
                ['slug' => $poli->slug.'-konsultasi'],
                [
                    'polyclinic_id' => $poli->id,
                    'name' => 'Konsultasi '.str_replace('Poliklinik ', '', $poli->name),
                    'description' => 'Konsultasi rutin & pemeriksaan di '.$poli->name.'.',
                    'type' => ServiceType::POLI->value,
                ],
            );
        }

        return Service::query()->get();
    }

    /** @param  \Illuminate\Support\Collection<int,Polyclinic>  $polyclinics */
    private function seedDoctorsAndSchedules($polyclinics): void
    {
        $doctors = [
            ['Andi Pratama', 'Penyakit Dalam', 'M', 'Lulusan FK Universitas Andalas, pengalaman 15 tahun di bidang penyakit dalam.'],
            ['Budi Hartono', 'Anak', 'M', 'Spesialis anak, fokus pada tumbuh kembang dan imunisasi anak.'],
            ['Citra Maharani', 'Bedah', 'F', 'Spesialis bedah umum dengan keahlian bedah minimal invasif.'],
            ['Dewi Lestari', 'Mata', 'F', 'Operasi katarak dan pemeriksaan retina dengan teknologi terkini.'],
            ['Eka Sumarno', 'Gigi & Mulut', 'M', 'Lulusan FKG UI, ahli konservasi gigi dan ortodonti.'],
            ['Fitri Rahayu', 'Kandungan & Kebidanan', 'F', 'Spesialis Obstetri & Ginekologi, fokus kehamilan risiko tinggi.'],
            ['Galih Saputra', 'Saraf', 'M', 'Neurolog dengan pengalaman penanganan stroke dan epilepsi.'],
            ['Hana Wirawan', 'THT', 'F', 'Spesialis THT, ahli rinosinusitis dan otoplasti.'],
        ];

        $polyMap = $polyclinics->keyBy(fn ($p) => str_replace('Poliklinik ', '', $p->name));

        foreach ($doctors as [$name, $specialization, $gender, $bio]) {
            $poli = $polyMap->get($specialization);
            if (! $poli) {
                continue;
            }

            $fullName = 'dr. '.$name.', Sp.'.substr($specialization, 0, 2);
            $slug = Str::slug($fullName);
            // Foto dari DiceBear (avatar konsisten per slug)
            $photoUrl = 'https://api.dicebear.com/9.x/avataaars/svg?seed='.$slug.'&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc';

            /** @var Doctor $doctor */
            $doctor = Doctor::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'polyclinic_id' => $poli->id,
                    'name' => $fullName,
                    'specialization' => 'Spesialis '.$specialization,
                    'photo' => $photoUrl,
                    'bio' => $bio,
                    'is_active' => true,
                ],
            );

            // 3 hari per dokter, jam berbeda
            $daysAndTimes = match ($specialization) {
                'Penyakit Dalam', 'Anak' => [[Day::SENIN, '08:00:00', '12:00:00'], [Day::RABU, '08:00:00', '12:00:00'], [Day::JUMAT, '08:00:00', '11:00:00']],
                'Bedah', 'Saraf' => [[Day::SELASA, '09:00:00', '13:00:00'], [Day::KAMIS, '09:00:00', '13:00:00']],
                'Mata', 'THT' => [[Day::SENIN, '13:00:00', '16:00:00'], [Day::RABU, '13:00:00', '16:00:00']],
                'Gigi & Mulut' => [[Day::SELASA, '08:00:00', '12:00:00'], [Day::KAMIS, '08:00:00', '12:00:00'], [Day::SABTU, '08:00:00', '11:00:00']],
                default => [[Day::SENIN, '08:00:00', '12:00:00'], [Day::RABU, '08:00:00', '12:00:00']],
            };

            foreach ($daysAndTimes as [$day, $start, $end]) {
                DoctorSchedule::query()->updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day' => $day->value, 'start_time' => $start],
                    ['polyclinic_id' => $poli->id, 'end_time' => $end, 'is_active' => true],
                );
            }
        }
    }

    private function seedNewsCategoriesAndNews(User $author): void
    {
        $cats = [
            ['Pengumuman', 'pengumuman'],
            ['Kesehatan', 'kesehatan'],
            ['Layanan Baru', 'layanan-baru'],
            ['Kegiatan', 'kegiatan'],
        ];
        $catModels = [];
        foreach ($cats as [$name, $slug]) {
            $catModels[$name] = NewsCategory::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        // 8 berita demo dengan cover dari Unsplash (deterministik berdasarkan id keyword)
        $articles = [
            ['Pengumuman', 'Layanan Hemodialisa Resmi Beroperasi', 'Layanan Hemodialisa kini tersedia 24 jam dengan 8 mesin baru.', 'photo-1576091160550-2173dba999ef'],
            ['Layanan Baru', 'Vaksinasi Influenza Tahunan Dimulai', 'Vaksinasi influenza tahunan untuk lansia tersedia gratis bagi peserta BPJS.', 'photo-1632598571994-7e62a7c5d28a'],
            ['Kesehatan', 'Tips Menjaga Kesehatan Jantung di Usia Muda', 'Penyakit jantung tidak hanya menyerang usia tua, simak tips berikut.', 'photo-1505751172876-fa1923c5c528'],
            ['Pengumuman', 'RSUD Raih Akreditasi Paripurna 2026', 'Komitmen mutu pelayanan kami diakui dengan akreditasi tertinggi KARS.', 'photo-1551601651-2a8555f1a136'],
            ['Kegiatan', 'Bakti Sosial Donor Darah Bersama PMI', 'Stok darah PMI Agam meningkat berkat partisipasi staf RSUD.', 'photo-1615461066841-6116e61058f4'],
            ['Kesehatan', 'Kenali Gejala Awal Stroke dengan FAST', 'Face, Arm, Speech, Time — ingat 4 indikator penting penanganan stroke.', 'photo-1559757148-5c350d0d3c56'],
            ['Layanan Baru', 'Klinik Tumbuh Kembang Anak Dibuka', 'Layanan terpadu pemantauan tumbuh kembang anak 0–18 tahun.', 'photo-1581595220892-b0739db3ba8c'],
            ['Kegiatan', 'Workshop Kegawatdaruratan untuk Bidan Desa', 'RSUD melatih 50 bidan desa dalam penanganan kegawatdaruratan obstetri.', 'photo-1631815589968-fdb09a223b1e'],
        ];

        foreach ($articles as $i => [$cat, $title, $excerpt, $unsplashId]) {
            News::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'category_id' => $catModels[$cat]->id,
                    'author_id' => $author->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => '<p>'.$excerpt.'</p><p>'.fake()->paragraph(8).'</p><p>'.fake()->paragraph(6).'</p><h2>Detail Pelaksanaan</h2><p>'.fake()->paragraph(5).'</p><ul><li>'.fake()->sentence().'</li><li>'.fake()->sentence().'</li><li>'.fake()->sentence().'</li></ul><p>'.fake()->paragraph(4).'</p>',
                    'cover_image' => "https://images.unsplash.com/{$unsplashId}?w=1200&q=80&auto=format&fit=crop",
                    'status' => NewsStatus::PUBLISHED,
                    'published_at' => now()->subDays($i + 1),
                    'views' => random_int(50, 1500),
                ],
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['Bagaimana cara mendaftar pasien rawat jalan?', 'Anda dapat datang langsung ke loket pendaftaran lantai 1 mulai pukul 07.30 dengan membawa KTP, kartu BPJS/asuransi, dan rujukan FKTP (jika BPJS). Pendaftaran online akan segera tersedia melalui WhatsApp Business.'],
            ['Apakah RSUD menerima BPJS Kesehatan?', 'Ya, kami melayani peserta BPJS Kesehatan dengan rujukan dari Fasilitas Kesehatan Tingkat Pertama (FKTP) sesuai ketentuan BPJS.'],
            ['Berapa nomor IGD yang bisa dihubungi?', 'IGD dapat dihubungi 24 jam di nomor 0752-1234567 ext. 911 atau langsung datang ke pintu IGD di sisi timur gedung utama.'],
            ['Apakah tersedia layanan ambulans?', 'Tersedia ambulans untuk evakuasi pasien dengan tarif sesuai jarak. Hubungi 0752-1234567 untuk pemesanan.'],
            ['Berapa jam besuk pasien rawat inap?', 'Jam besuk: 11.00–13.00 dan 17.00–20.00. Maksimal 2 pengunjung per pasien untuk menjaga kenyamanan.'],
            ['Apakah saya bisa memilih dokter spesialis?', 'Ya, Anda dapat memilih dokter sesuai jadwal yang tersedia di halaman Jadwal Dokter. Konsultasi sesuai antrian.'],
            ['Apa saja kelas perawatan rawat inap?', 'Tersedia kelas VIP, Kelas 1, Kelas 2, dan Kelas 3. Tarif dan fasilitas dapat dilihat di halaman Tarif.'],
            ['Bagaimana cara mengajukan komplain atau pengaduan?', 'Anda dapat menyampaikan pengaduan melalui form online di /pengaduan. Setiap pengaduan diberikan nomor tiket untuk pelacakan status tindak lanjut.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::query()->updateOrCreate(
                ['question' => $q],
                ['answer' => $a, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }

    private function seedPpid(): void
    {
        foreach (PpidCategoryType::cases() as $type) {
            $name = match ($type) {
                PpidCategoryType::BERKALA => 'Informasi Berkala',
                PpidCategoryType::SERTA_MERTA => 'Informasi Serta-Merta',
                PpidCategoryType::SETIAP_SAAT => 'Informasi Setiap Saat',
                PpidCategoryType::DIKECUALIKAN => 'Informasi Dikecualikan',
            };
            PpidCategory::query()->updateOrCreate(
                ['type' => $type->value],
                ['name' => $name],
            );
        }

        // Dokumen demo
        $berkala = PpidCategory::query()->where('type', PpidCategoryType::BERKALA->value)->first();
        if ($berkala) {
            $docs = [
                ['Laporan Kinerja RSUD 2025', 2025],
                ['Profil RSUD Lubuk Basung 2026', 2026],
                ['Standar Pelayanan Minimal', 2025],
            ];
            foreach ($docs as [$title, $year]) {
                PpidDocument::query()->updateOrCreate(
                    ['title' => $title],
                    [
                        'category_id' => $berkala->id,
                        'file_path' => 'ppid/demo-'.Str::slug($title).'.pdf',
                        'year' => $year,
                        'published_at' => now()->subDays(30),
                    ],
                );
            }
        }
    }

    private function seedGalleries(): void
    {
        $galleries = [
            ['Fasilitas Gedung Baru', 'fasilitas-gedung-baru', 'Tour fasilitas gedung baru RSUD.', GalleryType::PHOTO],
            ['Bakti Sosial Donor Darah', 'bakti-sosial-donor-darah', 'Dokumentasi bakti sosial donor darah PMI.', GalleryType::PHOTO],
            ['Workshop Tenaga Medis', 'workshop-tenaga-medis', 'Kegiatan pelatihan tenaga medis berkala.', GalleryType::PHOTO],
        ];
        foreach ($galleries as [$title, $slug, $desc, $type]) {
            /** @var Gallery $gal */
            $gal = Gallery::query()->updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'description' => $desc, 'type' => $type->value],
            );

            // Tambah 3 media demo per galeri (Unsplash placeholder)
            $images = [
                'photo-1538108149393-fbbd81895907',
                'photo-1551076805-e1869033e561',
                'photo-1579684385127-1ef15d508118',
                'photo-1631815589968-fdb09a223b1e',
                'photo-1576091160550-2173dba999ef',
            ];
            foreach (array_slice($images, 0, 3) as $i => $imgId) {
                Media::query()->updateOrCreate(
                    [
                        'mediable_type' => Gallery::class,
                        'mediable_id' => $gal->id,
                        'sort_order' => $i,
                    ],
                    [
                        'disk' => 'public',
                        'path' => "https://images.unsplash.com/{$imgId}?w=1200&q=80",
                        'mime' => 'image/jpeg',
                        'size' => 0,
                        'caption' => $title.' #'.($i + 1),
                    ],
                );
            }
        }
    }
}

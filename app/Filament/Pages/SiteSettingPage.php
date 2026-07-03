<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\SiteSettingService;
use App\Support\SiteContent;
use App\Support\UiIcons;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Halaman Site Settings (custom Filament page).
 *
 * Validates: Requirements 12.1, 26.1–26.3.
 */
class SiteSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Situs';

    protected static string $view = 'filament.pages.site-setting';

    /** @var array<string,mixed> */
    public array $data = [];

    public function mount(): void
    {
        /** @var SiteSettingService $svc */
        $svc = app(SiteSettingService::class);
        $this->form->fill([
            'rs_name' => $svc->get('rs_name'),
            'rs_description' => $svc->get('rs_description'),
            'logo' => $svc->get('logo'),
            'address' => $svc->get('address'),
            'phone' => $svc->get('phone'),
            'emergency_phone' => $svc->get('emergency_phone'),
            'igd_active' => (bool) $svc->get('igd_active', true),
            'ppid_active' => (bool) $svc->get('ppid_active', true),
            'karir_active' => (bool) $svc->get('karir_active', true),
            'home_show_quick_actions' => (bool) $svc->get('home_show_quick_actions', true),
            'home_show_trust' => (bool) $svc->get('home_show_trust', true),
            'email' => $svc->get('email'),
            'operational_hours' => $svc->get('operational_hours'),
            'registration_url' => $svc->get('registration_url'),
            'latitude' => $svc->get('latitude'),
            'longitude' => $svc->get('longitude'),
            'social_facebook' => $svc->get('social_facebook'),
            'social_instagram' => $svc->get('social_instagram'),
            'social_youtube' => $svc->get('social_youtube'),
            'og_image' => $svc->get('og_image'),
            'theme_color' => $svc->get('theme_color', 'sky'),
            // Konten beranda & branding (editable)
            'footer_tagline' => $svc->get('footer_tagline', SiteContent::text('footer_tagline')),
            'header_subtitle' => $svc->get('header_subtitle', ''),
            'home_services_eyebrow' => $svc->get('home_services_eyebrow', SiteContent::text('home_services_eyebrow')),
            'home_services_heading' => $svc->get('home_services_heading', SiteContent::text('home_services_heading')),
            'home_services_subheading' => $svc->get('home_services_subheading', SiteContent::text('home_services_subheading')),
            'home_schedule_heading' => $svc->get('home_schedule_heading', SiteContent::text('home_schedule_heading')),
            'home_schedule_subheading' => $svc->get('home_schedule_subheading', SiteContent::text('home_schedule_subheading')),
            'home_news_heading' => $svc->get('home_news_heading', SiteContent::text('home_news_heading')),
            'home_news_subheading' => $svc->get('home_news_subheading', SiteContent::text('home_news_subheading')),
            'home_complaint_heading' => $svc->get('home_complaint_heading', SiteContent::text('home_complaint_heading')),
            'home_complaint_text' => $svc->get('home_complaint_text', SiteContent::text('home_complaint_text')),
            'home_about_eyebrow' => $svc->get('home_about_eyebrow', SiteContent::text('home_about_eyebrow')),
            'home_facilities_eyebrow' => $svc->get('home_facilities_eyebrow', SiteContent::text('home_facilities_eyebrow')),
            'home_facilities_heading' => $svc->get('home_facilities_heading', SiteContent::text('home_facilities_heading')),
            'home_facilities_subheading' => $svc->get('home_facilities_subheading', SiteContent::text('home_facilities_subheading')),
            'home_gallery_eyebrow' => $svc->get('home_gallery_eyebrow', SiteContent::text('home_gallery_eyebrow')),
            'home_gallery_heading' => $svc->get('home_gallery_heading', SiteContent::text('home_gallery_heading')),
            'home_gallery_subheading' => $svc->get('home_gallery_subheading', SiteContent::text('home_gallery_subheading')),
            'home_contact_heading' => $svc->get('home_contact_heading', SiteContent::text('home_contact_heading')),
            'home_contact_text' => $svc->get('home_contact_text', SiteContent::text('home_contact_text')),
            'home_quick_actions' => $this->asList($svc->get('home_quick_actions'), SiteContent::quickActions()),
            'home_trust_badges' => $this->asList($svc->get('home_trust_badges'), SiteContent::trustBadges()),
            'home_about_heading' => $svc->get('home_about_heading', ''),
            'home_about_text' => $svc->get('home_about_text', ''),
            'home_about_image' => $svc->get('home_about_image'),
            'home_about_highlights' => $this->asList($svc->get('home_about_highlights'), SiteContent::aboutHighlights()),
            'home_facilities' => $this->asList($svc->get('home_facilities'), SiteContent::facilities()),
        ]);
    }

    /**
     * Kembalikan list array dari setting bila valid, selain itu default.
     *
     * @param  mixed  $value
     * @param  array<int,array<string,mixed>>  $default
     * @return array<int,array<string,mixed>>
     */
    private function asList(mixed $value, array $default): array
    {
        return is_array($value) && $value !== [] ? array_values($value) : $default;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas')->schema([
                    Forms\Components\TextInput::make('rs_name')->label('Nama RS')->required()->maxLength(120),
                    Forms\Components\Textarea::make('rs_description')->label('Deskripsi singkat')->rows(2)->maxLength(300),
                    Forms\Components\FileUpload::make('logo')->image()->disk('public')->directory('site'),
                    Forms\Components\FileUpload::make('og_image')->image()->disk('public')->directory('site')->label('Open Graph Image'),
                ])->columns(2),

                Forms\Components\Section::make('Kontak')->schema([
                    Forms\Components\Textarea::make('address')->rows(2),
                    Forms\Components\TextInput::make('phone')->tel(),
                    Forms\Components\TextInput::make('emergency_phone')->label('Nomor IGD (Gawat Darurat)')
                        ->maxLength(60)
                        ->helperText('Tampil sebagai badge "IGD 24 Jam" di header. Boleh berisi keterangan, mis. "0752-1234567 (IGD)".'),
                    Forms\Components\Toggle::make('igd_active')->label('Tampilkan badge IGD 24 Jam')
                        ->helperText('Matikan untuk menyembunyikan badge IGD merah di header.')
                        ->default(true),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('operational_hours')->label('Jam Operasional'),
                    Forms\Components\TextInput::make('registration_url')->label('URL Pendaftaran Online')->url()
                        ->helperText('Tombol "Pendaftaran Online" di header mengarah ke URL ini.'),
                ])->columns(2),

                Forms\Components\Section::make('Lokasi')->schema([
                    Forms\Components\TextInput::make('latitude')->numeric(),
                    Forms\Components\TextInput::make('longitude')->numeric(),
                ])->columns(2),

                Forms\Components\Section::make('Sosial Media')->schema([
                    Forms\Components\TextInput::make('social_facebook')->url(),
                    Forms\Components\TextInput::make('social_instagram')->url(),
                    Forms\Components\TextInput::make('social_youtube')->url(),
                ])->columns(2),

                Forms\Components\Section::make('Tampilan')->schema([
                    Forms\Components\Select::make('theme_color')
                        ->label('Warna Tema')
                        ->options([
                            'kemenkes' => '🏥 Hijau Kemenkes',
                            'emerald' => '🟢 Hijau (Emerald)',
                            'blue' => '🔵 Biru',
                            'sky' => '🩵 Biru Langit',
                            'teal' => '🌊 Teal',
                            'indigo' => '🟣 Indigo',
                            'violet' => '💜 Violet',
                            'rose' => '🌹 Rose',
                            'amber' => '🟡 Amber',
                            'orange' => '🟠 Oranye',
                            'red' => '🔴 Merah',
                            'slate' => '⚫ Slate',
                        ])
                        ->default('kemenkes')
                        ->helperText('Warna utama yang diterapkan di seluruh situs dan panel admin.'),
                ])->columns(1),

                Forms\Components\Section::make('Menu Header')->schema([
                    Forms\Components\Toggle::make('ppid_active')->label('Tampilkan menu PPID')->default(true),
                ])->columns(2),

                Forms\Components\Section::make('Branding & Footer')->schema([
                    Forms\Components\TextInput::make('header_subtitle')
                        ->label('Subjudul Header (di bawah nama RS)')
                        ->maxLength(120)
                        ->helperText('Kosongkan untuk menyembunyikan subjudul di header.'),
                    Forms\Components\TextInput::make('footer_tagline')
                        ->label('Tagline (header hero & footer)')
                        ->maxLength(120),
                ])->columns(1),

                Forms\Components\Section::make('Teks Beranda')
                    ->description('Judul & deskripsi tiap bagian di halaman beranda.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('home_services_eyebrow')->label('Layanan — label kecil'),
                        Forms\Components\TextInput::make('home_services_heading')->label('Layanan — judul'),
                        Forms\Components\Textarea::make('home_services_subheading')->label('Layanan — deskripsi')->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('home_schedule_heading')->label('Jadwal — judul')
                            ->helperText('Nama hari otomatis ditambahkan di belakang.'),
                        Forms\Components\TextInput::make('home_schedule_subheading')->label('Jadwal — deskripsi'),
                        Forms\Components\TextInput::make('home_news_heading')->label('Berita — judul'),
                        Forms\Components\TextInput::make('home_news_subheading')->label('Berita — deskripsi'),
                        Forms\Components\TextInput::make('home_complaint_heading')->label('Pengaduan — judul'),
                        Forms\Components\Textarea::make('home_complaint_text')->label('Pengaduan — deskripsi')->rows(3)->columnSpanFull(),
                        Forms\Components\TextInput::make('home_about_eyebrow')->label('Tentang — label kecil'),
                        Forms\Components\TextInput::make('home_facilities_eyebrow')->label('Fasilitas — label kecil'),
                        Forms\Components\TextInput::make('home_facilities_heading')->label('Fasilitas — judul'),
                        Forms\Components\Textarea::make('home_facilities_subheading')->label('Fasilitas — deskripsi')->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('home_gallery_eyebrow')->label('Galeri — label kecil'),
                        Forms\Components\TextInput::make('home_gallery_heading')->label('Galeri — judul'),
                        Forms\Components\TextInput::make('home_gallery_subheading')->label('Galeri — deskripsi'),
                        Forms\Components\TextInput::make('home_contact_heading')->label('Band Kontak — judul'),
                        Forms\Components\Textarea::make('home_contact_text')->label('Band Kontak — deskripsi')->rows(2)->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Bagian Beranda')
                    ->description('Aktif/nonaktifkan bagian opsional pada halaman beranda.')
                    ->schema([
                        Forms\Components\Toggle::make('home_show_quick_actions')->label('Tampilkan Akses Cepat (tombol pintasan)')->default(true),
                        Forms\Components\Toggle::make('home_show_trust')->label('Tampilkan Keunggulan (badge/logo)')->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Tentang (Beranda)')
                    ->description('Bagian "Tentang" di beranda.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('home_about_heading')->label('Judul')
                            ->helperText('Kosongkan untuk memakai "Tentang {nama RS}".'),
                        Forms\Components\Textarea::make('home_about_text')->label('Deskripsi')->rows(4)
                            ->helperText('Kosongkan untuk memakai Deskripsi singkat RS.')->columnSpanFull(),
                        Forms\Components\FileUpload::make('home_about_image')->label('Gambar')
                            ->image()->disk('public')->directory('home')->imageEditor()->maxSize(2048)
                            ->helperText('Foto gedung/suasana RS. Kosong = gambar default.'),
                        Forms\Components\Repeater::make('home_about_highlights')->label('Poin Unggulan')
                            ->schema([
                                Forms\Components\TextInput::make('text')->required()->label('Teks'),
                            ])
                            ->reorderable()->collapsible()->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['text'] ?? null),
                    ]),

                Forms\Components\Section::make('Fasilitas & Penunjang (Beranda)')
                    ->description('Daftar fasilitas/penunjang medis yang tampil di beranda.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('home_facilities')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('name')->required()->label('Nama'),
                                Forms\Components\Select::make('icon')->options(UiIcons::options())->default('first-aid')->required()->searchable()->native(false),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Aksi Cepat (Beranda)')
                    ->description('Empat tombol pintasan di bawah header. Kosongkan untuk memakai default.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('home_quick_actions')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('label')->required(),
                                Forms\Components\TextInput::make('description'),
                                Forms\Components\TextInput::make('url')->required()
                                    ->helperText('URL internal (mis. /tarif) atau eksternal (https://...).'),
                                Forms\Components\Select::make('icon')->options(UiIcons::options())->default('star')->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Keunggulan (Beranda)')
                    ->description('Badge kepercayaan (akreditasi, BPJS, dll).')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('home_trust_badges')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('label')->required(),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('trust')
                                    ->maxSize(1024)
                                    ->label('Logo/Gambar (opsional)')
                                    ->helperText('Mis. logo BPJS. Jika diisi, gambar dipakai menggantikan ikon.'),
                                Forms\Components\Select::make('icon')->options(UiIcons::options())->default('star')
                                    ->helperText('Dipakai bila tidak mengunggah gambar.'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        /** @var SiteSettingService $svc */
        $svc = app(SiteSettingService::class);
        foreach ($this->form->getState() as $key => $value) {
            $svc->set((string) $key, $value);
        }

        Notification::make()->title('Pengaturan tersimpan')->success()->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user !== null && method_exists($user, 'can') && $user->can('setting.update');
    }
}

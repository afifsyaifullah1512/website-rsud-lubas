<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NavItemResource\Pages;
use App\Models\NavItem;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource pengelolaan menu navigasi publik.
 *
 * UX automation:
 *  - Pilih "Tipe Tujuan" → URL otomatis terisi:
 *      • Halaman Bawaan: pilih route name → URL ke route()
 *      • Halaman Custom: pilih Page → URL ke /halaman/{slug}
 *      • URL Eksternal: ketik URL bebas (auto opens_new_tab=true)
 *      • Hanya Header (parent dropdown tanpa link): URL = #
 */
class NavItemResource extends Resource
{
    protected static ?string $model = NavItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $modelLabel = 'Menu Navigasi';

    protected static ?string $pluralModelLabel = 'Menu Navigasi';

    /**
     * Map route name → label untuk pilihan "Halaman Bawaan".
     *
     * @return array<string,string>
     */
    private static function builtInRoutes(): array
    {
        return [
            'home' => 'Beranda',
            'profil.sejarah' => 'Profil — Sejarah',
            'profil.visi-misi' => 'Profil — Visi & Misi',
            'profil.struktur' => 'Profil — Struktur Organisasi',
            'profil.direktur' => 'Profil — Sambutan Direktur',
            'layanan.index' => 'Layanan',
            'jadwal' => 'Jadwal Dokter',
            'berita.index' => 'Berita',
            'galeri.index' => 'Galeri',
            'pendaftaran' => 'Pendaftaran',
            'ppid.index' => 'PPID',
            'pengaduan.create' => 'Pengaduan',
            'kontak' => 'Kontak',
            'faq' => 'FAQ',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->required()->maxLength(120)
                ->helperText('Teks yang ditampilkan di navbar.'),

            Forms\Components\Select::make('parent_id')
                ->label('Parent (untuk dropdown)')
                ->relationship(
                    name: 'parent',
                    titleAttribute: 'label',
                    modifyQueryUsing: fn ($query) => $query->whereNull('parent_id'),
                )
                ->searchable()
                ->preload()
                ->placeholder('— Item utama (tanpa parent) —')
                ->helperText('Kosongkan untuk menu utama. Pilih item lain untuk membuat dropdown anak.'),

            // Virtual: tipe tujuan menu. Tidak di-store ke DB; hanya untuk
            // auto-fill kolom `url`.
            Forms\Components\Select::make('target_type')
                ->label('Tipe Tujuan')
                ->options([
                    'route' => 'Halaman Bawaan',
                    'page' => 'Halaman Custom (yang sudah dibuat)',
                    'external' => 'URL Eksternal',
                    'parent' => 'Hanya Header (untuk dropdown)',
                ])
                ->default('route')
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function (Set $set, ?string $state, ?NavItem $record) {
                    // Saat edit: tebak tipe dari url eksisting.
                    if (! $record) {
                        return;
                    }
                    $url = $record->url ?? '';
                    if ($url === '#') {
                        $set('target_type', 'parent');
                    } elseif (str_starts_with($url, 'http')) {
                        $set('target_type', 'external');
                    } elseif (str_starts_with($url, '/halaman/')) {
                        $set('target_type', 'page');
                    } else {
                        $set('target_type', 'route');
                    }
                })
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state === 'parent') {
                        $set('url', '#');
                    }
                })
                ->required(),

            // Pilih route bawaan
            Forms\Components\Select::make('target_route')
                ->label('Pilih Halaman')
                ->options(self::builtInRoutes())
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function (Set $set, ?string $state, ?NavItem $record) {
                    if (! $record || ! $record->url) {
                        return;
                    }
                    foreach (self::builtInRoutes() as $name => $_label) {
                        try {
                            if (route($name, [], false) === $record->url || route($name) === $record->url) {
                                $set('target_route', $name);
                                return;
                            }
                        } catch (\Throwable) {
                        }
                    }
                })
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state) {
                        try {
                            $set('url', route($state, [], false));
                            $set('label_suggest', self::builtInRoutes()[$state] ?? '');
                        } catch (\Throwable) {
                            $set('url', '/'.$state);
                        }
                    }
                })
                ->visible(fn (Get $get) => $get('target_type') === 'route'),

            // Pilih Page custom
            Forms\Components\Select::make('target_page')
                ->label('Pilih Halaman Custom')
                ->options(fn () => Page::query()->orderBy('title')->pluck('title', 'slug')->all())
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function (Set $set, ?string $state, ?NavItem $record) {
                    if (! $record || ! str_starts_with($record->url ?? '', '/halaman/')) {
                        return;
                    }
                    $set('target_page', substr($record->url, strlen('/halaman/')));
                })
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state) {
                        $set('url', '/halaman/'.$state);
                    }
                })
                ->createOptionForm([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('slug', \Illuminate\Support\Str::slug((string) $state));
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->regex('/^[a-z0-9-]+$/')
                        ->unique('pages', 'slug')
                        ->helperText('Otomatis dari judul. Hanya huruf kecil, angka, dash.'),
                    Forms\Components\RichEditor::make('body')
                        ->required()
                        ->columnSpanFull()
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('pages')
                        ->fileAttachmentsVisibility('public'),
                ])
                ->createOptionUsing(function (array $data): string {
                    \App\Models\Page::query()->create($data);
                    return $data['slug'];
                })
                ->visible(fn (Get $get) => $get('target_type') === 'page')
                ->helperText('Pilih halaman yang sudah dibuat di /admin/pages, atau buat baru langsung dari sini.'),

            // URL eksternal
            Forms\Components\TextInput::make('url_external')
                ->label('URL Eksternal')
                ->url()
                ->placeholder('https://...')
                ->live(onBlur: true)
                ->dehydrated(false)
                ->afterStateHydrated(function (Set $set, ?string $state, ?NavItem $record) {
                    if (! $record) {
                        return;
                    }
                    if (str_starts_with($record->url ?? '', 'http')) {
                        $set('url_external', $record->url);
                    }
                })
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state) {
                        $set('url', $state);
                        $set('opens_new_tab', true);
                    }
                })
                ->visible(fn (Get $get) => $get('target_type') === 'external'),

            // URL final — terisi otomatis dari pilihan di atas
            Forms\Components\TextInput::make('url')
                ->label('URL (otomatis)')
                ->maxLength(255)
                ->helperText('Terisi otomatis saat memilih tipe tujuan di atas.')
                ->dehydrateStateUsing(function ($state, Get $get) {
                    if ($get('target_type') === 'parent') {
                        return '#';
                    }
                    if (! empty($state)) {
                        return $state;
                    }
                    // Fallback: derive dari field virtual jika $set tidak sync
                    if ($get('target_type') === 'route' && $get('target_route')) {
                        try {
                            return route($get('target_route'), [], false);
                        } catch (\Throwable) {
                        }
                    }
                    if ($get('target_type') === 'page' && $get('target_page')) {
                        return '/halaman/' . $get('target_page');
                    }
                    if ($get('target_type') === 'external' && $get('url_external')) {
                        return $get('url_external');
                    }
                    return $state ?: '#';
                }),

            Forms\Components\TextInput::make('sort_order')
                ->numeric()->default(0)
                ->helperText('Urutan tampil; nilai lebih kecil tampil duluan.'),

            Forms\Components\Toggle::make('opens_new_tab')
                ->label('Buka di tab baru')
                ->helperText('Otomatis aktif untuk URL eksternal.'),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->description(fn (NavItem $record) => $record->parent?->label ? '↳ child of '.$record->parent->label : null),
                Tables\Columns\TextColumn::make('url')->limit(50)->copyable(),
                Tables\Columns\IconColumn::make('opens_new_tab')->boolean()->label('Tab baru'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\Filter::make('roots_only')
                    ->label('Hanya menu utama')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereNull('parent_id')),
            ])
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNavItems::route('/')];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Halaman';

    protected static ?string $pluralModelLabel = 'Halaman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, callable $get, ?string $operation): void {
                    // Auto-generate slug saat create, atau saat edit jika slug masih kosong.
                    if ($operation === 'create' || empty($get('slug'))) {
                        $set('slug', \Illuminate\Support\Str::slug((string) $state));
                    }
                }),
            Forms\Components\TextInput::make('slug')
                ->required()->maxLength(120)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true)
                ->helperText('Otomatis dari judul. Halaman dapat diakses di /halaman/{slug}. Hanya huruf kecil, angka, dan dash.'),
            Forms\Components\RichEditor::make('body')
                ->required()
                ->columnSpanFull()
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('pages')
                ->fileAttachmentsVisibility('public'),

            Forms\Components\Repeater::make('media')
                ->relationship('media')
                ->schema([
                    Forms\Components\FileUpload::make('path')
                        ->label('File PDF')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480)
                        ->disk('public')
                        ->directory('pages/pdf')
                        ->downloadable()
                        ->previewable(false)
                        ->required(),
                    Forms\Components\Hidden::make('disk')->default('public'),
                ])
                ->orderColumn('sort_order')
                ->defaultItems(0)
                ->addActionLabel('Tambah PDF')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => empty($state['path']) ? null : basename(is_array($state['path']) ? (string) reset($state['path']) : (string) $state['path'])),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('slug')->searchable(),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('updated_at')->since(),
        ])
            ->defaultSort('slug')
            ->actions([
                Tables\Actions\EditAction::make()->mutateFormDataUsing(fn (array $data): array => static::mutateFormData($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * Sanitasi body HTML dengan HTMLPurifier sebelum disimpan (Requirement 13.1, 16.1).
     *
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    public static function mutateFormData(array $data): array
    {
        if (isset($data['body']) && is_string($data['body'])) {
            $data['body'] = self::stripAttachmentChrome($data['body']);
            $data['body'] = \Mews\Purifier\Facades\Purifier::clean($data['body']);
        }

        return $data;
    }

    /**
     * Hilangkan "kartu attachment" RichEditor: anchor yang membungkus
     * <img> diikuti <span> nama-file & ukuran. Sisakan hanya <img> agar
     * tidak ada teks nama file/ukuran yang tampil di halaman publik.
     */
    public static function stripAttachmentChrome(string $html): string
    {
        // <a ...><img ...><span>nama.png</span><span>110 KB</span></a>  =>  <img ...>
        $html = (string) preg_replace(
            '#<a\b[^>]*>\s*(<img\b[^>]*>)\s*(?:<span\b[^>]*>.*?</span>\s*)+</a>#is',
            '$1',
            $html
        );

        // Bersihkan sisa atribut alt yang berisi nama file acak (opsional, kosmetik).
        return $html;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePages::route('/')];
    }
}

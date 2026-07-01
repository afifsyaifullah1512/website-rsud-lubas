<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Support\Enums\NewsStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

/**
 * Filament resource untuk {@see News}. Validates: Requirements 19.1–19.5.
 */
class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, ?string $operation): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),
            Forms\Components\TextInput::make('slug')
                ->required()->maxLength(180)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required()->searchable()->preload(),
            Forms\Components\Textarea::make('excerpt')->rows(3)->maxLength(500),
            Forms\Components\RichEditor::make('body')
                ->required()
                ->minLength(50)
                ->columnSpanFull(),
            Forms\Components\FileUpload::make('cover_image')
                ->image()
                ->maxSize(2048)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->helperText('Format jpg/jpeg/png/webp, maksimal 2MB.')
                ->directory('news')
                ->disk('public'),
            Forms\Components\Select::make('status')
                ->options(function (?News $record): array {
                    $options = NewsStatus::optionsId();

                    // Req 19.3: sembunyikan opsi PUBLISHED dari user tanpa
                    // permission `news.publish` (kecuali record memang sudah
                    // berstatus PUBLISHED agar nilai tetap valid saat edit).
                    if (! Auth::user()?->can('news.publish')
                        && $record?->status !== NewsStatus::PUBLISHED) {
                        unset($options[NewsStatus::PUBLISHED->value]);
                    }

                    return $options;
                })
                ->default(NewsStatus::DRAFT->value)
                ->required()
                ->live(),
            Forms\Components\DateTimePicker::make('published_at')
                ->required(fn (callable $get) => $get('status') === NewsStatus::PUBLISHED->value)
                ->visible(fn (callable $get) => in_array($get('status'), [NewsStatus::PUBLISHED->value, NewsStatus::DRAFT->value], true)),
        ]);
    }

    /**
     * Sanitasi konten kaya `body` via HTMLPurifier dan tegakkan gerbang
     * permission `news.publish` di sisi server.
     *
     * Dipanggil dari `mutateFormDataUsing` pada aksi create/edit
     * (ManageRecords) sehingga berlaku untuk seluruh jalur penyimpanan.
     *
     * Validates: Requirements 19.2, 19.3, 30.2.
     *
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    public static function mutateNewsData(array $data, ?News $record = null): array
    {
        if (isset($data['body'])) {
            $data['body'] = Purifier::clean($data['body']);
        }

        $becomesPublished = ($data['status'] ?? null) === NewsStatus::PUBLISHED->value
            && $record?->status !== NewsStatus::PUBLISHED;

        // Req 19.3: ubah status menjadi PUBLISHED wajib permission `news.publish`.
        if ($becomesPublished && ! Auth::user()?->can('news.publish')) {
            abort(403, 'Anda tidak memiliki izin untuk memublikasikan berita.');
        }

        // Req 19.2: publikasi langsung wajib mengisi published_at.
        if (($data['status'] ?? null) === NewsStatus::PUBLISHED->value && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->square(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(60),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->since(),
                Tables\Columns\TextColumn::make('views')->numeric()->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(NewsStatus::optionsId()),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data, News $record): array => static::mutateNewsData($data, $record)),
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (News $record) => $record->status !== NewsStatus::PUBLISHED)
                    ->authorize('publish')
                    ->requiresConfirmation()
                    ->action(function (News $record): void {
                        $record->status = NewsStatus::PUBLISHED;
                        $record->published_at = $record->published_at ?? now();
                        $record->save();
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNews::route('/'),
        ];
    }
}

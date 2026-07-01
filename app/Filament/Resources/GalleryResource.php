<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Jobs\GenerateImageVariantsJob;
use App\Models\Gallery;
use App\Support\Enums\GalleryType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resource Gallery + media manager. Validates Requirement 20.1, 20.2, 20.3.
 *
 * Media dikelola lewat Filament Repeater pada relasi polymorphic `media`.
 * MIME divalidasi sesuai tipe galeri (PHOTO: image/jpeg|png|webp,
 * VIDEO: video mime atau URL eksternal). Setelah simpan, varian gambar
 * (thumbnail 400px + main 1200px) di-generate via {@see GenerateImageVariantsJob}.
 */
class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Galeri';

    protected static ?string $pluralModelLabel = 'Galeri';

    /** MIME yang diizinkan untuk galeri foto (Req 20.2). */
    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** MIME yang diizinkan untuk galeri video (Req 20.2). */
    private const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/ogg'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(160)
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
            Forms\Components\Select::make('type')
                ->options(GalleryType::optionsId())
                ->default(GalleryType::PHOTO->value)
                ->required()
                ->live(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),

            Forms\Components\Repeater::make('media')
                ->label('Media')
                ->relationship()
                ->schema([
                    Forms\Components\FileUpload::make('path')
                        ->label('Berkas')
                        ->disk('public')
                        ->directory('galleries')
                        ->imagePreviewHeight('120')
                        ->acceptedFileTypes(fn (callable $get): array => $get('../../type') === GalleryType::VIDEO->value
                            ? self::VIDEO_MIMES
                            : self::PHOTO_MIMES)
                        ->maxSize(fn (callable $get): int => $get('../../type') === GalleryType::VIDEO->value
                            ? 51200
                            : 4096)
                        ->required(fn (callable $get): bool => blank($get('external_url'))),
                    Forms\Components\TextInput::make('external_url')
                        ->label('URL Eksternal (mis. YouTube)')
                        ->url()
                        ->visible(fn (callable $get): bool => $get('../../type') === GalleryType::VIDEO->value)
                        ->required(fn (callable $get): bool => blank($get('path')))
                        ->dehydrated(false)
                        ->maxLength(500),
                    Forms\Components\TextInput::make('caption')->maxLength(200),
                    Forms\Components\Hidden::make('disk')->default('public'),
                ])
                ->orderColumn('sort_order')
                ->reorderable()
                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::prepareMediaData($data))
                ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::prepareMediaData($data))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('media_count')->counts('media')->label('Item'),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(),
            ])
            ->defaultSort('title')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(GalleryType::optionsId()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn (Gallery $record) => self::dispatchVariantJobs($record)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * Normalisasi data media sebelum disimpan: isi mime/size dari berkas
     * terupload, atau petakan URL eksternal ke kolom path (Req 20.2).
     *
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    protected static function prepareMediaData(array $data): array
    {
        $externalUrl = $data['external_url'] ?? null;
        unset($data['external_url']);

        if (is_string($externalUrl) && $externalUrl !== '') {
            $data['path'] = $externalUrl;
            $data['disk'] = 'external';
            $data['mime'] = 'video/url';
            $data['size'] = 0;

            return $data;
        }

        $disk = is_string($data['disk'] ?? null) ? $data['disk'] : 'public';
        $path = $data['path'] ?? null;

        if (is_string($path) && $path !== '') {
            $storage = Storage::disk($disk);
            if ($storage->exists($path)) {
                $mime = $storage->mimeType($path);
                $data['mime'] = $mime !== false ? $mime : null;
                $data['size'] = $storage->size($path);
            }
        }

        $data['disk'] = $disk;

        return $data;
    }

    /**
     * Dispatch GenerateImageVariantsJob untuk setiap media bertipe gambar
     * milik galeri (thumbnail 400px + main 1200px, Req 20.3).
     */
    public static function dispatchVariantJobs(Gallery $record): void
    {
        foreach ($record->media()->get() as $media) {
            $mime = $media->mime;
            if (is_string($mime) && str_starts_with($mime, 'image/')) {
                GenerateImageVariantsJob::dispatch((int) $media->id);
            }
        }
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGalleries::route('/')];
    }
}

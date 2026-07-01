<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament resource untuk {@see HeroSlide} (hero slider beranda).
 *
 * Validates: Requirements 36.1, 36.2, 36.3, 36.4, 36.5, 36.6, 36.7.
 *
 * - Upload gambar ke disk `public` (mimes jpg/jpeg/png/webp, ≤ 2MB).
 * - Tabel reorderable berdasarkan `sort_order`; toggle `is_active` inline.
 * - Validasi CTA berpasangan (`cta_label` & `cta_url` saling requiredWith);
 *   `cta_url` harus lolos rule `url`.
 * - Authorization via {@see \App\Policies\HeroSlidePolicy} (`slider.*`).
 * - Audit log via LogsActivity (pada model) & cache beranda di-invalidate
 *   oleh {@see \App\Observers\HeroSlideObserver} pada save/delete/reorder.
 */
class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Hero Slider';

    protected static ?string $modelLabel = 'Hero Slide';

    protected static ?string $pluralModelLabel = 'Hero Slider';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image_path')
                ->label('Gambar')
                ->image()
                ->disk('public')
                ->directory('hero')
                ->maxSize(2048)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->required(fn (string $operation): bool => $operation === 'create')
                ->helperText('Format jpg/jpeg/png/webp, maksimal 2MB.')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('headline')
                ->label('Judul')
                ->maxLength(150),
            Forms\Components\TextInput::make('subheadline')
                ->label('Subjudul')
                ->maxLength(255),
            Forms\Components\TextInput::make('cta_label')
                ->label('Label CTA')
                ->maxLength(60)
                ->requiredWith('cta_url'),
            Forms\Components\TextInput::make('cta_url')
                ->label('URL CTA')
                ->url()
                ->maxLength(255)
                ->requiredWith('cta_label'),
            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan')
                ->integer()
                ->default(0)
                ->minValue(0),
            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('headline')
                    ->label('Judul')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
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
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}

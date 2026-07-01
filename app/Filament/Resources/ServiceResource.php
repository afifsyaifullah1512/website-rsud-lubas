<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\Enums\ServiceType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Filament resource untuk {@see Service}.
 *
 * Validates: Requirements 17.2, 17.4, 33.1.
 */
class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Layanan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            Forms\Components\TextInput::make('slug')
                ->required()->maxLength(220)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('type')
                ->options(ServiceType::optionsId())
                ->required(),
            Forms\Components\Select::make('polyclinic_id')
                ->relationship('polyclinic', 'name')
                ->searchable()->preload()
                ->label('Poliklinik (opsional)'),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('icon')
                ->label('Ikon')
                ->options(\App\Support\UiIcons::options())
                ->searchable()
                ->native(false)
                ->helperText('Ikon untuk kartu layanan. Kosongkan untuk memakai ikon default sesuai tipe.'),
            Forms\Components\FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('services')
                ->imageEditor()
                ->maxSize(2048)
                ->label('Gambar Layanan')
                ->helperText('Tampil pada kartu Layanan Unggulan di beranda. Format jpg/png/webp, maks 2MB.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Gambar')->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ServiceType ? $state->label() : (ServiceType::tryFrom((string) $state)?->label() ?? (string) $state)),
                Tables\Columns\TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('polyclinic.name')->label('Poliklinik')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(ServiceType::optionsId()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}

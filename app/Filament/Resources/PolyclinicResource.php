<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PolyclinicResource\Pages;
use App\Models\Polyclinic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Filament resource untuk {@see Polyclinic}.
 *
 * Validates: Requirements 17.1, 17.4, 33.1.
 */
class PolyclinicResource extends Resource
{
    protected static ?string $model = Polyclinic::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $modelLabel = 'Poliklinik';

    protected static ?string $pluralModelLabel = 'Poliklinik';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(160)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true)
                ->helperText('Hanya huruf kecil, angka, dan dash.'),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('icon')->maxLength(60)->helperText('Nama ikon Heroicons (opsional).'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Urutan'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListPolyclinics::route('/'),
            'create' => Pages\CreatePolyclinic::route('/create'),
            'edit' => Pages\EditPolyclinic::route('/{record}/edit'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Filament resource untuk {@see Doctor}. Requirement 18.1.
 */
class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Dokter & Jadwal';

    protected static ?string $modelLabel = 'Dokter';

    protected static ?string $pluralModelLabel = 'Dokter';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('polyclinic_id')
                ->relationship('polyclinic', 'name')
                ->required()->searchable()->preload(),
            Forms\Components\TextInput::make('name')
                ->required()->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            Forms\Components\TextInput::make('slug')
                ->required()->maxLength(160)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('specialization')->required()->maxLength(120),
            Forms\Components\FileUpload::make('photo')
                ->image()
                ->avatar()
                ->imageEditor()
                ->imageEditorAspectRatios(['1:1'])
                ->imageResizeMode('cover')
                ->imageResizeTargetWidth('512')
                ->imageResizeTargetHeight('512')
                ->maxSize(2048)
                ->directory('doctors')
                ->disk('public')
                ->helperText('Foto persegi (1:1). Disarankan 400×400–800×800 px. Gunakan ikon pensil untuk crop ke kotak agar tidak terpotong.'),
            Forms\Components\Textarea::make('bio')->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('specialization')->searchable(),
                Tables\Columns\TextColumn::make('polyclinic.name')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('polyclinic_id')->relationship('polyclinic', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ManageDoctors::route('/'),
        ];
    }
}

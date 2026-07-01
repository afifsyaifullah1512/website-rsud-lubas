<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\JobVacancyResource\Pages;
use App\Models\JobVacancy;
use App\Support\Enums\JobVacancyStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Resource JobVacancy. Validates Requirements 22.1–22.3.
 */
class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Karir';

    protected static ?string $modelLabel = 'Lowongan';

    protected static ?string $pluralModelLabel = 'Lowongan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            Forms\Components\TextInput::make('slug')
                ->required()->maxLength(220)
                ->regex('/^[a-z0-9-]+$/')
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')->rows(6)->required()->columnSpanFull(),
            Forms\Components\DatePicker::make('open_at')->required(),
            Forms\Components\DatePicker::make('close_at')->required()->afterOrEqual('open_at'),
            Forms\Components\Select::make('status')
                ->options(JobVacancyStatus::optionsId())
                ->default(JobVacancyStatus::OPEN->value)
                ->required(),
            Forms\Components\FileUpload::make('attachment')
                ->disk('public')
                ->directory('vacancies')
                ->acceptedFileTypes(['application/pdf']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('open_at')->date(),
                Tables\Columns\TextColumn::make('close_at')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->defaultSort('open_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(JobVacancyStatus::optionsId()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageJobVacancies::route('/')];
    }
}

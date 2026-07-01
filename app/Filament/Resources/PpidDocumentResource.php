<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PpidDocumentResource\Pages;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Support\Enums\PpidCategoryType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource PpidDocument. Validates Requirements 23.1–23.4, 32.3.
 *
 * Dokumen kategori `DIKECUALIKAN` disimpan ke disk `local` (privat).
 * Kategori lain ke disk `public`.
 */
class PpidDocumentResource extends Resource
{
    protected static ?string $model = PpidDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'PPID';

    protected static ?string $modelLabel = 'Dokumen PPID';

    protected static ?string $pluralModelLabel = 'Dokumen PPID';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required()->searchable()->preload()
                ->live(),
            Forms\Components\TextInput::make('title')->required()->maxLength(200),
            Forms\Components\TextInput::make('year')
                ->numeric()->minValue(2000)
                ->required()
                ->default((int) date('Y')),
            Forms\Components\DateTimePicker::make('published_at')
                ->helperText('Kosongkan untuk draft (tidak tampil publik).'),
            Forms\Components\FileUpload::make('file_path')
                ->disk(function (Forms\Get $get): string {
                    $categoryId = $get('category_id');
                    if (! $categoryId) {
                        return 'public';
                    }

                    // Req 23.3 / 32.3: dokumen DIKECUALIKAN disimpan ke disk privat (`local`).
                    $category = PpidCategory::find($categoryId);

                    return $category?->type === PpidCategoryType::DIKECUALIKAN
                        ? 'local'
                        : 'public';
                })
                ->directory('ppid')
                ->acceptedFileTypes([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->maxSize(10240)
                ->helperText('Format pdf/docx/xlsx, maksimal 10MB. Dokumen kategori Dikecualikan disimpan privat.')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('category.type')->badge()->label('Klasifikasi'),
                Tables\Columns\TextColumn::make('year')->sortable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePpidDocuments::route('/')];
    }
}

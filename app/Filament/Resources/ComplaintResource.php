<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Support\Enums\ComplaintStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Filament resource untuk {@see Complaint}.
 *
 * Validates: Requirements 11.3, 11.5, 15.5, 24.1–24.5, 32.1–32.2.
 */
class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Pengaduan';

    protected static ?string $modelLabel = 'Pengaduan';

    protected static ?string $pluralModelLabel = 'Pengaduan';

    /**
     * Pengaduan dibuat dari Public_Site, bukan dari panel admin.
     * Nonaktifkan pembuatan manual (resource read-only + changeStatus).
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('ticket_number')->disabled(),
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('phone')->disabled(),
            Forms\Components\TextInput::make('subject')->disabled(),
            Forms\Components\Textarea::make('message')
                ->disabled()
                ->columnSpanFull()
                ->visible(fn () => Auth::user()?->can('viewBody', Complaint::class) ?? false),
            Forms\Components\Select::make('status')
                ->options(ComplaintStatus::optionsId())
                ->disabled(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail Pengaduan')
                ->schema([
                    Infolists\Components\TextEntry::make('ticket_number')->label('Nomor Tiket'),
                    Infolists\Components\TextEntry::make('name')->label('Nama'),
                    Infolists\Components\TextEntry::make('subject')->label('Subjek'),
                    Infolists\Components\TextEntry::make('status')->label('Status')->badge(),
                    Infolists\Components\TextEntry::make('created_at')->label('Dibuat')->dateTime(),
                    Infolists\Components\TextEntry::make('message')
                        ->label('Isi Pengaduan')
                        ->columnSpanFull()
                        ->visible(fn (Complaint $record): bool => Auth::user()?->can('viewBody', $record) ?? false),
                ])
                ->columns(2),
            Infolists\Components\Section::make('Riwayat Tindak Lanjut')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('logs')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('status')->label('Status')->badge(),
                            Infolists\Components\TextEntry::make('user.name')->label('Oleh')->default('Sistem'),
                            Infolists\Components\TextEntry::make('note')->label('Catatan')->default('-'),
                            Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime(),
                        ])
                        ->columns(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('subject')->limit(40),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ComplaintStatus::optionsId()),
            ])
            ->actions([
                Tables\Actions\Action::make('changeStatus')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->authorize('respond')
                    ->hidden(fn (Complaint $record): bool => $record->status === ComplaintStatus::CLOSED)
                    ->form(fn (Complaint $record): array => [
                        Forms\Components\Select::make('next')
                            ->label('Status Baru')
                            ->options(function () use ($record): array {
                                $allowed = $record->status->allowedNext();

                                return collect($allowed)
                                    ->mapWithKeys(fn (ComplaintStatus $s) => [$s->value => $s->label()])
                                    ->all();
                            })
                            ->required(),
                        Forms\Components\Textarea::make('note')->label('Catatan')->rows(3),
                    ])
                    ->action(function (Complaint $record, array $data): void {
                        try {
                            app(ComplaintService::class)->changeStatus(
                                $record,
                                ComplaintStatus::from($data['next']),
                                $data['note'] ?? null,
                                Auth::user(),
                            );
                            Notification::make()
                                ->title('Status berhasil diperbarui')
                                ->success()->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Transisi tidak valid')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageComplaints::route('/'),
        ];
    }
}

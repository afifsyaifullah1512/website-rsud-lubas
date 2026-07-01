<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Tabel "Pengaduan terbaru" — hanya tampil untuk role `petugas-pengaduan` /
 * `super-admin` (Requirement 15.5, 24.1).
 */
class LatestComplaints extends BaseWidget
{
    protected static ?string $heading = 'Pengaduan Terbaru';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }
        return method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['petugas-pengaduan', 'super-admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Complaint::query()
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->searchable(),
                Tables\Columns\TextColumn::make('subject')->limit(40),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
            ]);
    }
}

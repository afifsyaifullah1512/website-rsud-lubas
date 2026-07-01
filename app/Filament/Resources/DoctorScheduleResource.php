<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorScheduleResource\Pages;
use App\Models\DoctorSchedule;
use App\Services\ScheduleService;
use App\Support\Enums\Day;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Filament resource untuk {@see DoctorSchedule}.
 *
 * Validates: Requirements 18.2, 18.3, 18.4, 18.5.
 *
 * Validasi server-side overlap dilakukan via `ScheduleService::checkOverlap`
 * pada custom validation rule field `end_time` (lihat `form()`), sehingga
 * berlaku konsisten baik pada Create maupun Edit (modal ManageRecords).
 * Saat Edit, record yang sedang diubah dikecualikan dari pengecekan.
 */
class DoctorScheduleResource extends Resource
{
    protected static ?string $model = DoctorSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Dokter & Jadwal';

    protected static ?string $modelLabel = 'Jadwal Dokter';

    protected static ?string $pluralModelLabel = 'Jadwal Dokter';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('doctor_id')
                ->relationship('doctor', 'name')
                ->required()->searchable()->preload()
                ->live()
                ->afterStateUpdated(function ($state, callable $set): void {
                    /** @var \App\Models\Doctor|null $doctor */
                    $doctor = \App\Models\Doctor::find($state);
                    if ($doctor) {
                        $set('polyclinic_id', $doctor->polyclinic_id);
                    }
                }),
            Forms\Components\Select::make('polyclinic_id')
                ->relationship('polyclinic', 'name')
                ->required()->searchable()->preload(),
            Forms\Components\Select::make('day')
                ->options(Day::optionsId())->required(),
            Forms\Components\TimePicker::make('start_time')
                ->seconds(false)->required(),
            Forms\Components\TimePicker::make('end_time')
                ->seconds(false)->required()
                ->after('start_time')
                ->rules([
                    fn (Get $get, ?Model $record) => function ($attribute, $value, $fail) use ($get, $record) {
                        $doctorId = (int) $get('doctor_id');
                        $day = $get('day');
                        $start = $get('start_time');
                        $end = $value;
                        // Saat edit, kecualikan record yang sedang diubah agar
                        // tidak terdeteksi "bentrok dengan dirinya sendiri".
                        $excludeId = $record?->getKey();
                        if (! $doctorId || ! $day || ! $start || ! $end) {
                            return;
                        }
                        try {
                            $overlap = app(ScheduleService::class)
                                ->checkOverlap(
                                    $doctorId,
                                    $day instanceof Day ? $day : Day::from((string) $day),
                                    (string) $start,
                                    (string) $end,
                                    $excludeId !== null ? (int) $excludeId : null,
                                );
                            if ($overlap) {
                                $fail('Jadwal bentrok dengan jadwal aktif lain pada hari & dokter yang sama.');
                            }
                        } catch (\InvalidArgumentException $e) {
                            // ScheduleService melempar ini saat start_time >= end_time.
                            $fail($e->getMessage());
                        }
                    },
                ]),
            Forms\Components\TextInput::make('note')->maxLength(255),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('polyclinic.name')->label('Poliklinik')->sortable(),
                Tables\Columns\TextColumn::make('day')->badge(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day')->options(Day::optionsId()),
                Tables\Filters\SelectFilter::make('polyclinic_id')->relationship('polyclinic', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDoctorSchedules::route('/'),
        ];
    }
}

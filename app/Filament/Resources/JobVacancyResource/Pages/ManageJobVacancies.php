<?php

namespace App\Filament\Resources\JobVacancyResource\Pages;

use App\Filament\Resources\JobVacancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJobVacancies extends ManageRecords
{
    protected static string $resource = JobVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

<?php

namespace App\Filament\Resources\PpidDocumentResource\Pages;

use App\Filament\Resources\PpidDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePpidDocuments extends ManageRecords
{
    protected static string $resource = PpidDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

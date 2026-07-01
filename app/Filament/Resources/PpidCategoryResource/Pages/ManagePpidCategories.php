<?php

namespace App\Filament\Resources\PpidCategoryResource\Pages;

use App\Filament\Resources\PpidCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePpidCategories extends ManageRecords
{
    protected static string $resource = PpidCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

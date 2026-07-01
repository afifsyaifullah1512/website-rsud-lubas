<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageNews extends ManageRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-set author = user login (Req 19.4 / 30.2).
                    $data['author_id'] = Auth::id();

                    // Sanitasi body + gerbang permission publish.
                    return NewsResource::mutateNewsData($data);
                }),
        ];
    }
}

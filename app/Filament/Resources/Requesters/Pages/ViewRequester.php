<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters\Pages;

use App\Filament\Resources\Requesters\RequesterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRequester extends ViewRecord
{
    protected static string $resource = RequesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

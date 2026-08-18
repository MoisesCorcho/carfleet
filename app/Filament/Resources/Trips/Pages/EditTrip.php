<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\Pages;

use App\Filament\Resources\Trips\TripResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

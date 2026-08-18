<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips\Pages;

use App\Filament\Driver\Resources\Trips\AssignedTripResource;
use Filament\Resources\Pages\ListRecords;

class ListAssignedTrips extends ListRecords
{
    protected static string $resource = AssignedTripResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

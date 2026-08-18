<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters\Pages;

use App\Filament\Resources\Requesters\RequesterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRequesters extends ListRecords
{
    protected static string $resource = RequesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Solicitante'),
        ];
    }
}

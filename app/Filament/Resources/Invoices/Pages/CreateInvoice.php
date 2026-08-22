<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\Pages;

use App\Actions\Invoices\GenerateInvoiceAction;
use App\DTOs\Invoices\GenerateInvoiceDTO;
use App\Exceptions\Invoices\EmptyInvoiceTripsException;
use App\Exceptions\Invoices\RequesterMismatchException;
use App\Exceptions\Invoices\TripAlreadyInvoicedException;
use App\Exceptions\Invoices\TripNotEligibleForInvoicingException;
use App\Filament\Resources\Invoices\InvoiceResource;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = GenerateInvoiceDTO::fromArray($data);
        $action = app(GenerateInvoiceAction::class);

        try {
            return $action($dto);
        } catch (TripNotEligibleForInvoicingException|TripAlreadyInvoicedException|RequesterMismatchException|EmptyInvoiceTripsException|DomainException $e) {
            Notification::make()
                ->title('Error al Generar Factura')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

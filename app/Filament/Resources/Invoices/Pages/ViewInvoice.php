<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\Pages;

use App\Actions\Invoices\CancelInvoiceAction;
use App\Actions\Invoices\MarkInvoicePaidAction;
use App\Exceptions\Invoices\InvoiceImmutableException;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Ver / Imprimir Factura (PDF)')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->url(fn (Invoice $record): string => route('invoices.pdf', $record))
                ->openUrlInNewTab(),

            Action::make('markAsPaid')
                ->label('Marcar como Pagada')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->visible(fn (Invoice $record): bool => $record->canBeMarkedPaid())
                ->requiresConfirmation()
                ->modalHeading('Confirmar Pago de Factura')
                ->modalDescription('¿Confirmas que esta factura ha sido pagada en su totalidad?')
                ->action(function (Invoice $record): void {
                    try {
                        app(MarkInvoicePaidAction::class)($record);

                        Notification::make()
                            ->title('Factura Pagada')
                            ->body("La factura {$record->invoice_number} ha sido marcada como pagada.")
                            ->success()
                            ->send();
                    } catch (InvoiceImmutableException $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('cancelInvoice')
                ->label('Anular Factura')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn (Invoice $record): bool => $record->canBeCancelled())
                ->requiresConfirmation()
                ->modalHeading('Anular Factura Comercial')
                ->modalDescription('Al anular la factura, los viajes asociados quedarán liberados para ser incluidos en una nueva factura.')
                ->schema([
                    Textarea::make('reason')
                        ->label('Motivo de Anulación')
                        ->placeholder('Explique la razón de la anulación...')
                        ->required(),
                ])
                ->action(function (Invoice $record, array $data): void {
                    try {
                        app(CancelInvoiceAction::class)($record, (string) ($data['reason'] ?? ''));

                        Notification::make()
                            ->title('Factura Anulada')
                            ->body("La factura {$record->invoice_number} fue anulada y los viajes han sido liberados.")
                            ->warning()
                            ->send();
                    } catch (InvoiceImmutableException $e) {
                        Notification::make()
                            ->title('Error al Anular')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

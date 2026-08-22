<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices;

use App\Actions\Invoices\CancelInvoiceAction;
use App\Actions\Invoices\MarkInvoicePaidAction;
use App\Enums\Invoices\InvoiceStatusEnum;
use App\Exceptions\Invoices\InvoiceImmutableException;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\RelationManagers\TripsRelationManager;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Override;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function getModelLabel(): string
    {
        return 'Factura Comercial';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Facturación y Cuentas de Cobro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Facturación';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Información del Cliente y Emisión')
                            ->description('Seleccione el cliente solicitante y la fecha de facturación.')
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('Número de Factura')
                                    ->placeholder('Autogenerado: FACT-YYYY-NNNN')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?Invoice $record): bool => $record !== null),

                                Select::make('requester_id')
                                    ->label('Cliente / Solicitante')
                                    ->prefixIcon('heroicon-m-building-office')
                                    ->options(fn (): array => Requester::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (?Invoice $record): bool => $record !== null)
                                    ->helperText('Selecciona el cliente al que se consolidarán los servicios.'),

                                DatePicker::make('issue_date')
                                    ->label('Fecha de Emisión')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->default(now()->toDateString())
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled(fn (?Invoice $record): bool => $record !== null),

                                Select::make('status')
                                    ->label('Estado')
                                    ->options(collect(InvoiceStatusEnum::cases())->mapWithKeys(
                                        fn (InvoiceStatusEnum $status): array => [$status->value => $status->label()]
                                    )->toArray())
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?Invoice $record): bool => $record !== null),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Consolidación de Viajes Cerrados')
                            ->description('Seleccione los viajes formalmente cerrados que se incluirán en la cuenta de cobro.')
                            ->schema([
                                Select::make('trip_ids')
                                    ->label('Viajes a Consolidar')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->options(function (Get $get, ?Invoice $record): array {
                                        if ($record) {
                                            return $record->trips->mapWithKeys(fn (Trip $t): array => [
                                                $t->id => "{$t->code} ({$t->origin} → {$t->destination}) — {$t->distance_traveled} km",
                                            ])->toArray();
                                        }

                                        $requesterId = (int) $get('requester_id');
                                        if ($requesterId <= 0) {
                                            return [];
                                        }

                                        return Trip::query()
                                            ->where('requester_id', $requesterId)
                                            ->eligibleForInvoicing()
                                            ->uninvoiced()
                                            ->orderByDesc('scheduled_departure_at')
                                            ->get()
                                            ->mapWithKeys(fn (Trip $t): array => [
                                                $t->id => "{$t->code} — {$t->origin} a {$t->destination} ({$t->distance_traveled} km) [Salida: ".($t->actual_departure_at?->format('d/m/Y') ?? $t->scheduled_departure_at->format('d/m/Y')).']',
                                            ])
                                            ->toArray();
                                    })
                                    ->helperText('Solo se muestran viajes en estado Cerrado que no hayan sido facturados previamente.')
                                    ->visible(fn (?Invoice $record): bool => $record === null)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->visible(fn (?Invoice $record): bool => $record === null),

                        Section::make('Parámetros de Liquidación y Tarifas')
                            ->description('Valores base aplicados para calcular los subtotales de cada servicio.')
                            ->schema([
                                TextInput::make('base_rate_per_trip')
                                    ->label('Tarifa Mínima Base por Viaje')
                                    ->prefixIcon('heroicon-m-currency-dollar')
                                    ->numeric()
                                    ->default(50000)
                                    ->suffix('COP')
                                    ->required()
                                    ->helperText('Piso mínimo liquidado para cada servicio.')
                                    ->visible(fn (?Invoice $record): bool => $record === null),

                                TextInput::make('rate_per_km')
                                    ->label('Tarifa por Kilómetro')
                                    ->prefixIcon('heroicon-m-variable')
                                    ->numeric()
                                    ->default(3500)
                                    ->suffix('COP / km')
                                    ->required()
                                    ->helperText('Valor multiplicado por la distancia recorrida en cada viaje.')
                                    ->visible(fn (?Invoice $record): bool => $record === null),

                                TextInput::make('total_amount')
                                    ->label('Monto Total Facturado')
                                    ->prefixIcon('heroicon-m-currency-dollar')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn (?int $state): string => $state !== null ? '$ '.number_format($state, 0, ',', '.').' COP' : '')
                                    ->visible(fn (?Invoice $record): bool => $record !== null),

                                Textarea::make('notes')
                                    ->label('Observaciones / Notas')
                                    ->placeholder('Instrucciones de pago, orden de compra o notas de liquidación...')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(fn (?Invoice $record): bool => $record?->isImmutable() ?? false),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Número')
                    ->badge()
                    ->color('gray')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Número de factura copiado'),

                TextColumn::make('requester.name')
                    ->label('Cliente / Empresa')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Invoice $record): string => $record->requester?->company_name ?? ''),

                TextColumn::make('issue_date')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('trips_count')
                    ->counts('trips')
                    ->label('Servicios')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Facturado')
                    ->weight(FontWeight::Bold)
                    ->color('success')
                    ->formatStateUsing(fn (int $state): string => '$ '.number_format($state, 0, ',', '.').' COP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (InvoiceStatusEnum $state): string => $state->color())
                    ->formatStateUsing(fn (InvoiceStatusEnum $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado de Factura')
                    ->options(collect(InvoiceStatusEnum::cases())->mapWithKeys(
                        fn (InvoiceStatusEnum $status): array => [$status->value => $status->label()]
                    )->toArray()),

                SelectFilter::make('requester_id')
                    ->label('Cliente')
                    ->relationship('requester', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('downloadPdf')
                        ->label('Ver / Imprimir Factura')
                        ->icon('heroicon-m-document-arrow-down')
                        ->color('info')
                        ->url(fn (Invoice $record): string => route('invoices.pdf', $record))
                        ->openUrlInNewTab(),

                    Action::make('markAsPaid')
                        ->label('Marcar Pagada')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (Invoice $record): bool => $record->canBeMarkedPaid())
                        ->requiresConfirmation()
                        ->modalHeading('Confirmar Pago')
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
                        ->modalHeading('Anular Factura')
                        ->modalDescription('Los viajes asociados quedarán liberados para ser facturados nuevamente.')
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
                                    ->body("La factura {$record->invoice_number} fue anulada.")
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
                ]),
            ])
            ->emptyStateHeading('No hay facturas registradas')
            ->emptyStateDescription('Genera una factura consolidando viajes cerrados para comenzar.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            TripsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
        ];
    }
}

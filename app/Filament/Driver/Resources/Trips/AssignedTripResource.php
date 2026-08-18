<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips;

use App\Actions\Trips\StartTripAction;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Driver\Resources\Trips\Pages\ViewAssignedTrip;
use App\Models\Trip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class AssignedTripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Mis Viajes';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getModelLabel(): string
    {
        return 'Mi Viaje';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mis Viajes';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $driverId = auth()->user()?->driver?->id ?? 0;

        return parent::getEloquentQuery()
            ->where('driver_id', $driverId);
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Detalle del Servicio Asignado')
                            ->description('Información operativa del viaje para su ejecución.')
                            ->schema([
                                TextInput::make('code')
                                    ->label('Código de Viaje')
                                    ->disabled(),

                                TextInput::make('status')
                                    ->label('Estado')
                                    ->formatStateUsing(fn ($state) => $state instanceof TripStatusEnum ? $state->label() : (string) $state)
                                    ->disabled(),

                                TextInput::make('origin')
                                    ->label('Punto de Origen')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->disabled(),

                                TextInput::make('destination')
                                    ->label('Punto de Destino')
                                    ->prefixIcon('heroicon-m-flag')
                                    ->disabled(),

                                DateTimePicker::make('scheduled_departure_at')
                                    ->label('Salida Programada')
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->native(false)
                                    ->disabled(),

                                DateTimePicker::make('actual_departure_at')
                                    ->label('Salida Real Registrada')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->native(false)
                                    ->disabled(),

                                Textarea::make('notes')
                                    ->label('Instrucciones / Observaciones')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
                    ->columns(1),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => 1,
                'lg' => 2,
            ])
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('code')
                            ->badge()
                            ->color('gray')
                            ->weight(FontWeight::Bold)
                            ->searchable(),

                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (TripStatusEnum $state): string => $state->color())
                            ->formatStateUsing(fn (TripStatusEnum $state): string => $state->label())
                            ->alignEnd(),
                    ]),

                    TextColumn::make('origin')
                        ->icon('heroicon-m-map-pin')
                        ->formatStateUsing(fn (Trip $record): string => "{$record->origin}  ➔  {$record->destination}")
                        ->weight(FontWeight::SemiBold)
                        ->searchable(),

                    Split::make([
                        TextColumn::make('vehicle.plate_number')
                            ->icon('heroicon-m-truck')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn (Trip $record): string => $record->vehicle ? "{$record->vehicle->plate_number} ({$record->vehicle->brand} {$record->vehicle->model})" : 'Sin Vehículo'),

                        TextColumn::make('scheduled_departure_at')
                            ->icon('heroicon-m-calendar')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray')
                            ->alignEnd(),
                    ]),
                ])->space(3),
            ])
            ->defaultSort('scheduled_departure_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(collect(TripStatusEnum::cases())->mapWithKeys(
                        fn (TripStatusEnum $status): array => [$status->value => $status->label()]
                    )),
            ])
            ->actions([
                Action::make('startTrip')
                    ->label('INICIAR SERVICIO')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeStarted())
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Salida de Viaje')
                    ->modalDescription('¿Confirmas que inicias el recorrido del servicio ahora?')
                    ->action(function (Trip $record): void {
                        try {
                            app(StartTripAction::class)($record);

                            Notification::make()
                                ->title('Servicio Iniciado')
                                ->body("El viaje {$record->code} ha iniciado exitosamente.")
                                ->success()
                                ->send();
                        } catch (TripImmutableException|InvalidTripStateException $e) {
                            Notification::make()
                                ->title('Error al Iniciar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make()
                    ->button()
                    ->color('gray'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignedTrips::route('/'),
            'view' => ViewAssignedTrip::route('/{record}'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips;

use App\Actions\Trips\RecordTripMileageAction;
use App\DTOs\Trips\RecordMileageDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\DriverAlreadyInTripException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Driver\Resources\Trips\Pages\ViewAssignedTrip;
use App\Models\Trip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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

                                DateTimePicker::make('actual_arrival_at')
                                    ->label('Llegada Real Registrada')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->native(false)
                                    ->disabled(),

                                Textarea::make('notes')
                                    ->label('Instrucciones / Observaciones')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Kilometraje y Rendimiento')
                            ->description('Lecturas de odómetro registradas durante el servicio.')
                            ->schema([
                                TextInput::make('initial_mileage')
                                    ->label('Kilometraje Inicial')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),

                                TextInput::make('final_mileage')
                                    ->label('Kilometraje Final')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),

                                TextInput::make('distance_traveled')
                                    ->label('Distancia Recorrida')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
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
            ->recordActions([
                Action::make('startTrip')
                    ->label('INICIAR SERVICIO')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeStarted())
                    ->modalHeading('Iniciar Salida de Viaje')
                    ->modalDescription('Ingresa la lectura inicial del odómetro y adjunta la foto de evidencia para iniciar el recorrido.')
                    ->schema([
                        TextInput::make('initial_mileage')
                            ->label('Kilometraje Inicial (Salida)')
                            ->prefixIcon('heroicon-m-calculator')
                            ->numeric()
                            ->required()
                            ->minValue(fn (Trip $record): int => $record->vehicle?->current_mileage ?? 0)
                            ->default(fn (Trip $record): ?int => $record->vehicle?->current_mileage)
                            ->helperText(fn (Trip $record): string => 'Odómetro actual del vehículo: '.number_format($record->vehicle?->current_mileage ?? 0).' km'),

                        ToggleButtons::make('photo_source')
                            ->label('Origen de la Foto')
                            ->options([
                                'camera' => 'Tomar Foto',
                                'gallery' => 'Elegir de Galería',
                            ])
                            ->icons([
                                'camera' => 'heroicon-m-camera',
                                'gallery' => 'heroicon-m-photo',
                            ])
                            ->colors([
                                'camera' => 'primary',
                                'gallery' => 'gray',
                            ])
                            ->default('camera')
                            ->inline()
                            ->live()
                            ->dehydrated(false),

                        FileUpload::make('photo_evidence')
                            ->label('Foto del Odómetro de Salida')
                            ->key(fn (Get $get): string => 'photo_evidence_start_'.($get('photo_source') ?? 'camera'))
                            ->image()
                            ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                            ->directory('evidences/odometers')
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->required()
                            ->helperText(fn (Get $get): string => ($get('photo_source') ?? 'camera') === 'camera'
                                ? 'Se abrirá la cámara de tu celular para capturar el odómetro en tiempo real.'
                                : 'Selecciona una fotografía nítida del tablero desde tu galería o archivos.'),

                        Textarea::make('notes')
                            ->label('Observaciones de Salida')
                            ->placeholder('Observaciones opcionales sobre el estado de salida...')
                            ->rows(2),
                    ])
                    ->action(function (Trip $record, array $data): void {
                        try {
                            app(RecordTripMileageAction::class)(new RecordMileageDTO(
                                tripId: $record->id,
                                mileage: (int) $data['initial_mileage'],
                                photoEvidence: $data['photo_evidence'],
                                isFinal: false,
                                notes: isset($data['notes']) ? (string) $data['notes'] : null,
                            ));

                            Notification::make()
                                ->title('Servicio Iniciado')
                                ->body("El viaje {$record->code} ha iniciado exitosamente.")
                                ->success()
                                ->send();
                        } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException|DriverAlreadyInTripException $e) {
                            Notification::make()
                                ->title('Error al Iniciar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('finishTrip')
                    ->label('FINALIZAR SERVICIO')
                    ->icon('heroicon-m-check-circle')
                    ->color('primary')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeFinished())
                    ->modalHeading('Finalizar Servicio y Registrar Llegada')
                    ->modalDescription('Ingresa la lectura final del odómetro y adjunta la foto de evidencia para completar el recorrido.')
                    ->schema([
                        TextInput::make('final_mileage')
                            ->label('Kilometraje Final (Llegada)')
                            ->prefixIcon('heroicon-m-calculator')
                            ->numeric()
                            ->required()
                            ->minValue(fn (Trip $record): int => ($record->initial_mileage ?? 0) + 1)
                            ->helperText(fn (Trip $record): string => 'Kilometraje de salida registrado: '.number_format($record->initial_mileage ?? 0).' km'),

                        ToggleButtons::make('photo_source')
                            ->label('Origen de la Foto')
                            ->options([
                                'camera' => 'Tomar Foto',
                                'gallery' => 'Elegir de Galería',
                            ])
                            ->icons([
                                'camera' => 'heroicon-m-camera',
                                'gallery' => 'heroicon-m-photo',
                            ])
                            ->colors([
                                'camera' => 'primary',
                                'gallery' => 'gray',
                            ])
                            ->default('camera')
                            ->inline()
                            ->live()
                            ->dehydrated(false),

                        FileUpload::make('photo_evidence')
                            ->label('Foto del Odómetro de Llegada')
                            ->key(fn (Get $get): string => 'photo_evidence_finish_'.($get('photo_source') ?? 'camera'))
                            ->image()
                            ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                            ->directory('evidences/odometers')
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->required()
                            ->helperText(fn (Get $get): string => ($get('photo_source') ?? 'camera') === 'camera'
                                ? 'Se abrirá la cámara de tu celular para capturar el odómetro en tiempo real.'
                                : 'Selecciona una fotografía nítida del tablero desde tu galería o archivos.'),

                        Textarea::make('notes')
                            ->label('Observaciones de Llegada')
                            ->placeholder('Observaciones opcionales de la entrega o estado final...')
                            ->rows(2),
                    ])
                    ->action(function (Trip $record, array $data): void {
                        try {
                            app(RecordTripMileageAction::class)(new RecordMileageDTO(
                                tripId: $record->id,
                                mileage: (int) $data['final_mileage'],
                                photoEvidence: $data['photo_evidence'],
                                isFinal: true,
                                notes: isset($data['notes']) ? (string) $data['notes'] : null,
                            ));

                            Notification::make()
                                ->title('Servicio Finalizado')
                                ->body("El viaje {$record->code} ha sido finalizado con éxito.")
                                ->success()
                                ->send();
                        } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException $e) {
                            Notification::make()
                                ->title('Error al Finalizar')
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

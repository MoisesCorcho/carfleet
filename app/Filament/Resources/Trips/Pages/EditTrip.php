<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\Pages;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\DriverScheduleConflictException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Filament\Resources\Trips\TripResource;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

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

    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Trip $trip */
        $trip = $record;

        if (! empty($data['scheduled_departure_at']) && ! empty($data['scheduled_arrival_at'])) {
            $departure = Carbon::parse($data['scheduled_departure_at']);
            $arrival = Carbon::parse($data['scheduled_arrival_at']);

            if ($arrival->lessThanOrEqualTo($departure)) {
                Notification::make()
                    ->title('Error en Fechas')
                    ->body('La fecha de llegada estimada debe ser posterior a la fecha de salida programada.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        try {
            $newVehicleId = ! empty($data['vehicle_id']) ? (int) $data['vehicle_id'] : null;
            $newDriverId = ! empty($data['driver_id']) ? (int) $data['driver_id'] : null;

            $trip->update([
                'requester_id' => $data['requester_id'] ?? $trip->requester_id,
                'origin' => $data['origin'] ?? $trip->origin,
                'destination' => $data['destination'] ?? $trip->destination,
                'scheduled_departure_at' => $data['scheduled_departure_at'] ?? $trip->scheduled_departure_at,
                'scheduled_arrival_at' => $data['scheduled_arrival_at'] ?? $trip->scheduled_arrival_at,
                'notes' => $data['notes'] ?? $trip->notes,
            ]);

            if ($newVehicleId && $newDriverId) {
                if (! $trip->isAssigned() || $trip->vehicle_id !== $newVehicleId || $trip->driver_id !== $newDriverId) {
                    app(AssignTripResourcesAction::class)($trip, $newVehicleId, $newDriverId);
                }
            } elseif (! $newVehicleId && ! $newDriverId && $trip->isAssigned()) {
                if ($trip->vehicle_id) {
                    $prevVehicle = Vehicle::find($trip->vehicle_id);
                    if ($prevVehicle && $prevVehicle->status === VehicleStatusEnum::ASIGNADO) {
                        $prevVehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
                    }
                }

                $trip->update([
                    'vehicle_id' => null,
                    'driver_id' => null,
                    'status' => TripStatusEnum::PROGRAMADO,
                ]);
            }

            return $trip->fresh(['requester', 'vehicle', 'driver']);
        } catch (TripImmutableException|VehicleNotAvailableException|DriverNotEligibleException|DriverScheduleConflictException $e) {
            Notification::make()
                ->title('Error al Actualizar Viaje')
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

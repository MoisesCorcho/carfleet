<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\DTOs\Trips\CreateTripDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\IncompleteTripResourcesException;
use App\Exceptions\Trips\InvalidTripDatesException;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateTripAction
{
    /**
     * Create a new scheduled trip with an atomic sequence code.
     *
     * @throws InvalidTripDatesException
     * @throws IncompleteTripResourcesException
     */
    public function __invoke(CreateTripDTO $dto): Trip
    {
        if (($dto->vehicleId && ! $dto->driverId) || (! $dto->vehicleId && $dto->driverId)) {
            throw IncompleteTripResourcesException::forPartialAssignment();
        }

        $departureAt = Carbon::parse($dto->scheduledDepartureAt);

        if ($dto->scheduledArrivalAt !== null) {
            $arrivalAt = Carbon::parse($dto->scheduledArrivalAt);
            if ($arrivalAt->lessThanOrEqualTo($departureAt)) {
                throw InvalidTripDatesException::arrivalBeforeDeparture(
                    $dto->scheduledDepartureAt,
                    $dto->scheduledArrivalAt
                );
            }
        }

        return DB::transaction(function () use ($dto): Trip {
            $year = (int) date('Y');
            $prefix = "TRIP-{$year}-";

            $latestCode = Trip::query()
                ->where('code', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $nextNumber = 1;
            if ($latestCode && preg_match('/TRIP-\d{4}-(\d+)/', $latestCode, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            $code = sprintf('TRIP-%d-%04d', $year, $nextNumber);

            $trip = Trip::create([
                'code' => $code,
                'requester_id' => $dto->requesterId,
                'origin' => $dto->origin,
                'destination' => $dto->destination,
                'scheduled_departure_at' => $dto->scheduledDepartureAt,
                'scheduled_arrival_at' => $dto->scheduledArrivalAt,
                'status' => TripStatusEnum::PROGRAMADO,
                'notes' => $dto->notes,
            ]);

            if ($dto->vehicleId && $dto->driverId) {
                app(AssignTripResourcesAction::class)($trip, $dto->vehicleId, $dto->driverId);
            }

            return $trip->fresh(['requester', 'vehicle', 'driver']);
        });
    }
}

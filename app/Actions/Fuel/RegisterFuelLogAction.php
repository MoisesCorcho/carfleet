<?php

declare(strict_types=1);

namespace App\Actions\Fuel;

use App\DTOs\Fuel\RegisterFuelLogDTO;
use App\Enums\Evidences\EvidenceTypeEnum;
use App\Exceptions\Fuel\FuelVehicleMismatchException;
use App\Exceptions\Fuel\FutureRefuelDateException;
use App\Exceptions\Fuel\InvalidFuelCostException;
use App\Exceptions\Fuel\InvalidFuelMileageException;
use App\Exceptions\Fuel\InvalidFuelQuantityException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterFuelLogAction
{
    /**
     * Register a fuel refuel log and its voucher evidence.
     *
     * @throws InvalidFuelQuantityException
     * @throws InvalidFuelCostException
     * @throws InvalidFuelMileageException
     * @throws FutureRefuelDateException
     * @throws FuelVehicleMismatchException
     * @throws TripImmutableException
     */
    public function __invoke(RegisterFuelLogDTO $dto): FuelLog
    {
        if ($dto->gallons <= 0.0) {
            throw InvalidFuelQuantityException::nonPositive($dto->gallons);
        }

        if ($dto->totalCost <= 0) {
            throw InvalidFuelCostException::nonPositive($dto->totalCost);
        }

        if ($dto->mileageAtRefuel < 0) {
            throw InvalidFuelMileageException::negativeMileage($dto->mileageAtRefuel);
        }

        $refuelDate = Carbon::parse($dto->refuelDate);
        if ($refuelDate->isFuture()) {
            throw FutureRefuelDateException::futureDate($dto->refuelDate);
        }

        return DB::transaction(function () use ($dto, $refuelDate): FuelLog {
            /** @var Vehicle $vehicle */
            $vehicle = Vehicle::where('id', $dto->vehicleId)->lockForUpdate()->firstOrFail();

            /** @var Trip|null $trip */
            $trip = null;
            $driverId = $dto->driverId;

            if ($dto->tripId !== null) {
                /** @var Trip $trip */
                $trip = Trip::where('id', $dto->tripId)->lockForUpdate()->firstOrFail();

                if ($trip->isImmutable()) {
                    throw TripImmutableException::forTrip($trip->code, $trip->status);
                }

                if ($trip->vehicle_id !== null && $trip->vehicle_id !== $vehicle->id) {
                    throw FuelVehicleMismatchException::mismatch($vehicle->id, $trip->vehicle_id);
                }

                if ($trip->initial_mileage !== null && $dto->mileageAtRefuel < $trip->initial_mileage) {
                    throw InvalidFuelMileageException::belowTripInitialMileage($trip->initial_mileage, $dto->mileageAtRefuel);
                }

                if ($driverId === null && $trip->driver_id !== null) {
                    $driverId = $trip->driver_id;
                }
            }

            $voucherPath = null;
            if ($dto->voucherPhoto instanceof UploadedFile) {
                $voucherPath = $dto->voucherPhoto->store('evidences/vouchers', 'public');
            } elseif (is_string($dto->voucherPhoto) && trim($dto->voucherPhoto) !== '') {
                $voucherPath = trim($dto->voucherPhoto);
            }

            /** @var FuelLog $fuelLog */
            $fuelLog = FuelLog::create([
                'vehicle_id' => $vehicle->id,
                'trip_id' => $trip?->id,
                'driver_id' => $driverId,
                'refuel_date' => $refuelDate,
                'mileage_at_refuel' => $dto->mileageAtRefuel,
                'gallons' => $dto->gallons,
                'total_cost' => $dto->totalCost,
                'voucher_number' => $dto->voucherNumber,
                'voucher_photo_path' => $voucherPath,
                'notes' => $dto->notes,
            ]);

            if ($trip !== null && $voucherPath !== null) {
                TripEvidence::create([
                    'trip_id' => $trip->id,
                    'type' => EvidenceTypeEnum::VOUCHER_COMBUSTIBLE,
                    'file_path' => $voucherPath,
                    'recorded_mileage' => $dto->mileageAtRefuel,
                    'notes' => $dto->voucherNumber ? "Voucher #{$dto->voucherNumber}" : $dto->notes,
                ]);
            }

            if ($dto->mileageAtRefuel > $vehicle->current_mileage) {
                $vehicle->update(['current_mileage' => $dto->mileageAtRefuel]);
            }

            return $fuelLog->fresh(['vehicle', 'trip', 'driver']);
        });
    }
}

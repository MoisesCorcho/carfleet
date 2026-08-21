<?php

declare(strict_types=1);

namespace Tests\Unit\Fuel;

use App\DTOs\Fuel\RegisterFuelLogDTO;

test('RegisterFuelLogDTO can be instantiated from array with snake_case and camelCase keys (R1, R2)', function () {
    $data = [
        'vehicle_id' => 3,
        'refuel_date' => '2026-08-20 10:30:00',
        'mileage_at_refuel' => 45000,
        'gallons' => 15.75,
        'total_cost' => 220000,
        'trip_id' => 12,
        'driver_id' => 5,
        'voucher_number' => 'V-998877',
        'voucher_photo' => 'evidences/vouchers/test.jpg',
        'notes' => 'Tanqueo completo',
    ];

    $dto = RegisterFuelLogDTO::fromArray($data);

    expect($dto->vehicleId)->toBe(3)
        ->and($dto->refuelDate)->toBe('2026-08-20 10:30:00')
        ->and($dto->mileageAtRefuel)->toBe(45000)
        ->and($dto->gallons)->toBe(15.75)
        ->and($dto->totalCost)->toBe(220000)
        ->and($dto->tripId)->toBe(12)
        ->and($dto->driverId)->toBe(5)
        ->and($dto->voucherNumber)->toBe('V-998877')
        ->and($dto->voucherPhoto)->toBe('evidences/vouchers/test.jpg')
        ->and($dto->notes)->toBe('Tanqueo completo');
});

test('RegisterFuelLogDTO handles nullable values properly', function () {
    $dto = RegisterFuelLogDTO::fromArray([
        'vehicle_id' => 1,
        'mileage_at_refuel' => 1000,
        'gallons' => 5.0,
        'total_cost' => 75000,
    ]);

    expect($dto->vehicleId)->toBe(1)
        ->and($dto->tripId)->toBeNull()
        ->and($dto->driverId)->toBeNull()
        ->and($dto->voucherNumber)->toBeNull()
        ->and($dto->voucherPhoto)->toBeNull()
        ->and($dto->notes)->toBeNull();
});

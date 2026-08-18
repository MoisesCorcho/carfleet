<?php

declare(strict_types=1);

use App\Actions\Drivers\RegisterDriverAction;
use App\Actions\Drivers\UpdateDriverAction;
use App\DTOs\Drivers\UpsertDriverDTO;
use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Exceptions\Drivers\InvalidDriverException;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('upsert driver dto maps array correctly and normalizes fields', function () {
    $dto = UpsertDriverDTO::fromArray([
        'user_id' => 42,
        'full_name' => '  Carlos Pérez  ',
        'document_type' => 'CC',
        'document_number' => '12345678',
        'phone' => ' 3001234567 ',
        'license_number' => 'lic-987654',
        'license_category' => 'C1',
        'license_expires_at' => '2028-12-31',
        'status' => 'activo',
    ]);

    expect($dto->userId)->toBe(42)
        ->and($dto->fullName)->toBe('Carlos Pérez')
        ->and($dto->documentType)->toBe(DocumentTypeEnum::CC)
        ->and($dto->documentNumber)->toBe('12345678')
        ->and($dto->phone)->toBe('3001234567')
        ->and($dto->licenseNumber)->toBe('LIC-987654')
        ->and($dto->licenseCategory)->toBe(LicenseCategoryEnum::C1)
        ->and($dto->licenseExpiresAt)->toBe('2028-12-31')
        ->and($dto->status)->toBe(DriverStatusEnum::ACTIVO);

    $array = $dto->toArray();
    expect($array['document_type'])->toBe(DocumentTypeEnum::CC)
        ->and($array['document_number'])->toBe('12345678')
        ->and($array['license_number'])->toBe('LIC-987654')
        ->and($array['license_category'])->toBe(LicenseCategoryEnum::C1);
});

test('registers driver successfully with action (R1)', function () {
    $user = User::factory()->create();
    $action = app(RegisterDriverAction::class);

    $dto = new UpsertDriverDTO(
        userId: $user->id,
        fullName: 'Juan Manuel Torres',
        documentType: DocumentTypeEnum::CC,
        documentNumber: '98765432',
        phone: '+57 311 000 0000',
        licenseNumber: 'LIC-112233',
        licenseCategory: LicenseCategoryEnum::C1,
        licenseExpiresAt: '2028-06-30',
        status: DriverStatusEnum::ACTIVO,
    );

    $driver = $action($dto);

    expect($driver)->toBeInstanceOf(Driver::class)
        ->and($driver->user_id)->toBe($user->id)
        ->and($driver->full_name)->toBe('Juan Manuel Torres')
        ->and($driver->document_type)->toBe(DocumentTypeEnum::CC)
        ->and($driver->document_number)->toBe('98765432')
        ->and($driver->license_category)->toBe(LicenseCategoryEnum::C1)
        ->and($driver->canDrivePublicService())->toBeTrue()
        ->and($driver->formattedDocument())->toBe('CC 98765432')
        ->and($driver->phone)->toBe('+57 311 000 0000')
        ->and($driver->license_number)->toBe('LIC-112233')
        ->and($driver->status)->toBe(DriverStatusEnum::ACTIVO);

    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'user_id' => $user->id,
        'document_type' => 'CC',
        'document_number' => '98765432',
        'license_number' => 'LIC-112233',
        'license_category' => 'C1',
    ]);
});

test('rejects registering driver when user already has a driver profile (R5)', function () {
    $user = User::factory()->create();
    Driver::factory()->create(['user_id' => $user->id]);

    $action = app(RegisterDriverAction::class);
    $dto = new UpsertDriverDTO(
        userId: $user->id,
        fullName: 'Otro Conductor',
        documentType: DocumentTypeEnum::CE,
        documentNumber: '55555555',
        phone: '3000000000',
        licenseNumber: 'LIC-999999',
    );

    expect(fn () => $action($dto))
        ->toThrow(InvalidDriverException::class, "El usuario con ID #{$user->id} ya tiene un perfil de conductor asociado.");
});

test('updates driver information with action (R3)', function () {
    $driver = Driver::factory()->create([
        'full_name' => 'Nombre Original',
        'document_type' => DocumentTypeEnum::CC,
        'license_category' => LicenseCategoryEnum::C1,
        'phone' => '+57 300 111 2233',
        'status' => DriverStatusEnum::ACTIVO,
    ]);

    $action = app(UpdateDriverAction::class);
    $dto = new UpsertDriverDTO(
        userId: $driver->user_id,
        fullName: 'Nombre Actualizado',
        documentType: DocumentTypeEnum::CE,
        documentNumber: $driver->document_number,
        phone: '+57 300 999 8877',
        licenseNumber: $driver->license_number,
        licenseCategory: LicenseCategoryEnum::C2,
        licenseExpiresAt: '2029-01-01',
        status: DriverStatusEnum::SUSPENDIDO,
    );

    $updated = $action($driver, $dto);

    expect($updated->full_name)->toBe('Nombre Actualizado')
        ->and($updated->document_type)->toBe(DocumentTypeEnum::CE)
        ->and($updated->license_category)->toBe(LicenseCategoryEnum::C2)
        ->and($updated->phone)->toBe('+57 300 999 8877')
        ->and($updated->status)->toBe(DriverStatusEnum::SUSPENDIDO);
});

test('driver model helper methods detect active and expired status (R6)', function () {
    $activeDriver = Driver::factory()->active()->validLicense()->create();
    $expiredDriver = Driver::factory()->active()->expiredLicense()->create();
    $suspendedDriver = Driver::factory()->suspended()->validLicense()->create();
    $noExpiryDriver = Driver::factory()->active()->withoutLicenseExpiration()->create();

    expect($activeDriver->isActive())->toBeTrue()
        ->and($activeDriver->hasValidLicense())->toBeTrue()
        ->and($activeDriver->isEligibleForTrip())->toBeTrue();

    expect($expiredDriver->isActive())->toBeTrue()
        ->and($expiredDriver->isLicenseExpired())->toBeTrue()
        ->and($expiredDriver->hasValidLicense())->toBeFalse()
        ->and($expiredDriver->isEligibleForTrip())->toBeFalse();

    expect($suspendedDriver->isActive())->toBeFalse()
        ->and($suspendedDriver->hasValidLicense())->toBeTrue()
        ->and($suspendedDriver->isEligibleForTrip())->toBeFalse();

    expect($noExpiryDriver->isActive())->toBeTrue()
        ->and($noExpiryDriver->isLicenseExpired())->toBeFalse()
        ->and($noExpiryDriver->hasValidLicense())->toBeTrue()
        ->and($noExpiryDriver->isEligibleForTrip())->toBeTrue();
});

test('driver scopes filter active and eligible drivers correctly (R2, R6)', function () {
    $eligible1 = Driver::factory()->active()->validLicense()->create();
    $eligible2 = Driver::factory()->active()->withoutLicenseExpiration()->create();
    $expired = Driver::factory()->active()->expiredLicense()->create();
    $suspended = Driver::factory()->suspended()->validLicense()->create();
    $inactive = Driver::factory()->inactive()->validLicense()->create();

    $activeList = Driver::query()->active()->pluck('id');
    expect($activeList)->toContain($eligible1->id, $eligible2->id, $expired->id)
        ->and($activeList)->not->toContain($suspended->id, $inactive->id);

    $eligibleList = Driver::query()->eligibleForTrip()->pluck('id');
    expect($eligibleList)->toContain($eligible1->id, $eligible2->id)
        ->and($eligibleList)->not->toContain($expired->id, $suspended->id, $inactive->id);
});

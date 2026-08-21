<?php

declare(strict_types=1);

namespace Tests\Unit\Drivers;

use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverInServiceInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_deactivate_driver_with_active_trip(): void
    {
        $driver = Driver::factory()->active()->create(['full_name' => 'Carlos Conductor']);
        Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

        $this->expectException(DriverNotEligibleException::class);
        $this->expectExceptionMessage('No se puede desactivar o suspender el conductor Carlos Conductor porque tiene servicios activos o asignados.');

        $driver->update(['status' => DriverStatusEnum::INACTIVO]);
    }

    public function test_cannot_suspend_driver_with_assigned_trip(): void
    {
        $driver = Driver::factory()->active()->create(['full_name' => 'Carlos Conductor']);
        Trip::factory()->assigned()->create(['driver_id' => $driver->id]);

        $this->expectException(DriverNotEligibleException::class);
        $this->expectExceptionMessage('No se puede desactivar o suspender el conductor Carlos Conductor porque tiene servicios activos o asignados.');

        $driver->update(['status' => DriverStatusEnum::SUSPENDIDO]);
    }

    public function test_cannot_change_user_id_of_driver_with_active_trip(): void
    {
        $driver = Driver::factory()->active()->create(['full_name' => 'Carlos Conductor']);
        $newUser = User::factory()->create();
        Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

        $this->expectException(DriverNotEligibleException::class);
        $this->expectExceptionMessage('No se puede cambiar la cuenta de usuario del conductor Carlos Conductor mientras tiene servicios activos o asignados.');

        $driver->update(['user_id' => $newUser->id]);
    }

    public function test_cannot_change_license_category_of_driver_with_active_trip(): void
    {
        $driver = Driver::factory()->active()->create([
            'full_name' => 'Carlos Conductor',
            'license_category' => LicenseCategoryEnum::C2,
        ]);
        Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

        $this->expectException(DriverNotEligibleException::class);
        $this->expectExceptionMessage('No se puede modificar la categoría de licencia del conductor Carlos Conductor mientras tiene servicios activos o asignados.');

        $driver->update(['license_category' => LicenseCategoryEnum::B1]);
    }

    public function test_can_update_phone_and_license_expires_at_for_driver_in_service(): void
    {
        $driver = Driver::factory()->active()->create([
            'phone' => '3001112233',
            'license_expires_at' => '2026-08-30',
        ]);
        Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

        $driver->update([
            'phone' => '3009998877',
            'license_expires_at' => '2028-12-31',
        ]);

        $this->assertSame('3009998877', $driver->fresh()->phone);
        $this->assertSame('2028-12-31', $driver->fresh()->license_expires_at->toDateString());
    }

    public function test_can_update_all_fields_when_driver_has_no_active_trips(): void
    {
        $driver = Driver::factory()->active()->create([
            'license_category' => LicenseCategoryEnum::C1,
        ]);
        $newUser = User::factory()->create();

        $driver->update([
            'user_id' => $newUser->id,
            'status' => DriverStatusEnum::INACTIVO,
            'license_category' => LicenseCategoryEnum::B2,
        ]);

        $this->assertSame($newUser->id, $driver->fresh()->user_id);
        $this->assertSame(DriverStatusEnum::INACTIVO, $driver->fresh()->status);
        $this->assertSame(LicenseCategoryEnum::B2, $driver->fresh()->license_category);
    }
}

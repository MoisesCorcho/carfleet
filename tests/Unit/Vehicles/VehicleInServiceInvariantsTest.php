<?php

declare(strict_types=1);

namespace Tests\Unit\Vehicles;

use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleInServiceInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_change_status_of_vehicle_with_active_trip(): void
    {
        $vehicle = Vehicle::factory()->inTrip()->create(['plate_number' => 'XYZ-123']);
        Trip::factory()->inProgress()->create(['vehicle_id' => $vehicle->id]);

        $this->expectException(VehicleNotAvailableException::class);
        $this->expectExceptionMessage('No se puede cambiar el estado del vehículo XYZ-123 mientras tiene servicios activos o asignados');

        $vehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
    }

    public function test_cannot_change_plate_number_of_vehicle_with_active_trip(): void
    {
        $vehicle = Vehicle::factory()->inTrip()->create(['plate_number' => 'XYZ-123']);
        Trip::factory()->inProgress()->create(['vehicle_id' => $vehicle->id]);

        $this->expectException(VehicleNotAvailableException::class);
        $this->expectExceptionMessage('No se pueden modificar datos críticos (placa, tipo de vehículo o modalidad) del vehículo XYZ-123 mientras tiene servicios activos o asignados.');

        $vehicle->update(['plate_number' => 'NEW-999']);
    }

    public function test_cannot_change_service_type_of_vehicle_with_assigned_trip(): void
    {
        $vehicle = Vehicle::factory()->assigned()->create(['plate_number' => 'XYZ-123', 'service_type' => ServiceTypeEnum::PUBLICO]);
        Trip::factory()->assigned()->create(['vehicle_id' => $vehicle->id]);

        $this->expectException(VehicleNotAvailableException::class);
        $this->expectExceptionMessage('No se pueden modificar datos críticos (placa, tipo de vehículo o modalidad) del vehículo XYZ-123 mientras tiene servicios activos o asignados.');

        $vehicle->update(['service_type' => ServiceTypeEnum::PARTICULAR]);
    }

    public function test_can_update_vehicle_fields_when_no_active_trips(): void
    {
        $vehicle = Vehicle::factory()->available()->create([
            'plate_number' => 'XYZ-123',
            'brand' => 'Toyota',
        ]);

        $vehicle->update([
            'brand' => 'Nissan',
            'status' => VehicleStatusEnum::MANTENIMIENTO,
            'vehicle_type' => VehicleTypeEnum::VAN,
        ]);

        $this->assertSame('Nissan', $vehicle->fresh()->brand);
        $this->assertSame(VehicleStatusEnum::MANTENIMIENTO, $vehicle->fresh()->status);
        $this->assertSame(VehicleTypeEnum::VAN, $vehicle->fresh()->vehicle_type);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Filament\Resources\Vehicles\Pages\ViewVehicle;
use App\Filament\Resources\Vehicles\RelationManagers\TripsRelationManager;
use App\Filament\Resources\Vehicles\Widgets\VehiclePerformanceWidget;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VehicleTripHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');
    }

    public function test_admin_can_view_vehicle_trip_history_in_view_vehicle_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create(['plate_number' => 'XYZ-789']);
        $requester = Requester::factory()->create(['name' => 'Acme Logistics']);
        $driver = Driver::factory()->active()->create(['full_name' => 'Carlos Mendoza']);

        $trip1 = Trip::factory()->closed()->create([
            'code' => 'TRIP-2026-0001',
            'vehicle_id' => $vehicle->id,
            'requester_id' => $requester->id,
            'driver_id' => $driver->id,
            'distance_traveled' => 320,
            'actual_departure_at' => '2026-08-10 08:00:00',
            'actual_arrival_at' => '2026-08-10 14:00:00',
        ]);

        FuelLog::factory()->create([
            'trip_id' => $trip1->id,
            'vehicle_id' => $vehicle->id,
            'gallons' => 12.50,
            'total_cost' => 180000,
        ]);

        Livewire::test(TripsRelationManager::class, [
            'ownerRecord' => $vehicle,
            'pageClass' => ViewVehicle::class,
        ])
            ->assertSuccessful()
            ->assertSee('TRIP-2026-0001')
            ->assertSee('Acme Logistics')
            ->assertSee('Carlos Mendoza')
            ->assertSee('320 km');
    }

    public function test_trips_relation_manager_filters_by_driver_and_date_range(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create();
        $driverA = Driver::factory()->active()->create(['full_name' => 'Conductor Alfa']);
        $driverB = Driver::factory()->active()->create(['full_name' => 'Conductor Beta']);

        $tripA = Trip::factory()->closed()->create([
            'code' => 'TRIP-ALFA',
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driverA->id,
            'scheduled_departure_at' => '2026-08-01 08:00:00',
            'actual_departure_at' => '2026-08-01 08:00:00',
        ]);

        $tripB = Trip::factory()->closed()->create([
            'code' => 'TRIP-BETA',
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driverB->id,
            'scheduled_departure_at' => '2026-08-15 08:00:00',
            'actual_departure_at' => '2026-08-15 08:00:00',
        ]);

        // Filter by Driver A
        Livewire::test(TripsRelationManager::class, [
            'ownerRecord' => $vehicle,
            'pageClass' => ViewVehicle::class,
        ])
            ->filterTable('driver_id', $driverA->id)
            ->assertCanSeeTableRecords([$tripA])
            ->assertCanNotSeeTableRecords([$tripB]);

        // Filter by Date Range (only Trip B in range)
        Livewire::test(TripsRelationManager::class, [
            'ownerRecord' => $vehicle,
            'pageClass' => ViewVehicle::class,
        ])
            ->filterTable('date_range', [
                'from' => '2026-08-10',
                'until' => '2026-08-20',
            ])
            ->assertCanSeeTableRecords([$tripB])
            ->assertCanNotSeeTableRecords([$tripA]);
    }

    public function test_trips_relation_manager_is_read_only_to_prevent_domain_bypass(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create();
        $trip = Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        Livewire::test(TripsRelationManager::class, [
            'ownerRecord' => $vehicle,
            'pageClass' => ViewVehicle::class,
        ])
            ->assertTableActionDoesNotExist('create')
            ->assertTableActionDoesNotExist('delete')
            ->assertTableActionDoesNotExist('edit')
            ->assertTableActionExists('viewTrip');
    }

    public function test_admin_sees_vehicle_performance_metrics_in_widget(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create(['plate_number' => 'ABC-123']);

        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 500,
        ]);

        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 20.0,
            'total_cost' => 300000,
        ]);

        Livewire::test(VehiclePerformanceWidget::class, ['record' => $vehicle])
            ->assertSuccessful()
            ->assertSee('500 km')
            ->assertSee('25,00 km/gal')
            ->assertSee('20,00 gal');

        Livewire::test(ViewVehicle::class, ['record' => $vehicle->id])
            ->assertSuccessful();
    }
}

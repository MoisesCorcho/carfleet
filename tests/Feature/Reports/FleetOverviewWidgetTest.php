<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Filament\Widgets\FleetOverviewWidget;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FleetOverviewWidgetTest extends TestCase
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

    public function test_fleet_overview_widget_renders_accurate_fleet_statistics(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle1 = Vehicle::factory()->create(['current_mileage' => 20000]);
        $vehicle2 = Vehicle::factory()->create(['current_mileage' => 40000]);

        // 2 trips: 400 km + 600 km = 1000 km
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle1->id,
            'distance_traveled' => 400,
        ]);
        Trip::factory()->completed()->create([
            'vehicle_id' => $vehicle2->id,
            'distance_traveled' => 600,
        ]);

        // 2 fuel logs: 20 gal ($300k) + 20 gal ($300k) = 40 gal ($600k)
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'gallons' => 20.0,
            'total_cost' => 300000,
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'gallons' => 20.0,
            'total_cost' => 300000,
        ]);

        Livewire::test(FleetOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('Kilometraje Recorrido')
            ->assertSee('1.000 km')
            ->assertSee('Rendimiento Promedio')
            ->assertSee('25,00 km/gal')
            ->assertSee('Combustible Total')
            ->assertSee('40,00 gal')
            ->assertSee('$600.000');
    }

    public function test_fleet_overview_widget_handles_empty_database_without_errors(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Livewire::test(FleetOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('0 km')
            ->assertSee('0,00 km/gal')
            ->assertSee('0,00 gal');
    }

    public function test_fleet_overview_widget_prevents_division_by_zero_when_trips_exist_without_fuel_logs(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create(['current_mileage' => 15000]);

        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 500,
        ]);

        Livewire::test(FleetOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('500 km')
            ->assertSee('0,00 km/gal')
            ->assertSee('Sin consumos registrados')
            ->assertSee('0,00 gal');
    }
}

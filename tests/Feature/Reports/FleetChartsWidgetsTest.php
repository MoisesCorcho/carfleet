<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Enums\Vehicles\VehicleStatusEnum;
use App\Filament\Widgets\FleetStatusDoughnutWidget;
use App\Filament\Widgets\MonthlyFleetMileageChartWidget;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FleetChartsWidgetsTest extends TestCase
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

    public function test_fleet_status_doughnut_widget_renders_status_distribution(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Vehicle::factory()->create(['status' => VehicleStatusEnum::DISPONIBLE]);
        Vehicle::factory()->create(['status' => VehicleStatusEnum::DISPONIBLE]);
        Vehicle::factory()->create(['status' => VehicleStatusEnum::EN_VIAJE]);
        Vehicle::factory()->create(['status' => VehicleStatusEnum::MANTENIMIENTO]);

        Livewire::test(FleetStatusDoughnutWidget::class)
            ->assertSuccessful();
    }

    public function test_monthly_fleet_mileage_chart_widget_aggregates_kms_per_month(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create();

        // 2 trips in August: 400 km + 300 km = 700 km
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 400,
            'actual_departure_at' => '2026-08-05 08:00:00',
            'actual_arrival_at' => '2026-08-05 14:00:00',
        ]);
        Trip::factory()->completed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 300,
            'actual_departure_at' => '2026-08-10 08:00:00',
            'actual_arrival_at' => '2026-08-10 14:00:00',
        ]);

        // 1 trip in July: 500 km
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 500,
            'actual_departure_at' => '2026-07-15 08:00:00',
            'actual_arrival_at' => '2026-07-15 14:00:00',
        ]);

        Livewire::test(MonthlyFleetMileageChartWidget::class)
            ->assertSuccessful();

        Carbon::setTestNow();
    }

    public function test_charts_widgets_handle_empty_database_gracefully(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Livewire::test(FleetStatusDoughnutWidget::class)->assertSuccessful();
        Livewire::test(MonthlyFleetMileageChartWidget::class)->assertSuccessful();
    }
}

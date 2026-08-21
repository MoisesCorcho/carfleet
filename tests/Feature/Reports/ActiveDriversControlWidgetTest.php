<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Enums\Drivers\DriverStatusEnum;
use App\Filament\Widgets\ActiveDriversControlWidget;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ActiveDriversControlWidgetTest extends TestCase
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

    public function test_active_drivers_control_widget_renders_drivers_and_active_trips(): void
    {
        Carbon::setTestNow('2026-08-21 15:00:00');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::factory()->create(['plate_number' => 'ABC-999']);
        $driver = Driver::factory()->active()->create([
            'full_name' => 'Pedro Despachador',
            'phone' => '3001234567',
        ]);

        Trip::factory()->inProgress()->create([
            'code' => 'TRIP-ACT-01',
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'destination' => 'Planta Industrial Norte',
            'actual_departure_at' => '2026-08-21 12:30:00', // 2h 30m
        ]);

        Livewire::test(ActiveDriversControlWidget::class)
            ->assertSuccessful()
            ->assertSee('Pedro Despachador')
            ->assertSee('3001234567')
            ->assertSee('ABC-999')
            ->assertSee('TRIP-ACT-01')
            ->assertSee('Planta Industrial Norte')
            ->assertSee('2h 30m')
            ->assertSee('En Viaje');

        Carbon::setTestNow();
    }

    public function test_active_drivers_control_widget_shows_available_state_for_idle_drivers(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Driver::factory()->active()->create([
            'full_name' => 'Juan Disponible',
            'status' => DriverStatusEnum::ACTIVO,
        ]);

        Livewire::test(ActiveDriversControlWidget::class)
            ->assertSuccessful()
            ->assertSee('Juan Disponible')
            ->assertSee('Disponible');
    }

    public function test_active_drivers_control_widget_detects_expired_licenses(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Driver::factory()->create([
            'full_name' => 'Chofer Vencido',
            'status' => DriverStatusEnum::ACTIVO,
            'license_expires_at' => now()->subDays(5),
        ]);

        Livewire::test(ActiveDriversControlWidget::class)
            ->assertSuccessful()
            ->assertSee('Chofer Vencido')
            ->assertSee('Licencia Vencida');
    }
}

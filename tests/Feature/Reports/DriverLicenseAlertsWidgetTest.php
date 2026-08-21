<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Filament\Widgets\DriverLicenseAlertsWidget;
use App\Models\Driver;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class DriverLicenseAlertsWidgetTest extends TestCase
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

    public function test_driver_license_alerts_widget_lists_expired_and_expiring_licenses(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        // Driver 1: Expired 5 days ago
        $driverExpired = Driver::factory()->active()->create([
            'full_name' => 'Conductor Vencido',
            'license_expires_at' => '2026-08-16',
        ]);

        // Driver 2: Expiring in 10 days
        $driverExpiringSoon = Driver::factory()->active()->create([
            'full_name' => 'Conductor Proximo Vencer',
            'license_expires_at' => '2026-08-31',
        ]);

        // Driver 3: Valid for 6 months (should NOT appear in alert widget)
        $driverValid = Driver::factory()->active()->create([
            'full_name' => 'Conductor Licencia Al Dia',
            'license_expires_at' => '2027-02-28',
        ]);

        Livewire::test(DriverLicenseAlertsWidget::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$driverExpired, $driverExpiringSoon])
            ->assertCanNotSeeTableRecords([$driverValid]);

        Carbon::setTestNow();
    }

    public function test_driver_license_alerts_widget_renders_empty_state_when_all_licenses_valid(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Driver::factory()->active()->create([
            'full_name' => 'Conductor Excelente',
            'license_expires_at' => '2028-12-31',
        ]);

        Livewire::test(DriverLicenseAlertsWidget::class)
            ->assertSuccessful()
            ->assertSee('Todas las licencias al día');

        Carbon::setTestNow();
    }
}

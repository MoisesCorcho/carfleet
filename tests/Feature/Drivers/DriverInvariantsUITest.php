<?php

declare(strict_types=1);

namespace Tests\Feature\Drivers;

use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DriverInvariantsUITest extends TestCase
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

    public function test_edit_driver_form_disables_critical_fields_when_driver_has_active_trip(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $driver = Driver::factory()->active()->create();
        Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

        Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
            ->assertFormFieldIsDisabled('status')
            ->assertFormFieldIsDisabled('user_id')
            ->assertFormFieldIsDisabled('license_category')
            ->assertFormFieldIsEnabled('phone')
            ->assertFormFieldIsEnabled('license_expires_at');
    }

    public function test_edit_driver_form_enables_all_fields_when_driver_has_no_active_trips(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $driver = Driver::factory()->active()->create();

        Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
            ->assertFormFieldIsEnabled('status')
            ->assertFormFieldIsEnabled('user_id')
            ->assertFormFieldIsEnabled('license_category')
            ->assertFormFieldIsEnabled('phone')
            ->assertFormFieldIsEnabled('license_expires_at');
    }
}

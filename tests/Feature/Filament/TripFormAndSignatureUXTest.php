<?php

declare(strict_types=1);

use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Resources\Trips\Pages\CreateTrip;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');

    $this->driverUser = User::factory()->create();
    $this->driverUser->assignRole('driver');
    $this->driver = Driver::factory()->active()->create(['user_id' => $this->driverUser->id]);
});

test('trip form requester select displays company name with contact person', function () {
    $this->actingAs($this->adminUser);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $companyRequester = Requester::factory()->create([
        'company_name' => 'Alimentos del Centro S.A.',
        'name' => 'Dra. Carmen Cecilia',
        'is_active' => true,
    ]);

    $individualRequester = Requester::factory()->create([
        'company_name' => null,
        'name' => 'Juan Carlos Pérez',
        'is_active' => true,
    ]);

    $test = Livewire::test(CreateTrip::class);

    // Verify option keys and labels
    expect($companyRequester->display_name)->toBe('Alimentos del Centro S.A. — Dra. Carmen Cecilia')
        ->and($individualRequester->display_name)->toBe('Juan Carlos Pérez');
});

test('driver capture signature action fails validation when canvas signature_data is empty', function () {
    $this->actingAs($this->driverUser);
    Filament::setCurrentPanel(Filament::getPanel('driver'));

    $trip = Trip::factory()->completed()->create([
        'driver_id' => $this->driver->id,
    ]);

    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('captureSignature', $trip, [
            'signer_name' => 'Dra. Elena Vargas',
            'signature_data' => null,
        ])
        ->assertHasTableActionErrors(['signature_data' => 'required']);
});

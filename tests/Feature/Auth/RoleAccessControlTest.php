<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Driver;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('super_admin has permission to view and manage roles', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    expect($superAdmin->can('ViewAny:Role'))->toBeTrue()
        ->and($superAdmin->can('Create:Role'))->toBeTrue()
        ->and($superAdmin->can('Update:Role'))->toBeTrue()
        ->and($superAdmin->can('Delete:Role'))->toBeTrue()
        ->and($superAdmin->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

test('admin (fleet dispatcher) has domain permissions but CANNOT manage roles', function () {
    $dispatcher = User::factory()->create();
    $dispatcher->assignRole('admin');

    // Operational permissions granted
    expect($dispatcher->can('ViewAny:Vehicle'))->toBeTrue()
        ->and($dispatcher->can('ViewAny:Trip'))->toBeTrue()
        ->and($dispatcher->can('ViewAny:Driver'))->toBeTrue()
        ->and($dispatcher->can('ViewAny:FuelLog'))->toBeTrue()
        ->and($dispatcher->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();

    // Security & Role permissions strictly denied
    expect($dispatcher->can('ViewAny:Role'))->toBeFalse()
        ->and($dispatcher->can('Create:Role'))->toBeFalse()
        ->and($dispatcher->can('Update:Role'))->toBeFalse()
        ->and($dispatcher->can('Delete:Role'))->toBeFalse();
});

test('driver role cannot access admin panel', function () {
    $driverUser = User::factory()->create();
    $driverUser->assignRole('driver');
    Driver::factory()->active()->create(['user_id' => $driverUser->id]);

    expect($driverUser->canAccessPanel(Filament::getPanel('admin')))->toBeFalse()
        ->and($driverUser->canAccessPanel(Filament::getPanel('driver')))->toBeTrue();
});

test('inactive driver cannot access driver panel', function () {
    $driverUser = User::factory()->create();
    $driverUser->assignRole('driver');
    Driver::factory()->inactive()->create(['user_id' => $driverUser->id]);

    expect($driverUser->canAccessPanel(Filament::getPanel('driver')))->toBeFalse();
});

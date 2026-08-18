<?php

declare(strict_types=1);

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Filament\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Filament\Resources\Drivers\Pages\ListDrivers;
use App\Models\Driver;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('admin can access drivers list in filament panel', function () {
    $this->actingAs($this->adminUser);

    Driver::factory()->count(3)->create();

    Livewire::test(ListDrivers::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('admin can filter drivers by operational status (R2)', function () {
    $this->actingAs($this->adminUser);

    $activeDriver = Driver::factory()->active()->create(['full_name' => 'Conductor Activo']);
    $inactiveDriver = Driver::factory()->inactive()->create(['full_name' => 'Conductor Inactivo']);
    $suspendedDriver = Driver::factory()->suspended()->create(['full_name' => 'Conductor Suspendido']);

    Livewire::test(ListDrivers::class)
        ->assertCanSeeTableRecords([$activeDriver, $inactiveDriver, $suspendedDriver])
        ->filterTable('status', DriverStatusEnum::ACTIVO->value)
        ->assertCanSeeTableRecords([$activeDriver])
        ->assertCanNotSeeTableRecords([$inactiveDriver, $suspendedDriver]);
});

test('admin can filter drivers by document type', function () {
    $this->actingAs($this->adminUser);

    $ccDriver = Driver::factory()->withDocumentType(DocumentTypeEnum::CC)->create(['full_name' => 'Conductor CC']);
    $ceDriver = Driver::factory()->withDocumentType(DocumentTypeEnum::CE)->create(['full_name' => 'Conductor CE']);

    Livewire::test(ListDrivers::class)
        ->assertCanSeeTableRecords([$ccDriver, $ceDriver])
        ->filterTable('document_type', DocumentTypeEnum::CC->value)
        ->assertCanSeeTableRecords([$ccDriver])
        ->assertCanNotSeeTableRecords([$ceDriver]);
});

test('admin can create a driver via filament form (R1)', function () {
    $this->actingAs($this->adminUser);

    $driverUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $driverUser->id,
            'full_name' => 'Andrés Cepeda',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '1098765432',
            'phone' => '+57 320 987 6543',
            'license_number' => 'LIC-77665544',
            'license_expires_at' => now()->addYears(3)->format('Y-m-d'),
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('drivers', [
        'user_id' => $driverUser->id,
        'full_name' => 'Andrés Cepeda',
        'document_type' => 'CC',
        'document_number' => '1098765432',
        'phone' => '+57 320 987 6543',
        'license_number' => 'LIC-77665544',
        'status' => 'activo',
    ]);
});

test('rejects duplicate document number of same document type via filament form validation (R4)', function () {
    $this->actingAs($this->adminUser);

    Driver::factory()->create([
        'document_type' => DocumentTypeEnum::CC,
        'document_number' => '12345678',
    ]);
    $newUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $newUser->id,
            'full_name' => 'Otro Conductor',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '12345678',
            'phone' => '+57 300 123 4567',
            'license_number' => 'LIC-999999',
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'unique']);
});

test('rejects invalid phone format with customized message', function () {
    $this->actingAs($this->adminUser);

    $newUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $newUser->id,
            'full_name' => 'Conductor Teléfono Inválido',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '99887766',
            'phone' => 'telefono-invalido',
            'license_number' => 'LIC-11223344',
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['phone' => 'regex']);
});

test('rejects duplicate license number via filament form validation (R4)', function () {
    $this->actingAs($this->adminUser);

    Driver::factory()->create(['license_number' => 'LIC-DUPLICADA']);
    $newUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $newUser->id,
            'full_name' => 'Conductor Prueba',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '88888888',
            'phone' => '+57 300 123 4567',
            'license_number' => 'LIC-DUPLICADA',
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['license_number' => 'unique']);
});

test('rejects duplicate user assignment via filament form validation (R5)', function () {
    $this->actingAs($this->adminUser);

    $assignedUser = User::factory()->create();
    Driver::factory()->create(['user_id' => $assignedUser->id]);

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $assignedUser->id,
            'full_name' => 'Duplicado User',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '77777777',
            'phone' => '+57 300 123 4567',
            'license_number' => 'LIC-777777',
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['user_id' => 'unique']);
});

test('admin can update driver information via edit page (R3)', function () {
    $this->actingAs($this->adminUser);

    $driver = Driver::factory()->create([
        'full_name' => 'Conductor Original',
        'document_type' => DocumentTypeEnum::CC,
        'status' => DriverStatusEnum::ACTIVO,
    ]);

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->fillForm([
            'full_name' => 'Conductor Modificado',
            'document_type' => DocumentTypeEnum::CE->value,
            'phone' => '+57 310 999 8877',
            'status' => DriverStatusEnum::SUSPENDIDO->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $driver->refresh();
    expect($driver->full_name)->toBe('Conductor Modificado')
        ->and($driver->document_type)->toBe(DocumentTypeEnum::CE)
        ->and($driver->phone)->toBe('+57 310 999 8877')
        ->and($driver->status)->toBe(DriverStatusEnum::SUSPENDIDO);
});

test('user without view driver permissions cannot access drivers list', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('panel_user');

    $this->actingAs($regularUser);

    Livewire::test(ListDrivers::class)
        ->assertForbidden();
});

<?php

declare(strict_types=1);

use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Filament\Resources\Requesters\Pages\CreateRequester;
use App\Filament\Resources\Requesters\Pages\EditRequester;
use App\Filament\Resources\Requesters\Pages\ListRequesters;
use App\Models\Requester;
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

test('admin can access requesters list in filament panel (R2)', function () {
    $this->actingAs($this->adminUser);

    Requester::factory()->count(3)->create();

    Livewire::test(ListRequesters::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('admin can filter requesters by document type and active status (R2)', function () {
    $this->actingAs($this->adminUser);

    $activeCompany = Requester::factory()->company()->active()->create(['name' => 'Contacto Activo']);
    $inactivePerson = Requester::factory()->individual()->inactive()->create(['name' => 'Persona Inactiva']);

    Livewire::test(ListRequesters::class)
        ->assertCanSeeTableRecords([$activeCompany, $inactivePerson])
        ->filterTable('document_type', RequesterDocumentTypeEnum::NIT->value)
        ->assertCanSeeTableRecords([$activeCompany])
        ->assertCanNotSeeTableRecords([$inactivePerson]);
});

test('admin can create a requester via filament form (R1)', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateRequester::class)
        ->fillForm([
            'company_name' => 'Logística Andina S.A.S.',
            'name' => 'Felipe Restrepo',
            'document_type' => RequesterDocumentTypeEnum::NIT->value,
            'document_number' => '900888777-1',
            'phone' => '+57 315 123 4567',
            'email' => 'operaciones@andina.com',
            'is_active' => true,
            'notes' => 'Condición de pago a 30 días',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('requesters', [
        'company_name' => 'Logística Andina S.A.S.',
        'name' => 'Felipe Restrepo',
        'document_type' => 'NIT',
        'document_number' => '900888777-1',
        'phone' => '+57 315 123 4567',
        'email' => 'operaciones@andina.com',
        'is_active' => 1,
    ]);
});

test('rejects duplicate document number of same document type via filament form validation (R4)', function () {
    $this->actingAs($this->adminUser);

    Requester::factory()->create([
        'document_type' => RequesterDocumentTypeEnum::NIT,
        'document_number' => '900123456-1',
    ]);

    Livewire::test(CreateRequester::class)
        ->fillForm([
            'name' => 'Otro Solicitante',
            'document_type' => RequesterDocumentTypeEnum::NIT->value,
            'document_number' => '900123456-1',
            'phone' => '+57 300 111 2222',
        ])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'unique']);
});

test('admin can edit requester details via filament form (R3)', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create([
        'name' => 'Nombre Viejo',
        'phone' => '+57 300 000 0000',
        'is_active' => true,
    ]);

    Livewire::test(EditRequester::class, ['record' => $requester->getRouteKey()])
        ->fillForm([
            'name' => 'Nombre Modificado',
            'phone' => '+57 310 999 8888',
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $requester->refresh();
    expect($requester->name)->toBe('Nombre Modificado')
        ->and($requester->phone)->toBe('+57 310 999 8888')
        ->and($requester->is_active)->toBeFalse();
});

test('admin can soft-delete and restore requester from table actions (R5)', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create([
        'name' => 'Solicitante Para Borrar',
    ]);

    Livewire::test(EditRequester::class, ['record' => $requester->getRouteKey()])
        ->callAction('delete');

    $this->assertSoftDeleted('requesters', ['id' => $requester->id]);

    Livewire::test(EditRequester::class, ['record' => $requester->getRouteKey()])
        ->callAction('restore');

    $requester->refresh();
    expect($requester->trashed())->toBeFalse();
});

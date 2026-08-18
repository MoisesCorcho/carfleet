<?php

declare(strict_types=1);

use App\Actions\Requesters\RegisterRequesterAction;
use App\Actions\Requesters\UpdateRequesterAction;
use App\DTOs\Requesters\UpsertRequesterDTO;
use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Exceptions\Requesters\InvalidRequesterException;
use App\Models\Requester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('requester document type enum has correct labels and short labels', function () {
    expect(RequesterDocumentTypeEnum::NIT->value)->toBe('NIT')
        ->and(RequesterDocumentTypeEnum::NIT->label())->toContain('NIT')
        ->and(RequesterDocumentTypeEnum::CC->value)->toBe('CC')
        ->and(RequesterDocumentTypeEnum::CC->label())->toContain('Cédula de Ciudadanía')
        ->and(RequesterDocumentTypeEnum::CE->value)->toBe('CE')
        ->and(RequesterDocumentTypeEnum::PA->value)->toBe('PA')
        ->and(RequesterDocumentTypeEnum::PPT->value)->toBe('PPT')
        ->and(RequesterDocumentTypeEnum::PEP->value)->toBe('PEP');
});

test('upsert requester dto maps array correctly and normalizes fields', function () {
    $dto = UpsertRequesterDTO::fromArray([
        'name' => '  Juan Valdés  ',
        'company_name' => '  Café de Colombia S.A.S.  ',
        'document_type' => 'NIT',
        'document_number' => '900123456-1',
        'phone' => ' +57 300 123 4567 ',
        'email' => ' INFO@CAFEDECOLOMBIA.CO ',
        'is_active' => true,
        'notes' => '  Cliente preferencial  ',
    ]);

    expect($dto->name)->toBe('Juan Valdés')
        ->and($dto->companyName)->toBe('Café de Colombia S.A.S.')
        ->and($dto->documentType)->toBe(RequesterDocumentTypeEnum::NIT)
        ->and($dto->documentNumber)->toBe('900123456-1')
        ->and($dto->phone)->toBe('+57 300 123 4567')
        ->and($dto->email)->toBe('info@cafedecolombia.co')
        ->and($dto->isActive)->toBeTrue()
        ->and($dto->notes)->toBe('Cliente preferencial');

    $array = $dto->toArray();
    expect($array['document_type'])->toBe(RequesterDocumentTypeEnum::NIT)
        ->and($array['document_number'])->toBe('900123456-1')
        ->and($array['name'])->toBe('Juan Valdés');
});

test('requester model formats document and display name correctly', function () {
    $companyRequester = Requester::factory()->create([
        'name' => 'Mario Rossi',
        'company_name' => 'Rossi Logistics S.A.S.',
        'document_type' => RequesterDocumentTypeEnum::NIT,
        'document_number' => '901234567-8',
        'is_active' => true,
    ]);

    expect($companyRequester->formattedDocument())->toBe('NIT 901234567-8')
        ->and($companyRequester->display_name)->toBe('Rossi Logistics S.A.S. — Mario Rossi')
        ->and($companyRequester->isActive())->toBeTrue();

    $individualRequester = Requester::factory()->create([
        'name' => 'Laura Gómez',
        'company_name' => null,
        'document_type' => RequesterDocumentTypeEnum::CC,
        'document_number' => '1020304050',
    ]);

    expect($individualRequester->formattedDocument())->toBe('CC 1020304050')
        ->and($individualRequester->display_name)->toBe('Laura Gómez');
});

test('requester model scopeActive filters active records', function () {
    Requester::factory()->active()->count(2)->create();
    Requester::factory()->inactive()->count(3)->create();

    expect(Requester::query()->active()->count())->toBe(2)
        ->and(Requester::query()->count())->toBe(5);
});

test('registers requester successfully via action (R1)', function () {
    $action = app(RegisterRequesterAction::class);

    $dto = new UpsertRequesterDTO(
        name: 'Carlos Sarmiento',
        documentType: RequesterDocumentTypeEnum::NIT,
        documentNumber: '860000000-1',
        phone: '+57 310 999 8888',
        companyName: 'Grupo Aval',
        email: 'contacto@grupoaval.com',
        isActive: true,
        notes: 'Sede Principal'
    );

    $requester = $action($dto);

    expect($requester)->toBeInstanceOf(Requester::class)
        ->and($requester->name)->toBe('Carlos Sarmiento')
        ->and($requester->company_name)->toBe('Grupo Aval')
        ->and($requester->document_type)->toBe(RequesterDocumentTypeEnum::NIT)
        ->and($requester->document_number)->toBe('860000000-1')
        ->and($requester->is_active)->toBeTrue();

    $this->assertDatabaseHas('requesters', [
        'id' => $requester->id,
        'document_number' => '860000000-1',
        'document_type' => 'NIT',
    ]);
});

test('register action rejects duplicate document number and type (R4)', function () {
    Requester::factory()->create([
        'document_type' => RequesterDocumentTypeEnum::NIT,
        'document_number' => '900999888-1',
    ]);

    $action = app(RegisterRequesterAction::class);
    $dto = new UpsertRequesterDTO(
        name: 'Otro Solicitante',
        documentType: RequesterDocumentTypeEnum::NIT,
        documentNumber: '900999888-1',
        phone: '+57 300 111 2222'
    );

    expect(fn () => $action($dto))
        ->toThrow(InvalidRequesterException::class, 'Ya existe un solicitante registrado con el documento NIT 900999888-1.');
});

test('updates existing requester via action (R3)', function () {
    $requester = Requester::factory()->create([
        'name' => 'Nombre Inicial',
        'document_type' => RequesterDocumentTypeEnum::CC,
        'document_number' => '50123456',
        'phone' => '+57 300 000 0000',
        'is_active' => true,
    ]);

    $action = app(UpdateRequesterAction::class);
    $dto = new UpsertRequesterDTO(
        name: 'Nombre Actualizado',
        documentType: RequesterDocumentTypeEnum::CC,
        documentNumber: '50123456',
        phone: '+57 311 222 3333',
        companyName: 'Nueva Empresa',
        email: 'nuevo@correo.com',
        isActive: false,
        notes: 'Nota actualizada'
    );

    $updated = $action($requester, $dto);

    expect($updated->name)->toBe('Nombre Actualizado')
        ->and($updated->phone)->toBe('+57 311 222 3333')
        ->and($updated->company_name)->toBe('Nueva Empresa')
        ->and($updated->email)->toBe('nuevo@correo.com')
        ->and($updated->is_active)->toBeFalse()
        ->and($updated->notes)->toBe('Nota actualizada');
});

test('update action rejects duplicate document number and type from another record (R4)', function () {
    Requester::factory()->create([
        'document_type' => RequesterDocumentTypeEnum::NIT,
        'document_number' => '900111222-3',
    ]);

    $requesterToUpdate = Requester::factory()->create([
        'document_type' => RequesterDocumentTypeEnum::NIT,
        'document_number' => '900555666-7',
    ]);

    $action = app(UpdateRequesterAction::class);
    $dto = new UpsertRequesterDTO(
        name: $requesterToUpdate->name,
        documentType: RequesterDocumentTypeEnum::NIT,
        documentNumber: '900111222-3', // Duplicado de otro
        phone: '+57 300 123 4567'
    );

    expect(fn () => $action($requesterToUpdate, $dto))
        ->toThrow(InvalidRequesterException::class, 'Ya existe un solicitante registrado con el documento NIT 900111222-3.');
});

test('requester supports soft deletes and preserves data (R5)', function () {
    $requester = Requester::factory()->create();

    $requester->delete();

    expect($requester->trashed())->toBeTrue();
    $this->assertSoftDeleted('requesters', ['id' => $requester->id]);

    $requester->restore();
    expect($requester->trashed())->toBeFalse();
    $this->assertDatabaseHas('requesters', [
        'id' => $requester->id,
        'deleted_at' => null,
    ]);
});

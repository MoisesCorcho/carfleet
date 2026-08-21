<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Models\Requester;
use Illuminate\Database\Seeder;

class RequesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requesters = [
            [
                'document_type' => RequesterDocumentTypeEnum::NIT,
                'document_number' => '900123456-1',
                'name' => 'Ing. Roberto Sánchez',
                'company_name' => 'Consorcio Vial de los Llanos S.A.S.',
                'phone' => '+57 310 555 0101',
                'email' => 'operaciones@consorciovial.com',
                'is_active' => true,
                'notes' => 'Gerencia de Operaciones y Transporte.',
            ],
            [
                'document_type' => RequesterDocumentTypeEnum::NIT,
                'document_number' => '900234567-2',
                'name' => 'Dra. Patricia Ortiz',
                'company_name' => 'Alimentos del Centro S.A.',
                'phone' => '+57 311 555 0202',
                'email' => 'comercial@alimentoscentro.com',
                'is_active' => true,
                'notes' => 'Departamento Comercial y Ventas Corporativas.',
            ],
            [
                'document_type' => RequesterDocumentTypeEnum::NIT,
                'document_number' => '900345678-3',
                'name' => 'Lic. Fernando Gómez',
                'company_name' => 'Logística Nacional & Carga S.A.S.',
                'phone' => '+57 312 555 0303',
                'email' => 'logistica@logisticacarga.com',
                'is_active' => true,
                'notes' => 'Cadena de Suministro y Almacén Central.',
            ],
            [
                'document_type' => RequesterDocumentTypeEnum::NIT,
                'document_number' => '900456789-4',
                'name' => 'Dra. Elena Vargas',
                'company_name' => 'Corporación de Servicios Empresariales',
                'phone' => '+57 313 555 0404',
                'email' => 'rrhh@serviciosempresariales.com',
                'is_active' => true,
                'notes' => 'Dirección de Talento Humano y Bienestar.',
            ],
            [
                'document_type' => RequesterDocumentTypeEnum::NIT,
                'document_number' => '900567890-5',
                'name' => 'Arq. Mauricio Cárdenas',
                'company_name' => 'Constructora e Inmobiliaria Andina S.A.',
                'phone' => '+57 314 555 0505',
                'email' => 'infraestructura@andina.com',
                'is_active' => true,
                'notes' => 'Infraestructura, Obras Civiles y Proyectos.',
            ],
            [
                'document_type' => RequesterDocumentTypeEnum::CC,
                'document_number' => '52987654',
                'name' => 'Dra. Carmen Cecilia Morales',
                'company_name' => 'Firma Auditora Morales & Asociados',
                'phone' => '+57 315 555 0606',
                'email' => 'auditoria@moralesauditores.com',
                'is_active' => true,
                'notes' => 'Auditoría Externa y Control de Gestión.',
            ],
        ];

        foreach ($requesters as $data) {
            Requester::updateOrCreate(
                [
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                ],
                $data
            );
        }
    }
}

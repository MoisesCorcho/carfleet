<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'email' => 'driver@carfleet.test',
                'full_name' => 'Carlos Andrés Rodríguez',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1020304050',
                'phone' => '+57 300 123 4567',
                'license_number' => 'LIC-10203040',
                'license_category' => LicenseCategoryEnum::C1,
                'license_expires_at' => now()->addYears(3)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ],
            [
                'email' => 'driver2@carfleet.test',
                'full_name' => 'Jorge Eliécer Gaitán',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1030405060',
                'phone' => '+57 301 234 5678',
                'license_number' => 'LIC-20304050',
                'license_category' => LicenseCategoryEnum::C2,
                'license_expires_at' => now()->addYears(2)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ],
            [
                'email' => 'driver3@carfleet.test',
                'full_name' => 'María Fernanda Gómez',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1040506070',
                'phone' => '+57 302 345 6789',
                'license_number' => 'LIC-30405060',
                'license_category' => LicenseCategoryEnum::C1,
                'license_expires_at' => now()->addYears(4)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ],
            [
                'email' => 'driver4@carfleet.test',
                'full_name' => 'Juan Pablo Montoya',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1050607080',
                'phone' => '+57 303 456 7890',
                'license_number' => 'LIC-40506070',
                'license_category' => LicenseCategoryEnum::C3,
                'license_expires_at' => now()->addYears(2)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ],
            [
                'email' => 'driver5@carfleet.test',
                'full_name' => 'Andrés Felipe Arias',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1060708090',
                'phone' => '+57 304 567 8901',
                'license_number' => 'LIC-50607080',
                'license_category' => LicenseCategoryEnum::B1,
                'license_expires_at' => now()->addYears(3)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ],
            [
                'email' => 'driver6@carfleet.test',
                'full_name' => 'Ricardo Arjona Morales',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1070809010',
                'phone' => '+57 305 678 9012',
                'license_number' => 'LIC-60708090',
                'license_category' => LicenseCategoryEnum::C1,
                'license_expires_at' => now()->subMonth()->toDateString(),
                'status' => DriverStatusEnum::INACTIVO,
            ],
        ];

        foreach ($drivers as $data) {
            $user = User::where('email', $data['email'])->first();

            Driver::updateOrCreate(
                ['document_number' => $data['document_number']],
                [
                    'user_id' => $user?->id,
                    'full_name' => $data['full_name'],
                    'document_type' => $data['document_type'],
                    'phone' => $data['phone'],
                    'license_number' => $data['license_number'],
                    'license_category' => $data['license_category'],
                    'license_expires_at' => $data['license_expires_at'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@carfleet.test'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['super_admin']);

        // 2. Driver User & Profile
        $driverUser = User::firstOrCreate(
            ['email' => 'driver@carfleet.test'],
            [
                'name' => 'Conductor de Prueba',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $driverUser->syncRoles(['driver']);

        Driver::firstOrCreate(
            ['user_id' => $driverUser->id],
            [
                'full_name' => 'Carlos Andrés Rodríguez',
                'document_type' => DocumentTypeEnum::CC,
                'document_number' => '1020304050',
                'phone' => '+57 300 123 4567',
                'license_number' => 'LIC-10203040',
                'license_category' => LicenseCategoryEnum::C1,
                'license_expires_at' => now()->addYears(3)->toDateString(),
                'status' => DriverStatusEnum::ACTIVO,
            ]
        );

        // 3. Demo Fleet Vehicles
        Vehicle::firstOrCreate(
            ['plate_number' => 'ABC-123'],
            [
                'brand' => 'Toyota',
                'model' => 'Hilux 4x4',
                'year' => 2024,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 12500,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Camioneta operativa para viajes empresariales.',
            ]
        );

        Vehicle::firstOrCreate(
            ['plate_number' => 'XYZ-789'],
            [
                'brand' => 'Renault',
                'model' => 'Master Furgón',
                'year' => 2023,
                'vehicle_type' => VehicleTypeEnum::FURGON,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 28400,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Furgón de carga para logística urbana.',
            ]
        );
    }
}

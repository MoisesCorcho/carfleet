<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'plate_number' => 'ABC-123',
                'brand' => 'Toyota',
                'model' => 'Hilux 4x4 Diésel',
                'year' => 2024,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 12500,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Camioneta operativa para viajes intermunicipales y visitas de campo.',
            ],
            [
                'plate_number' => 'XYZ-789',
                'brand' => 'Renault',
                'model' => 'Master Furgón Maxi',
                'year' => 2023,
                'vehicle_type' => VehicleTypeEnum::FURGON,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 28400,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Furgón de alta capacidad para carga y distribución de encomiendas.',
            ],
            [
                'plate_number' => 'FLT-101',
                'brand' => 'Chevrolet',
                'model' => 'D-Max High Power',
                'year' => 2024,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 18200,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Camioneta doble cabina para cuadrillas de infraestructura.',
            ],
            [
                'plate_number' => 'FLT-202',
                'brand' => 'Nissan',
                'model' => 'Frontier PRO-4X',
                'year' => 2023,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PARTICULAR,
                'current_mileage' => 34000,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::GASOLINA,
                'notes' => 'Vehículo particular de supervisión y visitas comerciales.',
            ],
            [
                'plate_number' => 'VAN-303',
                'brand' => 'Mercedes-Benz',
                'model' => 'Sprinter 516 Pasajeros',
                'year' => 2022,
                'vehicle_type' => VehicleTypeEnum::MICROBUS,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 52100,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Microbús de 19 pasajeros para transporte de personal y eventos corporativos.',
            ],
            [
                'plate_number' => 'SED-404',
                'brand' => 'Toyota',
                'model' => 'Corolla SEG Híbrido',
                'year' => 2024,
                'vehicle_type' => VehicleTypeEnum::AUTOMOVIL,
                'service_type' => ServiceTypeEnum::PARTICULAR,
                'current_mileage' => 8900,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::GASOLINA,
                'notes' => 'Sedán ejecutivo de bajo consumo para movilidad urbana de gerencia.',
            ],
            [
                'plate_number' => 'SUV-505',
                'brand' => 'Ford',
                'model' => 'Explorer XLT 4WD',
                'year' => 2023,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PARTICULAR,
                'current_mileage' => 22300,
                'status' => VehicleStatusEnum::DISPONIBLE,
                'fuel_type' => FuelTypeEnum::GASOLINA,
                'notes' => 'Camioneta ejecutiva blindada para comisiones de auditoría y directivos.',
            ],
            [
                'plate_number' => 'TRK-606',
                'brand' => 'Hino',
                'model' => 'Dutro City 3.5T',
                'year' => 2022,
                'vehicle_type' => VehicleTypeEnum::CAMION,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 65400,
                'status' => VehicleStatusEnum::MANTENIMIENTO,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'En taller por mantenimiento preventivo mayor (frenos y suspensión).',
            ],
            [
                'plate_number' => 'ACT-707',
                'brand' => 'Hyundai',
                'model' => 'Staria Premium 9P',
                'year' => 2024,
                'vehicle_type' => VehicleTypeEnum::VAN,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 14800,
                'status' => VehicleStatusEnum::ASIGNADO,
                'fuel_type' => FuelTypeEnum::DIESEL,
                'notes' => 'Asignado a comisión intermunicipal programada.',
            ],
            [
                'plate_number' => 'RUN-808',
                'brand' => 'Renault',
                'model' => 'Duster Iconic 4x4',
                'year' => 2023,
                'vehicle_type' => VehicleTypeEnum::CAMIONETA,
                'service_type' => ServiceTypeEnum::PUBLICO,
                'current_mileage' => 26500,
                'status' => VehicleStatusEnum::EN_VIAJE,
                'fuel_type' => FuelTypeEnum::GASOLINA,
                'notes' => 'En ruta activa hacia sede regional.',
            ],
        ];

        foreach ($vehicles as $data) {
            Vehicle::firstOrCreate(
                ['plate_number' => $data['plate_number']],
                $data
            );
        }
    }
}

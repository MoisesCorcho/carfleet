<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FuelLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureVoucherDirectory();

        // 1. Fuel logs linked to Trips
        $trip1 = Trip::where('code', 'TRIP-2026-0001')->first();
        if ($trip1) {
            $this->createFuelLog(
                vehicle: $trip1->vehicle,
                trip: $trip1,
                driver: $trip1->driver,
                refuelDate: now()->subDays(7)->setTime(9, 45),
                mileage: 12130,
                gallons: 14.50,
                cost: 215000,
                voucherNumber: 'V-BOG-78901',
                notes: 'Tanqueo diésel en estación Terpel Pipiral (Vía al Llano).'
            );
        }

        $trip2 = Trip::where('code', 'TRIP-2026-0002')->first();
        if ($trip2) {
            $this->createFuelLog(
                vehicle: $trip2->vehicle,
                trip: $trip2,
                driver: $trip2->driver,
                refuelDate: now()->subDays(5)->setTime(11, 15),
                mileage: 28160,
                gallons: 18.20,
                cost: 270000,
                voucherNumber: 'V-TUN-45612',
                notes: 'Tanqueo full diésel en estación Primax Tunja Norte.'
            );
        }

        $trip3 = Trip::where('code', 'TRIP-2026-0003')->first();
        if ($trip3) {
            $this->createFuelLog(
                vehicle: $trip3->vehicle,
                trip: $trip3,
                driver: $trip3->driver,
                refuelDate: now()->subDays(3)->setTime(9, 30),
                mileage: 17960,
                gallons: 11.80,
                cost: 175000,
                voucherNumber: 'V-GIR-11223',
                notes: 'Tanqueo en estación Texaco Girardot Centro.'
            );
        }

        // 2. Base / Yard Fuel Logs (trip_id = null)
        $vToyota = Vehicle::where('plate_number', 'ABC-123')->first();
        if ($vToyota) {
            $this->createFuelLog(
                vehicle: $vToyota,
                trip: null,
                driver: null,
                refuelDate: now()->subDays(2)->setTime(16, 0),
                mileage: 12480,
                gallons: 15.00,
                cost: 220000,
                voucherNumber: 'V-BASE-0091',
                notes: 'Tanqueo preventivo en patio central previo a despacho.'
            );
        }

        $vSprinter = Vehicle::where('plate_number', 'VAN-303')->first();
        if ($vSprinter) {
            $this->createFuelLog(
                vehicle: $vSprinter,
                trip: null,
                driver: null,
                refuelDate: now()->subDay()->setTime(17, 30),
                mileage: 52080,
                gallons: 22.50,
                cost: 330000,
                voucherNumber: 'V-BASE-0092',
                notes: 'Tanqueo completo de microbús para comisión semanal.'
            );
        }

        $vCorolla = Vehicle::where('plate_number', 'SED-404')->first();
        if ($vCorolla) {
            $this->createFuelLog(
                vehicle: $vCorolla,
                trip: null,
                driver: null,
                refuelDate: now()->subHours(8),
                mileage: 8880,
                gallons: 8.50,
                cost: 135000,
                voucherNumber: 'V-BASE-0093',
                notes: 'Recarga gasolina extra en estación de servicio convenio Calle 26.'
            );
        }
    }

    private function createFuelLog(
        ?Vehicle $vehicle,
        ?Trip $trip,
        ?Driver $driver,
        \DateTimeInterface $refuelDate,
        int $mileage,
        float $gallons,
        int $cost,
        string $voucherNumber,
        string $notes
    ): void {
        if (! $vehicle) {
            return;
        }

        $voucherPath = "evidences/vouchers/{$voucherNumber}.png";
        $this->generateMockVoucher($voucherPath, $voucherNumber, $gallons, $cost, $vehicle->plate_number);

        $fuelLog = FuelLog::updateOrCreate(
            ['voucher_number' => $voucherNumber],
            [
                'vehicle_id' => $vehicle->id,
                'trip_id' => $trip?->id,
                'driver_id' => $driver?->id,
                'refuel_date' => $refuelDate,
                'mileage_at_refuel' => $mileage,
                'gallons' => $gallons,
                'total_cost' => $cost,
                'voucher_photo_path' => $voucherPath,
                'notes' => $notes,
            ]
        );

        if ($trip !== null) {
            TripEvidence::updateOrCreate(
                [
                    'trip_id' => $trip->id,
                    'type' => EvidenceTypeEnum::VOUCHER_COMBUSTIBLE,
                    'file_path' => $voucherPath,
                ],
                [
                    'recorded_mileage' => $mileage,
                    'notes' => "Voucher #{$voucherNumber} - {$notes}",
                ]
            );
        }
    }

    private function ensureVoucherDirectory(): void
    {
        $disk = Storage::disk('public');
        if (! $disk->exists('evidences/vouchers')) {
            $disk->makeDirectory('evidences/vouchers');
        }
    }

    private function generateMockVoucher(
        string $relativePath,
        string $voucherNumber,
        float $gallons,
        int $cost,
        string $plate
    ): void {
        if (Storage::disk('public')->exists($relativePath)) {
            return;
        }

        $img = imagecreatetruecolor(500, 350);
        $bgColor = imagecolorallocate($img, 255, 255, 255);
        $textColor = imagecolorallocate($img, 30, 41, 59);
        $borderColor = imagecolorallocate($img, 203, 213, 225);
        $headerColor = imagecolorallocate($img, 14, 116, 144);

        imagefilledrectangle($img, 0, 0, 500, 350, $bgColor);
        imagerectangle($img, 2, 2, 497, 347, $borderColor);

        // Header
        imagestring($img, 5, 120, 20, 'ESTACION DE SERVICIO EDS', $headerColor);
        imagestring($img, 3, 160, 45, 'CONVENIO CORPORATIVO', $textColor);
        imageline($img, 20, 70, 480, 70, $borderColor);

        // Receipt data
        $formattedCost = '$'.number_format($cost, 0, ',', '.');
        $formattedGallons = number_format($gallons, 2, ',', '.').' GAL';

        imagestring($img, 4, 30, 90, "VOUCHER: {$voucherNumber}", $textColor);
        imagestring($img, 4, 30, 125, "PLACA:   {$plate}", $textColor);
        imagestring($img, 4, 30, 160, "VOLUMEN: {$formattedGallons}", $textColor);
        imagestring($img, 5, 30, 200, "TOTAL:   {$formattedCost}", $headerColor);
        imagestring($img, 3, 30, 245, 'METODO:  CONVENIO FACTURA CREDITO', $textColor);

        imageline($img, 20, 280, 480, 280, $borderColor);
        imagestring($img, 2, 140, 300, '*** COPIA DE AUDITORIA FLOTA ***', $textColor);

        ob_start();
        imagepng($img);
        $content = (string) ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($relativePath, $content);
    }
}

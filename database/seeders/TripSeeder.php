<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Models\DigitalSignature;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureStorageDirectories();

        // 1. Resolve seed references
        $reqOps = Requester::where('document_number', '900123456-1')->first();
        $reqCom = Requester::where('document_number', '900234567-2')->first();
        $reqLog = Requester::where('document_number', '900345678-3')->first();
        $reqRRHH = Requester::where('document_number', '900456789-4')->first();
        $reqInfra = Requester::where('document_number', '900567890-5')->first();
        $reqAudit = Requester::where('document_number', '52987654')->first();

        $vToyota = Vehicle::where('plate_number', 'ABC-123')->first();
        $vMaster = Vehicle::where('plate_number', 'XYZ-789')->first();
        $vDMax = Vehicle::where('plate_number', 'FLT-101')->first();
        $vSprinter = Vehicle::where('plate_number', 'VAN-303')->first();
        $vCorolla = Vehicle::where('plate_number', 'SED-404')->first();
        $vStaria = Vehicle::where('plate_number', 'ACT-707')->first();
        $vDuster = Vehicle::where('plate_number', 'RUN-808')->first();

        $dCarlos = Driver::where('document_number', '1020304050')->first();
        $dJorge = Driver::where('document_number', '1030405060')->first();
        $dMaria = Driver::where('document_number', '1040506070')->first();
        $dJuan = Driver::where('document_number', '1050607080')->first();

        // 2. Closed Historical Trips (Full lifecycle with evidences & signature)
        $this->createClosedTrip(
            code: 'TRIP-2026-0001',
            requester: $reqOps,
            driver: $dCarlos,
            vehicle: $vToyota,
            origin: 'Sede Principal Bogotá (Calle 26 # 68-35)',
            destination: 'Estación de Bombeo Villavicencio (Meta)',
            departure: now()->subDays(7)->setTime(7, 0),
            arrival: now()->subDays(7)->setTime(12, 30),
            initialKm: 12000,
            finalKm: 12250,
            signerName: 'Ing. Roberto Sánchez (Jefe de Planta)',
            notes: 'Inspección técnica de válvulas y mantenimiento en planta.'
        );

        $this->createClosedTrip(
            code: 'TRIP-2026-0002',
            requester: $reqCom,
            driver: $dJorge,
            vehicle: $vMaster,
            origin: 'Centro Logístico Bogotá',
            destination: 'Centro de Convenciones Tunja (Boyacá)',
            departure: now()->subDays(5)->setTime(8, 30),
            arrival: now()->subDays(5)->setTime(15, 0),
            initialKm: 28000,
            finalKm: 28310,
            signerName: 'Dra. Patricia Ortiz (Directora Comercial)',
            notes: 'Transporte de material publicitario y stands corporativos.'
        );

        $this->createClosedTrip(
            code: 'TRIP-2026-0003',
            requester: $reqLog,
            driver: $dMaria,
            vehicle: $vDMax,
            origin: 'Bodega Central Fontibón',
            destination: 'Almacén Regional Girardot (Cundinamarca)',
            departure: now()->subDays(3)->setTime(6, 0),
            arrival: now()->subDays(3)->setTime(13, 15),
            initialKm: 17800,
            finalKm: 18120,
            signerName: 'Lic. Fernando Gómez (Supervisor de Almacén)',
            notes: 'Entrega de repuestos críticos para maquinaria pesada.'
        );

        $this->createClosedTrip(
            code: 'TRIP-2026-0004',
            requester: $reqInfra,
            driver: $dJuan,
            vehicle: $vSprinter,
            origin: 'Sede Norte Bogotá',
            destination: 'Campamento Obras Zipaquirá',
            departure: now()->subDays(2)->setTime(7, 30),
            arrival: now()->subDays(2)->setTime(17, 0),
            initialKm: 51800,
            finalKm: 52050,
            signerName: 'Arq. Mauricio Cárdenas (Residente de Obra)',
            notes: 'Traslado de cuadrilla de ingenieros y topógrafos.'
        );

        // 3. Completed Trip (Ready for Digital Signature & Trip Closure Test)
        $this->createCompletedTrip(
            code: 'TRIP-2026-0005',
            requester: $reqRRHH,
            driver: $dCarlos,
            vehicle: $vCorolla,
            origin: 'Sede Principal Bogotá',
            destination: 'Centro de Capacitación Chía',
            departure: now()->subHours(5),
            arrival: now()->subHours(2),
            initialKm: 8750,
            finalKm: 8900,
            notes: 'Traslado de facilitadores de bienestar laboral. Listo para capturar firma del solicitante.'
        );

        // 4. In-Progress Trip (Active in Route)
        $this->createInProgressTrip(
            code: 'TRIP-2026-0006',
            requester: $reqOps,
            driver: $dJorge,
            vehicle: $vDuster,
            origin: 'Sede Principal Bogotá',
            destination: 'Sede Operativa Ibagué (Tolima)',
            departure: now()->subHours(3),
            initialKm: 26500,
            notes: 'Comisión de supervisión en ruta por La Mesa - Anapoima.'
        );

        // 5. Assigned Trip (Ready for Driver to Start Trip in /driver)
        $this->createAssignedTrip(
            code: 'TRIP-2026-0007',
            requester: $reqCom,
            driver: $dMaria,
            vehicle: $vStaria,
            origin: 'Aeropuerto El Dorado Bogotá',
            destination: 'Hotel Estelar Melgar (Tolima)',
            scheduledDeparture: now()->addHours(2),
            scheduledArrival: now()->addHours(6),
            notes: 'Recepción de ejecutivos internacionales para convención anual.'
        );

        // 6. Scheduled Trips (Requests waiting for vehicle/driver assignment)
        $this->createScheduledTrip(
            code: 'TRIP-2026-0008',
            requester: $reqLog,
            origin: 'Terminal de Carga Cali (Valle)',
            destination: 'Puerto Marítimo Buenaventura',
            scheduledDeparture: now()->addDay()->setTime(8, 0),
            scheduledArrival: now()->addDay()->setTime(14, 0),
            notes: 'Despacho de contenedores refrigerados para exportación.'
        );

        $this->createScheduledTrip(
            code: 'TRIP-2026-0009',
            requester: $reqAudit,
            origin: 'Sede Bogotá',
            destination: 'Sucursal Pereira (Risaralda)',
            scheduledDeparture: now()->addDays(2)->setTime(6, 0),
            scheduledArrival: now()->addDays(2)->setTime(16, 0),
            notes: 'Auditoría fiscal y arqueo de inventarios en eje cafetero.'
        );

        // 7. Cancelled Trip
        $this->createCancelledTrip(
            code: 'TRIP-2026-0010',
            requester: $reqRRHH,
            origin: 'Sede Principal Bogotá',
            destination: 'Parque Jaime Duque Tocancipá',
            scheduledDeparture: now()->subDays(4)->setTime(9, 0),
            scheduledArrival: now()->subDays(4)->setTime(18, 0),
            cancelReason: 'Cancelado por solicitud del solicitante debido a alerta meteorológica de fuertes lluvias.'
        );
    }

    private function createClosedTrip(
        string $code,
        ?Requester $requester,
        ?Driver $driver,
        ?Vehicle $vehicle,
        string $origin,
        string $destination,
        \DateTimeInterface $departure,
        \DateTimeInterface $arrival,
        int $initialKm,
        int $finalKm,
        string $signerName,
        string $notes
    ): void {
        $trip = Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'driver_id' => $driver?->id,
                'vehicle_id' => $vehicle?->id,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $departure,
                'scheduled_arrival_at' => $arrival,
                'actual_departure_at' => $departure,
                'actual_arrival_at' => $arrival,
                'initial_mileage' => $initialKm,
                'final_mileage' => $finalKm,
                'distance_traveled' => $finalKm - $initialKm,
                'status' => TripStatusEnum::CERRADO,
                'notes' => $notes,
            ]
        );

        // Departure photo
        $depPath = "evidences/odometers/{$code}_salida.png";
        $this->generateMockImage($depPath, "ODOMETRO SALIDA: {$initialKm} km\n{$code}\n".Carbon::parse($departure)->format('d/m/Y H:i'));
        TripEvidence::updateOrCreate(
            ['trip_id' => $trip->id, 'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA],
            [
                'file_path' => $depPath,
                'recorded_mileage' => $initialKm,
                'notes' => "Foto odómetro inicial registrada en {$origin}",
            ]
        );

        // Arrival photo
        $arrPath = "evidences/odometers/{$code}_llegada.png";
        $this->generateMockImage($arrPath, "ODOMETRO LLEGADA: {$finalKm} km\n{$code}\n".Carbon::parse($arrival)->format('d/m/Y H:i'));
        TripEvidence::updateOrCreate(
            ['trip_id' => $trip->id, 'type' => EvidenceTypeEnum::KILOMETRAJE_LLEGADA],
            [
                'file_path' => $arrPath,
                'recorded_mileage' => $finalKm,
                'notes' => "Foto odómetro final registrada en {$destination}",
            ]
        );

        // Digital signature
        $sigPath = "evidences/signatures/{$code}_firma.png";
        $this->generateMockSignature($sigPath, $signerName);
        DigitalSignature::updateOrCreate(
            ['trip_id' => $trip->id],
            [
                'signer_name' => $signerName,
                'signature_path' => $sigPath,
                'signed_at' => $arrival,
            ]
        );
    }

    private function createCompletedTrip(
        string $code,
        ?Requester $requester,
        ?Driver $driver,
        ?Vehicle $vehicle,
        string $origin,
        string $destination,
        \DateTimeInterface $departure,
        \DateTimeInterface $arrival,
        int $initialKm,
        int $finalKm,
        string $notes
    ): void {
        $trip = Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'driver_id' => $driver?->id,
                'vehicle_id' => $vehicle?->id,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $departure,
                'scheduled_arrival_at' => $arrival,
                'actual_departure_at' => $departure,
                'actual_arrival_at' => $arrival,
                'initial_mileage' => $initialKm,
                'final_mileage' => $finalKm,
                'distance_traveled' => $finalKm - $initialKm,
                'status' => TripStatusEnum::FINALIZADO,
                'notes' => $notes,
            ]
        );

        $depPath = "evidences/odometers/{$code}_salida.png";
        $this->generateMockImage($depPath, "ODOMETRO SALIDA: {$initialKm} km\n{$code}");
        TripEvidence::updateOrCreate(
            ['trip_id' => $trip->id, 'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA],
            [
                'file_path' => $depPath,
                'recorded_mileage' => $initialKm,
                'notes' => 'Odómetro de salida verificado',
            ]
        );

        $arrPath = "evidences/odometers/{$code}_llegada.png";
        $this->generateMockImage($arrPath, "ODOMETRO LLEGADA: {$finalKm} km\n{$code}");
        TripEvidence::updateOrCreate(
            ['trip_id' => $trip->id, 'type' => EvidenceTypeEnum::KILOMETRAJE_LLEGADA],
            [
                'file_path' => $arrPath,
                'recorded_mileage' => $finalKm,
                'notes' => 'Odómetro de llegada verificado',
            ]
        );
    }

    private function createInProgressTrip(
        string $code,
        ?Requester $requester,
        ?Driver $driver,
        ?Vehicle $vehicle,
        string $origin,
        string $destination,
        \DateTimeInterface $departure,
        int $initialKm,
        string $notes
    ): void {
        $trip = Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'driver_id' => $driver?->id,
                'vehicle_id' => $vehicle?->id,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $departure,
                'scheduled_arrival_at' => Carbon::parse($departure)->addHours(6),
                'actual_departure_at' => $departure,
                'initial_mileage' => $initialKm,
                'status' => TripStatusEnum::EN_CURSO,
                'notes' => $notes,
            ]
        );

        $depPath = "evidences/odometers/{$code}_salida.png";
        $this->generateMockImage($depPath, "ODOMETRO SALIDA: {$initialKm} km\n{$code}");
        TripEvidence::updateOrCreate(
            ['trip_id' => $trip->id, 'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA],
            [
                'file_path' => $depPath,
                'recorded_mileage' => $initialKm,
                'notes' => 'Inicio de servicio en curso',
            ]
        );
    }

    private function createAssignedTrip(
        string $code,
        ?Requester $requester,
        ?Driver $driver,
        ?Vehicle $vehicle,
        string $origin,
        string $destination,
        \DateTimeInterface $scheduledDeparture,
        \DateTimeInterface $scheduledArrival,
        string $notes
    ): void {
        Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'driver_id' => $driver?->id,
                'vehicle_id' => $vehicle?->id,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $scheduledDeparture,
                'scheduled_arrival_at' => $scheduledArrival,
                'status' => TripStatusEnum::ASIGNADO,
                'notes' => $notes,
            ]
        );
    }

    private function createScheduledTrip(
        string $code,
        ?Requester $requester,
        string $origin,
        string $destination,
        \DateTimeInterface $scheduledDeparture,
        \DateTimeInterface $scheduledArrival,
        string $notes
    ): void {
        Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $scheduledDeparture,
                'scheduled_arrival_at' => $scheduledArrival,
                'status' => TripStatusEnum::PROGRAMADO,
                'notes' => $notes,
            ]
        );
    }

    private function createCancelledTrip(
        string $code,
        ?Requester $requester,
        string $origin,
        string $destination,
        \DateTimeInterface $scheduledDeparture,
        \DateTimeInterface $scheduledArrival,
        string $cancelReason
    ): void {
        Trip::updateOrCreate(
            ['code' => $code],
            [
                'requester_id' => $requester?->id ?? 1,
                'origin' => $origin,
                'destination' => $destination,
                'scheduled_departure_at' => $scheduledDeparture,
                'scheduled_arrival_at' => $scheduledArrival,
                'status' => TripStatusEnum::CANCELADO,
                'notes' => "[Cancelación]: {$cancelReason}",
            ]
        );
    }

    private function ensureStorageDirectories(): void
    {
        $disk = Storage::disk('public');
        if (! $disk->exists('evidences/odometers')) {
            $disk->makeDirectory('evidences/odometers');
        }
        if (! $disk->exists('evidences/vouchers')) {
            $disk->makeDirectory('evidences/vouchers');
        }
        if (! $disk->exists('evidences/signatures')) {
            $disk->makeDirectory('evidences/signatures');
        }
    }

    private function generateMockImage(string $relativePath, string $label): void
    {
        if (Storage::disk('public')->exists($relativePath)) {
            return;
        }

        $img = imagecreatetruecolor(600, 300);
        $bgColor = imagecolorallocate($img, 240, 243, 246);
        $textColor = imagecolorallocate($img, 15, 23, 42);
        $borderColor = imagecolorallocate($img, 148, 163, 184);

        imagefilledrectangle($img, 0, 0, 600, 300, $bgColor);
        imagerectangle($img, 2, 2, 597, 297, $borderColor);

        $lines = explode("\n", $label);
        $y = 100;
        foreach ($lines as $line) {
            imagestring($img, 5, 40, $y, $line, $textColor);
            $y += 30;
        }

        ob_start();
        imagepng($img);
        $content = (string) ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($relativePath, $content);
    }

    private function generateMockSignature(string $relativePath, string $signerName): void
    {
        if (Storage::disk('public')->exists($relativePath)) {
            return;
        }

        $img = imagecreatetruecolor(600, 220);
        $white = imagecolorallocate($img, 255, 255, 255);
        $ink = imagecolorallocate($img, 15, 23, 42);

        imagefilledrectangle($img, 0, 0, 600, 220, $white);

        // Draw stylized signature strokes
        imagesetthickness($img, 3);
        imagearc($img, 150, 110, 180, 80, 0, 300, $ink);
        imageline($img, 100, 130, 480, 110, $ink);
        imageline($img, 200, 70, 250, 150, $ink);
        imageline($img, 320, 90, 380, 140, $ink);

        // Signer name subtitle
        imagestring($img, 4, 180, 180, "Firmado: {$signerName}", $ink);

        ob_start();
        imagepng($img);
        $content = (string) ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($relativePath, $content);
    }
}

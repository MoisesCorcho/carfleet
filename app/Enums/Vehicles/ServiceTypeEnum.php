<?php

declare(strict_types=1);

namespace App\Enums\Vehicles;

enum ServiceTypeEnum: string
{
    case PUBLICO = 'publico';
    case PARTICULAR = 'particular';

    public function label(): string
    {
        return match ($this) {
            self::PUBLICO => 'Servicio Público (Placa Blanca)',
            self::PARTICULAR => 'Servicio Particular (Placa Amarilla)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLICO => 'warning',
            self::PARTICULAR => 'gray',
        };
    }
}

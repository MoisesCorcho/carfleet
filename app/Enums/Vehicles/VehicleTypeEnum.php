<?php

declare(strict_types=1);

namespace App\Enums\Vehicles;

enum VehicleTypeEnum: string
{
    case AUTOMOVIL = 'automovil';
    case CAMIONETA = 'camioneta';
    case VAN = 'van';
    case MICROBUS = 'microbus';
    case BUSETA = 'buseta';
    case CAMION = 'camion';
    case FURGON = 'furgon';
    case TRACTOCAMION = 'tractocamion';

    public function label(): string
    {
        return match ($this) {
            self::AUTOMOVIL => 'Automóvil',
            self::CAMIONETA => 'Camioneta',
            self::VAN => 'Van / Minivan',
            self::MICROBUS => 'Microbús',
            self::BUSETA => 'Buseta',
            self::CAMION => 'Camión Rígido',
            self::FURGON => 'Furgón',
            self::TRACTOCAMION => 'Tractocamión',
        };
    }
}

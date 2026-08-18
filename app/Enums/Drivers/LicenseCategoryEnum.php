<?php

declare(strict_types=1);

namespace App\Enums\Drivers;

enum LicenseCategoryEnum: string
{
    case B1 = 'B1';
    case B2 = 'B2';
    case B3 = 'B3';
    case C1 = 'C1';
    case C2 = 'C2';
    case C3 = 'C3';

    public function label(): string
    {
        return match ($this) {
            self::B1 => 'B1 · Automóviles y Camionetas (Particular)',
            self::B2 => 'B2 · Camiones Rígidos y Buses (Particular)',
            self::B3 => 'B3 · Articulados y Tractocamiones (Particular)',
            self::C1 => 'C1 · Automóviles y Camionetas (Servicio Público)',
            self::C2 => 'C2 · Camiones Rígidos y Buses (Servicio Público)',
            self::C3 => 'C3 · Articulados y Tractocamiones (Servicio Público)',
        };
    }

    public function isPublicService(): bool
    {
        return in_array($this, [self::C1, self::C2, self::C3], true);
    }

    public function isParticularService(): bool
    {
        return in_array($this, [self::B1, self::B2, self::B3], true);
    }

    public function color(): string
    {
        return match ($this) {
            self::C1, self::C2, self::C3 => 'warning',
            self::B1, self::B2, self::B3 => 'info',
        };
    }
}

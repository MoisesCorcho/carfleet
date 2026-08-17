<?php

declare(strict_types=1);

namespace App\Enums\Vehicles;

enum FuelTypeEnum: string
{
    case GASOLINA = 'gasolina';
    case DIESEL = 'diesel';
    case GAS = 'gas';
    case ELECTRICO = 'electrico';

    public function label(): string
    {
        return match ($this) {
            self::GASOLINA => 'Gasolina',
            self::DIESEL => 'Diésel',
            self::GAS => 'Gas',
            self::ELECTRICO => 'Eléctrico',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class InvalidFuelQuantityException extends DomainException
{
    public static function nonPositive(float $gallons): self
    {
        return new self("La cantidad de combustible debe ser mayor a cero ({$gallons} galones ingresados).");
    }
}

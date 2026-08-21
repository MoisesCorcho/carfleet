<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class InvalidFuelCostException extends DomainException
{
    public static function nonPositive(int $totalCost): self
    {
        return new self("El monto total debe ser estrictamente positivo ({$totalCost} ingresado).");
    }
}

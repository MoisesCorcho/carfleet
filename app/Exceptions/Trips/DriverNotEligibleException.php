<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class DriverNotEligibleException extends DomainException
{
    public static function inactive(string $driverName): self
    {
        return new self("El conductor {$driverName} no está activo en el sistema.");
    }

    public static function expiredLicense(string $driverName): self
    {
        return new self("El conductor {$driverName} tiene la licencia de conducción vencida.");
    }

    public static function notAuthorizedForPublicService(string $driverName): self
    {
        return new self("El conductor {$driverName} no posee categoría de licencia autorizada para servicio público.");
    }

    public static function cannotDeleteInService(string $driverName): self
    {
        return new self("No se puede eliminar el conductor {$driverName} porque tiene servicios activos o asignados.");
    }
}

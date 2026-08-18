<?php

declare(strict_types=1);

namespace App\Exceptions\Drivers;

use DomainException;

class InvalidDriverException extends DomainException
{
    public static function userAlreadyAssigned(int $userId): self
    {
        return new self("El usuario con ID #{$userId} ya tiene un perfil de conductor asociado.");
    }

    public static function expiredLicense(string $expiresAt): self
    {
        return new self("La fecha de vencimiento de la licencia ({$expiresAt}) es anterior a la fecha actual.");
    }
}

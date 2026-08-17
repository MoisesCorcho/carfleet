<?php

declare(strict_types=1);

namespace App\Enums\Drivers;

enum DriverStatusEnum: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case SUSPENDIDO = 'suspendido';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
            self::SUSPENDIDO => 'Suspendido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVO => 'success',
            self::INACTIVO => 'gray',
            self::SUSPENDIDO => 'danger',
        };
    }
}

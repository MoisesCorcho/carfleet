<?php

declare(strict_types=1);

namespace App\Enums\Trips;

enum TripStatusEnum: string
{
    case PROGRAMADO = 'programado';
    case ASIGNADO = 'asignado';
    case EN_CURSO = 'en_curso';
    case FINALIZADO = 'finalizado';
    case CERRADO = 'cerrado';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PROGRAMADO => 'Programado',
            self::ASIGNADO => 'Asignado',
            self::EN_CURSO => 'En Curso',
            self::FINALIZADO => 'Finalizado',
            self::CERRADO => 'Cerrado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROGRAMADO => 'gray',
            self::ASIGNADO => 'info',
            self::EN_CURSO => 'warning',
            self::FINALIZADO => 'primary',
            self::CERRADO => 'success',
            self::CANCELADO => 'danger',
        };
    }
}

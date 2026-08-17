<?php

declare(strict_types=1);

namespace App\Enums\Vehicles;

enum VehicleStatusEnum: string
{
    case DISPONIBLE = 'disponible';
    case ASIGNADO = 'asignado';
    case EN_VIAJE = 'en_viaje';
    case MANTENIMIENTO = 'mantenimiento';
    case FUERA_DE_SERVICIO = 'fuera_de_servicio';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'Disponible',
            self::ASIGNADO => 'Asignado',
            self::EN_VIAJE => 'En Viaje',
            self::MANTENIMIENTO => 'En Mantenimiento',
            self::FUERA_DE_SERVICIO => 'Fuera de Servicio',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'success',
            self::ASIGNADO => 'info',
            self::EN_VIAJE => 'warning',
            self::MANTENIMIENTO => 'danger',
            self::FUERA_DE_SERVICIO => 'gray',
        };
    }
}

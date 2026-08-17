<?php

declare(strict_types=1);

namespace App\Enums\Evidences;

enum EvidenceTypeEnum: string
{
    case KILOMETRAJE_SALIDA = 'kilometraje_salida';
    case KILOMETRAJE_LLEGADA = 'kilometraje_llegada';
    case VOUCHER_COMBUSTIBLE = 'voucher_combustible';

    public function label(): string
    {
        return match ($this) {
            self::KILOMETRAJE_SALIDA => 'Kilometraje de Salida',
            self::KILOMETRAJE_LLEGADA => 'Kilometraje de Llegada',
            self::VOUCHER_COMBUSTIBLE => 'Voucher de Combustible',
        };
    }
}

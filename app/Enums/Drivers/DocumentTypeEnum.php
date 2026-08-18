<?php

declare(strict_types=1);

namespace App\Enums\Drivers;

enum DocumentTypeEnum: string
{
    case CC = 'CC';
    case CE = 'CE';
    case PA = 'PA';
    case PPT = 'PPT';
    case PEP = 'PEP';

    public function label(): string
    {
        return match ($this) {
            self::CC => 'Cédula de Ciudadanía (CC)',
            self::CE => 'Cédula de Extranjería (CE)',
            self::PA => 'Pasaporte (PA)',
            self::PPT => 'Permiso por Protección Temporal (PPT)',
            self::PEP => 'Permiso Especial de Permanencia (PEP)',
        };
    }

    public function shortLabel(): string
    {
        return $this->value;
    }
}

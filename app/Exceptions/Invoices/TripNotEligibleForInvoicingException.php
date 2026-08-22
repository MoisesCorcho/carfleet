<?php

declare(strict_types=1);

namespace App\Exceptions\Invoices;

use App\Enums\Trips\TripStatusEnum;
use DomainException;

final class TripNotEligibleForInvoicingException extends DomainException
{
    public static function forTrip(string $tripCode, TripStatusEnum $status): self
    {
        return new self("El viaje {$tripCode} no es elegible para facturar porque se encuentra en estado '{$status->label()}'. Solo los viajes en estado 'Cerrado' pueden ser facturados.");
    }
}

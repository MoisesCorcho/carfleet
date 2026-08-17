<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'trip_id',
        'driver_id',
        'refuel_date',
        'mileage_at_refuel',
        'gallons',
        'total_cost',
        'voucher_number',
        'voucher_photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'refuel_date' => 'datetime',
            'mileage_at_refuel' => 'integer',
            'gallons' => 'decimal:2',
            'total_cost' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FuelLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property int|null $trip_id
 * @property int|null $driver_id
 * @property Carbon $refuel_date
 * @property int $mileage_at_refuel
 * @property string $gallons
 * @property int $total_cost
 * @property string|null $voucher_number
 * @property string|null $voucher_photo_path
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class FuelLog extends Model
{
    /** @use HasFactory<FuelLogFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (FuelLog $fuelLog): void {
            if ($fuelLog->voucher_photo_path && Storage::disk('public')->exists($fuelLog->voucher_photo_path)) {
                Storage::disk('public')->delete($fuelLog->voucher_photo_path);
            }
        });
    }

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

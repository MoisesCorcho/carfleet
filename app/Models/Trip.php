<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Trips\TripStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'requester_id',
        'vehicle_id',
        'driver_id',
        'origin',
        'destination',
        'scheduled_departure_at',
        'scheduled_arrival_at',
        'actual_departure_at',
        'actual_arrival_at',
        'initial_mileage',
        'final_mileage',
        'distance_traveled',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_departure_at' => 'datetime',
            'scheduled_arrival_at' => 'datetime',
            'actual_departure_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
            'initial_mileage' => 'integer',
            'final_mileage' => 'integer',
            'distance_traveled' => 'integer',
            'status' => TripStatusEnum::class,
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(TripEvidence::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function signature(): HasOne
    {
        return $this->hasOne(DigitalSignature::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class)->withPivot('subtotal_amount')->withTimestamps();
    }
}

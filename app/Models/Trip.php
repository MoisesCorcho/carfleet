<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property int $requester_id
 * @property int|null $vehicle_id
 * @property int|null $driver_id
 * @property string $origin
 * @property string $destination
 * @property Carbon $scheduled_departure_at
 * @property Carbon|null $scheduled_arrival_at
 * @property Carbon|null $actual_departure_at
 * @property Carbon|null $actual_arrival_at
 * @property int|null $initial_mileage
 * @property int|null $final_mileage
 * @property int|null $distance_traveled
 * @property TripStatusEnum $status
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Trip $trip): void {
            if ($trip->vehicle_id && $trip->vehicle?->status === VehicleStatusEnum::ASIGNADO) {
                $trip->vehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
            }
        });
    }

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

    public function durationInMinutes(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (! $this->actual_departure_at || ! $this->actual_arrival_at) {
                return null;
            }

            return (int) $this->actual_departure_at->diffInMinutes($this->actual_arrival_at);
        });
    }

    public function durationForHumans(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->actual_departure_at || ! $this->actual_arrival_at) {
                return null;
            }

            return $this->actual_departure_at->diffForHumans($this->actual_arrival_at, true);
        });
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

    public function isScheduled(): bool
    {
        return $this->status === TripStatusEnum::PROGRAMADO;
    }

    public function isAssigned(): bool
    {
        return $this->status === TripStatusEnum::ASIGNADO;
    }

    public function isInProgress(): bool
    {
        return $this->status === TripStatusEnum::EN_CURSO;
    }

    public function isCompleted(): bool
    {
        return $this->status === TripStatusEnum::FINALIZADO;
    }

    public function isClosed(): bool
    {
        return $this->status === TripStatusEnum::CERRADO;
    }

    public function isCancelled(): bool
    {
        return $this->status === TripStatusEnum::CANCELADO;
    }

    public function isImmutable(): bool
    {
        return $this->isClosed() || $this->isCancelled();
    }

    public function canBeAssigned(): bool
    {
        return $this->isScheduled() || $this->isAssigned();
    }

    public function canBeStarted(): bool
    {
        return $this->isAssigned();
    }

    public function canBeFinished(): bool
    {
        return $this->isInProgress();
    }

    public function canBeCancelled(): bool
    {
        return ! $this->isImmutable() && ! $this->isInProgress() && ! $this->isCompleted();
    }

    public function canBeSigned(): bool
    {
        return $this->isCompleted() && ! $this->isImmutable();
    }

    public function canBeClosed(): bool
    {
        return $this->isCompleted()
            && ! $this->isImmutable()
            && $this->initial_mileage !== null
            && $this->final_mileage !== null
            && $this->signature !== null;
    }

    /**
     * @param  Builder<Trip>  $query
     * @return Builder<Trip>
     */
    public function scopeForDriver(Builder $query, int $driverId): Builder
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * @param  Builder<Trip>  $query
     * @return Builder<Trip>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TripStatusEnum::PROGRAMADO,
            TripStatusEnum::ASIGNADO,
            TripStatusEnum::EN_CURSO,
        ]);
    }
}

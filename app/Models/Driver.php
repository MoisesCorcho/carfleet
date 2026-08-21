<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property DocumentTypeEnum $document_type
 * @property string $document_number
 * @property string $phone
 * @property string $license_number
 * @property LicenseCategoryEnum $license_category
 * @property Carbon|null $license_expires_at
 * @property DriverStatusEnum $status
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::updating(function (Driver $driver): void {
            $hasActiveTrips = $driver->trips()
                ->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])
                ->exists();

            if (! $hasActiveTrips) {
                return;
            }

            if ($driver->isDirty('status') && $driver->status !== DriverStatusEnum::ACTIVO) {
                throw DriverNotEligibleException::cannotDeactivateInService($driver->full_name);
            }

            if ($driver->isDirty('user_id')) {
                throw DriverNotEligibleException::cannotChangeUserInService($driver->full_name);
            }

            if ($driver->isDirty('license_category')) {
                throw DriverNotEligibleException::cannotChangeLicenseCategoryInService($driver->full_name);
            }
        });

        static::deleting(function (Driver $driver): void {
            $hasActiveTrips = $driver->trips()
                ->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])
                ->exists();

            if ($hasActiveTrips) {
                throw DriverNotEligibleException::cannotDeleteInService($driver->full_name);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'full_name',
        'document_type',
        'document_number',
        'phone',
        'license_number',
        'license_category',
        'license_expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentTypeEnum::class,
            'license_category' => LicenseCategoryEnum::class,
            'license_expires_at' => 'date',
            'status' => DriverStatusEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function activeTrip(): HasOne
    {
        return $this->hasOne(Trip::class)
            ->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])
            ->latestOfMany();
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function formattedDocument(): string
    {
        return "{$this->document_type->value} {$this->document_number}";
    }

    public function isActive(): bool
    {
        return $this->status === DriverStatusEnum::ACTIVO;
    }

    public function isLicenseExpired(): bool
    {
        if ($this->license_expires_at === null) {
            return false;
        }

        return $this->license_expires_at->isPast() && ! $this->license_expires_at->isToday();
    }

    public function hasValidLicense(): bool
    {
        return ! $this->isLicenseExpired();
    }

    public function isEligibleForTrip(): bool
    {
        return $this->isActive() && $this->hasValidLicense();
    }

    public function canDrivePublicService(): bool
    {
        return $this->license_category->isPublicService();
    }

    /**
     * @param  Builder<Driver>  $query
     * @return Builder<Driver>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', DriverStatusEnum::ACTIVO);
    }

    /**
     * @param  Builder<Driver>  $query
     * @return Builder<Driver>
     */
    public function scopeEligibleForTrip(Builder $query): Builder
    {
        return $query->where('status', DriverStatusEnum::ACTIVO)
            ->where(function (Builder $q): void {
                $q->whereNull('license_expires_at')
                    ->orWhereDate('license_expires_at', '>=', now()->toDateString());
            });
    }
}

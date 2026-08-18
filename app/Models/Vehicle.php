<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $plate_number
 * @property string $brand
 * @property string $model
 * @property int $year
 * @property VehicleTypeEnum $vehicle_type
 * @property ServiceTypeEnum $service_type
 * @property int $current_mileage
 * @property VehicleStatusEnum $status
 * @property FuelTypeEnum $fuel_type
 * @property string|null $notes
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'vehicle_type',
        'service_type',
        'current_mileage',
        'status',
        'fuel_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'vehicle_type' => VehicleTypeEnum::class,
            'service_type' => ServiceTypeEnum::class,
            'current_mileage' => 'integer',
            'status' => VehicleStatusEnum::class,
            'fuel_type' => FuelTypeEnum::class,
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === VehicleStatusEnum::DISPONIBLE;
    }

    public function isPublicService(): bool
    {
        return $this->service_type === ServiceTypeEnum::PUBLICO;
    }

    public function canBeAssigned(): bool
    {
        return $this->isAvailable();
    }

    /**
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', VehicleStatusEnum::DISPONIBLE);
    }

    /**
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeInService(Builder $query): Builder
    {
        return $query->whereIn('status', [
            VehicleStatusEnum::DISPONIBLE,
            VehicleStatusEnum::ASIGNADO,
            VehicleStatusEnum::EN_VIAJE,
        ]);
    }
}

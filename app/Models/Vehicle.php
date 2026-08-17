<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'current_mileage',
        'status',
        'fuel_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
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
}

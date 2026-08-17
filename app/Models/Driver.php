<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Drivers\DriverStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'document_number',
        'phone',
        'license_number',
        'license_expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
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

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }
}

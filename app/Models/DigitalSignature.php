<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\TripImmutableException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $trip_id
 * @property string $signer_name
 * @property string $signature_path
 * @property Carbon|null $signed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DigitalSignature extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (DigitalSignature $signature): void {
            if ($signature->isImmutable()) {
                throw TripImmutableException::forTrip(
                    $signature->trip?->code ?? 'N/A',
                    $signature->trip?->status ?? TripStatusEnum::CERRADO
                );
            }

            if ($signature->signature_path && Storage::disk('public')->exists($signature->signature_path)) {
                Storage::disk('public')->delete($signature->signature_path);
            }
        });
    }

    public function isImmutable(): bool
    {
        return $this->trip?->isImmutable() ?? false;
    }

    protected $fillable = [
        'trip_id',
        'signer_name',
        'signature_path',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}

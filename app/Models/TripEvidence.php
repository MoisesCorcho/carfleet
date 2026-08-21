<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\TripImmutableException;
use Database\Factories\TripEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $trip_id
 * @property EvidenceTypeEnum $type
 * @property string $file_path
 * @property int|null $recorded_mileage
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TripEvidence extends Model
{
    /** @use HasFactory<TripEvidenceFactory> */
    use HasFactory;

    protected $table = 'trip_evidences';

    protected static function booted(): void
    {
        static::deleting(function (TripEvidence $evidence): void {
            if ($evidence->isImmutable()) {
                throw TripImmutableException::forTrip(
                    $evidence->trip?->code ?? 'N/A',
                    $evidence->trip?->status ?? TripStatusEnum::CERRADO
                );
            }

            if ($evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
                Storage::disk('public')->delete($evidence->file_path);
            }
        });
    }

    public function isImmutable(): bool
    {
        return $this->trip?->isImmutable() ?? false;
    }

    protected $fillable = [
        'trip_id',
        'type',
        'file_path',
        'recorded_mileage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => EvidenceTypeEnum::class,
            'recorded_mileage' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}

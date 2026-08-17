<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Evidences\EvidenceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripEvidence extends Model
{
    use HasFactory;

    protected $table = 'trip_evidences';

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

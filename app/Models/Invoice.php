<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'requester_id',
        'issue_date',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'total_amount' => 'integer',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class);
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class)->withPivot('subtotal_amount')->withTimestamps();
    }
}

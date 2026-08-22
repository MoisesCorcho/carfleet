<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Invoices\InvoiceStatusEnum;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $invoice_number
 * @property int $requester_id
 * @property Carbon $issue_date
 * @property int $total_amount
 * @property InvoiceStatusEnum $status
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
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
            'status' => InvoiceStatusEnum::class,
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

    public function isIssued(): bool
    {
        return $this->status === InvoiceStatusEnum::EMITIDA;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatusEnum::PAGADA;
    }

    public function isCancelled(): bool
    {
        return $this->status === InvoiceStatusEnum::ANULADA;
    }

    public function isImmutable(): bool
    {
        return $this->isPaid() || $this->isCancelled();
    }

    public function canBeCancelled(): bool
    {
        return $this->isIssued();
    }

    public function canBeMarkedPaid(): bool
    {
        return $this->isIssued();
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatusEnum::EMITIDA,
            InvoiceStatusEnum::PAGADA,
        ]);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatusEnum::EMITIDA);
    }
}

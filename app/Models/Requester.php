<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Requesters\RequesterDocumentTypeEnum;
use Database\Factories\RequesterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $company_name
 * @property RequesterDocumentTypeEnum $document_type
 * @property string $document_number
 * @property string $phone
 * @property string|null $email
 * @property bool $is_active
 * @property string|null $notes
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $display_name
 * @property-read string $formatted_document
 */
class Requester extends Model
{
    /** @use HasFactory<RequesterFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => RequesterDocumentTypeEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function formattedDocument(): string
    {
        return "{$this->document_type->value} {$this->document_number}";
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name !== null && trim($this->company_name) !== '') {
            return "{$this->company_name} — {$this->name}";
        }

        return $this->name;
    }
}

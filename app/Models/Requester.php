<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'document_number',
        'phone',
        'email',
        'notes',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

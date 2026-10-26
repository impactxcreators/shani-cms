<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'original_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->original_price > 0 && $this->sale_price !== null) {
            return (int) round(100 - ($this->sale_price / $this->original_price * 100));
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Handpick extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}

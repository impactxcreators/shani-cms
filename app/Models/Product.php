<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use SoftDeletes, HasSlug;

    protected $guarded = [];

    protected $casts = [
        'highlights' => 'array',
        'rating' => 'decimal:2',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')
            ->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class, 'product_category_id'); }
}

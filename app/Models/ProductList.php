<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRedirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ProductList extends Model
{
    use SoftDeletes, HasSlug, HasSlugRedirect;

    protected $guarded = [];

    protected $casts = [
        'faq_items' => 'array',
        'related_list_ids' => 'array',
        'no_index' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')
            ->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class, 'product_category_id'); }

    public function items(): HasMany { return $this->hasMany(ProductListItem::class)->orderBy('rank'); }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function slugRoutePrefix(): string
    {
        return '';
    }
}

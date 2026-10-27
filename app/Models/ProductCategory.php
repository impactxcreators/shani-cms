<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ProductCategory extends Model
{
    use HasSlug;

    protected $guarded = [];
    protected $casts = ['is_featured' => 'boolean'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')
            ->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function parent(): BelongsTo { return $this->belongsTo(ProductCategory::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(ProductCategory::class, 'parent_id'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function productLists(): HasMany { return $this->hasMany(ProductList::class, 'product_category_id'); }
}

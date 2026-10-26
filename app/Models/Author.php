<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Author extends Model
{
    use HasSlug;

    protected $guarded = [];
    protected $casts = ['socials' => 'array'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')
            ->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): HasMany { return $this->hasMany(Post::class); }
}

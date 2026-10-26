<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use SoftDeletes, HasSlug;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'no_index' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')
            ->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function author(): BelongsTo { return $this->belongsTo(Author::class); }
    public function categories(): BelongsToMany { return $this->belongsToMany(BlogCategory::class); }
    public function comments(): MorphMany { return $this->morphMany(Comment::class, 'commentable'); }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}

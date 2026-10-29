<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefererDestination extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'checked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (RefererDestination $d) {
            $d->url = RefererLink::normalizeUrl($d->url);
            $d->url_hash = static::hash($d->url);
            if ($d->isDirty('url')) {
                $d->check_status = null; // re-check the new address
                $d->check_code = null;
                $d->checked_at = null;
            }
        });
    }

    public static function hash(string $url): string
    {
        return hash('sha256', RefererLink::normalizeUrl($url));
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(RefererLink::class, 'referer_link_id');
    }
}

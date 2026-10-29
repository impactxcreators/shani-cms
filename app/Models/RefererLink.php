<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefererLink extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    public const TYPES = [
        'direct' => 'Direct Referer',
        'blank' => 'Blank Referer',
        'rotational' => 'Rotational',
    ];

    public const MODES = [
        'in_order' => 'In order (A → B → C → A…)',
        'random' => 'Random',
        'weighted' => 'Weighted (e.g. 70% / 30%)',
    ];

    protected static function booted(): void
    {
        static::creating(function (RefererLink $link) {
            if (blank($link->code)) {
                $link->code = static::uniqueCode();
            }
        });
    }

    public static function uniqueCode(): string
    {
        do {
            $code = Str::random(11);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /** "r-bloop.cc/abc" → "https://r-bloop.cc/abc" */
    public static function normalizeUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    public static function isValidUrl(?string $url): bool
    {
        $url = static::normalizeUrl($url);

        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false && strlen($url) <= 2000;
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(RefererDestination::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getShortUrlAttribute(): string
    {
        return url('/ref/'.$this->code);
    }

    public function isLive(): bool
    {
        return $this->is_active && (! $this->expires_at || $this->expires_at->isFuture());
    }

    /** Which destination this click goes to. */
    public function pickDestination(): ?RefererDestination
    {
        $dests = $this->destinations->where('is_active', true)->values();
        if ($dests->isEmpty()) {
            return null;
        }
        if ($this->type !== 'rotational' || $dests->count() === 1) {
            return $dests->first();
        }

        return match ($this->rotation_mode) {
            'random' => $dests->random(),
            'weighted' => $this->pickWeighted($dests),
            default => $dests[$this->nextPointer() % $dests->count()],
        };
    }

    private function pickWeighted(Collection $dests): RefererDestination
    {
        $total = $dests->sum(fn ($d) => max(0, (int) $d->weight));
        if ($total <= 0) {
            return $dests->random();
        }
        $roll = random_int(1, $total);
        foreach ($dests as $d) {
            $roll -= max(0, (int) $d->weight);
            if ($roll <= 0) {
                return $d;
            }
        }

        return $dests->last();
    }

    /** next position for "in order" rotation; safe when two clicks arrive at once */
    private function nextPointer(): int
    {
        return DB::transaction(function () {
            $p = (int) static::whereKey($this->getKey())->lockForUpdate()->value('rotation_pointer');
            static::whereKey($this->getKey())->update(['rotation_pointer' => $p + 1]);

            return $p;
        });
    }

    /** Other short links that already send visitors to this url. */
    public static function existingFor(?string $url, ?int $exceptLinkId = null, ?string $exceptBatch = null): Collection
    {
        $url = static::normalizeUrl($url);
        if ($url === '') {
            return collect();
        }

        return static::whereHas('destinations', fn ($q) => $q->where('url_hash', RefererDestination::hash($url)))
            ->when($exceptLinkId, fn ($q) => $q->whereKeyNot($exceptLinkId))
            ->when($exceptBatch, fn ($q) => $q->where(fn ($w) => $w->whereNull('batch')->orWhere('batch', '!=', $exceptBatch)))
            ->orderBy('id')
            ->limit(3)
            ->get();
    }

    public static function duplicateNotice(?string $url, ?int $exceptLinkId = null): ?string
    {
        $others = static::existingFor($url, $exceptLinkId);

        return $others->isEmpty() ? null
            : 'Heads up: this destination already has a short link ('.$others->map->short_url->implode(', ').'). You can still create another.';
    }

    /** id => label, for pickers */
    public static function searchOptions(?string $q = null): array
    {
        return static::query()
            ->when(filled($q), fn ($query) => $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")
                ->orWhereHas('destinations', fn ($d) => $d->where('url', 'like', "%{$q}%"))))
            ->with('destinations')
            ->latest('id')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn ($l) => [$l->id => '/ref/'.$l->code.' → '.($l->name ?: Str::limit((string) $l->destinations->first()?->url, 60))])
            ->all();
    }

    /**
     * Bulk add: one short link per pasted line (direct / blank),
     * or one rotating link with every line as a destination (rotational).
     */
    public static function bulkCreate(string $type, ?string $mode, string $text, ?string $name = null): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $text))->map(fn ($l) => trim($l))->filter()->values();
        $valid = $lines->filter(fn ($l) => static::isValidUrl($l))->map(fn ($l) => static::normalizeUrl($l))->values();
        $invalid = $lines->reject(fn ($l) => static::isValidUrl($l))->values()->all();
        $batch = (string) Str::uuid();

        DB::transaction(function () use ($type, $mode, $valid, $name, $batch) {
            if ($valid->isEmpty()) {
                return;
            }
            if ($type === 'rotational') {
                $link = static::create(['type' => 'rotational', 'rotation_mode' => $mode ?: 'in_order', 'name' => $name, 'batch' => $batch]);
                foreach ($valid as $i => $url) {
                    $link->destinations()->create(['url' => $url, 'sort_order' => $i]);
                }

                return;
            }
            foreach ($valid as $url) {
                $link = static::create(['type' => $type, 'name' => $name, 'batch' => $batch]);
                $link->destinations()->create(['url' => $url]);
            }
        });

        return ['batch' => $batch, 'created' => $valid->count(), 'invalid' => $invalid];
    }
}

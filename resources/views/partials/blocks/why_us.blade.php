@php
    $points = collect($block->config['points'] ?? [])->filter(fn ($p) => filled($p['title'] ?? null))->values();
    if ($points->isEmpty()) {
        $points = collect(\App\Support\HomeDefaults::WHY_US);
    }
@endphp
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Why shoppers use '.\App\Models\Setting::get('site_name', config('app.name')) }}</h2>
    <div class="whygrid">
        @foreach($points as $p)
            <div class="whyitem">
                <span class="whyicon">{{ $p['icon'] ?? '✓' }}</span>
                <h3>{{ $p['title'] }}</h3>
                @if(filled($p['text'] ?? null))<p>{{ $p['text'] }}</p>@endif
            </div>
        @endforeach
    </div>
</section>

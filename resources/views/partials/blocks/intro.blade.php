@php
    $stats = \App\Support\OfferFeed::stats(\App\Models\Store::published()->select('stores.id'));
    $categories = \App\Models\Category::count();
    $line = \App\Support\OfferFeed::intro($stats);
    if ($line && $categories) {
        $line = preg_replace('/ (stores?)\./', ' $1 across '.$categories.' '.\Illuminate\Support\Str::plural('category', $categories).'.', $line, 1);
    }
@endphp
<section class="homeintro">
    <h1>{{ $block->title ?: 'Coupon codes & deals from brands you trust' }}</h1>
    @if(filled($block->config['text'] ?? null))<p class="homeintro-text">{{ $block->config['text'] }}</p>@endif
    @if($line)<p class="offerintro">{{ $line }}</p>@endif
</section>

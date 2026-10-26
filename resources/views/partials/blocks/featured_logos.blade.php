@php($items = \App\Models\Store::published()->where('is_featured', true)->limit((int)($block->config['limit'] ?? 12))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Featured stores' }}</h2>
    <div class="grid cols2">
        @foreach($items as $store)
            <a class="card searchresult" href="{{ url('/store/'.$store->slug) }}" style="text-decoration:none;color:inherit;display:flex;gap:14px;align-items:center">
                @if($store->logo_url)
                    <img class="brandlogo-lg" src="{{ $store->logo_url }}" alt="{{ $store->name }} logo">
                @else
                    <span class="brandlogo-lg logoavatar" style="font-size:1.3rem">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                @endif
                <span style="min-width:0">
                    <span style="display:block;font-weight:700;color:var(--fg)">{{ $store->name }}</span>
                    <span class="badges" style="margin-top:4px">
                        <span class="pill save">Featured</span>
                        @if($store->average_rating)<span class="rating">&starf; {{ rtrim(rtrim(number_format($store->average_rating, 1), '0'), '.') }}</span>@endif
                    </span>
                </span>
            </a>
        @endforeach
    </div>
</section>

@php($items = \App\Models\Store::published()->where('is_featured', true)->limit((int)($block->config['limit'] ?? 12))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Featured stores' }}</h2>
    <div class="grid cols2">
        @foreach($items as $store)
            <a class="card" href="{{ url('/store/'.$store->slug) }}" style="text-align:center;text-decoration:none">
                @if($store->logo)<img src="{{ $store->logo }}" alt="{{ $store->name }}" style="height:48px;object-fit:contain">@endif
                <div>{{ $store->name }}</div>
            </a>
        @endforeach
    </div>
</section>

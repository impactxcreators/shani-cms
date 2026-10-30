@php
    $limit = (int) ($block->config['limit'] ?? 6) ?: 6;
    $groups = \App\Models\Category::whereHas('stores', fn ($query) => $query->published())
        ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')
        ->limit($limit)->get()
        ->map(fn ($cat) => [
            'cat' => $cat,
            'stores' => $cat->stores()->published()
                ->withCount(['coupons as active_coupons_count' => fn ($query) => $query->active()])
                ->orderByDesc('is_featured')->orderBy('name')->limit(6)->get(),
        ]);
@endphp
@if($groups->isNotEmpty())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Featured stores by category' }}</h2>
    <div class="catboxes">
        @foreach($groups as $g)
            <div class="catbox">
                <a class="catbox-title" href="{{ route('category.show', $g['cat']->slug) }}">{{ $g['cat']->name }}</a>
                <ul class="storelist">
                    @foreach($g['stores'] as $store)
                        <li>
                            <a href="{{ route('store.show', $store->slug) }}">
                                @if($store->logo_url)
                                    <img src="{{ $store->logo_url }}" alt="" loading="lazy" width="32" height="32">
                                @else
                                    <span class="logoavatar">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                                @endif
                                <span>{{ $store->name }}</span>
                                <small>{{ $store->active_coupons_count }} {{ \Illuminate\Support\Str::plural('coupon', $store->active_coupons_count) }}</small>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>
@endif

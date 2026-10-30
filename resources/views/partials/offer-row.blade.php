@php
    $isCoupon = $offer instanceof \App\Models\Coupon;
    $store = $offer->store;
    $money = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $big = null;
    $unit = 'OFF';
    if ($isCoupon) {
        if ($offer->discount_type === 'percentage' && $offer->discount_value > 0) {
            $big = $money($offer->discount_value).'%';
        } elseif ($offer->discount_type === 'fixed' && $offer->discount_value > 0) {
            $big = '$'.$money($offer->discount_value);
        } elseif ($offer->discount_type === 'free_shipping') {
            [$big, $unit] = ['FREE', 'SHIPPING'];
        }
    } elseif ($offer->discount_percent > 0) {
        $big = $offer->discount_percent.'%';
    } elseif ($offer->sale_price > 0) {
        [$big, $unit] = ['$'.$money($offer->sale_price), 'ONLY'];
    }
    if (! $big) {
        [$big, $unit] = [$isCoupon && $offer->code ? 'CODE' : 'DEAL', ''];
    }
    $hasCode = $isCoupon && filled($offer->code);
    $go = url('/go/'.($isCoupon ? 'coupon' : 'deal').'/'.$offer->id);
    $storeUrl = $store ? route('store.show', $store->slug) : null;
    $desc = filled($offer->description) ? \Illuminate\Support\Str::limit(trim(strip_tags($offer->description)), 170) : null;
    $endsSoon = $offer->expires_at && $offer->expires_at->isFuture() && $offer->expires_at->lte(now()->addDays(7));
@endphp
<article class="offer" data-kind="{{ $isCoupon ? 'coupon' : 'deal' }}">
    <a class="offer-logo" href="{{ $storeUrl ?? $go }}" aria-label="{{ $store?->name }}">
        @if($store?->logo_url)
            <img src="{{ $store->logo_url }}" alt="{{ $store->name }} logo" loading="lazy" width="64" height="64">
        @else
            <span class="logoavatar">{{ strtoupper(substr($store?->name ?? '?', 0, 1)) }}</span>
        @endif
    </a>
    <div class="offer-off"><b>{{ $big }}</b>@if($unit)<span>{{ $unit }}</span>@endif</div>
    <div class="offer-body">
        @if($store)<a class="offer-store" href="{{ $storeUrl }}">{{ $store->name }}</a>@endif
        <h3 class="offer-title">{{ $offer->title }}</h3>
        @if(! $isCoupon && $offer->sale_price > 0 && $offer->original_price > 0)
            <div class="offer-price"><span class="price">${{ $money($offer->sale_price) }}</span><span class="was">${{ $money($offer->original_price) }}</span></div>
        @endif
        @if($desc)<p class="offer-desc">{{ $desc }}</p>@endif
        <div class="badges">
            <span class="pill">{{ $hasCode ? 'Code' : 'Deal' }}</span>
            @if($isCoupon && $offer->is_verified)<span class="pill good">&check; Verified</span>@endif
            @if($isCoupon && $offer->is_exclusive)<span class="pill new">Exclusive</span>@endif
            @if(! $isCoupon && $offer->is_featured)<span class="pill hot">Hot</span>@endif
            @if($endsSoon)
                <span class="pill hot">Ends soon</span>
            @elseif($offer->expires_at)
                <span class="pill muted-pill">Expires {{ $offer->expires_at->format('M j, Y') }}</span>
            @endif
        </div>
    </div>
    <div class="offer-cta">
        @if($hasCode)
            <a class="btn" href="{{ $go }}" target="_blank" rel="sponsored nofollow noopener" data-reveal data-code="{{ $offer->code }}" data-store="{{ $store?->name }}" data-title="{{ $offer->title }}" data-logo="{{ $store?->logo_url }}">Show code</a>
            <span class="code" hidden>{{ $offer->code }}</span>
        @else
            <a class="btn" href="{{ $go }}" target="_blank" rel="sponsored nofollow noopener">Show deal</a>
        @endif
    </div>
</article>

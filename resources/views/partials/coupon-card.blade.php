@php
    $disc = null;
    if ($coupon->discount_type === 'percentage' && $coupon->discount_value) {
        $disc = rtrim(rtrim(number_format((float) $coupon->discount_value, 2), '0'), '.') . '% OFF';
    } elseif ($coupon->discount_type === 'fixed' && $coupon->discount_value) {
        $disc = '$' . rtrim(rtrim(number_format((float) $coupon->discount_value, 2), '0'), '.') . ' OFF';
    } elseif ($coupon->discount_type === 'free_shipping') {
        $disc = 'Free shipping';
    }
    $endsSoon = $coupon->expires_at && $coupon->expires_at->isFuture() && $coupon->expires_at->lte(now()->addDays(7));
    $withStore = ($showStore ?? false) && $coupon->store;
@endphp
<div class="card">
    <div style="display:flex;gap:12px">
        @if($withStore)
            @if($coupon->store->logo_url)
                <img class="brandlogo" src="{{ $coupon->store->logo_url }}" alt="{{ $coupon->store->name }} logo">
            @else
                <span class="brandlogo logoavatar">{{ strtoupper(substr($coupon->store->name, 0, 1)) }}</span>
            @endif
        @endif
        <div style="flex:1;min-width:0">
            @if($withStore)<div class="muted-sm">{{ $coupon->store->name }}</div>@endif
            <h3 style="margin:.1rem 0 .45rem">{{ $coupon->title }}</h3>
            <div class="badges">
                @if($disc)<span class="pill save">{{ $disc }}</span>@endif
                @if($coupon->is_verified)<span class="pill good">&check; Verified</span>@endif
                @if($coupon->is_exclusive)<span class="pill new">Exclusive</span>@endif
                @if($endsSoon)<span class="pill hot">Ends soon</span>@endif
            </div>
        </div>
    </div>
    @if($coupon->description)<p class="muted-sm" style="margin:.6rem 0 0">{{ $coupon->description }}</p>@endif
    @if($coupon->code)
        <div class="codebox" x-data="{shown:false}">
            <a class="btn" href="{{ url('/go/coupon/' . $coupon->id) }}" target="_blank" rel="sponsored nofollow noopener"
               onclick="this.closest('.codebox').querySelector('.code').style.display='inline-block';this.style.display='none'">Show code</a>
            <span class="code" style="display:none">{{ $coupon->code }}</span>
        </div>
    @else
        <div style="margin-top:12px">
            <a class="btn" href="{{ url('/go/coupon/' . $coupon->id) }}" target="_blank" rel="sponsored nofollow noopener">Get deal</a>
        </div>
    @endif
</div>

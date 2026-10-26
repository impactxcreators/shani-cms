@extends('layouts.public')
@section('title', $store->meta_title ?: $store->name.' coupons & deals')
@if($store->meta_description)@section('meta_description', $store->meta_description)@endif
@section('content')
    <div class="storehead">
        @if($store->logo_url)
            <img src="{{ $store->logo_url }}" alt="{{ $store->name }} logo">
        @else
            <span class="logoavatar" style="width:72px;height:72px;border-radius:16px;font-size:1.9rem;box-shadow:var(--shadow)">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
        @endif
        <div>
            <h1 style="margin:0">{{ $store->name }}</h1>
            <div class="badges" style="margin-top:7px">
                @if($store->is_featured)<span class="pill save">Featured</span>@endif
                @if($store->average_rating)<span class="rating">&starf; {{ rtrim(rtrim(number_format($store->average_rating, 1), '0'), '.') }}/5</span>@endif
                @if($coupons->count())<span class="pill good">{{ $coupons->count() }} active coupon{{ $coupons->count() === 1 ? '' : 's' }}</span>@endif
            </div>
        </div>
    </div>
    @if($store->description)<div class="prose">{!! $store->description !!}</div>@endif

    <h2 style="margin-top:28px">Coupons ({{ $coupons->count() }})</h2>
    @if($coupons->isEmpty())
        <p class="muted-sm">No active coupons right now. Check back soon.</p>
    @else
        <div class="grid cols2">
            @foreach($coupons as $coupon)
                @include('partials.coupon-card', ['coupon' => $coupon, 'showStore' => false])
            @endforeach
        </div>
    @endif

    <h2 style="margin-top:28px">Deals ({{ $deals->count() }})</h2>
    @if($deals->isEmpty())
        <p class="muted-sm">No active deals right now.</p>
    @else
        <div class="grid cols2">
            @foreach($deals as $deal)
                @php($dealEndsSoon = $deal->expires_at && $deal->expires_at->isFuture() && $deal->expires_at->lte(now()->addDays(7)))
                <div class="card">
                    <h3>{{ $deal->title }}</h3>
                    <div class="badges">
                        @if($deal->discount_percent)<span class="pill save">-{{ $deal->discount_percent }}%</span>@endif
                        @if($deal->is_featured)<span class="pill">Hot deal</span>@endif
                        @if($dealEndsSoon)<span class="pill hot">Ends soon</span>@endif
                    </div>
                    @if($deal->sale_price)
                        <div style="margin-top:8px"><span class="price">${{ $deal->sale_price }}</span>@if($deal->original_price)<span class="was">${{ $deal->original_price }}</span>@endif</div>
                    @endif
                    @if($deal->description)<p class="muted-sm" style="margin:.5rem 0 0">{{ $deal->description }}</p>@endif
                    <div style="margin-top:12px"><a class="btn" href="{{ url('/go/deal/'.$deal->id) }}" target="_blank" rel="sponsored nofollow noopener">Get deal</a></div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

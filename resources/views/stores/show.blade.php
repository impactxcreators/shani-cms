@extends('layouts.public')
@section('title', $store->meta_title ?: $store->name.' coupons & deals')
@if($store->meta_description)@section('meta_description', $store->meta_description)@endif
@section('content')
    <div class="storehead">
        @if($store->logo)<img src="{{ $store->logo }}" alt="{{ $store->name }} logo">@endif
        <div>
            <h1 style="margin:0">{{ $store->name }}</h1>
            @if($store->average_rating)<div class="muted-sm">Rating: {{ $store->average_rating }}/5</div>@endif
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
                <div class="card">
                    <h3>{{ $deal->title }}</h3>
                    @if($deal->sale_price)<div><span class="price">${{ $deal->sale_price }}</span>@if($deal->original_price)<span class="was">${{ $deal->original_price }}</span>@endif @if($deal->discount_percent)<span class="pill">-{{ $deal->discount_percent }}%</span>@endif</div>@endif
                    @if($deal->description)<p class="muted-sm">{{ $deal->description }}</p>@endif
                    <div style="margin-top:10px"><a class="btn" href="{{ url('/go/deal/'.$deal->id) }}" target="_blank" rel="sponsored nofollow noopener">Get deal</a></div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

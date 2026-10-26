@extends('layouts.public')
@section('title', $category->name.' stores, coupons & deals')
@section('meta_description', $category->description ? \Illuminate\Support\Str::limit(strip_tags($category->description), 150) : 'Browse '.$category->name.' brands with the latest coupons and deals.')
@section('content')
    <p class="muted-sm"><a href="{{ url('/categories') }}">Categories</a> / {{ $category->name }}</p>
    <h1>{{ $category->name }}</h1>
    @if($category->description)<div class="prose">{!! $category->description !!}</div>@endif

    @if($stores->isEmpty())
        <p class="muted-sm" style="margin-top:20px">No stores in this category yet. <a href="{{ url('/store') }}">See all stores</a>.</p>
    @else
        <div class="grid cols2" style="margin-top:22px">
            @foreach($stores as $store)
                <a class="card searchresult" href="{{ route('store.show', $store->slug) }}" style="text-decoration:none;color:inherit;display:flex;gap:14px;align-items:center">
                    @if($store->logo_url)
                        <img class="brandlogo-lg" src="{{ $store->logo_url }}" alt="{{ $store->name }} logo">
                    @else
                        <span class="brandlogo-lg logoavatar" style="font-size:1.3rem">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                    @endif
                    <span style="min-width:0;flex:1">
                        <span style="display:block;font-weight:700;color:var(--fg)">{{ $store->name }}</span>
                        <span class="badges" style="margin-top:4px">
                            @if($store->is_featured)<span class="pill save">Featured</span>@endif
                            @if($store->average_rating)<span class="rating">&starf; {{ rtrim(rtrim(number_format($store->average_rating, 1), '0'), '.') }}</span>@endif
                        </span>
                        <span class="muted-sm" style="display:block;margin-top:4px">{{ $store->active_coupons_count }} coupon{{ $store->active_coupons_count === 1 ? '' : 's' }} · {{ $store->active_deals_count }} deal{{ $store->active_deals_count === 1 ? '' : 's' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
        <div style="margin-top:26px">{{ $stores->links() }}</div>
    @endif
@endsection

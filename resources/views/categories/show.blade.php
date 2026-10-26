@extends("layouts.public")
@section("title", $category->name." stores, coupons & deals")
@section("meta_description", $category->description ? \Illuminate\Support\Str::limit(strip_tags($category->description),150) : "Browse ".$category->name." brands with the latest coupons and deals.")
@section("content")
    <p class="muted-sm"><a href="{{ url("/categories") }}">Categories</a> / {{ $category->name }}</p>
    <h1>{{ $category->name }}</h1>
    @if($category->description)<div class="prose">{!! $category->description !!}</div>@endif

    @if($stores->isEmpty())
        <p class="muted-sm" style="margin-top:20px">No stores in this category yet. <a href="{{ url("/store") }}">See all stores</a>.</p>
    @else
        <div class="grid cols2" style="margin-top:22px">
            @foreach($stores as $store)
                <a class="card searchresult" href="{{ route("store.show", $store->slug) }}" style="text-decoration:none;color:inherit;display:flex;gap:14px;align-items:center">
                    @if($store->logo)
                        <img src="{{ $store->logo }}" alt="{{ $store->name }} logo" style="width:52px;height:52px;object-fit:contain;border-radius:10px;background:#fff;padding:5px;flex:none">
                    @else
                        <span style="width:52px;height:52px;border-radius:10px;background:rgba(245,158,11,.12);color:var(--brand);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.3rem;flex:none">{{ strtoupper(substr($store->name,0,1)) }}</span>
                    @endif
                    <span style="min-width:0">
                        <span style="display:block;font-weight:700;color:var(--fg)">{{ $store->name }}</span>
                        <span class="muted-sm">{{ $store->active_coupons_count }} coupon{{ $store->active_coupons_count === 1 ? "" : "s" }} · {{ $store->active_deals_count }} deal{{ $store->active_deals_count === 1 ? "" : "s" }}</span>
                    </span>
                </a>
            @endforeach
        </div>
        <div style="margin-top:26px">{{ $stores->links() }}</div>
    @endif
@endsection

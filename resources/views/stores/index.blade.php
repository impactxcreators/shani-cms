@extends("layouts.public")
@section("title", "All stores & brands")
@section("meta_description", "Browse all brands and stores with the latest coupons, discount codes and deals.")
@section("content")
    <h1>All stores</h1>
    <p class="muted">Browse every brand with active coupons and deals.</p>

    @if($stores->isEmpty())
        <p class="muted-sm" style="margin-top:20px">No stores published yet. Check back soon.</p>
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
                        <span style="display:block;font-weight:700;color:var(--fg)">{{ $store->name }}@if($store->is_featured) <span class="pill">Featured</span>@endif</span>
                        <span class="muted-sm">{{ $store->active_coupons_count }} coupon{{ $store->active_coupons_count === 1 ? "" : "s" }} · {{ $store->active_deals_count }} deal{{ $store->active_deals_count === 1 ? "" : "s" }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        <div style="margin-top:26px">{{ $stores->links() }}</div>
    @endif
@endsection

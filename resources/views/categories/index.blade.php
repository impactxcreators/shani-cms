@extends("layouts.public")
@section("title", "Browse by category")
@section("meta_description", "Browse stores and brands by category to find the best coupons and deals.")
@section("content")
    <h1>Categories</h1>
    <p class="muted">Find brands and deals grouped by what you are shopping for.</p>

    @if($categories->isEmpty())
        <p class="muted-sm" style="margin-top:20px">No categories yet.</p>
    @else
        <div class="grid cols2" style="margin-top:22px">
            @foreach($categories as $category)
                <a class="card searchresult" href="{{ route("category.show", $category->slug) }}" style="text-decoration:none;color:inherit;display:flex;gap:14px;align-items:center">
                    <span style="width:48px;height:48px;border-radius:10px;background:rgba(245,158,11,.12);color:var(--brand);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;flex:none">{{ $category->icon ?: strtoupper(substr($category->name,0,1)) }}</span>
                    <span>
                        <span style="display:block;font-weight:700;color:var(--fg)">{{ $category->name }}@if($category->is_featured) <span class="pill">Featured</span>@endif</span>
                        <span class="muted-sm">{{ $category->stores_count }} store{{ $category->stores_count === 1 ? "" : "s" }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
@endsection

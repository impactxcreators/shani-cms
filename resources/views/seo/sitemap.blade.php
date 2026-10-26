@extends("layouts.public")
@section("title", "Sitemap")
@section("meta_description", "Browse every page on the site by category.")
@section("content")
    <h1>Sitemap</h1>
    <p class="muted">Browse everything by category. Tap a category to see the pages inside it.</p>

    <h2>Store categories</h2>
    @forelse($storeTree as $node)
        @include("partials.sitemap-node", ["node" => $node])
    @empty
        <p class="muted-sm">No categories yet.</p>
    @endforelse

    @if(!empty($productTree))
        <h2>Best picks by category</h2>
        @foreach($productTree as $node)
            @include("partials.sitemap-node", ["node" => $node])
        @endforeach
    @endif

    @if(!empty($blogTree))
        <h2>Blog categories</h2>
        @foreach($blogTree as $node)
            @include("partials.sitemap-node", ["node" => $node])
        @endforeach
    @endif

    <h2>Main pages</h2>
    <ul class="smlist">
        <li><a href="{{ url("/store") }}">All stores</a></li>
        <li><a href="{{ url("/categories") }}">All categories</a></li>
        <li><a href="{{ url("/coupons") }}">All coupons</a></li>
        <li><a href="{{ url("/deals") }}">All deals</a></li>
        <li><a href="{{ url("/best") }}">Best picks</a></li>
        <li><a href="{{ url("/blog") }}">Blog</a></li>
        @foreach($pages as $p)
            <li><a href="{{ $p["url"] }}">{{ $p["label"] }}</a></li>
        @endforeach
    </ul>

    <p class="muted-sm" style="margin-top:24px">Looking for the search-engine sitemap? It is at <a href="{{ url("/sitemap.xml") }}">/sitemap.xml</a>.</p>
@endsection

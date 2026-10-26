@extends("layouts.public")
@section("content")
    @if($banners->isNotEmpty())
        @include("partials.blocks.hero", ["banners" => $banners])
    @endif

    <div class="homesearch">
        @include("partials.search-form", ["variant" => "big", "placeholder" => "Search coupons, deals & reviews…"])
        <p class="hint">Find the best discount codes, deals and product picks</p>
    </div>

    @forelse($blocks as $block)
        @includeIf("partials.blocks.".$block->type, ["block" => $block])
    @empty
        @if($banners->isEmpty())
            <h1>{{ \App\Models\Setting::get("tagline", "Welcome") }}</h1>
            <p class="muted">{{ \App\Models\Setting::get("site_description") }}</p>
            <p><a class="btn" href="{{ url("/coupons") }}">Browse coupons</a>
               <a class="btn alt" href="{{ url("/best") }}">Best picks</a></p>
        @endif
    @endforelse

    @if(!empty($exploreLinks))
        <h2>Explore</h2>
        <div class="linkgrid">
            @foreach($exploreLinks as $l)
                <a class="linkchip" href="{{ $l["url"] }}">{{ $l["label"] }}</a>
            @endforeach
        </div>
        <p class="explore-note">Showing {{ count($exploreLinks) }} of {{ number_format($exploreTotal) }} pages — this selection refreshes about every {{ $exploreMinutes }} min so everything gets a turn.</p>
    @endif
@endsection

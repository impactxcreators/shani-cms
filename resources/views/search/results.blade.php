@extends("layouts.public")
@section("title", $q !== "" ? "Search: ".$q : "Search")
@section("robots", "noindex,follow")
@section("content")
    <h1>Search</h1>
    @include("partials.search-form", ["variant" => "big", "placeholder" => "Search coupons, deals, reviews…"])

    @if($q === "" || strlen($q) < 2)
        <p class="muted" style="margin-top:20px">Type at least 2 characters to search.</p>
    @elseif($total === 0)
        <p class="muted" style="margin-top:20px">No results for &ldquo;{{ $q }}&rdquo;. Try a different word.</p>
    @else
        <p class="muted" style="margin-top:16px">{{ $total }} result{{ $total === 1 ? "" : "s" }} for &ldquo;{{ $q }}&rdquo;</p>
        @foreach($groups as $label => $items)
            <h2 style="font-size:1.1rem;margin:28px 0 10px">{{ $label }}</h2>
            <div class="grid cols2">
                @foreach($items as $item)
                    <a class="card searchresult" href="{{ $item["url"] }}" style="text-decoration:none;color:inherit;display:block">
                        <h3 style="color:var(--brand)">{{ $item["title"] }}</h3>
                        @if($item["snippet"])<p class="muted-sm" style="margin:0">{{ $item["snippet"] }}</p>@endif
                    </a>
                @endforeach
            </div>
        @endforeach
    @endif
@endsection

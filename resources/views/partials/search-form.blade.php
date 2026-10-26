<form class="searchform {{ $variant ?? "" }}" action="{{ url("/search") }}" method="get" role="search">
    <input type="search" name="q" value="{{ request("q") }}" placeholder="{{ $placeholder ?? "Search coupons, deals, reviews…" }}" aria-label="Search" autocomplete="off">
    <button type="submit" class="btn">Search</button>
</form>

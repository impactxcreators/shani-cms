{{-- Expects: $offers (paginator), $stats, $sort, $type; optional $showTypes, $sorts, $intro, $empty --}}
@if(! empty($intro))<p class="offerintro">{{ $intro }}</p>@endif

<div class="offerbar">
    @if($showTypes ?? false)
        <nav class="chips" aria-label="Show">
            @foreach(\App\Support\OfferFeed::TYPES as $key => $label)
                @php($n = $key === 'all' ? $stats['coupons'] + $stats['deals'] : $stats[$key])
                <a class="chip {{ $type === $key ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['type' => $key === 'all' ? null : $key, 'page' => null]) }}">{{ $label }} <small>{{ $n }}</small></a>
            @endforeach
        </nav>
    @endif
    <form method="get" class="sortform">
        @if(($showTypes ?? false) && $type !== 'all')<input type="hidden" name="type" value="{{ $type }}">@endif
        <label>Sort
            <select name="sort" onchange="this.form.submit()">
                @foreach($sorts ?? \App\Support\OfferFeed::SORTS as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <noscript><button class="btn alt">Go</button></noscript>
    </form>
</div>

<div class="offerlist" id="offers">
    @forelse($offers as $offer)
        @include('partials.offer-row', ['offer' => $offer])
    @empty
        <p class="muted-sm">{{ $empty ?? 'No offers here yet. Check back soon.' }}</p>
    @endforelse
</div>

@if($offers->hasMorePages())
    <div class="loadmore"><a class="btn alt" href="{{ $offers->nextPageUrl() }}" data-loadmore>Load more offers</a></div>
@endif

@once
<script>
document.addEventListener('click', async function (e) {
    var more = e.target.closest('[data-loadmore]');
    if (!more) return;
    e.preventDefault();
    more.textContent = 'Loading…';
    try {
        var html = await (await fetch(more.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})).text();
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var list = document.getElementById('offers');
        doc.querySelectorAll('#offers > .offer').forEach(function (row) { list.appendChild(row); });
        var next = doc.querySelector('[data-loadmore]');
        if (next) { more.href = next.href; more.textContent = 'Load more offers'; }
        else { more.parentNode.remove(); }
    } catch (_) { location.href = more.href; }
});
</script>
@endonce

@php
    $stores = \App\Models\Store::published()->select('stores.id');
    $type = \App\Support\OfferFeed::type(request()->query('type'));
    $sort = request()->has('sort') ? \App\Support\OfferFeed::sort(request()->query('sort')) : 'new';
    $offers = \App\Support\OfferFeed::page(clone $stores, $type, $sort, (int) ($block->config['limit'] ?? 20) ?: 20);
    $stats = \App\Support\OfferFeed::stats($stores);
@endphp
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Latest coupons & deals' }}</h2>
    @include('partials.offer-feed', ['showTypes' => true, 'intro' => null, 'empty' => 'No coupons or deals yet.'])
</section>

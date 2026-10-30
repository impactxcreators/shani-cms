@php
    $stores = \App\Models\Store::published()->select('stores.id');
    $sorts = \Illuminate\Support\Arr::only(\App\Support\OfferFeed::SORTS, ['hot', 'new']);
    $sort = isset($sorts[request()->query('sort')]) ? request()->query('sort') : 'hot';
    $type = 'all';
    $offers = \App\Support\OfferFeed::page(clone $stores, $type, $sort, (int) ($block->config['limit'] ?? 20) ?: 20);
    $stats = \App\Support\OfferFeed::stats($stores);
@endphp
<section style="margin:22px 0">
    @if($block->title)<h2>{{ $block->title }}</h2>@endif
    @include('partials.offer-feed', ['showTypes' => false, 'sorts' => $sorts, 'intro' => null, 'empty' => 'No coupons or deals yet.'])
</section>

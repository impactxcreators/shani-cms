@php($items = \App\Models\Coupon::active()->with('store')->orderByDesc('is_verified')->limit((int)($block->config['limit'] ?? 8))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Latest coupons' }}</h2>
    <div class="grid cols2">
        @foreach($items as $coupon)
            @include('partials.coupon-card', ['coupon' => $coupon, 'showStore' => true])
        @endforeach
    </div>
</section>

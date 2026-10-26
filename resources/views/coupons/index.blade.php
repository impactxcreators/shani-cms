@extends('layouts.public')
@section('title', 'All coupons')
@section('content')
    <h1>All coupons</h1>
    <div class="grid cols2">
        @forelse($coupons as $coupon)
            @include('partials.coupon-card', ['coupon' => $coupon, 'showStore' => true])
        @empty
            <p class="muted-sm">No coupons yet.</p>
        @endforelse
    </div>
    <div style="margin-top:20px">{{ $coupons->links() }}</div>
@endsection

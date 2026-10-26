@extends('layouts.public')
@section('title', 'All deals')
@section('content')
    <h1>All deals</h1>
    <div class="grid cols2">
        @forelse($deals as $deal)
            <div class="card">
                <div class="muted-sm">{{ $deal->store->name }}</div>
                <h3>{{ $deal->title }}</h3>
                @if($deal->sale_price)<div><span class="price">${{ $deal->sale_price }}</span>@if($deal->original_price)<span class="was">${{ $deal->original_price }}</span>@endif</div>@endif
                <div style="margin-top:10px"><a class="btn" href="{{ url('/go/deal/'.$deal->id) }}" target="_blank" rel="sponsored nofollow noopener">Get deal</a></div>
            </div>
        @empty
            <p class="muted-sm">No deals yet.</p>
        @endforelse
    </div>
    <div style="margin-top:20px">{{ $deals->links() }}</div>
@endsection

@extends('layouts.public')
@section('title', 'Best product roundups')
@section('content')
    <h1>Best product roundups</h1>
    <div class="grid cols2">
        @forelse($lists as $list)
            <div class="card">
                <h3><a href="{{ url('/'.$list->slug) }}">{{ $list->title }}</a></h3>
                @if($list->subtitle)<p class="muted-sm">{{ $list->subtitle }}</p>@endif
                <div class="muted-sm">{{ $list->items_count }} products</div>
            </div>
        @empty
            <p class="muted-sm">No roundups yet.</p>
        @endforelse
    </div>
    <div style="margin-top:20px">{{ $lists->links() }}</div>
@endsection

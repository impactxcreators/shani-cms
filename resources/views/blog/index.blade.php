@extends('layouts.public')
@section('title', $heading ?? 'Blog')
@section('content')
    <h1>{{ $heading ?? 'Blog' }}</h1>
    <div class="grid cols2">
        @forelse($posts as $post)
            <div class="card">
                @if($post->cover_image)<img src="{{ $post->cover_image }}" alt="" style="width:100%;height:150px;object-fit:cover;border-radius:10px;margin-bottom:10px">@endif
                <h3><a href="{{ url('/blog/'.$post->slug) }}">{{ $post->title }}</a></h3>
                <div class="muted-sm">@if($post->author){{ $post->author->name }} · @endif{{ optional($post->published_at)->format('M j, Y') }}</div>
                @if($post->excerpt)<p class="muted-sm">{{ $post->excerpt }}</p>@endif
            </div>
        @empty
            <p class="muted-sm">No posts yet.</p>
        @endforelse
    </div>
    <div style="margin-top:20px">{{ $posts->links() }}</div>
@endsection

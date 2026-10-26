@extends('layouts.public')

@section('title', $post->meta_title ?: $post->title)

@if($post->meta_description)
    @section('meta_description', $post->meta_description)
@endif

@section('content')
    @if($post->cover_image)
        <img src="{{ $post->cover_image }}" alt="" style="width:100%;border-radius:14px;margin-bottom:16px">
    @endif
    <h1>{{ $post->title }}</h1>
    <div class="muted-sm">
        @if($post->author)By {{ $post->author->name }} · @endif
        {{ optional($post->published_at)->format('M j, Y') }}
        @if($post->reading_minutes) · {{ $post->reading_minutes }} min read @endif
    </div>
    @if($post->categories->isNotEmpty())
        <div style="margin:8px 0">
            @foreach($post->categories as $c)
                <a class="pill" href="{{ url('/blog/category/'.$c->slug) }}">{{ $c->name }}</a>
            @endforeach
        </div>
    @endif
    <div class="prose">{!! $post->body !!}</div>

    <hr style="border:none;border-top:1px solid var(--line);margin:28px 0">
    <h2>Comments ({{ $comments->count() }})</h2>

    @if(session('comment_status'))
        <div class="card" style="border-color:var(--brand)">{{ session('comment_status') }}</div>
    @endif

    @forelse($comments as $comment)
        <div class="card" style="margin-bottom:10px">
            <strong>{{ $comment->author_name }}</strong>
            <span class="muted-sm">· {{ $comment->created_at->diffForHumans() }}</span>
            <p>{{ $comment->body }}</p>
            @foreach($comment->replies->where('status', 'approved') as $reply)
                <div class="card" style="margin:8px 0 0 20px">
                    <strong>{{ $reply->author_name }}</strong>
                    <p>{{ $reply->body }}</p>
                </div>
            @endforeach
        </div>
    @empty
        <p class="muted-sm">Be the first to comment.</p>
    @endforelse

    <h3 style="margin-top:20px">Leave a comment</h3>
    @if($errors->any())
        <div class="card" style="border-color:#e11d48">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ url('/blog/'.$post->slug.'/comment') }}" class="card">
        @csrf
        <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>
        <p><input type="text" name="author_name" placeholder="Your name" required
                  style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--fg)"></p>
        <p><input type="email" name="author_email" placeholder="Your email (optional, not shown)"
                  style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--fg)"></p>
        <p><textarea name="body" rows="4" placeholder="Your comment" required
                  style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--fg)"></textarea></p>
        <button class="btn" type="submit">Post comment</button>
    </form>
@endsection

@php($items = \App\Models\Post::published()->with('author')->latest('published_at')->limit((int)($block->config['limit'] ?? 6))->get())
@if($items->isNotEmpty())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'From the blog' }}</h2>
    <div class="grid cols3">
        @foreach($items as $post)
            <a class="card postcard" href="{{ url('/blog/'.$post->slug) }}">
                @if($post->cover_image)<img src="{{ $post->cover_image }}" alt="" loading="lazy">@endif
                <h3>{{ $post->title }}</h3>
                @if($post->excerpt)<p class="muted-sm">{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</p>@endif
                <span class="muted-sm">{{ optional($post->published_at)->format('M j, Y') }}@if($post->reading_minutes) · {{ $post->reading_minutes }} min read @endif</span>
            </a>
        @endforeach
    </div>
    <p class="sectionmore"><a href="{{ url('/blog') }}">View all articles &rarr;</a></p>
</section>
@endif

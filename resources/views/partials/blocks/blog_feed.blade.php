@php($items = \App\Models\Post::published()->with('author')->latest('published_at')->limit((int)($block->config['limit'] ?? 6))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'From the blog' }}</h2>
    <div class="grid cols2">
        @foreach($items as $post)
            <div class="card">
                <h3><a href="{{ url('/blog/'.$post->slug) }}">{{ $post->title }}</a></h3>
                @if($post->excerpt)<p class="muted-sm">{{ $post->excerpt }}</p>@endif
            </div>
        @endforeach
    </div>
</section>

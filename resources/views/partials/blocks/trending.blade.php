@php($items = \App\Models\Category::where('is_featured', true)->limit((int)($block->config['limit'] ?? 8))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Trending categories' }}</h2>
    <div class="grid cols2">
        @foreach($items as $cat)
            <a class="card searchresult" href="{{ url('/category/'.$cat->slug) }}" style="text-decoration:none;color:inherit;display:flex;gap:12px;align-items:center">
                <span class="brandlogo logoavatar" style="font-size:1.1rem">{{ $cat->icon ?: strtoupper(substr($cat->name, 0, 1)) }}</span>
                <span style="min-width:0">
                    <span style="display:block;font-weight:700;color:var(--fg)">{{ $cat->name }}</span>
                    @if($cat->description)<span class="muted-sm">{{ \Illuminate\Support\Str::limit(strip_tags($cat->description), 60) }}</span>@endif
                </span>
            </a>
        @endforeach
    </div>
</section>

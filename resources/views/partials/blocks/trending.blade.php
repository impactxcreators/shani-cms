@php($items = \App\Models\Category::where('is_featured', true)->limit((int)($block->config['limit'] ?? 8))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Trending categories' }}</h2>
    <div class="grid cols2">
        @foreach($items as $cat)
            <div class="card"><h3>{{ $cat->name }}</h3>@if($cat->description)<p class="muted-sm">{{ $cat->description }}</p>@endif</div>
        @endforeach
    </div>
</section>

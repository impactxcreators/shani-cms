@php
    $limit = (int) ($block->config['limit'] ?? 12) ?: 12;
    $cats = \App\Models\Category::whereNull('parent_id')
        ->with(['children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
        ->withCount(['stores' => fn ($query) => $query->published()])
        ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')
        ->limit($limit)->get();
@endphp
@if($cats->isNotEmpty())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Shop by category' }}</h2>
    <div class="catboxes">
        @foreach($cats as $cat)
            <div class="catbox">
                <a class="catbox-head" href="{{ route('category.show', $cat->slug) }}">
                    <span class="logoavatar catbox-icon">{{ $cat->icon ?: strtoupper(substr($cat->name, 0, 1)) }}</span>
                    <span><b>{{ $cat->name }}</b><small>{{ $cat->stores_count }} {{ \Illuminate\Support\Str::plural('store', $cat->stores_count) }}</small></span>
                </a>
                @if($cat->children->isNotEmpty())
                    <ul class="catbox-subs">
                        @foreach($cat->children->take(6) as $child)
                            <li><a href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
                <a class="catbox-all" href="{{ route('category.show', $cat->slug) }}">View all &rarr;</a>
            </div>
        @endforeach
    </div>
    <p class="sectionmore"><a href="{{ url('/categories') }}">View all categories &rarr;</a></p>
</section>
@endif

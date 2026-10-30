@php
    $navCats = \Illuminate\Support\Facades\Cache::remember('nav_categories', now()->addMinutes(10), fn () =>
        \App\Models\Category::whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')->select('id', 'parent_id', 'name', 'slug')])
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')
            ->limit(24)->get(['id', 'name', 'slug', 'icon'])
            ->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon,
                'children' => $c->children->take(8)->map(fn ($s) => ['name' => $s->name, 'slug' => $s->slug])->all(),
                'more' => $c->children->count() > 8])
            ->all());
@endphp
<details class="navdrop" data-navdrop>
    <summary class="{{ request()->is('categories', 'category/*') ? 'active' : '' }}">Categories <span class="caret" aria-hidden="true">&#9662;</span></summary>
    <div class="navpanel">
        <div class="wrap">
            <div class="navcats">
                @foreach($navCats as $cat)
                    <div class="navcat">
                        <a class="navcat-name" href="{{ route('category.show', $cat['slug']) }}">
                            <span class="logoavatar">{{ $cat['icon'] ?: strtoupper(substr($cat['name'], 0, 1)) }}</span>{{ $cat['name'] }}
                        </a>
                        @if($cat['children'])
                            <ul>
                                @foreach($cat['children'] as $sub)
                                    <li><a href="{{ route('category.show', $sub['slug']) }}">{{ $sub['name'] }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                        @if($cat['children'] || $cat['more'])
                            <a class="navcat-all" href="{{ route('category.show', $cat['slug']) }}">View all &rarr;</a>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="navpanel-foot"><a href="{{ url('/categories') }}">Browse all categories &rarr;</a></p>
        </div>
    </div>
</details>

@php($items = \App\Models\Handpick::active()->orderBy('sort_order')->limit((int)($block->config['limit'] ?? 6))->get())
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Handpicked for you' }}</h2>
    <div class="grid cols2">
        @foreach($items as $h)
            <a class="card" href="{{ $h->destination_url ?: '#' }}" style="text-decoration:none">
                @if($h->image)<img src="{{ $h->image }}" alt="{{ $h->title }}" style="width:100%;height:120px;object-fit:cover;border-radius:10px;margin-bottom:8px">@endif
                <h3>{{ $h->title }}</h3>
                @if($h->blurb)<p class="muted-sm">{{ $h->blurb }}</p>@endif
            </a>
        @endforeach
    </div>
</section>

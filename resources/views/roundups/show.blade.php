@extends('layouts.public')
@section('title', $list->meta_title ?: $list->title)
@if($list->meta_description)@section('meta_description', $list->meta_description)@endif
@section('content')
    @if($list->banner)<img src="{{ $list->banner }}" alt="" style="width:100%;border-radius:14px;margin-bottom:16px">@endif
    <h1>{{ $list->h1 ?: $list->title }}</h1>
    @if($list->subtitle)<p class="muted">{{ $list->subtitle }}</p>@endif
    @if($list->updated_label)<p class="pill">Updated {{ $list->updated_label }}</p>@endif
    @if($list->intro)<div class="prose">{!! $list->intro !!}</div>@endif

    @if($list->items->isNotEmpty())
        <nav class="card" style="margin:18px 0">
            <strong class="muted-sm">Jump to:</strong>
            <ol style="margin:.4rem 0 0;padding-left:1.2rem">
                @foreach($list->items as $item)
                    <li><a href="#item-{{ $item->id }}">{{ $item->custom_title ?: optional($item->product)->title }}</a></li>
                @endforeach
            </ol>
        </nav>
    @endif

    @foreach($list->items as $item)
        @php($p = $item->product)
        @continue(! $p)
        <div class="card" id="item-{{ $item->id }}" style="margin-bottom:16px">
            <div style="display:flex;gap:16px;flex-wrap:wrap">
                <div style="flex:0 0 72px"><span class="price">#{{ $loop->iteration }}</span></div>
                <div style="flex:1;min-width:220px">
                    @if($item->badge)<span class="pill">{{ $item->badge }}</span>@endif
                    <h3 style="margin:.3rem 0">{{ $item->custom_title ?: $p->title }}</h3>
                    @if($p->brand)<div class="muted-sm">{{ $p->brand }}</div>@endif
                    @if($p->rating)<div class="muted-sm">★ {{ $p->rating }}@if($p->review_count) ({{ number_format($p->review_count) }} reviews)@endif</div>@endif
                    @if($item->custom_blurb)<p>{{ $item->custom_blurb }}</p>@endif
                    @if($item->editor_note)<p class="muted-sm"><em>Editor note: {{ $item->editor_note }}</em></p>@endif
                    @if(!empty($p->highlights))
                        <details style="margin:.4rem 0">
                            <summary class="muted-sm" style="cursor:pointer">Show highlights</summary>
                            <ul>@foreach($p->highlights as $h)<li>{{ $h }}</li>@endforeach</ul>
                        </details>
                    @endif
                    <div style="margin-top:8px">
                        @if($p->price)<span class="price">${{ $p->price }}</span>@endif
                        <a class="btn" href="{{ url('/go/product/'.$p->id) }}" target="_blank" rel="sponsored nofollow noopener"
                           onclick="return true">Check price</a>
                    </div>
                </div>
                @if($p->image_url)<div style="flex:0 0 120px"><img src="{{ $p->image_url }}" alt="{{ $p->title }}" style="width:120px;height:120px;object-fit:contain"></div>@endif
            </div>
        </div>
    @endforeach

    @if($list->outro)<div class="prose">{!! $list->outro !!}</div>@endif

    @if(!empty($list->faq_items))
        <h2 style="margin-top:24px">FAQ</h2>
        @foreach($list->faq_items as $faq)
            <details class="card" style="margin-bottom:8px">
                <summary style="cursor:pointer;font-weight:700">{{ $faq['question'] ?? '' }}</summary>
                <p class="muted-sm">{{ $faq['answer'] ?? '' }}</p>
            </details>
        @endforeach
    @endif

    @if($related->isNotEmpty())
        <h2 style="margin-top:24px">Related roundups</h2>
        <ul>@foreach($related as $r)<li><a href="{{ url('/'.$r->slug) }}">{{ $r->title }}</a></li>@endforeach</ul>
    @endif

    <p class="muted-sm" style="margin-top:24px">{{ \App\Models\Setting::get('affiliate_disclosure') }}</p>
@endsection

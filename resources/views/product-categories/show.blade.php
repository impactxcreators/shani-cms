@extends('layouts.public')
@section('title', $category->name)
@if($category->description)@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($category->description), 150))@endif

@if($roundups->isEmpty() && $products->total() === 0)

@section('robots', 'noindex,follow')

@endif

@section('content')
    <p class="muted-sm"><a href="{{ url('/') }}">Home</a> / {{ $category->name }}</p>
    <h1>{{ $category->name }}</h1>
    @if($category->description)<div class="prose">{!! $category->description !!}</div>@endif

    @if($roundups->isNotEmpty())
        <h2>Best picks</h2>
        <div class="grid cols2">
            @foreach($roundups as $r)
                <a class="card searchresult" href="{{ url('/'.$r->slug) }}" style="text-decoration:none;color:inherit;display:block">
                    <h3 style="color:var(--brand)">{{ $r->h1 ?: $r->title }}</h3>
                    @if($r->subtitle)<p class="muted-sm" style="margin:0">{{ $r->subtitle }}</p>@endif
                </a>
            @endforeach
        </div>
    @endif

    <h2>Products</h2>
    @if($products->total() === 0)
        <p class="muted-sm">No products in this category yet. <a href="{{ url('/best') }}">Browse all best picks</a>.</p>
    @else
        <div class="grid cols2">
            @foreach($products as $p)
                <div class="card">
                    @if($p->image_url)<img src="{{ $p->image_url }}" alt="{{ $p->title }}" style="width:100%;height:150px;object-fit:contain;border-radius:10px;background:#fff;padding:8px;margin-bottom:10px">@endif
                    <h3>{{ $p->title }}</h3>
                    <div class="badges">
                        @if($p->brand)<span class="pill new">{{ $p->brand }}</span>@endif
                        @if($p->rating)<span class="rating">&starf; {{ rtrim(rtrim(number_format($p->rating, 1), '0'), '.') }}</span>@endif
                    </div>
                    @if($p->price)<div style="margin-top:8px"><span class="price">{{ $p->currency ? $p->currency.' ' : '$' }}{{ $p->price }}</span></div>@endif
                    <div style="margin-top:12px"><a class="btn" href="{{ url('/go/product/'.$p->id) }}" target="_blank" rel="sponsored nofollow noopener">View deal</a></div>
                </div>
            @endforeach
        </div>
        <div style="margin-top:26px">{{ $products->links() }}</div>
    @endif
@endsection

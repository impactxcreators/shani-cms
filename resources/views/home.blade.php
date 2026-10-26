@extends('layouts.public')
@section('content')
    @if($banners->isNotEmpty())
        @include('partials.blocks.hero', ['banners' => $banners])
    @endif

    @forelse($blocks as $block)
        @includeIf('partials.blocks.'.$block->type, ['block' => $block])
    @empty
        @if($banners->isEmpty())
            <h1>{{ \App\Models\Setting::get('tagline', 'Welcome') }}</h1>
            <p class="muted">{{ \App\Models\Setting::get('site_description') }}</p>
            <p><a class="btn" href="{{ url('/coupons') }}">Browse coupons</a>
               <a class="btn alt" href="{{ url('/best') }}">Best picks</a></p>
        @endif
    @endforelse
@endsection

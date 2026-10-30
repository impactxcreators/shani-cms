@extends('layouts.public')
@php($intro = \App\Support\OfferFeed::intro($stats, $category->name))
@section('title', $category->name.' coupons & deals')
@section('meta_description', $category->description ? \Illuminate\Support\Str::limit(strip_tags($category->description), 150) : ($intro ? \Illuminate\Support\Str::limit($intro, 150) : 'The latest '.$category->name.' coupons and deals.'))
@section('content')
    <p class="muted-sm"><a href="{{ url('/categories') }}">Categories</a>@if($category->parent) / <a href="{{ route('category.show', $category->parent->slug) }}">{{ $category->parent->name }}</a>@endif / {{ $category->name }}</p>
    <h1>{{ $category->name }} coupons &amp; deals</h1>
    @if($category->description)<div class="prose">{!! $category->description !!}</div>@endif

    @if($children->isNotEmpty())
        <div class="linkgrid">
            @foreach($children as $child)
                <a class="linkchip" href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a>
            @endforeach
        </div>
    @endif

    @if($stores->isNotEmpty())
        <div class="storestrip" aria-label="Stores in {{ $category->name }}">
            @foreach($stores as $store)
                <a class="linkchip" href="{{ route('store.show', $store->slug) }}">
                    @if($store->logo_url)<img src="{{ $store->logo_url }}" alt="" loading="lazy" width="20" height="20">@endif{{ $store->name }}
                </a>
            @endforeach
            @if($stores->count() === 40)<a class="linkchip" href="{{ url('/store') }}">All stores &rarr;</a>@endif
        </div>
    @endif

    @include('partials.offer-feed', ['showTypes' => true, 'empty' => 'No offers in '.$category->name.' yet. Check back soon.'])
@endsection

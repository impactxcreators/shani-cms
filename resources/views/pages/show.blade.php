@extends('layouts.public')
@section('title', $page->meta_title ?: $page->title)
@if($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif

@if($page->canonical_url)
    @section('canonical', $page->canonical_url)
@endif
@if($page->no_index)
    @section('robots', 'noindex')
@endif

@section('content')
    <h1>{{ $page->title }}</h1>
    <div class="prose">{!! $page->body !!}</div>
@endsection

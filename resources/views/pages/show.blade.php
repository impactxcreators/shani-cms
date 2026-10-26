@extends('layouts.public')
@section('title', $page->meta_title ?: $page->title)
@if($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif
@section('content')
    <h1>{{ $page->title }}</h1>
    <div class="prose">{!! $page->body !!}</div>
@endsection

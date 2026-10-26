@extends('layouts.public')
@section('title', 'Sitemap')
@section('content')
    <h1>Sitemap</h1>
    <ul>
        @foreach($urls as $u)
            <li><a href="{{ $u['loc'] }}">{{ $u['loc'] }}</a></li>
        @endforeach
    </ul>
@endsection

@extends('layouts.public')
@php($intro = \App\Support\OfferFeed::intro($stats))
@section('title', 'All coupons')
@if($intro)@section('meta_description', \Illuminate\Support\Str::limit($intro, 150))@endif
@section('content')
    <h1>All coupons</h1>
    @include('partials.offer-feed', ['empty' => 'No coupons yet.'])
@endsection

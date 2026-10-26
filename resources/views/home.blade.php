@extends('layouts.public')
@section('content')
    <h1>{{ \App\Models\Setting::get('tagline', 'Welcome') }}</h1>
    <div class="prose">
        <p class="muted">{{ \App\Models\Setting::get('site_description') }}</p>
        <p class="muted">This is a preview of the homepage. The full homepage builder arrives in a later phase.</p>
    </div>
@endsection

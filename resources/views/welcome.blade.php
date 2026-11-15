@extends('layouts.marketing')

@section('title', 'Sure Procure')
@section('description', 'SureProcure gives your company a dedicated procurement officer, accredited vendors and a bid evaluation you can approve in one place.')

@section('content')
    <div class="workspace-rail text-mist">
        <a href="#landing-content" class="skip-link">Skip to content</a>
        @include('landing.header')
        <main id="landing-content" tabindex="-1">
            @include('landing.hero')
            @include('landing.how-it-works')
            @include('landing.audiences')
            @include('landing.vendor-inquiry')
        </main>
    </div>
    @include('landing.footer')
@endsection

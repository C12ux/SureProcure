@extends('layouts.marketing')

@section('title', config('app.name', 'SureProcure').': procurement handled, from request to purchase order')
@section('description', 'SureProcure gives your company a dedicated procurement officer, accredited vendors and a bid evaluation you can approve in one place.')

@section('content')
    @include('landing.hero')
    @include('landing.how-it-works')
    @include('landing.audiences')
    @include('landing.cta')
    @include('landing.footer')
@endsection

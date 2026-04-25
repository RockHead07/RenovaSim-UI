@extends('layouts.app')

@section('title', 'RenovaSim — AI Renovation Cost Estimator')
@section('description', 'Describe your renovation needs and let our AI engine build your cost blueprint.')

@section('content')
<div class="min-h-screen bg-background py-8 px-4">
    <div class="mx-auto max-w-[860px] flex flex-col gap-5">

        @include('partials.user-header')
        @include('partials.hero-cta')
        @include('partials.setup-project')
        @include('partials.recent-estimates')

    </div>
</div>
@endsection

@extends('layouts.app')
@section('header', __('Dashboard'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                <h5 class="card-title">{{ __("You're logged in!") }}</h5>
                <p class="card-text text-muted mt-2">Welcome to {{ config('app.name', 'Laravel') }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection

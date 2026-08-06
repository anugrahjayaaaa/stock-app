@extends('layouts.guest')
@section('title', 'Log In')

@section('content')
@if (session('status'))
    <x-auth-session-status class="alert alert-success" :status="session('status')" />
@endif

<p class="login-box-msg">Sign in to start your session</p>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
               class="form-control @error('email') is-invalid @enderror">
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="form-control @error('password') is-invalid @enderror">
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="mb-3 form-check">
        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
        <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
    </div>

    <div class="d-flex justify-content-end align-items-center">
        @if (Route::has('password.request'))
            <a class="small me-3" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        @endif
        <x-primary-button>{{ __('Log in') }}</x-primary-button>
    </div>
</form>
@endsection

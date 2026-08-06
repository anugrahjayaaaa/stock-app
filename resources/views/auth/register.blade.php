@extends('layouts.guest')
@section('title', 'Register')

@section('content')
<p class="login-box-msg">Register a new membership</p>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
               class="form-control @error('name') is-invalid @enderror">
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
               class="form-control @error('email') is-invalid @enderror">
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror">
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
               class="form-control @error('password_confirmation') is-invalid @enderror">
        <x-input-error :messages="$errors->get('password_confirmation')" />
    </div>

    <div class="d-flex justify-content-end align-items-center">
        <a class="small me-3" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
        <x-primary-button>{{ __('Register') }}</x-primary-button>
    </div>
</form>
@endsection

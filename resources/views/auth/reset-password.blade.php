@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
<p class="login-box-msg">Reset your password</p>

<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
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

    <div class="d-flex justify-content-end">
        <x-primary-button>{{ __('Reset Password') }}</x-primary-button>
    </div>
</form>
@endsection

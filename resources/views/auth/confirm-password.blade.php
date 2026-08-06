@extends('layouts.guest')
@section('title', 'Confirm Password')

@section('content')
<p class="login-box-msg">
    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="form-control @error('password') is-invalid @enderror">
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="d-flex justify-content-end">
        <x-primary-button>{{ __('Confirm') }}</x-primary-button>
    </div>
</form>
@endsection

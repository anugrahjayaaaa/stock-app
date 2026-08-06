@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('content')
<p class="login-box-msg">
    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
</p>

@if (session('status'))
    <x-auth-session-status class="alert alert-success" :status="session('status')" />
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="form-control @error('email') is-invalid @enderror">
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="d-flex justify-content-end">
        <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
    </div>
</form>
@endsection

@extends('layouts.app')
@section('header', __('Create Permission'))

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Create Permission') }}</h3></div>
    <div class="card-body">
        <x-flash-message />
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Permission Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('Save Permission') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

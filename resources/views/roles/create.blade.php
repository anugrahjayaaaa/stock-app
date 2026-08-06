@extends('layouts.app')
@section('header', __('Create Role'))

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Create Role') }}</h3></div>
    <div class="card-body">
        <x-flash-message />
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Role Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label d-block">{{ __('Permissions') }}</label>
                <div class="row">
                    @foreach ($permissions as $permission)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                       value="{{ $permission->name }}" id="perm-{{ $permission->id }}">
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('Save Role') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

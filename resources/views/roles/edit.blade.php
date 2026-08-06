@extends('layouts.app')
@section('header', __('Edit Role'))

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Edit Role') }}</h3></div>
    <div class="card-body">
        <x-flash-message />
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Role Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
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
                                       value="{{ $permission->name }}" id="perm-{{ $permission->id }}"
                                       {{ $role->permissions->pluck('name')->contains($permission->name) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('permissions')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('Update Role') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

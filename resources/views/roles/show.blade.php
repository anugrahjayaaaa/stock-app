@extends('layouts.app')
@section('header', $role->name)

@section('content')
<div class="card">
    <div class="card-body">
        <x-flash-message />

        <div class="mb-3">
            <label class="form-label text-muted">{{ __('Name') }}</label>
            <div>{{ $role->name }}</div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted">{{ __('Permissions') }}</label>
            <div>
                @forelse ($permissions as $permission)
                    <span class="badge bg-primary me-1 mb-1">{{ $permission->name }}</span>
                @empty
                    <span class="text-muted">{{ __('No permissions assigned.') }}</span>
                @endforelse
            </div>
            <div class="mt-2">
                @include('partials.pagination-info', ['items' => $permissions])
                {{ $permissions->links() }}
            </div>
        </div>

        <div>
            @can('edit roles')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> {{ __('Edit') }}
                </a>
            @endcan
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> {{ __('Back') }}
            </a>
        </div>
    </div>
</div>
@endsection

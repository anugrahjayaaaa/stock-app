@extends('layouts.app')
@section('header', __('Roles'))

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        @can('create roles')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Create Role') }}
            </a>
        @endcan

        <form method="GET" action="{{ route('roles.index') }}" class="ms-auto d-flex gap-1">
            <div class="input-group input-group-sm" style="width: 220px;">
                <input type="search" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="{{ __('Search roles...') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                @if (request('search'))
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-danger" title="Clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body">
        <x-flash-message />

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <x-sortable-th field="name" label="{{ __('Role Name') }}" />
                        <x-sortable-th field="permissions_count" label="{{ __('Permissions Count') }}" />
                        <th class="text-center" style="width: 120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $roles->firstItem() + $loop->index }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-center">
                                @can('edit roles')
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete roles')
                                    <button type="button" class="btn btn-danger btn-sm" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-action="{{ route('roles.destroy', $role) }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            @include('partials.pagination-info', ['items' => $roles])
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection

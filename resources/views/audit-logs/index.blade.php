@extends('layouts.app')
@section('header', __('Audit Logs'))

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Audit Logs</h3></div>
    <div class="card-body">
        <x-flash-message />

        <form method="GET" action="{{ route('audit-logs.index') }}" class="mb-3">
            <label class="form-label">{{ __('Show') }}:</label>
            <select name="event" class="form-select d-inline-block" style="width: auto;"
                    onchange="this.form.submit()">
                <option value="">{{ __('All Events') }}</option>
                <optgroup label="{{ __('Data') }}">
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                    <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>{{ __('Restored') }}</option>
                </optgroup>
                <optgroup label="{{ __('Authentication') }}">
                    <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>{{ __('Login') }}</option>
                    <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>{{ __('Logout') }}</option>
                    <option value="failed_login" {{ request('event') == 'failed_login' ? 'selected' : '' }}>{{ __('Failed Login') }}</option>
                    <option value="password_changed" {{ request('event') == 'password_changed' ? 'selected' : '' }}>{{ __('Password Changed') }}</option>
                    <option value="password_reset" {{ request('event') == 'password_reset' ? 'selected' : '' }}>{{ __('Password Reset') }}</option>
                    <option value="email_verified" {{ request('event') == 'email_verified' ? 'selected' : '' }}>{{ __('Email Verified') }}</option>
                    <option value="profile_updated" {{ request('event') == 'profile_updated' ? 'selected' : '' }}>{{ __('Profile Updated') }}</option>
                    <option value="account_deleted" {{ request('event') == 'account_deleted' ? 'selected' : '' }}>{{ __('Account Deleted') }}</option>
                </optgroup>
            </select>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-center" style="width: 100px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $badge = [
                            'created' => ['bg-success', __('Created')],
                            'updated' => ['bg-warning', __('Updated')],
                            'deleted' => ['bg-danger', __('Deleted')],
                            'restored' => ['bg-info', __('Restored')],
                            'login' => ['bg-primary', __('Login')],
                            'logout' => ['bg-secondary', __('Logout')],
                            'failed_login' => ['bg-danger', __('Failed Login')],
                            'password_changed' => ['bg-warning', __('Password Changed')],
                            'password_reset' => ['bg-dark', __('Password Reset')],
                            'email_verified' => ['bg-info', __('Email Verified')],
                            'profile_updated' => ['bg-info', __('Profile Updated')],
                            'account_deleted' => ['bg-danger', __('Account Deleted')],
                        ];
                    @endphp
                    @forelse ($activityLogs as $log)
                        @php
                            $action = $log->event ?: $log->description;
                            $badgeClass = $badge[$action][0] ?? 'bg-secondary';
                            $badgeLabel = $badge[$action][1] ?? $action;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($activityLogs->currentPage() - 1) * $activityLogs->perPage() }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                            <td>
                                {{ $log->causer?->name ?? __('System') }}
                                @if ($log->causer?->email)
                                    <div class="small text-muted">{{ $log->causer->email }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $subjectLabel = match ($log->subject_type) {
                                        'App\\Models\\Role' => __('Role'),
                                        'App\\Models\\Permission' => __('Permission'),
                                        'App\\Models\\User' => __('User'),
                                        default => class_basename($log->subject_type ?? ''),
                                    };
                                @endphp
                                @if (!empty($subjectLabel))
                                    <span class="fw-medium">{{ $subjectLabel }}</span>
                                    @if ($log->subject)
                                        <div class="small text-muted">
                                            {{ $log->subject_type === 'App\\Models\\Role' ? $log->subject->name : '' }}
                                            {{ $log->subject_type === 'App\\Models\\Permission' ? $log->subject->name : '' }}
                                            {{ $log->subject_type === 'App\\Models\\User' ? $log->subject->email : '' }}
                                        </div>
                                    @endif
                                @else
                                    <span class="fw-medium">{{ ucfirst($log->log_name) }}</span>
                                    <div class="small text-muted">{{ ucfirst($log->description) }}</div>
                                @endif
                            </td>
                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-info btn-sm" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ __('No audit logs found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            @include('partials.pagination-info', ['items' => $activityLogs])
            {{ $activityLogs->links() }}
        </div>
    </div>
</div>
@endsection

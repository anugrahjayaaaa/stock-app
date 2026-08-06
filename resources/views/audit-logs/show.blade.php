@extends('layouts.app')
@section('header', __('Audit Log Detail'))

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ __('Event Information') }}</h3>
            </div>
            <div class="card-body">
                <x-flash-message />

                <div class="d-flex align-items-center mb-3">
                    @php
                        $badge = [
                            'created' => 'bg-success', 'updated' => 'bg-warning',
                            'deleted' => 'bg-danger', 'restored' => 'bg-info',
                            'login' => 'bg-primary', 'logout' => 'bg-secondary',
                            'failed_login' => 'bg-danger', 'password_changed' => 'bg-warning',
                            'password_reset' => 'bg-dark', 'email_verified' => 'bg-info',
                            'profile_updated' => 'bg-info', 'account_deleted' => 'bg-danger',
                        ];
                    @endphp
                    <span class="badge {{ $badge[$activityLog->event] ?? 'bg-secondary' }} fs-6 me-2">
                        {{ Str::headline($activityLog->event) }}
                    </span>
                    <small class="text-muted">{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</small>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">{{ __('Performed By') }}</div>
                        <div class="fw-medium">{{ $activityLog->causer?->name ?? __('System / Automated') }}</div>
                        @if($activityLog->causer?->email)
                            <div class="small text-muted">{{ $activityLog->causer->email }}</div>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">{{ __('Affected Record') }}</div>
                        @php
                            $subjectLabel = match($activityLog->subject_type) {
                                'App\\Models\\Role' => 'Role',
                                'App\\Models\\Permission' => 'Permission',
                                'App\\Models\\User' => 'User',
                                default => $activityLog->subject_type ? class_basename($activityLog->subject_type) : __('Unknown'),
                            };
                        @endphp
                        <div class="fw-medium">{{ $subjectLabel }}</div>
                        <div class="small text-muted">
                            @if($activityLog->subject_type === 'App\\Models\\Role')
                                {{ $activityLog->subject->name ?? '' }}
                            @elseif($activityLog->subject_type === 'App\\Models\\Permission')
                                {{ $activityLog->subject->name ?? '' }}
                            @elseif($activityLog->subject_type === 'App\\Models\\User')
                                {{ $activityLog->subject->email ?? '' }}
                            @endif
                            @if($activityLog->subject_id)
                                <span class="ms-1">#{{ $activityLog->subject_id }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">{{ __('Description') }}</div>
                        <div>{{ $activityLog->description ?? __('No description') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">{{ __('Log Name') }}</div>
                        <div>{{ $activityLog->log_name }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $props = $activityLog->properties;
            $old = is_array($props) ? ($props['old'] ?? []) : ($props->get('old') ?? []);
            $new = is_array($props) ? ($props['attributes'] ?? []) : ($props->get('attributes') ?? []);
            $hasChanges = !empty($old) || !empty($new);
        @endphp

        @if ($hasChanges)
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">{{ __('Value Changes') }}</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30%">{{ __('Field') }}</th>
                                <th>{{ __('Old Value') }}</th>
                                <th>{{ __('New Value') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($new as $field => $value)
                                <tr>
                                    <td class="fw-medium text-break">{{ $field }}</td>
                                    <td class="text-break">
                                        @if(array_key_exists($field, $old))
                                            <span class="text-danger text-decoration-line-through">{{ is_scalar($old[$field]) ? $old[$field] : json_encode($old[$field]) }}</span>
                                        @else
                                            <span class="text-muted fst-italic">{{ __('(new)') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-break">
                                        <span class="text-success">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($old as $field => $value)
                                @if(!array_key_exists($field, $new))
                                    <tr>
                                        <td class="fw-medium text-break">{{ $field }}</td>
                                        <td class="text-break">
                                            <span class="text-danger text-decoration-line-through">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                        </td>
                                        <td class="text-break"><span class="text-muted fst-italic">{{ __('(removed)') }}</span></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">{{ __('Raw Properties') }}</h3></div>
            <div class="card-body">
                <pre class="bg-body-tertiary p-3 rounded mb-0 small">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

        <div class="text-end mt-3">
            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
            </a>
        </div>
    </div>
</div>
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Show') }}:
                            <select name="event"
                                class="ml-2 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('All Events') }}</option>
                                <optgroup label="{{ __('Data') }}">
                                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>
                                        {{ __('Created') }}</option>
                                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>
                                        {{ __('Updated') }}</option>
                                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>
                                        {{ __('Deleted') }}</option>
                                    <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>
                                        {{ __('Restored') }}</option>
                                </optgroup>
                                <optgroup label="{{ __('Authentication') }}">
                                    <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>
                                        {{ __('Login') }}</option>
                                    <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>
                                        {{ __('Logout') }}</option>
                                    <option value="failed_login"
                                        {{ request('event') == 'failed_login' ? 'selected' : '' }}>
                                        {{ __('Failed Login') }}</option>
                                    <option value="password_changed"
                                        {{ request('event') == 'password_changed' ? 'selected' : '' }}>
                                        {{ __('Password Changed') }}</option>
                                    <option value="password_reset"
                                        {{ request('event') == 'password_reset' ? 'selected' : '' }}>
                                        {{ __('Password Reset') }}</option>
                                    <option value="email_verified"
                                        {{ request('event') == 'email_verified' ? 'selected' : '' }}>
                                        {{ __('Email Verified') }}</option>
                                    <option value="profile_updated"
                                        {{ request('event') == 'profile_updated' ? 'selected' : '' }}>
                                        {{ __('Profile Updated') }}</option>
                                    <option value="account_deleted"
                                        {{ request('event') == 'account_deleted' ? 'selected' : '' }}>
                                        {{ __('Account Deleted') }}</option>
                                </optgroup>
                            </select>
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        #</th>
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Action') }}</th>
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('User') }}</th>
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Subject') }}</th>
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Date') }}</th>
                                    <th
                                        class="px-4 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse ($activityLogs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $loop->iteration + ($activityLogs->currentPage() - 1) * $activityLogs->perPage() }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @php
                                                $action = $log->event ?: $log->description;
                                                $badge = [
                                                    'created' => ['green', __('Created')],
                                                    'updated' => ['yellow', __('Updated')],
                                                    'deleted' => ['red', __('Deleted')],
                                                    'restored' => ['blue', __('Restored')],
                                                    'login' => ['indigo', __('Login')],
                                                    'logout' => ['gray', __('Logout')],
                                                    'failed_login' => ['red', __('Failed Login')],
                                                    'password_changed' => ['orange', __('Password Changed')],
                                                    'password_reset' => ['purple', __('Password Reset')],
                                                    'email_verified' => ['teal', __('Email Verified')],
                                                    'profile_updated' => ['cyan', __('Profile Updated')],
                                                    'account_deleted' => ['red', __('Account Deleted')],
                                                ][$action] ?? [null, $action];
                                                $colors = [
                                                    'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                    'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                    'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                                    'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                    'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                                    'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                                    'teal' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
                                                    'cyan' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200',
                                                ];
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$badge[0]] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                                {{ $badge[1] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $log->causer?->name ?? __('System') }}
                                            @if ($log->causer?->email)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $log->causer->email }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @php
                                                $subjectLabel = match ($log->subject_type) {
                                                    'App\\Models\\Role' => __('Role'),
                                                    'App\\Models\\Permission' => __('Permission'),
                                                    'App\\Models\\User' => __('User'),
                                                    default => class_basename($log->subject_type ?? ''),
                                                };
                                            @endphp
                                            @if (!empty($subjectLabel))
                                                <span class="font-medium">{{ $subjectLabel }}</span>
                                                @if ($log->subject)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $log->subject_type === 'App\\Models\\Role' ? $log->subject->name : '' }}
                                                        {{ $log->subject_type === 'App\\Models\\Permission' ? $log->subject->name : '' }}
                                                        {{ $log->subject_type === 'App\\Models\\User' ? $log->subject->email : '' }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="font-medium">{{ ucfirst($log->log_name) }}</span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ ucfirst($log->description) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('audit-logs.show', $log) }}"
                                                    class="p-1 bg-blue-500 hover:bg-blue-400 text-white rounded-md transition-colors"
                                                    title="{{ __('View') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('No audit logs found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $activityLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

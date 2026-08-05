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
                            <select name="event" class="ml-2 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('All Events') }}</option>
                                <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                                <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                                <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                                <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>{{ __('Restored') }}</option>
                            </select>
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">#</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Action') }}</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('User') }}</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Subject') }}</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse ($activityLogs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $loop->iteration + ($activityLogs->currentPage() - 1) * $activityLogs->perPage() }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @if($log->event === 'created')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('Created') }}</span>
                                            @elseif($log->event === 'updated')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">{{ __('Updated') }}</span>
                                            @elseif($log->event === 'deleted')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Deleted') }}</span>
                                            @else
                                                <span class="text-gray-500">{{ $log->event }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $log->causer?->name ?? __('Unknown') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $log->causer_type === 'App\Models\Role' ? __('Role') : '' }}
                                            {{ $log->causer_type === 'App\Models\Permission' ? __('Permission') : '' }}
                                            {{ $log->causer_type === 'App\Models\User' ? __('User') : '' }}
                                            @if($log->subject)
                                                <span class="block text-gray-500 dark:text-gray-400">{{ $log->subject_type === 'App\Models\Role' ? $log->subject->name : '' }}</span>
                                                <span class="block text-gray-500 dark:text-gray-400">{{ $log->subject_type === 'App\Models\User' ? $log->subject->email : '' }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
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
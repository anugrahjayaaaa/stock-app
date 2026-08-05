<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Audit Log Detail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="font-medium">{{ __('Action') }}</span>
                        <span class="text-sm bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 px-2.5 py-0.5 rounded-full">{{ $activityLog->event }}</span>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('By User') }}</span>
                        <span class="font-medium">{{ $activityLog->causer?->name }} ({{ $activityLog->causer?->email }})</span>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('Subject Type') }}</span>
                        <span class="font-medium">{{ $activityLog->subject_type ?? __('N/A') }}</span>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('Subject ID') }}</span>
                        <span class="font-medium">{{ $activityLog->subject_id ?? __('N/A') }}</span>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('Log Name') }}</span>
                        <span class="font-medium">{{ $activityLog->log_name }}</span>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('Date') }}</span>
                        <span class="font-medium">{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</span>
                    </div>

                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('Properties') }}</span>
                        <pre class="mt-2 p-3 bg-gray-100 dark:bg-gray-900/50 rounded-md text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-400 text-white text-sm font-medium rounded-md">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
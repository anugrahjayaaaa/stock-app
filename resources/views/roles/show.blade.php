<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $role->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Name') }}</span>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</p>
                </div>

                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Permissions') }}</span>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ($role->permissions as $permission)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $permission->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="flex space-x-2">
                    @can('edit roles')
                        <a href="{{ route('roles.edit', $role) }}"
                           class="px-4 py-2 bg-yellow-500 hover:bg-yellow-400 text-white text-sm font-medium rounded-md">{{ __('Edit') }}</a>
                    @endcan
                    <a href="{{ route('roles.index') }}"
                       class="px-4 py-2 bg-gray-500 hover:bg-gray-400 text-white text-sm font-medium rounded-md">{{ __('Back') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
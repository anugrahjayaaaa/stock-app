<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @can('create roles')
                        <a href="{{ route('roles.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-md mb-4">
                            {{ __('Create Role') }}
                        </a>
                    @endcan

                    @forelse ($roles as $role)
                        <div class="border-b border-gray-200 dark:border-gray-700 py-3 flex items-center justify-between">
                            <div>
                                <span class="font-medium">{{ $role->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $role->permissions_count }} permissions</span>
                            </div>
                            <div class="flex space-x-2">
                                @can('edit roles')
                                    <a href="{{ route('roles.edit', $role) }}"
                                       class="px-3 py-1 bg-yellow-500 hover:bg-yellow-400 text-white text-xs rounded-md">{{ __('Edit') }}</a>
                                @endcan
                                @can('delete roles')
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline"
                                          x-data="{ show: false }" @submit.prevent="show = true">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white text-xs rounded-md"
                                                @click="show = true">{{ __('Delete') }}</button>
                                        <!-- Alpine modal -->
                                        <div x-show="show" x-cloak
                                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                             @keydown.escape.window="show = false">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full">
                                                <p class="text-gray-900 dark:text-gray-100 mb-4">{{ __('Delete this role?') }}</p>
                                                <div class="flex justify-end space-x-2">
                                                    <button type="button" class="px-3 py-1 bg-gray-500 text-white rounded-md"
                                                            @click="show = false">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No roles found.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

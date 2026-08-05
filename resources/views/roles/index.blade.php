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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        @can('create roles')
                            <a href="{{ route('roles.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-md">
                                {{ __('Create Role') }}
                            </a>
                        @else
                            <span></span>
                        @endcan

                        <!-- Search -->
                        <form method="GET" action="{{ route('roles.index') }}" class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="search" name="search" value="{{ request('search') }}"
                                   class="block w-full sm:w-64 pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="{{ __('Search roles...') }}">
                            @if (request('search'))
                                <a href="{{ route('roles.index') }}"
                                   class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            @endif
                        </form>
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
                                        {{ __('Role Name') }}</th>
                                    <th
                                        class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Permissions Count') }}</th>
                                    <th
                                        class="px-4 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($roles as $role)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                           {{ $roles->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $role->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $role->permissions_count }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center space-x-2">
                                                @can('edit roles')
                                                    <a href="{{ route('roles.edit', $role) }}"
                                                        class="p-1 bg-yellow-500 hover:bg-yellow-400 text-white rounded-md transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                @endcan

                                                @can('delete roles')
                                                    <div x-data="{ confirm: false }" class="inline">
                                                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                                            class="inline">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="button"
                                                                class="p-1 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors"
                                                                @click="confirm = true">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </button>

                                                            <div x-show="confirm" x-cloak
                                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                                                @keydown.escape.window="confirm = false">
                                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full shadow-xl"
                                                                    @click.away="confirm = false">
                                                                    <p
                                                                        class="text-gray-900 dark:text-gray-100 mb-4 font-medium">
                                                                        {{ __('Delete this role?') }}</p>
                                                                    <div class="flex justify-end space-x-2">
                                                                        <button type="button"
                                                                            class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition"
                                                                            @click="confirm = false">
                                                                            {{ __('Cancel') }}
                                                                        </button>

                                                                        <button type="submit"
                                                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md transition">
                                                                            {{ __('Delete') }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

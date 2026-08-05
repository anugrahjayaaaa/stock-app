<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <form method="POST" action="{{ route('roles.store') }}"
                  class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Role Name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Permissions') }}</span>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-md">
                        {{ __('Save Role') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

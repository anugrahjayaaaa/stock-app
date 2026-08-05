@if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
         class="mb-4 rounded-lg border-l-4 border-green-500 bg-green-50 dark:bg-green-900/50 p-4 text-green-800 dark:text-green-100">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
         class="mb-4 rounded-lg border-l-4 border-red-500 bg-red-50 dark:bg-red-900/50 p-4 text-red-800 dark:text-red-100">
        {{ session('error') }}
    </div>
@endif

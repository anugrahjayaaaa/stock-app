<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app sidebar-mini layout-fixed sidebar-without-hover">

<div class="app-wrapper">

    <!-- Sidebar (left) -->
    @include('layouts.sidebar')

    <!-- Header (top) -->
    @include('layouts.header')

    <!-- Main -->
    <main class="app-main">
        @hasSection('header')
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('header')</h3>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}.</strong> All rights reserved.
    </footer>
</div>

@include('partials.delete-modal')
@stack('scripts')
</body>
</html>

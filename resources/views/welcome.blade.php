<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name', 'Stock App') }} - Indonesian stock analysis & monitoring">
    <title>{{ config('app.name', 'Stock App') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg bg-primary navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            <i class="fas fa-chart-line me-2"></i>{{ config('app.name', 'Stock App') }}
        </a>
        <div class="d-flex">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Log In</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-light">Register</a>
            @endif
        </div>
    </div>
</nav>

<header class="py-5 bg-primary text-white">
    <div class="container text-center py-4">
        <h1 class="display-5 fw-bold mb-3">Track & Analyze Indonesian Stocks</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 620px;">
            Real-time charts, role-based access, and a full audit trail — all in one clean dashboard.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4">Get Started</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Sign In</a>
        </div>
    </div>
</header>

<main class="container py-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-primary mb-3"><i class="fas fa-chart-line fa-2x"></i></div>
                    <h5 class="card-title">Live Charts</h5>
                    <p class="card-text text-muted">Interactive TradingView charts for any listed ticker, with theme-aware dark mode.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-primary mb-3"><i class="fas fa-user-shield fa-2x"></i></div>
                    <h5 class="card-title">Role & Permission</h5>
                    <p class="card-text text-muted">Granular RBAC — assign roles, scope permissions, and keep access under control.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-primary mb-3"><i class="fas fa-history fa-2x"></i></div>
                    <h5 class="card-title">Audit Trail</h5>
                    <p class="card-text text-muted">Every create, update, and delete is logged with who, what, and when.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0 small">&copy; {{ date('Y') }} {{ config('app.name', 'Stock App') }}. All rights reserved.</p>
    </div>
</footer>

</body>
</html>

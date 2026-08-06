<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Stock Chart - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <!-- Top Header -->
    @include('layouts.header')

    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Left Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <!-- Page Heading -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Chart</h1>
                </div>
            </header>

            <!-- Page Content -->
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- TradingView Widget -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div id="tradingview-chart" style="height: 600px;"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- TradingView Widget Script -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tradingview-widget/1.0.0/tradingview-widget.min.js"></script>
    <script type="text/javascript">
        new TradingView.widget({
            "autosize": true,
            "symbol": "IDX:BBCA",
            "interval": "D",
            "timezone": "Asia/Jakarta",
            "theme": "light",
            "style": "1",
            "locale": "id",
            "toolbar_bg": "#f1f3f6",
            "enable_publishing": false,
            "withdateranges": true,
            "allow_symbol_change": true,
            "container_id": "tradingview-chart",
            "hide_top_toolbar": false,
            "hide_legend": false,
            "save_image": false,
            "calendar": false,
            "hide_volume": false,
            "support_host": "https://www.tradingview.com"
        });
    </script>
</body>
</html>
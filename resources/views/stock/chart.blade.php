@extends('layouts.app')
@section('header', __('Stock Chart'))

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column gap-2">
            <h3 class="card-title mb-0">IDX TradingView</h3>
            <div class="d-flex gap-1 align-items-center">
                <input list="idx-stocks" id="tv-symbol" class="form-control form-control-sm" style="width:200px;"
                    placeholder="Kode saham, mis. BBCA" value="BBCA" autocomplete="off">
                <button id="tv-load" class="btn btn-sm btn-primary" type="button">Show</button>
                <datalist id="idx-stocks">
                    @foreach ($stocks as $stock)
                        <option value="{{ $stock->code }}">{{ $stock->name }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="card-body">
            <div id="tradingview-chart" style="height: 600px;"></div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
            (function() {
                const container = document.getElementById('tradingview-chart');
                const input = document.getElementById('tv-symbol');
                const loadBtn = document.getElementById('tv-load');

                function theme() {
                    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                }

                // User types a bare code (BBCA) or full (IDX:BBCA); always normalize to IDX:<CODE>.
                function symbol() {
                    let v = (input.value || '').trim().toUpperCase();
                    if (!v) return 'IDX:BBCA';
                    return v.startsWith('IDX:') ? v : 'IDX:' + v;
                }

                function config() {
                    return {
                        autosize: true,
                        symbol: symbol(),
                        interval: 'D',
                        timezone: 'Asia/Jakarta',
                        theme: theme(),
                        style: '1',
                        locale: 'en',
                        toolbar_bg: '#f1f3f6',
                        enable_publishing: false,
                        withdateranges: true,
                        allow_symbol_change: true,
                        container_id: 'tradingview-chart',
                        hide_top_toolbar: false,
                        hide_legend: false,
                        save_image: true, 
                        calendar: false,
                        hide_volume: false,
                        studies: ['EMA', 'SMA', 'MACD', 'RSI', 'BB'],

                        show_popup_button: true,
                        popup_width: "1000",
                        popup_height: "650",

                        disabled_features: [],
                        enabled_features: ["side_toolbar_in_fullscreen_mode"],
                        support_host: 'https://www.tradingview.com'
                    };
                }

                function init() {
                    if (typeof TradingView === 'undefined' || !TradingView.widget) {
                        console.warn('TradingView widget script not loaded yet.');
                        return;
                    }
                    new TradingView.widget(config());
                }

                if (typeof TradingView !== 'undefined' && TradingView.widget) {
                    init();
                } else {
                    window.addEventListener('load', init);
                }

                function reload() {
                    if (typeof TradingView === 'undefined' || !TradingView.widget) return;
                    container.innerHTML = '';
                    new TradingView.widget(config());
                }

                loadBtn.addEventListener('click', reload);
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') reload();
                });

                document.addEventListener('theme-changed', () => location.reload());
            })();
        </script>
    @endpush
@endsection

@extends('layouts.app')
@section('header', __('Stock Chart'))

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">IDX TradingView</h3>
        <div class="card-tools ms-auto">
            <select id="tv-symbol" class="form-select form-select-sm d-inline-block" style="width:auto;">
                <option value="IDX:BBCA">BBCA</option>
                <option value="IDX:BBRI">BBRI</option>
                <option value="IDX:TLKM">TLKM</option>
                <option value="IDX:ASII">ASII</option>
                <option value="IDX:GOTO">GOTO</option>
                <option value="IDX:ANTM">ANTM</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="tradingview-chart" style="height: 600px;"></div>
    </div>
</div>

@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tradingview-widget@1.0.0/tradingview-widget.min.js"></script>
<script type="text/javascript">
    (function () {
        const container = document.getElementById('tradingview-chart');
        const symbolSelect = document.getElementById('tv-symbol');

        function theme() {
            return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        }

        let widget = new TradingView.widget({
            autosize: true,
            symbol: symbolSelect.value,
            interval: 'D',
            timezone: 'Asia/Jakarta',
            theme: theme(),
            style: '1',
            locale: 'id',
            toolbar_bg: '#f1f3f6',
            enable_publishing: false,
            withdateranges: true,
            allow_symbol_change: true,
            container_id: 'tradingview-chart',
            hide_top_toolbar: false,
            hide_legend: false,
            save_image: false,
            calendar: false,
            hide_volume: false,
            support_host: 'https://www.tradingview.com'
        });

        symbolSelect.addEventListener('change', () => {
            container.innerHTML = '';
            widget = new TradingView.widget(Object.assign(widget.options_, {
                symbol: symbolSelect.value,
                container_id: 'tradingview-chart'
            }));
        });

        document.addEventListener('theme-changed', () => location.reload());
    })();
</script>
@endpush
@endsection

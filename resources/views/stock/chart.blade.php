@extends('layouts.app')
@section('header', __('Stock Chart'))

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-column gap-2">
        <h3 class="card-title mb-0">IDX Chart</h3>
        <div class="d-flex gap-1 align-items-center flex-wrap">
            <input list="idx-stocks" id="tv-symbol" class="form-control form-control-sm" style="width:200px;"
                   placeholder="Kode saham, mis. BBCA" value="BBCA" autocomplete="off">
            <select id="tv-tool" class="form-select form-select-sm" style="width:auto;">
                <option value="">— drawing tool —</option>
                <option value="trend_line">Trend Line</option>
                <option value="horizontal_line">Horizontal Line</option>
                <option value="rectangle">Rectangle</option>
                <option value="text">Text</option>
            </select>
            <button id="tv-load" class="btn btn-sm btn-primary" type="button">Show</button>
            <datalist id="idx-stocks">
                @foreach ($stocks as $stock)
                    <option value="{{ $stock->code }}">{{ $stock->name }}</option>
                @endforeach
            </datalist>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2 px-2 py-1 rounded" style="background:#1e222d;">
            <label class="form-label mb-0 small text-light">TF</label>
            <select id="tv-tf" class="form-select form-select-sm" style="width:auto;background:#2a2e39;color:#d1d4dc;border-color:#363a45;">
                <option value="1m">1m</option>
                <option value="5m">5m</option>
                <option value="15m">15m</option>
                <option value="1h">1h</option>
                <option value="2h">2h</option>
                <option value="4h">4h</option>
                <option value="1D" selected>1D</option>
                <option value="1W">1W</option>
                <option value="1M">1M</option>
            </select>
            <label class="form-label mb-0 small text-light ms-2">Indicator</label>
            <select id="tv-ind" class="form-select form-select-sm" style="width:auto;background:#2a2e39;color:#d1d4dc;border-color:#363a45;" multiple size="1">
                <option value="ma20" selected>MA 20</option>
                <option value="ma50" selected>MA 50</option>
                <option value="ma200">MA 200</option>
                <option value="macd" selected>MACD</option>
                <option value="rsi14">RSI 14</option>
                <option value="volume" selected>Volume</option>
            </select>
            <label class="form-label mb-0 small text-light ms-2">From</label>
            <input type="date" id="tv-from" class="form-control form-control-sm" style="width:150px;background:#2a2e39;color:#d1d4dc;border-color:#363a45;">
            <label class="form-label mb-0 small text-light ms-2">To</label>
            <input type="date" id="tv-to" class="form-control form-control-sm" style="width:150px;background:#2a2e39;color:#d1d4dc;border-color:#363a45;">
            <button id="tv-apply" class="btn btn-sm ms-1" type="button" style="background:#2962ff;color:#fff;">Apply</button>
        </div>
        <div id="tradingview-chart" style="height: 640px;"></div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/stock-chart.js')
@endpush
@endsection

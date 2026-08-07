@extends('layouts.app')
@section('header', __('Stock Analysis'))

@section('content')
    @php
        // ponytail: mock data — swap with Invezgo payload later.
        $buyers = [
            ['code' => 'YP', 'vol' => '5,200', 'val' => '53.2', 'avg' => '10,227'],
            ['code' => 'AK', 'vol' => '4,100', 'val' => '41.9', 'avg' => '10,229'],
            ['code' => 'PD', 'vol' => '3,300', 'val' => '33.7', 'avg' => '10,224'],
            ['code' => 'BK', 'vol' => '3,000', 'val' => '30.7', 'avg' => '10,220'],
            ['code' => 'KZ', 'vol' => '2,900', 'val' => '29.7', 'avg' => '10,228'],
        ];
        $sellers = [
            ['code' => 'AZ', 'vol' => '4,800', 'val' => '49.1', 'avg' => '10,225'],
            ['code' => 'XL', 'vol' => '3,900', 'val' => '39.9', 'avg' => '10,223'],
            ['code' => 'NI', 'vol' => '3,200', 'val' => '32.7', 'avg' => '10,219'],
            ['code' => 'CS', 'vol' => '2,800', 'val' => '28.6', 'avg' => '10,214'],
            ['code' => 'RX', 'vol' => '2,400', 'val' => '24.5', 'avg' => '10,225'],
        ];
        $doneRows = [
            ['time' => '09:15:22', 'action' => 'BUY',  'buyer' => 'YP', 'seller' => 'CC', 'market' => 'RG', 'inv' => 'D', 'price' => '9,800', 'vol' => '50,000', 'grouped' => true],
            ['time' => '09:15:30', 'action' => 'SELL', 'buyer' => 'NI', 'seller' => 'AZ', 'market' => 'RG', 'inv' => 'F', 'price' => '9,775', 'vol' => '1,250', 'grouped' => false],
            ['time' => '09:16:05', 'action' => 'BUY',  'buyer' => 'PD', 'seller' => 'AZ', 'market' => 'RG', 'inv' => 'F', 'price' => '9,805', 'vol' => '1,250', 'grouped' => false],
            ['time' => '09:16:48', 'action' => 'SELL', 'buyer' => 'NH', 'seller' => 'YP', 'market' => 'RG', 'inv' => 'D', 'price' => '9,770', 'vol' => '900', 'grouped' => false],
            ['time' => '09:17:40', 'action' => 'BUY',  'buyer' => 'CC', 'seller' => 'NH', 'market' => 'RG', 'inv' => 'D', 'price' => '9,810', 'vol' => '2,400', 'grouped' => false],
            ['time' => '09:18:20', 'action' => 'SELL', 'buyer' => 'PD', 'seller' => 'AZ', 'market' => 'NG', 'inv' => 'F', 'price' => '9,765', 'vol' => '1,100', 'grouped' => false],
            ['time' => '09:19:12', 'action' => 'BUY',  'buyer' => 'AZ', 'seller' => 'PD', 'market' => 'TN', 'inv' => 'F', 'price' => '9,815', 'vol' => '600', 'grouped' => false],
            ['time' => '09:20:02', 'action' => 'SELL', 'buyer' => 'CC', 'seller' => 'PD', 'market' => 'RG', 'inv' => 'D', 'price' => '9,760', 'vol' => '2,050', 'grouped' => false],
            ['time' => '09:21:55', 'action' => 'BUY',  'buyer' => 'YP', 'seller' => 'CC', 'market' => 'RG', 'inv' => 'D', 'price' => '9,800', 'vol' => '1,750', 'grouped' => false],
            ['time' => '09:22:39', 'action' => 'SELL', 'buyer' => 'YP', 'seller' => 'NH', 'market' => 'RG', 'inv' => 'F', 'price' => '9,755', 'vol' => '700', 'grouped' => false],
        ];
    @endphp
    <div class="card card-primary card-outline" id="analysis-page">
        <div class="card-header d-flex flex-column gap-2">
            <h3 class="card-title mb-0">Stock Analysis</h3>
            <div class="d-flex gap-1 align-items-center">
                <input list="idx-stocks" id="analysis-symbol" class="form-control form-control-sm" style="width:200px;"
                    placeholder="Kode saham, mis. BBCA" value="BBCA" autocomplete="off">
                <button id="analysis-load" class="btn btn-sm btn-primary" type="button">Cari</button>
                <button id="analysis-run" class="btn btn-sm btn-success" type="button">Analyze (AI)</button>
                <datalist id="idx-stocks">
                    @foreach ($stocks as $stock)
                        <option value="{{ $stock->code }}">{{ $stock->name }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Price + Volume chart --}}
                <div class="col-12">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Price & Volume</h5>
                        </div>
                        <div class="card-body">
                            <div id="analysis-chart" style="height:480px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Broker summary --}}
                <div class="col-lg-6 d-flex">
                    <x-broker-summary :ticker="'BBCA'" :avg="'10,224'" :vol="'18,500 Lot'" :val="'Rp 189.2M'"
                        :accum="'ACCUMULATION'" :buy-pct="58" :sell-pct="42" :buyers="$buyers" :sellers="$sellers"
                        :trade-day="$tradeDay" />
                </div>
                {{-- Done / detail transactions --}}
                <div class="col-lg-6 d-flex">
                    <x-done-detail-transactions :ticker="'BBCA'" :price="'10,225'" :change="'-25 / -0.2%'"
                        :vol="'52,800'" :rows="$doneRows" />
                </div>
                {{-- PV analysis --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Price-Volume Analysis (RYCAD / WYFCOOF)</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — PV signals from OHLC + volume.</div>
                    </div>
                </div>
                {{-- Technikal --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Technikal Analysis</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — RSI / MACD / MA signals.</div>
                    </div>
                </div>
                {{-- Smart-money classification --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Buyer/Seller Classification</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — bandar / emiten / smart money / ritel tags.</div>
                    </div>
                </div>
                {{-- Accumulation range + AVG lines --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Accumulation Range & AVG Lines</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — AVG SM/bandar/emiten as support, AVG ritel as
                            resistance.</div>
                    </div>
                </div>
                {{-- AI analysis result --}}
                <div class="col-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h5 class="card-title mb-0">AI Analysis Result</h5>
                        </div>
                        <div class="card-body text-muted" id="analysis-ai-result">Placeholder — output from
                            trained/validated AI skill.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    // ponytail: grouped-by-order-id toggle — shows only is_grouped rows when active.
    const groupToggle = document.getElementById('groupToggle');
    if (groupToggle) {
        groupToggle.addEventListener('change', function () {
            const active = this.checked;
            document.querySelectorAll('#analysis-page [data-grouped]').forEach(r => {
                r.style.display = (active && r.dataset.grouped !== '1') ? 'none' : '';
            });
        });
    }
</script>
@endpush
@endsection

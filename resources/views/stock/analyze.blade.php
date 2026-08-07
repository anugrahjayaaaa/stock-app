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
        $pvaRows = [
            ['date' => '2026-08-03', 'change' => 1.8,  'volRatio' => 1.9, 'status' => 'Accumulation', 'tag' => 'Strong demand, breakout'],
            ['date' => '2026-08-04', 'change' => 0.4,  'volRatio' => 0.8, 'status' => 'Weak Rally',   'tag' => 'Low participation'],
            ['date' => '2026-08-05', 'change' => -0.6, 'volRatio' => 0.7, 'status' => 'Retest',       'tag' => 'Healthy pullback'],
            ['date' => '2026-08-06', 'change' => -2.1, 'volRatio' => 2.3, 'status' => 'Distribution', 'tag' => 'Heavy selling'],
            ['date' => '2026-08-07', 'change' => 1.2,  'volRatio' => 1.5, 'status' => 'Accumulation', 'tag' => 'Bid support'],
        ];
        $techPatterns = [
            ['name' => 'Double Bottom', 'type' => 'Reversal', 'tp' => '10,650', 'sl' => '9,900', 'status' => 'Confirmed'],
            ['name' => 'Bullish Flag', 'type' => 'Continuation', 'tp' => '10,800', 'sl' => '10,050', 'status' => 'Forming'],
            ['name' => 'Triangle', 'type' => 'Continuation', 'tp' => '10,500', 'sl' => '9,950', 'status' => 'Forming'],
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
                <div class="col-lg-6 d-flex">
                    <x-price-volume-analysis :start="$tradeDay" :end="$tradeDay" :score="62"
                        :summary="'Net accumulation with above-average volume on up days; distribution day on 2026-08-06 was an exception. Bias mildly bullish.'"
                        :stats="['accumulation' => 2, 'weakRally' => 1, 'retest' => 1, 'distribution' => 1]"
                        :rows="$pvaRows" />
                </div>
                {{-- Technikal --}}
                <div class="col-lg-6 d-flex">
                    <x-technical-analysis-widget :ticker="'BBCA'" :price="'10,225'" :change="-0.2"
                        :score-label="'Bullish'" :summary="'Price above S1 with MACD golden cross and RSI neutral; foreign flow positive over 20D. Bias bullish toward R1.'"
                        :sr="['price' => '10,225', 's1' => '10,000', 's2' => '9,750', 'r1' => '10,450', 'r2' => '10,700', 'dS1' => '-2.2', 'dS2' => '-4.6', 'dR1' => '2.2', 'dR2' => '4.6']"
                        :macd-state="'Golden Cross'" :macd-hist="'12.4'" :macd-trend="'up'"
                        :rsi="54" :rsi-div="false"
                        :foreign="['d1' => '+8.2M', 'd5' => '+22.5M', 'd20' => '+115.3M']"
                        :patterns="$techPatterns"
                        :level-pos="['s2' => 5, 's1' => 25, 'price' => 60, 'r1' => 75, 'r2' => 95]" />
                </div>
                {{-- Smart-money classification --}}
                <div class="col-lg-6 d-flex">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Buyer/Seller Classification</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — bandar / emiten / smart money / ritel tags.</div>
                    </div>
                </div>
                {{-- Accumulation range + AVG lines --}}
                <div class="col-lg-6 d-flex">
                    <div class="card card-outline card-secondary h-100">
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

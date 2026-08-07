@extends('layouts.app')
{{-- Stock Analysis page: composes all analysis widgets (broksum, done-detail,
     impostor, accumulation, inventory) fed by StockAnalysisController::index. --}}
@section('header', __('Stock Analysis'))

@section('content')
    @php
        // ponytail: PVA keeps static UI mock; swap with GoAPI historical OHLC later.
        // $doneRows removed: done-detail widget commented out (needs Invezgo tape data).
        $pvaRows = [
            ['date' => '2026-08-03', 'change' => 1.8,  'volRatio' => 1.9, 'status' => 'Accumulation', 'tag' => 'Strong demand, breakout'],
            ['date' => '2026-08-04', 'change' => 0.4,  'volRatio' => 0.8, 'status' => 'Weak Rally',   'tag' => 'Low participation'],
            ['date' => '2026-08-05', 'change' => -0.6, 'volRatio' => 0.7, 'status' => 'Retest',       'tag' => 'Healthy pullback'],
            ['date' => '2026-08-06', 'change' => -2.1, 'volRatio' => 2.3, 'status' => 'Distribution', 'tag' => 'Heavy selling'],
            ['date' => '2026-08-07', 'change' => 1.2,  'volRatio' => 1.5, 'status' => 'Accumulation', 'tag' => 'Bid support'],
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
                {{-- Broker summary — COMMENTED: needs Invezgo broker-summary data (GoAPI free tier has no broker-level data). --}}
                {{--
                <div class="col-lg-6 d-flex">
                    <x-broker-summary :ticker="'BBCA'" :avg="'10,224'" :vol="'18,500 Lot'" :val="'Rp 189.2M'"
                        :accum="'ACCUMULATION'" :buy-pct="58" :sell-pct="42" :buyers="$buyers" :sellers="$sellers"
                        :trade-day="$tradeDay" />
                </div>
                --}}
                {{-- Done / detail transactions — COMMENTED: needs Invezgo transaction-tape data (GoAPI free tier has no per-trade tape). --}}
                {{--
                <div class="col-lg-6 d-flex">
                    <x-done-detail-transactions :ticker="'BBCA'" :price="'10,225'" :change="'-25 / -0.2%'"
                        :vol="'52,800'" :rows="$doneRows" />
                </div>
                --}}
                {{-- PV analysis — KEEP: derivable from GoAPI historical OHLC. --}}
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
                {{-- Smart-money classification / impostor — COMMENTED: needs Invezgo broker-behavior data (GoAPI free tier has no broker-level data). --}}
                {{--
                <div class="col-lg-6 d-flex">
                    <x-broker-impostor-widget :buyers="$buyersCls" :sellers="$sellersCls" :retail="$retail" />
                </div>
                --}}
                {{-- Accumulation range + AVG lines — COMMENTED: needs Invezgo accumulated-net-per-broker data (GoAPI free tier has no broker-level data). --}}
                {{--
                <div class="col-lg-6 d-flex">
                    <x-accumulation-avg-widget :data="$accumulation" />
                </div>
                --}}
                {{-- Broker inventory chart + AI summary — COMMENTED: needs Invezgo per-broker inventory data (GoAPI free tier has no broker-level data). --}}
                {{--
                <div class="col-12">
                    <x-broker-inventory-chart :inventory="$inventory" />
                </div>
                --}}
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

@php
    // ponytail: single source for accumulation render. `data` comes from StockAnalysisController::mockAccumulation.
    $d = $data;
    $fmtIdr = fn($v) => $v >= 1e12
        ? number_format($v / 1e12, 2) . ' T'
        : ($v >= 1e9
            ? number_format($v / 1e9, 2) . ' B'
            : ($v >= 1e6
                ? number_format($v / 1e6, 2) . ' M'
                : number_format($v)));
    $fmtLot = fn($v) => $v >= 1e6
        ? number_format($v / 1e6, 2) . 'M'
        : ($v >= 1e3
            ? number_format($v / 1e3, 1) . 'K'
            : (string) $v);

    $cur = $d['currentPrice'];
    $avg = $d['bandarAvg'];
    $diff = (($cur - $avg) / $avg) * 100;
    [$zoneCls, $zoneTxt] =
        $diff < 0
            ? ['bg-primary', 'Bandar Floating Loss Zone']
            : ($diff <= 5
                ? ['bg-success', 'Safe Entry Zone']
                : ($diff > 10
                    ? ['bg-warning text-dark', 'Floating Profit Zone']
                    : ['bg-info text-dark', 'Neutral Zone']));

    // price distance bar: position each marker on a min..max track.
    $track = collect([$d['sl'], $avg, $cur, $d['target']]);
    $min = $track->min();
    $max = $track->max();
    $span = max($max - $min, 1);
    $pos = fn($v) => round((($v - $min) / $span) * 100, 1);
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-layer-group me-1 text-secondary"></i> Bandar Accumulation Range &amp;
            AVG Price</h5>
        <span class="badge bg-success-subtle text-success rounded-pill">🟢 {{ $d['tradingDays'] }} Trading Days
            Accumulation</span>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">

        {{-- Date range control (display only) --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label small text-muted mb-1">Start Date</label>
                <input type="date" class="form-control form-control-sm font-monospace" value="{{ $d['start'] }}"
                    readonly>
            </div>
            <div class="col-6">
                <label class="form-label small text-muted mb-1">End Date</label>
                <input type="date" class="form-control form-control-sm font-monospace" value="{{ $d['end'] }}"
                    readonly>
            </div>
        </div>

        {{-- Key metric cards --}}
        <div class="row g-2 mb-3">
            <div class="col-7">
                <div class="border rounded p-2 h-100">
                    <div class="small text-muted">Bandar AVG Price</div>
                    <div class="font-monospace fw-bold text-xl">{{ number_format($avg) }}</div>
                    <span class="badge {{ $zoneCls }} mt-1" style="font-size:10px;">{{ $zoneTxt }}
                        ({{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 1) }}%)</span>
                </div>
            </div>
            <div class="col-5">
                <div class="border rounded p-2 h-100">
                    <div class="small text-muted">Total Ammunition</div>
                    <div class="font-monospace fw-bold" style="font-size:14px;">{{ $fmtLot($d['totalNetLot']) }} Lot
                    </div>
                    <div class="font-monospace text-success" style="font-size:12px;">Rp
                        {{ $fmtIdr($d['totalNetValue']) }}</div>
                </div>
            </div>
        </div>

        {{-- Top 3 accumulator brokers --}}
        <div class="fw-bold mb-1" style="font-size:11px;">Top 3 Accumulator Brokers</div>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-hover mb-0 align-middle font-monospace" style="font-size:11px;">
                <thead class="table-light">
                    <tr>
                        <th>Broker</th>
                        <th class="text-end">Net Lot</th>
                        <th class="text-end">Net Value</th>
                        <th class="text-end">AVG</th>
                        <th class="text-end">Dom%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($d['top3'] as $b)
                        <tr>
                            <td><x-broker-badge :code="$b['code']" class="me-1" /> <span
                                    class="small text-muted">{{ $b['name'] }}</span></td>
                            <td class="text-end">{{ $fmtLot($b['netLot']) }}</td>
                            <td class="text-end">{{ $fmtIdr($b['netValue']) }}</td>
                            <td class="text-end">{{ number_format($b['avgPrice']) }}</td>
                            <td class="text-end">{{ $b['concentration'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Price distance bar --}}
        <div class="mt-3 pt-3 border-top">
            {{-- Header Title & Sub-info --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted font-monospace" style="font-size: 11px;">
                    <i class="bi bi-arrows-expand me-1"></i>Price Range & Bandar AVG
                </span>
                @php
                    $diffPercent = $avg > 0 ? (($cur - $avg) / $avg) * 100 : 0;
                    $badgeColor =
                        $diffPercent >= 0
                            ? 'text-success bg-success-subtle border-success-subtle'
                            : 'text-danger bg-danger-subtle border-danger-subtle';
                @endphp
                <span class="badge border font-monospace {{ $badgeColor }}" style="font-size: 9px;">
                    {{ $diffPercent >= 0 ? '+' : '' }}{{ number_format($diffPercent, 2) }}% vs AVG
                </span>
            </div>

            {{-- Main Bar Container --}}
            <div class="position-relative px-2 py-1" style="height: 52px;">
                {{-- Base Track Line --}}
                <div class="position-absolute top-50 start-0 end-0 translate-middle-y rounded-pill"
                    style="height: 6px; background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color-translucent);">
                </div>

                {{-- 1. Stop Loss (SL) --}}
                <div class="position-absolute top-50 translate-middle text-center"
                    style="left: {{ $pos($d['sl']) }}%; z-index: 1;">
                    <div class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace py-1 px-1 mb-1 shadow-sm"
                        style="font-size: 8px; line-height: 1;">
                        SL {{ number_format($d['sl']) }}
                    </div>
                    <div class="mx-auto rounded-pill" style="width: 2px; height: 12px; background: var(--bs-danger);">
                    </div>
                </div>

                {{-- 2. Bandar AVG --}}
                <div class="position-absolute top-50 translate-middle text-center"
                    style="left: {{ $pos($avg) }}%; z-index: 2;">
                    <div class="badge bg-success-subtle text-success border border-success-subtle font-monospace py-1 px-1 mb-1 shadow-sm"
                        style="font-size: 8px; line-height: 1;">
                        AVG {{ number_format($avg) }}
                    </div>
                    <div class="mx-auto rounded-pill" style="width: 3px; height: 14px; background: var(--bs-success);">
                    </div>
                </div>

                {{-- 3. Current Price (NOW) - Highlighting Focal Point --}}
                <div class="position-absolute top-50 translate-middle text-center"
                    style="left: {{ $pos($cur) }}%; z-index: 3;">
                    <div class="badge bg-primary text-white font-monospace fw-bold py-1 px-1.5 mb-1 shadow"
                        style="font-size: 9px; line-height: 1;">
                        NOW {{ number_format($cur) }}
                    </div>
                    <div class="mx-auto rounded-circle border border-2 border-white shadow-sm"
                        style="width: 10px; height: 10px; background: var(--bs-primary); margin-top: -1px;"></div>
                </div>

                {{-- 4. Target (TGT) --}}
                <div class="position-absolute top-50 translate-middle text-center"
                    style="left: {{ $pos($d['target']) }}%; z-index: 1;">
                    <div class="badge bg-info-subtle text-info border border-info-subtle font-monospace py-1 px-1 mb-1 shadow-sm"
                        style="font-size: 8px; line-height: 1;">
                        TGT {{ number_format($d['target']) }}
                    </div>
                    <div class="mx-auto rounded-pill" style="width: 2px; height: 12px; background: var(--bs-info);">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

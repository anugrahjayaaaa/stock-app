@php
    $scoreClass = match ($scoreLabel) {
        'Strong Bullish','Bullish' => 'bg-success-subtle text-success border-success-subtle',
        'Bearish','Strong Bearish' => 'bg-danger-subtle text-danger border-danger-subtle',
        default => 'bg-warning-subtle text-warning border-warning-subtle',
    };
    $rsiClass = $rsi < 30 ? 'bg-success-subtle text-success' : ($rsi > 70 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
    $rsiCond = $rsi < 30 ? 'Oversold' : ($rsi > 70 ? 'Overbought' : 'Neutral');
    $macdClass = $macdState === 'Golden Cross' ? 'text-success' : 'text-danger';
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Technikal Analysis</h5>
        <div class="text-end font-monospace" style="font-size:11px;">
            <span class="fw-bold">{{ $ticker }}</span> {{ $price }}
            <span class="{{ $change >= 0 ? 'text-success' : 'text-danger' }}">{{ $change >= 0 ? '+' : '' }}{{ $change }}%</span>
        </div>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <span class="badge {{ $scoreClass }} border rounded-pill px-2 py-0.5 fw-bold" style="font-size:10px;">{{ $scoreLabel }}</span>
            <small class="text-muted">Technical Score</small>
        </div>
        <div class="small text-muted mb-3 border rounded p-2">{{ $summary }}</div>
        {{-- 4 indicator cards --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100 font-monospace" style="font-size:11px;">
                    <div class="text-muted mb-1">Support / Resistance</div>
                    <div>Price <span class="fw-bold">{{ $sr['price'] }}</span></div>
                    <div class="text-success">S1 {{ $sr['s1'] }} <small>({{ $sr['dS1'] }}%)</small></div>
                    <div class="text-success">S2 {{ $sr['s2'] }} <small>({{ $sr['dS2'] }}%)</small></div>
                    <div class="text-danger">R1 {{ $sr['r1'] }} <small>({{ $sr['dR1'] }}%)</small></div>
                    <div class="text-danger">R2 {{ $sr['r2'] }} <small>({{ $sr['dR2'] }}%)</small></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100 font-monospace" style="font-size:11px;">
                    <div class="text-muted mb-1">MACD</div>
                    <div class="{{ $macdClass }} fw-bold">{{ $macdState }}</div>
                    <div>Hist {{ $macdHist }}</div>
                    <div>{{ $macdTrend === 'up' ? '▲' : '▼' }} {{ $macdTrend === 'up' ? 'Up' : 'Down' }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100 font-monospace" style="font-size:11px;">
                    <div class="text-muted mb-1">RSI(14)</div>
                    <div class="fw-bold">{{ $rsi }}</div>
                    <div><span class="badge {{ $rsiClass }}">{{ $rsiCond }}</span></div>
                    <div class="small text-muted">{{ $rsiDiv ? 'Divergence' : 'No divergence' }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100 font-monospace" style="font-size:11px;">
                    <div class="text-muted mb-1">Net Foreign Flow</div>
                    <div>1D <span class="{{ $foreign['d1'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $foreign['d1'] }}</span></div>
                    <div>5D <span class="{{ $foreign['d5'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $foreign['d5'] }}</span></div>
                    <div>20D <span class="{{ $foreign['d20'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $foreign['d20'] }}</span></div>
                </div>
            </div>
            </div>
            {{-- Patterns table --}}
        <div class="mb-3">
            <div class="fw-bold mb-1" style="font-size:11px;">Detected Chart Patterns</div>
            <div style="max-height:160px;overflow:auto;">
                <table class="table table-sm table-hover mb-0 align-middle font-monospace" style="font-size:11px;">
                    <thead class="table-light sticky-top">
                        <tr><th>Pattern</th><th>Type</th><th class="text-end">TP</th><th class="text-end">SL</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($patterns as $p)
                            <tr>
                                <td>{{ $p['name'] }}</td>
                                <td class="small text-muted">{{ $p['type'] }}</td>
                                <td class="text-end text-success">{{ $p['tp'] }}</td>
                                <td class="text-end text-danger">{{ $p['sl'] }}</td>
                                <td><span class="badge {{ $p['status'] === 'Confirmed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $p['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Key levels bar --}}
        <div class="mt-auto">
            <div class="d-flex justify-content-between small text-muted mb-1" style="font-size:10px;">
                <span>S2 {{ $sr['s2'] }}</span><span>S1 {{ $sr['s1'] }}</span><span>Pivot {{ $sr['price'] }}</span><span>R1 {{ $sr['r1'] }}</span><span>R2 {{ $sr['r2'] }}</span>
            </div>
            <div class="progress" style="height:8px;">
                <div class="progress-bar bg-success" style="width:{{ $levelPos['s2'] }}%"></div>
                <div class="progress-bar bg-success-subtle" style="width:{{ $levelPos['s1'] - $levelPos['s2'] }}%"></div>
                <div class="progress-bar bg-warning" style="width:{{ $levelPos['r1'] - $levelPos['s1'] }}%"></div>
                <div class="progress-bar bg-danger-subtle" style="width:{{ $levelPos['r2'] - $levelPos['r1'] }}%"></div>
                <div class="progress-bar bg-danger" style="width:{{ 100 - $levelPos['r2'] }}%"></div>
            </div>
            <div class="position-relative" style="height:0;">
                <div style="position:absolute;left:{{ $levelPos['price'] }}%;top:-14px;transform:translateX(-50%);" class="fw-bold text-primary" title="Current">▼</div>
            </div>
        </div>
    </div>
</div>

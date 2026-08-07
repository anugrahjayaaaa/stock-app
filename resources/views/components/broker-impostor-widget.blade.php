{{-- Broker Impostor Detector: buyer/seller leaderboard + retail behavioral "Impostor Radar". --}}
{{-- Props: buyers[], sellers[] (leaderboard rows), retail[] (behavioral rows). --}}
@php
    // ponytail: single source for badge styling + impostor threshold. Swap with Invezgo payload later.
    $impostorThreshold = 65;

    $invTag = fn($inv) => $inv === 'F'
        ? ['F', 'bg-danger-subtle text-danger']
        : ['D', 'bg-primary-subtle text-primary'];

    $buyerClass = fn($r) => $r['impostor']
        ? ['Impostor Accumulation', 'bg-purple text-white']
        : ($r['net'] >= 0
            ? ['Strong Accumulation', 'bg-success-subtle text-success']
            : ['Normal Retail', 'bg-secondary-subtle text-secondary']);

    $sellerClass = fn($r) => $r['impostor']
        ? ['Impostor Distribution', 'bg-purple text-white']
        : ($r['net'] < 0
            ? ['Heavy Distribution', 'bg-danger-subtle text-danger']
            : ['Normal Sell', 'bg-secondary-subtle text-secondary']);

    $scoreClass = fn($s) => $s > $impostorThreshold
        ? 'bg-purple text-white'
        : ($s >= 30
            ? 'bg-warning text-dark'
            : 'bg-success-subtle text-success');

    $impostors = collect($retail)->filter(fn($r) => $r['score'] > $impostorThreshold)->values();
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-user-secret me-1 text-secondary"></i> Broker Classification & Impostor Detector</h5>
        <span class="badge bg-purple-subtle text-purple border rounded-pill" style="font-size:10px;">Impostor Radar</span>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">

        {{-- Impostor alert banner --}}
        @if ($impostors->isNotEmpty())
            <div class="alert border-warning bg-warning-subtle text-warning-emphasis mb-3 py-2 px-3" role="alert">
                <div class="d-flex align-items-center gap-2 fw-bold" style="font-size:12px;">
                    <i class="fas fa-triangle-exclamation"></i> Retail Impostor Detected
                </div>
                @foreach ($impostors as $im)
                    <div class="small mt-1">
                        <span class="badge bg-purple text-white me-1">{{ $im['code'] }}</span>
                        detected executing Institutional-sized HAKA blocks (score {{ $im['score'] }}, avg {{ number_format($im['avgLot']) }} lot/trade).
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Dual-column buyer/seller leaderboard --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="text-success fw-bold mb-1" style="font-size:11px;">TOP BUYERS</div>
                <table class="table table-sm table-hover mb-0 font-monospace" style="font-size:11px;">
                    <thead class="table-light">
                        <tr><th>Broker</th><th>Inv</th><th class="text-end">Net(M)</th><th class="text-end">HAKA%</th><th>Class</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($buyers as $b)
                            @php [$bClass,$bCls] = $buyerClass($b); @endphp
                            <tr>
                                <td>{{ $b['code'] }}</td>
                                <td>@php [$it,$itCls] = $invTag($b['inv']); @endphp<span class="badge {{ $itCls }} rounded-pill" style="font-size:9px;">{{ $it }}</span></td>
                                <td class="text-end {{ $b['net'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $b['net'] >= 0 ? '+' : '' }}{{ $b['net'] }}</td>
                                <td class="text-end">{{ $b['pct'] }}</td>
                                <td><span class="badge {{ $bCls }}" style="font-size:9px;">{{ $bClass }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <div class="text-danger fw-bold mb-1" style="font-size:11px;">TOP SELLERS</div>
                <table class="table table-sm table-hover mb-0 font-monospace" style="font-size:11px;">
                    <thead class="table-light">
                        <tr><th>Broker</th><th>Inv</th><th class="text-end">Net(M)</th><th class="text-end">HAKI%</th><th>Class</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($sellers as $s)
                            @php [$sClass,$sCls] = $sellerClass($s); @endphp
                            <tr>
                                <td>{{ $s['code'] }}</td>
                                <td>@php [$it,$itCls] = $invTag($s['inv']); @endphp<span class="badge {{ $itCls }} rounded-pill" style="font-size:9px;">{{ $it }}</span></td>
                                <td class="text-end {{ $s['net'] < 0 ? 'text-danger' : 'text-success' }}">{{ $s['net'] >= 0 ? '+' : '' }}{{ $s['net'] }}</td>
                                <td class="text-end">{{ $s['pct'] }}</td>
                                <td><span class="badge {{ $sCls }}" style="font-size:9px;">{{ $sClass }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Retail broker behavioral analysis (Impostor Radar) --}}
        <div class="fw-bold mb-1" style="font-size:11px;">Retail Broker Behavioral Analysis (Impostor Radar)</div>
        <div style="max-height:280px;overflow:auto;">
            <table class="table table-sm table-hover mb-0 align-middle font-monospace" style="font-size:11px;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Broker</th><th class="text-end">Tx</th><th class="text-end">Vol</th>
                        <th class="text-end">Avg Lot/Trade</th><th>HAKA vs HAKI</th><th>Score</th><th>Diagnosis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($retail as $r)
                        <tr class="{{ $r['score'] > $impostorThreshold ? 'table-warning' : '' }}">
                            <td>
                                <x-broker-badge :code="$r['code']" class="me-1" />
                                <span class="small text-muted">{{ $r['name'] }}</span>
                            </td>
                            <td class="text-end">{{ number_format($r['tx']) }}</td>
                            <td class="text-end">{{ number_format($r['vol']) }}</td>
                            <td class="text-end">
                                {{ number_format($r['avgLot']) }}
                                @if ($r['avgLot'] > 500)<span title="Above normal retail avg (>500)">🔥</span>@endif
                            </td>
                            <td style="min-width:120px;">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-success" style="width:{{ min($r['haka'],100) }}%" title="HAKA {{ $r['haka'] }}%"></div>
                                </div>
                                <div class="progress mt-1" style="height:6px;">
                                    <div class="progress-bar bg-danger" style="width:{{ min($r['haki'],100) }}%" title="HAKI {{ $r['haki'] }}%"></div>
                                </div>
                            </td>
                            <td><span class="badge {{ $scoreClass($r['score']) }}" style="font-size:10px;">{{ $r['score'] }}</span></td>
                            <td class="small">
                                @if ($r['score'] > $impostorThreshold)🕵️ {{ $r['diagnosis'] }}
                                @else {{ $r['diagnosis'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

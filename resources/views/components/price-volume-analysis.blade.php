@php
    // PVA status -> bootstrap subtle badge
    $statusBadge = function ($status) {
        return match ($status) {
            'Accumulation' => 'bg-success-subtle text-success',
            'Weak Rally'   => 'bg-warning-subtle text-warning',
            'Retest'       => 'bg-info-subtle text-info',
            'Distribution' => 'bg-danger-subtle text-danger',
            default        => 'bg-secondary-subtle text-secondary',
        };
    };
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Price Volume Analysis (PVA)</h5>
        <div class="d-flex gap-2 align-items-center">
            <input type="date" class="form-control form-control-sm" style="width:140px;" value="{{ $start }}">
            <input type="date" class="form-control form-control-sm" style="width:140px;" value="{{ $end }}">
            <button class="btn btn-sm btn-outline-secondary" type="button">Apply</button>
        </div>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">
        {{-- Summary banner --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 rounded border
            {{ $score >= 60 ? 'bg-success-subtle border-success-subtle text-success' : ($score >= 40 ? 'bg-warning-subtle border-warning-subtle text-warning' : 'bg-danger-subtle border-danger-subtle text-danger') }}">
            <div>
                <div class="fw-bold" style="font-size:11px;">ACCUMULATION / DISTRIBUTION SCORE</div>
                <div class="fw-bold" style="font-size:20px;">{{ $score }}%</div>
            </div>
            <span class="badge {{ $score >= 60 ? 'bg-success' : ($score >= 40 ? 'bg-warning' : 'bg-danger') }} rounded-pill">
                {{ $score >= 60 ? 'Healthy Accumulation' : ($score >= 40 ? 'Mixed / Weak Rally' : 'Heavy Distribution') }}
            </span>
        </div>
        <div class="small text-muted mb-3">{{ $summary }}</div>
        {{-- 4 stat cards --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center h-100">
                    <div class="fw-bold text-success" style="font-size:16px;">{{ $stats['accumulation'] }}</div>
                    <div class="small text-muted">Healthy Accumulation</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center h-100">
                    <div class="fw-bold text-warning" style="font-size:16px;">{{ $stats['weakRally'] }}</div>
                    <div class="small text-muted">Weak Rally</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center h-100">
                    <div class="fw-bold text-info" style="font-size:16px;">{{ $stats['retest'] }}</div>
                    <div class="small text-muted">Healthy Retest</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center h-100">
                    <div class="fw-bold text-danger" style="font-size:16px;">{{ $stats['distribution'] }}</div>
                    <div class="small text-muted">Heavy Distribution</div>
                </div>
            </div>
        </div>
        {{-- Daily breakdown --}}
        <div class="flex-grow-1" style="overflow:auto;">
            <table class="table table-sm table-hover mb-0 align-middle font-monospace" style="font-size:11px;">
                <thead class="table-light sticky-top">
                    <tr><th>Date</th><th class="text-end">Chg%</th><th class="text-end">Vol/MA20</th><th>Status</th><th>Tag</th></tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td>{{ $r['date'] }}</td>
                            <td class="text-end {{ $r['change'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $r['change'] >= 0 ? '+' : '' }}{{ $r['change'] }}%</td>
                            <td class="text-end">{{ $r['volRatio'] }}x</td>
                            <td><span class="badge {{ $statusBadge($r['status']) }}">{{ $r['status'] }}</span></td>
                            <td class="small text-muted">{{ $r['tag'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

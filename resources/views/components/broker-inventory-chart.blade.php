@php
    // ponytail: data = $inventory from StockAnalysisController::mockInventory.
    $inv = $inventory;
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-1 text-secondary"></i> Broker Inventory &amp; Price Action Chart</h5>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">
        {{-- Toolbar --}}
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-0">Start</label>
                <input type="date" class="form-control form-control-sm font-monospace" value="{{ $inv['dates'][0] }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-0">End</label>
                <input type="date" class="form-control form-control-sm font-monospace" value="{{ end($inv['dates']) }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-0">Mode</label>
                <select class="form-select form-select-sm">
                    <option>Net Bandar Line</option><option>Multi-Broker</option><option>Foreign Flow</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-0">Unit</label>
                <select class="form-select form-select-sm">
                    <option>Lots</option><option>IDR Value</option>
                </select>
            </div>
        </div>

        {{-- Charts --}}
        <div class="border rounded mb-2" style="background:var(--bs-tertiary-bg);">
            <div class="px-2 pt-1 small text-muted font-monospace">Price Action</div>
            <div id="inv-price" style="width:100%;"></div>
        </div>
        <div class="border rounded mb-2" style="background:var(--bs-tertiary-bg);">
            <div class="px-2 pt-1 small text-muted font-monospace">Cumulative Broker Inventory (0 = zero base line)</div>
            <div id="inv-inventory" style="width:100%;"></div>
        </div>

        {{-- Legend --}}
        <div class="d-flex flex-wrap gap-2 mb-2">
            @foreach ($inv['topAccum'] as $b)
                <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1" style="font-size:10px;">
                    <input type="checkbox" class="form-check-input mt-0" data-inv-toggle="{{ $b['code'] }}" checked>
                    {{ $b['code'] }} <span class="font-monospace">{{ number_format($b['end']) }}</span>
                </span>
            @endforeach
            @foreach ($inv['topDist'] as $b)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-inline-flex align-items-center gap-1" style="font-size:10px;">
                    <input type="checkbox" class="form-check-input mt-0" data-inv-toggle="{{ $b['code'] }}" checked>
                    {{ $b['code'] }} <span class="font-monospace">{{ number_format($b['end']) }}</span>
                </span>
            @endforeach
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-inline-flex align-items-center gap-1" style="font-size:10px;">
                <input type="checkbox" class="form-check-input mt-0" data-inv-toggle="__NET__" checked>
                Net Bandar <span class="font-monospace">{{ number_format($inv['netBandar']) }}</span>
            </span>
        </div>

        {{-- AI Summary --}}
        <div class="border rounded p-2 mt-auto" style="background:linear-gradient(180deg,var(--bs-tertiary-bg),transparent);">
            <div class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-robot me-1 text-secondary"></i> AI Bandarmology Summary</div>
            <p class="small text-muted mb-2">{{ $inv['summary'] }}</p>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;">Phase: {{ $inv['phase'] }}</span>
                <span class="badge bg-success-subtle text-success" style="font-size:10px;">Accum: {{ $inv['topAccum'][0]['code'] ?? '-' }}</span>
                <span class="badge bg-danger-subtle text-danger" style="font-size:10px;">Dist: {{ $inv['topDist'][0]['code'] ?? '-' }}</span>
                <span class="badge {{ $inv['score'] >= 60 ? 'bg-success' : ($inv['score'] <= 40 ? 'bg-danger' : 'bg-warning text-dark') }}" style="font-size:10px;">Action Score: {{ $inv['score'] }}</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.__INV__ = @json($inv);
</script>
@vite('resources/js/broker-inventory.js')
@endpush

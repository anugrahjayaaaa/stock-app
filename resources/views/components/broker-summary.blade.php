{{-- Broker Summary: header stats, filter bar, HAKA/HAKI net bar, merged buyer/seller scroll table. --}}
{{-- Props: ticker, avg, vol, val, accum, buyPct, sellPct, buyers[], sellers[], tradeDay. --}}
<div class="card card-outline card-secondary h-100">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-building me-1 text-secondary"></i> Broker Summary
            <small class="text-muted fw-normal">Broksum</small>
        </h5>
        <div class="w-100">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <span class="text-muted text-uppercase fw-semibold"
                        style="font-size: 10px; letter-spacing: 0.3px;">Avg</span>
                    <span class="fw-bold ms-1" style="font-size: 12px;">{{ $avg }}</span>
                </div>
                <div class="border-end" style="height: 16px;"></div>
                <div>
                    <span class="text-muted text-uppercase fw-semibold"
                        style="font-size: 10px; letter-spacing: 0.3px;">Vol</span>
                    <span class="fw-bold ms-1" style="font-size: 12px;">{{ $vol }}</span>
                </div>
                <div class="border-end" style="height: 16px;"></div>
                <div>
                    <span class="text-muted text-uppercase fw-semibold"
                        style="font-size: 10px; letter-spacing: 0.3px;">Val</span>
                    <span class="fw-bold ms-1" style="font-size: 12px;">{{ $val }}</span>
                </div>
            </div>
        </div>
        <div class="w-100 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 mt-1 border-top">
            <span
                class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded fw-bold"
                style="font-size: 10px;">
                {{ $accum }}
            </span>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="bgmodeSwitch"
                    checked style="cursor: pointer;">
                <label class="form-check-label small fw-semibold text-secondary" for="bgmodeSwitch"
                    style="cursor: pointer; user-select: none;">Net</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter bar --}}
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">Start</label>
                <input type="date" class="form-control form-control-sm" value="{{ $tradeDay }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">End</label>
                <input type="date" class="form-control form-control-sm" value="{{ $tradeDay }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">Investor</label>
                <select class="form-select form-select-sm">
                    <option>All Investor</option><option>Domestic (D)</option><option>Foreign (F)</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">Market</label>
                <select class="form-select form-select-sm">
                    <option>All Market</option><option>Regular (RG)</option><option>Negotiated (NG)</option><option>Cash (TN)</option>
                </select>
            </div>
        </div>
        {{-- HAKA/HAKI net bar (Net mode only) --}}
        <div id="bgHakaHaki" class="mb-3 px-1">
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.75rem;">
                <span class="fw-semibold text-success d-flex align-items-center gap-1">Buy {{ $buyPct }}%</span>
                <span class="fw-semibold text-danger d-flex align-items-center gap-1">Sell {{ $sellPct }}%</span>
            </div>
            <div class="progress bg-secondary-subtle p-0.5" style="height:8px;border-radius:999px;overflow:hidden;gap:2px;">
                <div class="progress-bar bg-success" style="width:{{ $buyPct }}%;border-radius:999px 0 0 999px;transition:width 0.6s cubic-bezier(0.4,0,0.2,1);"></div>
                <div class="progress-bar bg-danger" style="width:{{ $sellPct }}%;border-radius:0 999px 999px 0;transition:width 0.6s cubic-bezier(0.4,0,0.2,1);"></div>
            </div>
        </div>
        <div>
            <div class="d-flex justify-content-between fw-bold mb-1" style="font-size:11px;">
                <span class="text-success">TOP BUYER (ACCUMULATOR)</span>
                <span class="text-danger">TOP SELLER (DISTRIBUTOR)</span>
            </div>
            <div style="max-height:240px;overflow:auto;">
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size:11px;font-family:monospace;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width:1%;white-space:nowrap;">Buy Broker</th><th class="text-center">Vol</th><th class="text-center">Val(M)</th><th class="text-center">Avg</th>
                            <th style="width:1%;white-space:nowrap;">Sell Broker</th><th class="text-center">Vol</th><th class="text-center">Val(M)</th><th class="text-center">Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < max(count($buyers), count($sellers)); $i++)
                            <tr>
                                @if (isset($buyers[$i]))
                                    <td class="text-center" style="width:1%;white-space:nowrap;"><x-broker-badge :code="$buyers[$i]['code']" /></td>
                                    <td class="text-center">{{ $buyers[$i]['vol'] }}</td>
                                    <td class="text-center">{{ $buyers[$i]['val'] }}</td>
                                    <td class="text-center">{{ $buyers[$i]['avg'] }}</td>
                                @else
                                    <td colspan="4"></td>
                                @endif
                                @if (isset($sellers[$i]))
                                    <td class="text-center" style="width:1%;white-space:nowrap;"><x-broker-badge :code="$sellers[$i]['code']" /></td>
                                    <td class="text-center">{{ $sellers[$i]['vol'] }}</td>
                                    <td class="text-center">{{ $sellers[$i]['val'] }}</td>
                                    <td class="text-center">{{ $sellers[$i]['avg'] }}</td>
                                @else
                                    <td colspan="4"></td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

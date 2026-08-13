{{-- Broker Summary widget (Stockbit market-detector style).
     Props: ticker, from, to, txType, board, investor, stocks[], buyers[], sellers[], bandar[], total[], error, ipottRoute --}}
<div class="card card-outline card-secondary h-100">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-balance-scale me-1 text-secondary"></i> Broker Summary
            <small class="text-muted fw-normal">Stockbit</small>
        </h5>
        <div class="w-100 d-flex align-items-center gap-3 flex-wrap pt-2 mt-1 border-top">
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size:10px;letter-spacing:0.3px;">Avg</span>
                <span class="fw-bold ms-1" style="font-size:12px;">{{ number_format($total['avgPrice'] ?? 0, 2) }}</span>
            </div>
            <div class="border-end" style="height:16px;"></div>
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size:10px;letter-spacing:0.3px;">Val</span>
                <span class="fw-bold ms-1" style="font-size:12px;">{{ $total['valueRaw'] ?? '-' }}</span>
            </div>
            <div class="border-end" style="height:16px;"></div>
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size:10px;letter-spacing:0.3px;">Lot</span>
                <span class="fw-bold ms-1" style="font-size:12px;">{{ $total['volumeRaw'] ?? '-' }}</span>
            </div>
            <div class="border-end" style="height:16px;"></div>
            <div>
                <span class="badge bg-{{ ($total['accdist'] ?? '') === 'Acc' ? 'danger' : 'success' }}-subtle text-{{ ($total['accdist'] ?? '') === 'Acc' ? 'danger' : 'success' }} border px-2 py-0.5 rounded fw-bold" style="font-size:10px;">
                    {{ $total['accdist'] ?? '-' }}
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter bar --}}
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-2">
                <label class="form-label mb-0 small text-muted">From</label>
                <input type="date" id="bs-from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0 small text-muted">To</label>
                <input type="date" id="bs-to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0 small text-muted">Tx Type</label>
                <select id="bs-txtype" class="form-select form-select-sm">
                    <option value="TRANSACTION_TYPE_NET" @selected($txType === 'TRANSACTION_TYPE_NET')>Net</option>
                    <option value="TRANSACTION_TYPE_GROSS" @selected($txType === 'TRANSACTION_TYPE_GROSS')>Gross</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">Market</label>
                <select id="bs-board" class="form-select form-select-sm">
                    <option value="MARKET_BOARD_REGULER" @selected($board === 'MARKET_BOARD_REGULER')>Regular</option>
                    <option value="MARKET_BOARD_TUNAI" @selected($board === 'MARKET_BOARD_TUNAI')>Tunai</option>
                    <option value="MARKET_BOARD_ALL" @selected($board === 'MARKET_BOARD_ALL')>All</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-0 small text-muted">Investor</label>
                <select id="bs-investor" class="form-select form-select-sm">
                    <option value="INVESTOR_TYPE_ALL" @selected($investor === 'INVESTOR_TYPE_ALL')>All</option>
                    <option value="INVESTOR_TYPE_FOREIGN" @selected($investor === 'INVESTOR_TYPE_FOREIGN')>Foreign</option>
                    <option value="INVESTOR_TYPE_DOMESTIC" @selected($investor === 'INVESTOR_TYPE_DOMESTIC')>Domestic</option>
                </select>
            </div>
        </div>

        {{-- Search + trigger --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            @isset($stocks)
            <input list="bs-stocks" id="bs-search" class="form-control form-control-sm" style="max-width:160px;"
                value="{{ $ticker }}" placeholder="Cari saham…">
            <datalist id="bs-stocks">
                @foreach ($stocks as $s)
                    <option value="{{ $s->code }}">{{ $s->name }}</option>
                @endforeach
            </datalist>
            @endisset
            <button id="bs-ipott" class="btn btn-sm btn-outline-secondary ms-auto" data-route="{{ $ipottRoute }}">Ambil dari Stockbit</button>
        </div>

        @if ($error ?? false)
            <div class="alert alert-danger py-2 small mb-2">Gagal mengambil data Stockbit (HTTP {{ $error }}). Token mungkin expired.</div>
        @endif

        {{-- Bandar Detector panel --}}
        @if (!empty($bandar))
        <div class="mb-3 p-2 rounded bg-light" style="font-size:11px;">
            <div class="fw-bold mb-1 text-secondary">BANDAR DETECTOR</div>
            <div class="row g-1">
                <div class="col-6 col-md-3">Avg Price: <b>{{ number_format($bandar['average'] ?? 0, 2) }}</b></div>
                <div class="col-6 col-md-3">Buyers: <b>{{ $bandar['total_buyer'] ?? 0 }}</b></div>
                <div class="col-6 col-md-3">Sellers: <b>{{ $bandar['total_seller'] ?? 0 }}</b></div>
                <div class="col-6 col-md-3">Accdist: <b>{{ $bandar['broker_accdist'] ?? '-' }}</b></div>
            </div>
            <div class="row g-1 mt-1">
                @foreach (['top1'=>'Top 1','top3'=>'Top 3','top5'=>'Top 5','top10'=>'Top 10'] as $k => $label)
                    <div class="col-6 col-md-3">
                        {{ $label }}: <b>{{ $bandar[$k]['accdist'] ?? '-' }}</b>
                        <span class="text-muted">({{ $bandar[$k]['percent'] ?? 0 }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <div class="d-flex justify-content-between fw-bold mb-1" style="font-size:11px;">
                <span class="text-success">TOP BUYER (ACCUMULATOR)</span>
                <span class="text-danger">TOP SELLER (DISTRIBUTOR)</span>
            </div>
            <div style="max-height:260px;overflow:auto;">
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size:11px;font-family:monospace;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Buy</th><th>Type</th><th class="text-center">Lot</th><th class="text-center">Val</th><th class="text-center">Avg</th><th class="text-center">Freq</th>
                            <th>Sell</th><th>Type</th><th class="text-center">Lot</th><th class="text-center">Val</th><th class="text-center">Avg</th><th class="text-center">Freq</th>
                        </tr>
                    </thead>
                    <tbody id="bs-rows">
                        @include('components.broker-summary-rows', ['buyers' => $buyers, 'sellers' => $sellers])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (!empty($ipottRoute))
    <script>
    (function () {
        var btn = document.getElementById('bs-ipott');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var code = (document.getElementById('bs-search').value || '').trim().toUpperCase();
            if (!code) { alert('Ketik kode saham di kolom pencarian.'); return; }
            var from = document.getElementById('bs-from').value || btn.dataset.from;
            var to = document.getElementById('bs-to').value || btn.dataset.to;
            var txType = document.getElementById('bs-txtype').value;
            var board = document.getElementById('bs-board').value;
            var investor = document.getElementById('bs-investor').value;
            var url = btn.dataset.route.replace('PLACEHOLDER', code)
                + '?from=' + encodeURIComponent(from)
                + '&to=' + encodeURIComponent(to)
                + '&txType=' + encodeURIComponent(txType)
                + '&board=' + encodeURIComponent(board)
                + '&investor=' + encodeURIComponent(investor);
            btn.disabled = true; btn.textContent = 'Memuat…';
            fetch(url).then(function (r) { return r.json(); }).then(function (d) {
                if (d.error) {
                    document.getElementById('bs-rows').innerHTML = '<tr><td colspan="12" class="text-center text-danger py-3">Gagal (HTTP ' + d.error + '). Token expired?</td></tr>';
                    return;
                }
                var html = '';
                var n = Math.max((d.buyers || []).length, (d.sellers || []).length);
                for (var i = 0; i < n; i++) {
                    var b = d.buyers[i], s = d.sellers[i];
                    html += '<tr>';
                    html += b ? '<td>' + b.code + '</td><td>' + b.type + '</td><td class="text-center">' + b.volRaw + '</td><td class="text-center">' + b.valRaw + '</td><td class="text-center">' + b.avgRaw + '</td><td class="text-center">' + b.freq + '</td>' : '<td colspan="6"></td>';
                    html += s ? '<td>' + s.code + '</td><td>' + s.type + '</td><td class="text-center">' + s.volRaw + '</td><td class="text-center">' + s.valRaw + '</td><td class="text-center">' + s.avgRaw + '</td><td class="text-center">' + s.freq + '</td>' : '<td colspan="6"></td>';
                    html += '</tr>';
                }
                if (!n) html = '<tr><td colspan="12" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>';
                document.getElementById('bs-rows').innerHTML = html;
            }).catch(function () {
                document.getElementById('bs-rows').innerHTML = '<tr><td colspan="12" class="text-center text-danger py-3">Gagal mengambil data Stockbit.</td></tr>';
            }).finally(function () {
                btn.disabled = false; btn.textContent = 'Ambil dari Stockbit';
            });
        });
    })();
    </script>
    @endif
</div>

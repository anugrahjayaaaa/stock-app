{{-- Broker Summary widget (Stockbit market-detector style).
     Props: ticker, from, to, txType, board, investor, stocks[], buyers[], sellers[], bandar[], total[], error, triggerRoute --}}
<div class="card card-outline card-secondary h-100">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-balance-scale me-1 text-secondary"></i> Broker Summary
            <small class="text-muted fw-normal">Stockbit</small>
        </h5>
    </div>
    <div class="card-body">
        {{-- Filter bar: Stockbit-style single row (ticker | date | investor | market | txType) --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            @isset($stocks)
            <input list="bs-stocks" id="bs-search" class="form-control form-control-sm" style="max-width:130px;text-transform:uppercase;"
                value="{{ $ticker }}" placeholder="Kode…">
            <datalist id="bs-stocks">
                @foreach ($stocks as $s)
                    <option value="{{ $s->code }}">{{ $s->name }}</option>
                @endforeach
            </datalist>
            @endisset
            <div class="d-flex align-items-center gap-1">
                <input type="date" id="bs-from" class="form-control form-control-sm" style="max-width:140px;" value="{{ $from }}">
                <span class="text-muted">→</span>
                <input type="date" id="bs-to" class="form-control form-control-sm" style="max-width:140px;" value="{{ $to }}">
            </div>
            <select id="bs-investor" class="form-select form-select-sm" style="max-width:130px;">
                <option value="INVESTOR_TYPE_ALL" @selected($investor === 'INVESTOR_TYPE_ALL')>All Investor</option>
                <option value="INVESTOR_TYPE_FOREIGN" @selected($investor === 'INVESTOR_TYPE_FOREIGN')>Foreign</option>
                <option value="INVESTOR_TYPE_DOMESTIC" @selected($investor === 'INVESTOR_TYPE_DOMESTIC')>Domestic</option>
            </select>
            <select id="bs-board" class="form-select form-select-sm" style="max-width:120px;">
                <option value="MARKET_BOARD_ALL" @selected($board === 'MARKET_BOARD_ALL')>All</option>
                <option value="MARKET_BOARD_REGULER" @selected($board === 'MARKET_BOARD_REGULER')>Regular</option>
                <option value="MARKET_BOARD_TUNAI" @selected($board === 'MARKET_BOARD_TUNAI')>Tunai</option>
                <option value="MARKET_BOARD_NEGO" @selected($board === 'MARKET_BOARD_NEGO')>Nego</option>
            </select>
            <select id="bs-txtype" class="form-select form-select-sm" style="max-width:100px;">
                <option value="TRANSACTION_TYPE_NET" @selected($txType === 'TRANSACTION_TYPE_NET')>Net</option>
                <option value="TRANSACTION_TYPE_GROSS" @selected($txType === 'TRANSACTION_TYPE_GROSS')>Gross</option>
            </select>
            <button id="bs-trigger" class="btn btn-sm btn-outline-secondary ms-auto" data-route="{{ $triggerRoute }}">Ambil</button>
        </div>

        @if ($message ?? false)
            <div class="alert alert-info py-2 small mb-2">{{ $message }}</div>
        @endif

        {{-- Bandar Detector: Stockbit skeleton = 3 sub-tables (Acc/Dist, Broker summary, Net summary). Net only. --}}
        <div id="bs-bandar" class="mb-3 @if($txType !== 'TRANSACTION_TYPE_NET' || empty($bandar)) d-none @endif">
            @if ($txType === 'TRANSACTION_TYPE_NET' && !empty($bandar))
                @include('components.broker-bandar', ['bandar' => $bandar])
            @endif
        </div>

        <div>
            <div class="d-flex justify-content-between fw-bold mb-1" style="font-size:11px;">
                <span class="text-success"><i class="fas fa-arrow-down me-1"></i>TOP BUYER (ACCUMULATOR)</span>
                <span class="text-danger">TOP SELLER (DISTRIBUTOR)<i class="fas fa-arrow-up ms-1"></i></span>
            </div>
            <div style="max-height:260px;overflow:auto;">
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size:12px;">
                    <thead class="table-light sticky-top">
                        <tr class="text-center">
                            <th class="text-success">Broker</th><th class="text-center">Val</th><th class="text-center">Lot</th><th class="text-center">Avg</th><th class="text-center">Freq</th>
                            <th class="border-start" style="width:1px;"></th>
                            <th class="text-danger border-start">Broker</th><th class="text-center">Val</th><th class="text-center">Lot</th><th class="text-center">Avg</th><th class="text-center">Freq</th>
                        </tr>
                    </thead>
                    <tbody id="bs-rows">
                        @include('components.broker-summary-rows', ['buyers' => $buyers, 'sellers' => $sellers])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (!empty($triggerRoute))
    <script>
    (function () {
        var btn = document.getElementById('bs-trigger');
        if (!btn) return;
        var bandarEl = document.getElementById('bs-bandar');
        var txtypeEl = document.getElementById('bs-txtype');

        // Net toggle drives the Bandar Detector panel (AdminLTE d-none).
        function syncBandar(d) {
            if (!bandarEl) return;
            if (txtypeEl && txtypeEl.value === 'TRANSACTION_TYPE_GROSS') {
                bandarEl.classList.add('d-none');
                return;
            }
            if (d && d.bandar && d.bandar.average !== undefined) {
                var b = d.bandar;
                function ad(v) {
                    var s = (v || '').toLowerCase();
                    if (s.indexOf('dist') > -1) return 'bg-danger-subtle text-danger';
                    if (s.indexOf('acc') > -1) return 'bg-success-subtle text-success';
                    return 'bg-secondary-subtle text-secondary';
                }
                function row(label, t) {
                    t = t || {};
                    return '<tr><td class="fw-bold">' + label + '</td>' +
                        '<td class="text-end">' + Number(Math.abs(t.vol || 0)).toLocaleString('en-US') + '</td>' +
                        '<td class="text-end">' + Number((t.percent || 0)).toFixed(1) + '</td>' +
                        '<td class="text-end">' + (Number(t.amount || 0) / 1e9).toFixed(1) + '</td>' +
                        '<td class="text-center"><span class="badge ' + ad(t.accdist) + '">' + (t.accdist || '-') + '</span></td></tr>';
                }
                var rows = row('Top 1', b.top1) + row('Top 3', b.top3) + row('Top 5', b.top5) +
                    row('Top 10', b.top10) + row('Average', b.avg);
                var diff = (b.total_buyer || 0) - (b.total_seller || 0);
                bandarEl.innerHTML =
                    '<div class="small">' +
                    '<table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;">' +
                        '<thead class="text-muted"><tr><th>Top</th><th class="text-end">Volume</th><th class="text-end">%</th><th class="text-end">Rp(B)</th><th class="text-center">Acc/Dist</th></tr></thead>' +
                        '<tbody>' + rows + '</tbody></table>' +
                    '<div class="d-flex flex-wrap gap-3">' +
                        '<table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;min-width:240px;">' +
                            '<thead class="text-muted"><tr><th>Broker</th><th class="text-end">Buyer</th><th class="text-end">Seller</th><th class="text-center">#</th><th class="text-center">Acc/Dist</th></tr></thead>' +
                            '<tbody><tr><td class="fw-bold">Total</td><td class="text-end">' + (b.total_buyer || 0) + '</td><td class="text-end">' + (b.total_seller || 0) + '</td><td class="text-center">' + diff + '</td><td class="text-center"><span class="badge ' + ad(b.broker_accdist) + '">' + (b.broker_accdist || '-') + '</span></td></tr></tbody>' +
                        '</table>' +
                        '<table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;min-width:200px;">' +
                            '<tbody>' +
                                '<tr><td class="text-muted">Net Volume</td><td class="text-end fw-bold">' + Number(b.volume || 0).toLocaleString('en-US') + '</td></tr>' +
                                '<tr><td class="text-muted">Net Value</td><td class="text-end fw-bold">' + (Number(b.value || 0) / 1e9).toFixed(1) + 'B</td></tr>' +
                                '<tr><td class="text-muted">Average (Rp)</td><td class="text-end fw-bold">' + Number(b.average || 0).toFixed(2) + '</td></tr>' +
                            '</tbody>' +
                        '</table>' +
                    '</div></div>';
                bandarEl.classList.remove('d-none');
            } else {
                bandarEl.classList.add('d-none');
            }
        }
        if (txtypeEl) txtypeEl.addEventListener('change', function () { syncBandar(null); });

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
                    document.getElementById('bs-rows').innerHTML = '<tr><td colspan="11" class="text-center text-danger py-3">Gagal (HTTP ' + d.error + '). Token expired?</td></tr>';
                    return;
                }
                var html = '';
                var n = Math.max((d.buyers || []).length, (d.sellers || []).length);
                for (var i = 0; i < n; i++) {
                    var b = d.buyers[i], s = d.sellers[i];
                    html += '<tr>';
                    if (b) {
                        html += '<td class="text-center" style="width:1%;white-space:nowrap;"><span class="badge ' + b.cat + '">' + b.code + '</span></td>'
                            + '<td class="text-center">' + b.valRaw + '</td>'
                            + '<td class="text-center">' + b.volRaw + '</td>'
                            + '<td class="text-center">' + b.avgRaw + '</td>'
                            + '<td class="text-center text-muted">' + b.freq + '</td>';
                    } else { html += '<td colspan="5"></td>'; }
                    html += '<td class="border-start"></td>';
                    if (s) {
                        html += '<td class="text-center" style="width:1%;white-space:nowrap;"><span class="badge ' + s.cat + '">' + s.code + '</span></td>'
                            + '<td class="text-center">' + s.valRaw + '</td>'
                            + '<td class="text-center">' + s.volRaw + '</td>'
                            + '<td class="text-center">' + s.avgRaw + '</td>'
                            + '<td class="text-center text-muted">' + s.freq + '</td>';
                    } else { html += '<td colspan="5"></td>'; }
                    html += '</tr>';
                }
                if (!n) html = '<tr><td colspan="11" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>';
                document.getElementById('bs-rows').innerHTML = html;
                syncBandar(d);
            }).catch(function () {
                document.getElementById('bs-rows').innerHTML = '<tr><td colspan="11" class="text-center text-danger py-3">Gagal mengambil data Stockbit.</td></tr>';
            }).finally(function () {
                btn.disabled = false; btn.textContent = 'Ambil dari Stockbit';
            });
        });
    })();
    </script>
    @endif
</div>

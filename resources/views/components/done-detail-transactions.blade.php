@php
    $brokerClass = function ($code) {
        $asing = ['AK','BK','KZ','RX','CS','ZP'];
        $insti = ['CC','PZ','NI'];
        if (in_array($code, $asing, true)) return 'bg-danger';
        if (in_array($code, $insti, true)) return 'bg-success';
        return 'bg-purple';
    };
@endphp
<div class="card card-outline card-secondary h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Done Detail Transactions</h5>
        <div class="text-end" style="font-size:11px;font-family:monospace;">
            <span class="fw-bold">{{ $ticker }}</span>
            &nbsp;{{ $price }}
            &nbsp;<span class="text-danger">{{ $change }}</span>
            &nbsp;Vol <span class="fw-bold">{{ $vol }}</span>
        </div>
    </div>
    <div class="card-body d-flex flex-column flex-grow-1">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <select class="form-select form-select-sm" style="width:95px;">
                <option>All Mkt</option><option>RG</option><option>TN</option><option>NG</option>
            </select>
            <select class="form-select form-select-sm" style="width:85px;">
                <option>All Inv</option><option>F</option><option>D</option>
            </select>
            <select class="form-select form-select-sm" style="width:90px;" title="Buyer broker">
                <option>All B</option><option>YP</option><option>CC</option><option>NI</option><option>AZ</option><option>PD</option><option>NH</option><option>AK</option><option>BK</option><option>KZ</option><option>RX</option>
            </select>
            <select class="form-select form-select-sm" style="width:90px;" title="Seller broker">
                <option>All S</option><option>YP</option><option>CC</option><option>NI</option><option>AZ</option><option>PD</option><option>NH</option><option>AK</option><option>BK</option><option>KZ</option><option>RX</option>
            </select>
            <div class="ms-auto form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="groupToggle" style="cursor:pointer;">
                <label class="form-check-label" for="groupToggle" style="font-size:11px;cursor:pointer;">Group Order</label>
            </div>
        </div>
        <div class="flex-grow-1" style="overflow:auto;">
            <table class="table table-sm table-hover mb-0 align-middle" style="font-size:11px;font-family:monospace;">
                <thead class="table-light sticky-top">
                    <tr><th>Time</th><th>Action</th><th>B</th><th>S</th><th>Mkt</th><th>Inv</th><th class="text-end">Price</th><th class="text-end">Vol</th></tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr class="{{ $r['grouped'] ? 'bg-success-subtle' : '' }}" data-grouped="{{ $r['grouped'] ? '1' : '0' }}">
                            <td>{{ $r['time'] }}</td>
                            <td><span class="{{ $r['action'] === 'BUY' ? 'text-success' : 'text-danger' }} fw-bold">{{ $r['action'] }}</span></td>
                            <td><span class="badge {{ $brokerClass($r['buyer']) }}">{{ $r['buyer'] }}</span></td>
                            <td><span class="badge {{ $brokerClass($r['seller']) }}">{{ $r['seller'] }}</span></td>
                            <td>{{ $r['market'] }}</td>
                            <td>{{ $r['inv'] }}</td>
                            <td class="text-end {{ $r['action'] === 'BUY' ? 'text-success' : 'text-danger' }} fw-bold">{{ $r['price'] }}</td>
                            <td class="text-end">{{ $r['vol'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bandar Detector — Stockbit skeleton: 3 sub-tables. --}}
{{-- Props: bandar (array from StockbitParser::bandar). --}}
<div class="small">
    {{-- 1) Top1/3/5/10 + Average — Acc/Dist column --}}
    <table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;">
        <thead class="text-muted">
            <tr><th>Top</th><th class="text-end">Volume</th><th class="text-end">%</th><th class="text-end">Rp(B)</th><th class="text-center">Acc/Dist</th></tr>
        </thead>
        <tbody>
            @foreach (['top1'=>'Top 1','top3'=>'Top 3','top5'=>'Top 5','top10'=>'Top 10','avg'=>'Average'] as $k => $label)
                @php $t = $bandar[$k] ?? []; @endphp
                <tr>
                    <td class="fw-bold">{{ $label }}</td>
                    <td class="text-end">{{ number_format(abs($t['vol'] ?? 0)) }}</td>
                    <td class="text-end">{{ number_format($t['percent'] ?? 0, 1) }}</td>
                    <td class="text-end">{{ number_format(($t['amount'] ?? 0) / 1e9, 1) }}</td>
                    <td class="text-center"><span class="badge {{ accdistClass($t['accdist'] ?? '') }}">{{ $t['accdist'] ?? '-' }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex flex-wrap gap-3">
        {{-- 2) Broker summary: buyers / sellers / # / accdist --}}
        <table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;min-width:240px;">
            <thead class="text-muted">
                <tr><th>Broker</th><th class="text-end">Buyer</th><th class="text-end">Seller</th><th class="text-center">#</th><th class="text-center">Acc/Dist</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="text-end">{{ $bandar['total_buyer'] ?? 0 }}</td>
                    <td class="text-end">{{ $bandar['total_seller'] ?? 0 }}</td>
                    <td class="text-center">{{ ($bandar['total_buyer'] ?? 0) - ($bandar['total_seller'] ?? 0) }}</td>
                    <td class="text-center"><span class="badge {{ accdistClass($bandar['broker_accdist'] ?? '') }}">{{ $bandar['broker_accdist'] ?? '-' }}</span></td>
                </tr>
            </tbody>
        </table>

        {{-- 3) Net summary: volume / value / average --}}
        <table class="table table-sm table-borderless mb-2 align-middle" style="font-size:11px;min-width:200px;">
            <tbody>
                <tr><td class="text-muted">Net Volume</td><td class="text-end fw-bold">{{ number_format($bandar['volume'] ?? 0) }}</td></tr>
                <tr><td class="text-muted">Net Value</td><td class="text-end fw-bold">{{ number_format(($bandar['value'] ?? 0) / 1e9, 1) }}B</td></tr>
                <tr><td class="text-muted">Average (Rp)</td><td class="text-end fw-bold">{{ number_format($bandar['average'] ?? 0, 2) }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

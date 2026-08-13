{{-- Bandar Detector — Stockbit skeleton: 3 sub-tables (compact). --}}
{{-- Props: bandar (array from Stockbit NET bandar_detector). --}}
<div class="small" style="font-size:10px;line-height:1.35;">
    <table class="table table-sm table-borderless mb-1 align-middle" style="font-size:10px;">
        <thead class="text-muted"><tr><th class="py-0">Top</th><th class="text-end py-0">Volume</th><th class="text-end py-0">%</th><th class="text-end py-0">Rp(B)</th><th class="text-center py-0">A/D</th></tr></thead>
        <tbody>
            @foreach (['top1'=>'Top 1','top3'=>'Top 3','top5'=>'Top 5','top10'=>'Top 10','avg'=>'Avg'] as $k => $label)
                @php $t = $bandar[$k] ?? []; @endphp
                <tr class="py-0">
                    <td class="py-0 fw-bold">{{ $label }}</td>
                    <td class="py-0 text-end">{{ number_format(abs($t['vol'] ?? 0)) }}</td>
                    <td class="py-0 text-end">{{ number_format($t['percent'] ?? 0, 1) }}</td>
                    <td class="py-0 text-end">{{ number_format(($t['amount'] ?? 0) / 1e9, 1) }}</td>
                    <td class="py-0 text-center"><span class="badge {{ accdistClass($t['accdist'] ?? '') }}">{{ $t['accdist'] ?? '-' }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
        <span class="text-muted">Total:</span>
        <span class="badge bg-secondary-subtle text-secondary">Buy {{ $bandar['total_buyer'] ?? 0 }}</span>
        <span class="badge bg-secondary-subtle text-secondary">Sell {{ $bandar['total_seller'] ?? 0 }}</span>
        <span class="badge {{ accdistClass($bandar['broker_accdist'] ?? '') }}">{{ $bandar['broker_accdist'] ?? '-' }}</span>
    </div>

    <div class="d-flex flex-wrap gap-3 border-top pt-1">
        <span><span class="text-muted">Net Vol: </span><b>{{ number_format($bandar['volume'] ?? 0) }}</b></span>
        <span><span class="text-muted">Net Val: </span><b>{{ number_format(($bandar['value'] ?? 0) / 1e9, 1) }}B</b></span>
        <span><span class="text-muted">Avg: </span><b>{{ number_format($bandar['average'] ?? 0, 2) }}</b></span>
    </div>
</div>

<?php

namespace App\Services\BrokerSummary;

/**
 * Derive Stockbit-style bandar detector from GROSS broker rows
 * (buyers + sellers), so it can be recomputed from DB-stored gross
 * without re-calling Stockbit later.
 *
 * net = buy - sell per broker. Acc = net buyer, Dist = net seller.
 * Output shape matches StockbitParser::bandar() so it feeds <x-broker-bandar>.
 */
class BandarDetector
{
    /**
     * @param array $buyers  parser rows: ['code','lot'(int),'val'(int rupiah),'avg'(float)]
     * @param array $sellers same shape (lot/val are positive magnitudes)
     * @return array{buyers:array,sellers:array,bandar:array,total:array} bandar key only
     */
    public function compute(array $buyers, array $sellers): array
    {
        // merge net per broker code
        $net = [];
        foreach ($buyers as $r) {
            $c = $r['code'];
            $net[$c] = [
                'lot' => ($net[$c]['lot'] ?? 0) + (int) ($r['lot'] ?? 0),
                'val' => ($net[$c]['val'] ?? 0) + (int) ($r['val'] ?? 0),
            ];
        }
        foreach ($sellers as $r) {
            $c = $r['code'];
            $net[$c] = [
                'lot' => ($net[$c]['lot'] ?? 0) - (int) ($r['lot'] ?? 0),
                'val' => ($net[$c]['val'] ?? 0) - (int) ($r['val'] ?? 0),
            ];
        }

        $rows = [];
        foreach ($net as $code => $n) {
            $rows[] = [
                'code' => $code,
                'lot' => $n['lot'],
                'val' => $n['val'],
                'accdist' => $n['lot'] > 0 ? 'Acc' : ($n['lot'] < 0 ? 'Dist' : '-'),
            ];
        }

        $totalLot = array_sum(array_column($rows, 'lot'));
        $totalVal = array_sum(array_column($rows, 'val'));
        $absLot = array_sum(array_map('abs', array_column($rows, 'lot'))) ?: 1;
        $totalBuyer = count(array_filter($rows, fn ($x) => $x['lot'] > 0));
        $totalSeller = count(array_filter($rows, fn ($x) => $x['lot'] < 0));

        // Stockbit bandar volume/value = total BUY side (NET sell fields are negative offsets).
        // ponytail: matches Stockbit NET probe (vol = sum blot buy, val = sum bval buy).
        $buyLot = array_sum(array_column($buyers, 'lot'));
        $buyVal = array_sum(array_column($buyers, 'val'));

        // rank net buyers first for Top1/3/5/10 tiers
        $ranked = $rows;
        usort($ranked, fn ($a, $b) => $b['lot'] <=> $a['lot']);

        $tier = function (array $slice) use ($absLot) {
            $lot = array_sum(array_column($slice, 'lot'));
            $val = array_sum(array_column($slice, 'val'));

            return [
                'accdist' => $lot > 0 ? 'Acc' : ($lot < 0 ? 'Dist' : '-'),
                'amount' => $val,
                'percent' => round($lot / $absLot * 100, 2),
                'vol' => $lot,
            ];
        };

        return [
            'average' => $buyLot ? round($buyVal / ($buyLot * 100), 2) : 0, // avg price/share
            'broker_accdist' => $totalVal > 0 ? 'Acc' : ($totalVal < 0 ? 'Dist' : '-'),
            'total_buyer' => $totalBuyer,
            'total_seller' => $totalSeller,
            'value' => $buyVal,
            'volume' => $buyLot,
            'avg' => $tier($rows),
            'avg5' => $tier(array_slice($ranked, 0, 5)),
            'top1' => $tier(array_slice($ranked, 0, 1)),
            'top3' => $tier(array_slice($ranked, 0, 3)),
            'top5' => $tier(array_slice($ranked, 0, 5)),
            'top10' => $tier(array_slice($ranked, 0, 10)),
        ];
    }

    /** smallest runnable check: volume = buy total, net buyer -> Acc. */
    public static function demo(): void
    {
        $d = new self();
        $b = $d->compute(
            [['code' => 'BDMN', 'lot' => 100, 'val' => 1_000_000, 'avg' => 100]],
            [['code' => 'BDMN', 'lot' => 40, 'val' => 400_000, 'avg' => 100]], // net +60 Acc, volume = buy 100
        );
        assert($b['broker_accdist'] === 'Acc', 'net buyer should be Acc');
        assert($b['volume'] === 100, 'volume = buy total');

        $c = $d->compute(
            [['code' => 'CC', 'lot' => 10, 'val' => 100_000, 'avg' => 100]],
            [['code' => 'CC', 'lot' => 90, 'val' => 900_000, 'avg' => 100]], // net -80 -> Dist
        );
        assert($c['broker_accdist'] === 'Dist', 'net seller should be Dist');
        echo "BandarDetector OK\n";
    }
}

<?php

namespace App\Services\Stockbit;

use App\Models\Broker;

/**
 * Parse Stockbit market-detector JSON into a UI-ready shape.
 *
 * Stockbit already returns NET (transaction_type=NET): brokers_buy = net buyers,
 * brokers_sell = net sellers. Numbers arrive as scientific-notation strings
 * ("2.07965e+08") or plain strings -> cast to float then int.
 *
 * Output per broker row:
 *  ['code','type','lot'(int),'val'(int rupiah),'avg'(float),'freq'(int),
 *   'volRaw','valRaw','avgRaw' (IPOT-style short display)]
 * Plus 'bandar' (accumulation panel) and 'total'.
 */
class StockbitParser
{
    /** @return array{buyers:array,sellers:array,bandar:array,total:array} */
    public function parse(array $data): array
    {
        $bs = $data['broker_summary'] ?? [];
        $buyers = array_map(fn ($r) => $this->row($r, true), $bs['brokers_buy'] ?? []);
        $sellers = array_map(fn ($r) => $this->row($r, false), $bs['brokers_sell'] ?? []);

        $bandar = $this->bandar($data['bandar_detector'] ?? []);

        return [
            'buyers' => $buyers,
            'sellers' => $sellers,
            'bandar' => $bandar,
            'total' => [
                'value' => $bandar['value'],
                'volume' => $bandar['volume'],
                'valueRaw' => formatShort($bandar['value']),
                'volumeRaw' => formatShort($bandar['volume']).' Lot',
                'avgPrice' => $bandar['average'],
                'buyers' => $bandar['total_buyer'],
                'sellers' => $bandar['total_seller'],
                'accdist' => $bandar['broker_accdist'],
            ],
        ];
    }

    private function row(array $r, bool $isBuy): array
    {
        $lot = $this->num($isBuy ? ($r['blot'] ?? '0') : ($r['slot'] ?? '0'));
        $val = $this->num($isBuy ? ($r['bval'] ?? '0') : ($r['sval'] ?? '0'));
        $avg = (float) ($isBuy ? ($r['netbs_buy_avg_price'] ?? '0') : ($r['netbs_sell_avg_price'] ?? '0'));

        // seller side comes as negative net; show absolute magnitudes in the sell column.
        $lot = abs($lot);
        $val = abs($val);

        return [
            'code' => $r['netbs_broker_code'] ?? '',
            'type' => $r['type'] ?? '',
            'lot' => $lot,
            'val' => $val,
            'avg' => round($avg, 2),
            'freq' => (int) ($r['freq'] ?? 0),
            'volRaw' => formatShort($lot),
            'valRaw' => formatShort($val),
            'avgRaw' => number_format($avg, 2),
            'cat' => Broker::categoryClass($r['netbs_broker_code'] ?? ''),
        ];
    }

    private function bandar(array $bd): array
    {
        $acc = fn ($k) => [
            'accdist' => $bd[$k]['accdist'] ?? '-',
            'amount' => $this->num($bd[$k]['amount'] ?? '0'),
            'percent' => round((float) ($bd[$k]['percent'] ?? 0), 2),
            'vol' => $this->num($bd[$k]['vol'] ?? '0'),
        ];

        return [
            'average' => round((float) ($bd['average'] ?? 0), 2),
            'broker_accdist' => $bd['broker_accdist'] ?? '-',
            'total_buyer' => (int) ($bd['total_buyer'] ?? 0),
            'total_seller' => (int) ($bd['total_seller'] ?? 0),
            'value' => $this->num($bd['value'] ?? '0'),
            'volume' => $this->num($bd['volume'] ?? '0'),
            'avg' => $acc('avg'),
            'avg5' => $acc('avg5'),
            'top1' => $acc('top1'),
            'top3' => $acc('top3'),
            'top5' => $acc('top5'),
            'top10' => $acc('top10'),
        ];
    }

    /** Scientific/plain string -> int. Empty -> 0. */
    private function num(string $s): int
    {
        $s = trim($s);
        if ($s === '' || $s === '-') {
            return 0;
        }

        return (int) round((float) $s);
    }
}

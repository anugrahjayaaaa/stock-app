<?php

namespace App\Services\BrokerSummary;

use App\Models\BroksumRow;

/**
 * Broker Summary — reads GROSS rows from broksum_rows and derives the view:
 *  - NET  = buy - sell per broker, plus BandarDetector (Acc/Dist)
 *  - GROSS = raw buy + raw sell
 *  - board/investor "ALL" = SUM across stored grains (rg/tn/ng, f/d)
 *
 * Stockbit is only used by the crawler (CrawlBroksumCommand), not here.
 */
class BrokerSummaryService
{
    /**
     * @return array{buyers:array,sellers:array,bandar:array,total:array,code:string,from:string,to:string,txType:string,board:string,investor:string,error?:int}
     */
    public function getSummary(
        string $code,
        string $from,
        string $to,
        string $txType = 'TRANSACTION_TYPE_NET',
        string $board = 'MARKET_BOARD_REGULER',
        string $investor = 'INVESTOR_TYPE_ALL'
    ): array {
        $markets = $this->markets($board);   // ['rg', ...]
        $investors = $this->investors($investor); // ['f', 'd']

        $txStored = $txType === 'TRANSACTION_TYPE_GROSS' ? 'gross' : 'net';

        $rows = BroksumRow::where('stock_code', strtoupper($code))
            ->whereBetween('date', [$from, $to])
            ->where('tx_type', $txStored)
            ->whereIn('market_type', $markets)
            ->whereIn('investor_type', $investors)
            ->selectRaw('broker_code, side, SUM(lot) as lot, SUM(val) as val, AVG(avg) as avg, SUM(freq) as freq')
            ->groupBy('broker_code', 'side')
            ->get();

        if ($rows->isEmpty()) {
            return $this->empty($code, $from, $to, $txType, $board, $investor, 'Tidak ada data di database untuk filter ini.');
        }

        if ($txType === 'TRANSACTION_TYPE_GROSS') {
            return $this->gross($rows, $code, $from, $to, $txType, $board, $investor);
        }

        // NET rows are already net-per-broker (crawled from Stockbit NET source).
        return $this->net($rows, $code, $from, $to, $txType, $board, $investor, $markets, $investors);
    }

    private function net($rows, string $code, string $from, string $to, string $txType, string $board, string $investor, array $markets, array $investors): array
    {
        $buyers = $this->side($rows, 'buy');
        $sellers = $this->side($rows, 'sell');
        $bandar = (new BandarDetector())->compute($buyers, $sellers);

        // NET = one row per broker (buy - sell aggregated); split by sign.
        $net = $this->netSide($buyers, $sellers);
        $netBuyers = [];
        $netSellers = [];
        foreach ($net as $r) {
            if ($r['lot'] >= 0) {
                $netBuyers[] = $r;
            } else {
                $netSellers[] = ['code' => $r['code'], 'lot' => -$r['lot'], 'val' => -$r['val'], 'avg' => $r['avg'], 'freq' => $r['freq'], 'cat' => $r['cat'], 'volRaw' => formatShort(-$r['lot']), 'valRaw' => formatShort(-$r['val']), 'avgRaw' => number_format($r['avg'], 2)];
            }
        }

        // ponytail: NET sellers also val desc.
        usort($netSellers, fn ($a, $b) => $b['val'] <=> $a['val'] ?: $b['lot'] <=> $a['lot']);

        return array_merge([
            'buyers' => $netBuyers,
            'sellers' => $netSellers,
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
        ], $this->meta($code, $from, $to, $txType, $board, $investor));
    }

    private function gross($rows, string $code, string $from, string $to, string $txType, string $board, string $investor): array
    {
        return array_merge([
            'buyers' => $this->side($rows, 'buy'),
            'sellers' => $this->side($rows, 'sell'),
            'bandar' => [],
            'total' => [],
        ], $this->meta($code, $from, $to, $txType, $board, $investor));
    }

    /** Map rows of one side into UI-ready broker rows (ownership color via Broker). */
    private function side($rows, string $side): array
    {
        $out = [];
        foreach ($rows->where('side', $side) as $r) {
            $lot = (int) $r->lot;
            $val = (int) $r->val;
            $avg = (float) $r->avg;
            $out[] = [
                'code' => $r->broker_code,
                'type' => '',
                'lot' => $lot,
                'val' => $val,
                'avg' => round($avg, 2),
                'freq' => (int) $r->freq,
                'volRaw' => formatShort($lot),
                'valRaw' => formatShort($val),
                'avgRaw' => number_format($avg, 2),
                'cat' => \App\Models\Broker::categoryClass($r->broker_code),
            ];
        }

        // ponytail: UI top buyer/seller = val desc, then lot desc.
        usort($out, fn ($a, $b) => $b['val'] <=> $a['val'] ?: $b['lot'] <=> $a['lot']);

        return $out;
    }

    /** Merge buy/sell per broker into net rows (lot/val = buy - sell), sorted by net val desc. */
    private function netSide(array $buyers, array $sellers): array
    {
        $byCode = [];
        foreach ($buyers as $r) {
            $byCode[$r['code']] = ['code' => $r['code'], 'lot' => $r['lot'], 'val' => $r['val'], 'avg' => $r['avg'], 'freq' => $r['freq'], 'cat' => $r['cat']];
        }
        foreach ($sellers as $r) {
            if (!isset($byCode[$r['code']])) {
                $byCode[$r['code']] = ['code' => $r['code'], 'lot' => 0, 'val' => 0, 'avg' => $r['avg'], 'freq' => 0, 'cat' => $r['cat']];
            }
            $byCode[$r['code']]['lot'] -= $r['lot'];
            $byCode[$r['code']]['val'] -= $r['val'];
            $byCode[$r['code']]['freq'] += $r['freq'];
        }

        $out = array_values($byCode);
        foreach ($out as &$r) {
            $r['volRaw'] = formatShort($r['lot']);
            $r['valRaw'] = formatShort($r['val']);
            $r['avgRaw'] = number_format($r['avg'], 2);
        }
        unset($r);

        // ponytail: net view sorted by val desc (then lot desc).
        usort($out, fn ($a, $b) => $b['val'] <=> $a['val'] ?: $b['lot'] <=> $a['lot']);

        return $out;
    }

    private function markets(string $board): array
    {
        return match ($board) {
            'MARKET_BOARD_TUNAI' => ['tn'],
            'MARKET_BOARD_REGULER' => ['rg'],
            'MARKET_BOARD_NEGO' => ['ng'],
            default => ['all'], // Stockbit ALL is a stored server-side view, not a SUM
        };
    }

    private function investors(string $investor): array
    {
        return match ($investor) {
            'INVESTOR_TYPE_FOREIGN' => ['f'],
            'INVESTOR_TYPE_DOMESTIC' => ['d'],
            default => ['all'], // stored server-side view
        };
    }

    private function meta(string $code, string $from, string $to, string $txType, string $board, string $investor): array
    {
        return [
            'code' => strtoupper($code),
            'from' => $from,
            'to' => $to,
            'txType' => $txType,
            'board' => $board,
            'investor' => $investor,
        ];
    }

    private function empty(string $code, string $from, string $to, string $txType, string $board, string $investor, string $msg): array
    {
        return array_merge([
            'buyers' => [],
            'sellers' => [],
            'bandar' => [],
            'total' => [],
            'message' => $msg,
        ], $this->meta($code, $from, $to, $txType, $board, $investor));
    }
}

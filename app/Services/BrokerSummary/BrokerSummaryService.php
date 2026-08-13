<?php

namespace App\Services\BrokerSummary;

use App\Models\BroksumRow;
use App\Services\Stockbit\StockbitClient;
use App\Services\Stockbit\StockbitParser;

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
    public function __construct(
        private StockbitClient $client,
        private StockbitParser $parser,
    ) {}

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

        // ALL date range -> use the latest date present (crawler stores per-day).
        $date = $this->resolveDate($code, $from, $to, $markets, $investors);

        if ($date === null) {
            return $this->empty($code, $from, $to, $txType, $board, $investor, 'Tidak ada data di database untuk filter ini.');
        }

        $rows = BroksumRow::where('stock_code', strtoupper($code))
            ->where('date', $date)
            ->whereIn('market_type', $markets)
            ->whereIn('investor_type', $investors)
            ->get(['broker_code', 'side', 'lot', 'val', 'avg', 'freq']);

        if ($txType === 'TRANSACTION_TYPE_GROSS') {
            return $this->gross($rows, $code, $date, $txType, $board, $investor);
        }

        return $this->net($rows, $code, $date, $txType, $board, $investor);
    }

    private function net($rows, string $code, string $date, string $txType, string $board, string $investor): array
    {
        $buyers = $this->side($rows, 'buy');
        $sellers = $this->side($rows, 'sell');

        // merge net per broker for bandar detector
        $net = [];
        foreach ($buyers as $r) {
            $net[$r['code']] = [
                'lot' => ($net[$r['code']]['lot'] ?? 0) + $r['lot'],
                'val' => ($net[$r['code']]['val'] ?? 0) + $r['val'],
            ];
        }
        foreach ($sellers as $r) {
            $net[$r['code']] = [
                'lot' => ($net[$r['code']]['lot'] ?? 0) - $r['lot'],
                'val' => ($net[$r['code']]['val'] ?? 0) - $r['val'],
            ];
        }
        $bandarRows = [];
        foreach ($net as $c => $n) {
            $bandarRows[] = ['code' => $c, 'lot' => $n['lot'], 'val' => $n['val'], 'avg' => 0];
        }

        $bandar = (new BandarDetector())->compute($buyers, $sellers);

        return array_merge([
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
        ], $this->meta($code, $date, $txType, $board, $investor));
    }

    private function gross($rows, string $code, string $date, string $txType, string $board, string $investor): array
    {
        return array_merge([
            'buyers' => $this->side($rows, 'buy'),
            'sellers' => $this->side($rows, 'sell'),
            'bandar' => [],
            'total' => [],
        ], $this->meta($code, $date, $txType, $board, $investor));
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

    private function resolveDate(string $code, string $from, string $to, array $markets, array $investors): ?string
    {
        if ($from === $to) {
            return BroksumRow::where('stock_code', strtoupper($code))
                ->where('date', $from)
                ->whereIn('market_type', $markets)
                ->whereIn('investor_type', $investors)
                ->exists() ? $from : null;
        }

        // ponytail: ALL-date view = latest stored day in range (crawler is per-day).
        return BroksumRow::where('stock_code', strtoupper($code))
            ->whereBetween('date', [$from, $to])
            ->whereIn('market_type', $markets)
            ->whereIn('investor_type', $investors)
            ->max('date');
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

    private function meta(string $code, string $date, string $txType, string $board, string $investor): array
    {
        return [
            'code' => strtoupper($code),
            'from' => $date,
            'to' => $date,
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
        ], $this->meta($code, $from, $txType, $board, $investor));
    }
}

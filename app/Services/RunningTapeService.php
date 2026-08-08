<?php

namespace App\Services;

use App\Models\RunningTrade;
use Illuminate\Support\Facades\Http;

/**
 * Running-trade tape: fetch-or-generate with single-source-of-truth rollup.
 *
 * - for($code,$date): returns the tape, persisted on first fetch (generate-once).
 *   Subsequent calls read from DB. Swap the source URL via
 *   services.invezgo.base_url (mock server in dev, api.invezgo.com in prod).
 * - brokerSummary($code,$date): rolled up FROM the same persisted tape, so
 *   totals (lot/val/avg) are always consistent with the running trade.
 */
class RunningTapeService
{
    public function for(string $code, string $date): array
    {
        if (RunningTrade::forDay($code, $date)->exists()) {
            return $this->fromDb($code, $date);
        }

        return $this->fetchAndStore($code, $date);
    }

    private function fromDb(string $code, string $date): array
    {
        $rows = RunningTrade::forDay($code, $date)->get();

        return [
            'code' => $code,
            'date' => $date,
            ...$this->ohlcFrom($rows),
            'transactions' => $rows->map(fn ($r) => [
                'time' => $r->time,
                'price' => $r->price,
                'lot' => $r->lot,
                'broker' => $r->broker,
                'side' => $r->side,
            ])->all(),
        ];
    }

    private function fetchAndStore(string $code, string $date): array
    {
        $base = config('services.invezgo.base_url', 'http://localhost:8787');
        $resp = Http::get($base.'/running-trade', ['code' => $code, 'date' => $date]);

        if (! $resp->successful()) {
            throw new \RuntimeException("Running-trade source unavailable for {$code} {$date}");
        }

        $data = $resp->json();
        $this->store($code, $date, $data['transactions'] ?? []);

        return $data;
    }

    private function store(string $code, string $date, array $tx): void
    {
        $rows = [];
        foreach ($tx as $i => $t) {
            $rows[] = [
                'code' => $code,
                'trade_date' => $date,
                'seq' => $i,
                'time' => $t['time'],
                'price' => $t['price'],
                'lot' => $t['lot'] ?? 0,
                'broker' => $t['broker'] ?? null,
                'side' => $t['side'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ponytail: single upsert; per-day batch is tiny. Chunk if days grow huge.
        RunningTrade::upsert($rows, ['code', 'trade_date', 'seq'], ['time', 'price', 'lot', 'broker', 'side']);
    }

    /**
     * Broker summary rolled up from the tape — the single source of truth.
     * Because it is computed from the same transactions that compose the
     * running trade, totals always match by construction.
     */
    public function brokerSummary(string $code, string $date): array
    {
        $rows = RunningTrade::forDay($code, $date)
            ->whereNotNull('broker')
            ->get(['price', 'lot', 'broker', 'side']);

        $byBroker = [];
        $totLot = 0;
        $totVal = 0;

        foreach ($rows as $r) {
            $shares = $r->lot * 100;
            $val = $r->price * $shares;
            $totLot += $r->lot;
            $totVal += $val;

            $b = &$byBroker[$r->broker];
            if (!isset($b)) {
                $b = ['code' => $r->broker, 'buyLot' => 0, 'sellLot' => 0, 'buyVal' => 0, 'sellVal' => 0, 'buyCount' => 0, 'sellCount' => 0];
            }
            if ($r->side === 'buy') {
                $b['buyLot'] += $r->lot; $b['buyVal'] += $val; $b['buyCount']++;
            } else {
                $b['sellLot'] += $r->lot; $b['sellVal'] += $val; $b['sellCount']++;
            }
        }
        unset($b);

        $brokers = collect($byBroker)->map(function ($b) {
            $lot = $b['buyLot'] + $b['sellLot'];
            $val = $b['buyVal'] + $b['sellVal'];

            return [
                'code' => $b['code'],
                'lot' => $lot,
                'val' => $val,
                'avg' => $lot > 0 ? (int) round($val / ($lot * 100)) : 0,
                'buyLot' => $b['buyLot'],
                'sellLot' => $b['sellLot'],
                'buyVal' => $b['buyVal'],
                'sellVal' => $b['sellVal'],
                'netLot' => $b['buyLot'] - $b['sellLot'],
                'buyCount' => $b['buyCount'],
                'sellCount' => $b['sellCount'],
            ];
        })->sortByDesc('lot')->values()->all();

        return [
            'code' => $code,
            'date' => $date,
            'totalLot' => $totLot,
            'totalVal' => $totVal,
            'avg' => $totLot > 0 ? (int) round($totVal / ($totLot * 100)) : 0,
            'brokers' => $brokers,
        ];
    }

    private function ohlcFrom($rows): array
    {
        $trades = $rows->whereNotNull('broker');

        return [
            'open' => (int) $rows->first()->price,
            'close' => (int) $rows->last()->price,
            'high' => (int) ($trades->max('price') ?? $rows->first()->price),
            'low' => (int) ($trades->min('price') ?? $rows->first()->price),
        ];
    }
}

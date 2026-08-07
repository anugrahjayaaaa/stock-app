<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Stock;
use App\Services\TradingDayService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StockAnalysisController extends Controller
{
    public function __construct(private TradingDayService $tradingDay)
    {
    }

    public function index(): View
    {
        $stocks = Stock::orderBy('code')->get(['code', 'name']);
        $tradeDay = $this->tradingDay->defaultTradeDay();

        // ponytail: real broker master from DB; derived mock activity (net/pct/haka/haki/score).
        // Swap with Invezgo /analysis/summary/broker/{code} when API is wired.
        $brokers = Broker::orderBy('code')->get(['code', 'name', 'category']);
        $byCode = $brokers->keyBy('code');

        $buyers = $this->mockLeaders($brokers, 'buy');
        $sellers = $this->mockLeaders($brokers, 'sell');
        $retail = $this->mockRetail($brokers);

        $buyersCls = $this->classifyBuyers($buyers, $byCode);
        $sellersCls = $this->classifySellers($sellers, $byCode);

        $accumulation = $this->mockAccumulation($brokers, $tradeDay);
        $inventory = $this->mockInventory($brokers);

        // broker-summary wants lighter rows (vol/val/avg as strings — UI mock shape).
        $buyers = collect($buyers)->map(fn ($b) => [
            'code' => $b['code'], 'vol' => number_format(($this->seed($b['code']) * 4000) + 1000),
            'val' => number_format($this->seed($b['code']) * 50, 1), 'avg' => '10,2'.rand(10, 29),
        ])->all();
        $sellers = collect($sellers)->map(fn ($s) => [
            'code' => $s['code'], 'vol' => number_format(($this->seed($s['code']) * 4000) + 1000),
            'val' => number_format($this->seed($s['code']) * 50, 1), 'avg' => '10,2'.rand(10, 29),
        ])->all();

        // ponytail: kept here to avoid touching the technical widget — forward the same shape.
        $techPatterns = [
            ['name' => 'Double Bottom', 'type' => 'Reversal', 'tp' => '10,650', 'sl' => '9,900', 'status' => 'Confirmed'],
            ['name' => 'Bullish Flag', 'type' => 'Continuation', 'tp' => '10,800', 'sl' => '10,050', 'status' => 'Forming'],
            ['name' => 'Triangle', 'type' => 'Continuation', 'tp' => '10,500', 'sl' => '9,950', 'status' => 'Forming'],
        ];

        return view('stock.analyze', compact(
            'stocks', 'tradeDay', 'buyers', 'sellers', 'buyersCls', 'sellersCls', 'retail', 'techPatterns', 'accumulation', 'inventory'
        ));
    }

    // ponytail: inventory series for the chart. Deterministic per-broker cumulative net over a date range.
    // Swap with Invezgo /analysis/transactions/broker per day when API is wired.
    private function mockInventory(Collection $brokers): array
    {
        $codes = ['AK', 'BK', 'CC', 'PD', 'YP', 'ZP', 'YU'];
        $dates = [];
        $d = now()->subDays(29);
        for ($i = 0; $i < 30; $i++) {
            $dates[] = $d->copy()->addDays($i)->format('Y-m-d');
        }

        $series = [];
        $accum = []; $dist = [];
        foreach ($codes as $code) {
            if (! $brokers->contains('code', $code)) {
                continue;
            }
            $r = $this->seed($code.'inv');
            $sign = in_array($code, ['ZP', 'YU', 'YP']) ? -1 : 1;
            $cum = 0;
            $points = [];
            foreach ($dates as $j => $date) {
                $step = round($sign * ($r * 400 + 50) * (0.6 + 0.4 * sin($j / 4)));
                $cum += $step;
                $points[] = ['time' => $date, 'value' => $cum];
            }
            $series[] = [
                'code' => $code,
                'name' => optional($brokers->firstWhere('code', $code))->name ?? $code,
                'side' => $sign > 0 ? 'buy' : 'sell',
                'data' => $points,
            ];
            if ($sign > 0) $accum[] = ['code' => $code, 'name' => $series[count($series)-1]['name'], 'end' => $cum];
            else $dist[] = ['code' => $code, 'name' => $series[count($series)-1]['name'], 'end' => $cum];
        }

        $price = collect($dates)->map(function ($date, $j) {
            return ['time' => $date, 'value' => round(9800 + 600 * sin($j / 5) + $j * 12, 0)];
        })->all();

        // Net Bandar = sum of all cumulative at last date.
        $netBandar = collect($series)->sum(fn ($s) => end($s['data'])['value']);

        $score = $netBandar >= 0 ? min(100, 50 + (int) ($netBandar / 5000)) : max(0, 50 + (int) ($netBandar / 5000));

        return [
            'dates' => $dates,
            'price' => $price,
            'series' => $series,
            'topAccum' => collect($accum)->sortByDesc('end')->take(3)->values()->all(),
            'topDist' => collect($dist)->sortBy('end')->take(3)->values()->all(),
            'netBandar' => (int) $netBandar,
            'score' => (int) $score,
            'phase' => $score >= 60 ? 'Mark-Up / Accumulation' : ($score <= 40 ? 'Distribution' : 'Consolidation'),
            'summary' => $score >= 60
                ? 'Price making higher lows while Net Bandar inventory stays positive — classic Mark-Up phase. Major accumulator leading the charge; distribution risk low unless price stalls above target.'
                : ($score <= 40
                    ? 'Net Bandar inventory flipping negative with price unable to hold gains — Distribution phase underway. Watch for breakdown below AVG support.'
                    : 'Bandar inventory balanced; price coiling in range. Waiting for expansion — accumulation vs distribution not yet decisive.'),
        ];
    }

    // ponytail: accumulation view data. Top buyers from DB brokers; AVG/value derived deterministically.
    // Swap with Invezgo /analysis/summary/broker/{code} accumulated net per period when API is wired.
    private function mockAccumulation(Collection $brokers, string $tradeDay): array
    {
        $codes = ['AK', 'YP', 'CC', 'BK', 'PD', 'NI'];
        $rows = [];
        $totalLot = 0; $totalVal = 0;
        foreach ($codes as $code) {
            if (! $brokers->contains('code', $code)) {
                continue;
            }
            $r = $this->seed($code.'acc');
            $netLot = (int) ($r * 800000) + 50000;
            $avgPrice = (int) (($r * 200) + 9800);
            $netValue = $netLot * $avgPrice;
            $totalLot += $netLot; $totalVal += $netValue;
            $rows[] = [
                'code' => $code,
                'name' => optional($brokers->firstWhere('code', $code))->name ?? $code,
                'netLot' => $netLot, 'avgPrice' => $avgPrice, 'netValue' => $netValue,
            ];
        }
        $rows = collect($rows)->sortByDesc('netLot')->values();
        $top3 = $rows->take(3)->map(fn ($b) => [...$b, 'concentration' => round($b['netLot'] / max($totalLot, 1) * 100)])->all();

        $bandarAvg = (int) $rows->avg('avgPrice');
        $current = (int) ($bandarAvg * 1.04); // +4% -> Safe Entry Zone

        return [
            'start' => '2026-07-15',
            'end' => $tradeDay,
            'tradingDays' => 18,
            'top3' => $top3,
            'totalNetLot' => $totalLot,
            'totalNetValue' => $totalVal,
            'bandarAvg' => $bandarAvg,
            'currentPrice' => $current,
            'sl' => (int) ($bandarAvg * 0.94),
            'target' => (int) ($bandarAvg * 1.15),
        ];
    }

    // Deterministic pseudo-random so reloads are stable (no faker needed).
    private function seed(string $code): float
    {
        $h = crc32($code);
        return (($h % 1000) + 1) / 1000; // 0.001 - 1.0
    }

    private function mockLeaders(Collection $brokers, string $side): array
    {
        $codes = ['YP', 'AK', 'PD', 'BK', 'KZ', 'AZ', 'XL', 'NI', 'CS', 'RX'];
        $rows = [];
        foreach ($codes as $code) {
            if (! $brokers->contains('code', $code)) {
                continue;
            }
            $r = $this->seed($code.$side);
            $net = round($r * 60, 1) * ($side === 'buy' ? 1 : -1);
            $rows[] = [
                'code' => $code,
                'inv' => in_array($brokers->firstWhere('code', $code)->category, ['asing', 'bumn']) ? 'F' : 'D',
                'net' => $net,
                'pct' => (int) ($r * 80 + 10),
            ];
        }
        usort($rows, fn ($a, $b) => $side === 'buy' ? $b['net'] <=> $a['net'] : $a['net'] <=> $b['net']);

        return $rows;
    }

    private function classifyBuyers(array $rows, Collection $byCode): array
    {
        return collect($rows)->map(function ($r) use ($byCode) {
            $impostor = $r['code'] === 'YP' && $r['pct'] >= 70;
            return [...$r, 'impostor' => $impostor];
        })->all();
    }

    private function classifySellers(array $rows, Collection $byCode): array
    {
        return collect($rows)->map(fn ($r) => [...$r, 'impostor' => false])->all();
    }

    private function mockRetail(Collection $brokers): array
    {
        $codes = ['YP', 'CC', 'PD', 'XC', 'NI'];
        return collect($codes)->map(function ($code) use ($brokers) {
            $r = $this->seed($code.'retail');
            $avgLot = (int) ($r * 6000) + 50;
            $haka = (int) ($r * 60) + 20;
            $score = $avgLot > 2500 ? (int) ($r * 40) + 65 : (int) ($r * 50) + 5;
            $diag = $score > 65 ? 'Stealth Accumulation' : ($score >= 30 ? 'Mixed Activity' : 'Genuine Retail');
            $name = optional($brokers->firstWhere('code', $code))->name ?? $code;

            return [
                'code' => $code, 'name' => $name,
                'tx' => (int) ($r * 150) + 20,
                'vol' => (int) ($r * 240000) + 5000,
                'avgLot' => $avgLot,
                'haka' => $haka, 'haki' => 100 - $haka,
                'score' => $score, 'diagnosis' => $diag,
            ];
        })->all();
    }
}

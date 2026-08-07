<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Stock;
use App\Services\TradingDayService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StockAnalysisController extends Controller
{
    /**
     * Inject the trading-day service used to resolve the default analysis date.
     */
    public function __construct(private TradingDayService $tradingDay) {}

    /**
     * Render the stock analysis page.
     *
     * Phase 1 uses GoAPI (free tier): price/volume, PV analysis and technical
     * widgets are supported. Broker-level widgets (summary, tape, impostor,
     * accumulation, inventory) need Invezgo and are commented out in the view.
     * Swap the mock builders for Invezgo payloads once the API is wired.
     */
    public function index(): View
    {
        $stocks = Stock::orderBy('code')->get(['code', 'name']);
        $tradeDay = $this->tradingDay->defaultTradeDay();

        // ponytail: kept here to avoid touching the technical widget — forward the same shape.
        // Derivable from GoAPI historical OHLC; replace with live pattern detection later.
        $techPatterns = [
            ['name' => 'Double Bottom', 'type' => 'Reversal', 'tp' => '10,650', 'sl' => '9,900', 'status' => 'Confirmed'],
            ['name' => 'Bullish Flag', 'type' => 'Continuation', 'tp' => '10,800', 'sl' => '10,050', 'status' => 'Forming'],
            ['name' => 'Triangle', 'type' => 'Continuation', 'tp' => '10,500', 'sl' => '9,950', 'status' => 'Forming'],
        ];

        return view('stock.analyze', compact('stocks', 'tradeDay', 'techPatterns'));
    }

    /**
     * Build the broker-inventory chart dataset.
     *
     * Generates 30 daily points of deterministic cumulative net per broker
     * (AK/BK/CC/PD accumulate, YP/ZP/YU distribute), a synthetic price line,
     * the Top-3 accumulators/distributors, Net Bandar total, and an AI
     * bandarmology phase/score/summary. Replace with Invezgo per-day
     * transactions once the API is wired.
     *
     * @param  Collection  $brokers  Broker master (code/name/category) from DB.
     * @return array{dates:string[],price:array,series:array,topAccum:array,topDist:array,netBandar:int,score:int,phase:string,summary:string}
     */
    private function mockInventory(Collection $brokers): array
    {
        $codes = ['AK', 'BK', 'CC', 'PD', 'YP', 'ZP', 'YU'];
        $dates = [];
        $d = now()->subDays(29);
        for ($i = 0; $i < 30; $i++) {
            $dates[] = $d->copy()->addDays($i)->format('Y-m-d');
        }

        $series = [];
        $accum = [];
        $dist = [];
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
            if ($sign > 0) {
                $accum[] = ['code' => $code, 'name' => $series[count($series) - 1]['name'], 'end' => $cum];
            } else {
                $dist[] = ['code' => $code, 'name' => $series[count($series) - 1]['name'], 'end' => $cum];
            }
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

    /**
     * Build the accumulation-range & bandar AVG widget dataset.
     *
     * Derives per-broker net lot / average price / value from the DB broker
     * master deterministically, ranks the Top-3 accumulators, and computes
     * the Bandar AVG plus current/SL/target levels. Replace with Invezgo
     * accumulated-net-per-period once the API is wired.
     *
     * @param  Collection  $brokers  Broker master (code/name/category) from DB.
     * @param  string  $tradeDay  Default analysis date (used as range end).
     * @return array{start:string,end:string,tradingDays:int,top3:array,totalNetLot:int,totalNetValue:int,bandarAvg:int,currentPrice:int,sl:int,target:int}
     */
    private function mockAccumulation(Collection $brokers, string $tradeDay): array
    {
        $codes = ['AK', 'YP', 'CC', 'BK', 'PD', 'NI'];
        $rows = [];
        $totalLot = 0;
        $totalVal = 0;
        foreach ($codes as $code) {
            if (! $brokers->contains('code', $code)) {
                continue;
            }
            $r = $this->seed($code.'acc');
            $netLot = (int) ($r * 800000) + 50000;
            $avgPrice = (int) (($r * 200) + 9800);
            $netValue = $netLot * $avgPrice;
            $totalLot += $netLot;
            $totalVal += $netValue;
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

    /**
     * Deterministic pseudo-random in [0.001, 1.0] from a broker code.
     *
     * Keeps mock data stable across reloads without a faker dependency.
     */
    private function seed(string $code): float
    {
        $h = crc32($code);

        return (($h % 1000) + 1) / 1000; // 0.001 - 1.0
    }

    /**
     * Build the buyer/seller leaderboard rows from the DB broker master.
     *
     * @param  Collection  $brokers  Broker master from DB.
     * @param  string  $side  'buy' or 'sell' (flips the net sign).
     * @return array<int,array{code:string,inv:string,net:float,pct:int}>
     */
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

    /**
     * Flag buyers acting as retail impostors (YP with >=70% HAKA).
     *
     * @return array<int,array{code:string,inv:string,net:float,pct:int,impostor:bool}>
     */
    private function classifyBuyers(array $rows, Collection $byCode): array
    {
        return collect($rows)->map(function ($r) {
            $impostor = $r['code'] === 'YP' && $r['pct'] >= 70;

            return [...$r, 'impostor' => $impostor];
        })->all();
    }

    /**
     * Sellers are never flagged as impostors in the current mock.
     *
     * @return array<int,array{code:string,inv:string,net:float,pct:int,impostor:bool}>
     */
    private function classifySellers(array $rows, Collection $byCode): array
    {
        return collect($rows)->map(fn ($r) => [...$r, 'impostor' => false])->all();
    }

    /**
     * Build the retail-broker behavioral dataset (HAKA/HAKI, avg lot, score).
     *
     * @param  Collection  $brokers  Broker master from DB.
     * @return array<int,array{code:string,name:string,tx:int,vol:int,avgLot:int,haka:int,haki:int,score:int,diagnosis:string}>
     */
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

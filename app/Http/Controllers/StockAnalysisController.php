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
            'stocks', 'tradeDay', 'buyers', 'sellers', 'buyersCls', 'sellersCls', 'retail', 'techPatterns'
        ));
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

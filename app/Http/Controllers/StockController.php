<?php

namespace App\Http\Controllers;

use App\Models\ChartDrawing;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function showChart(): View
    {
        // ponytail: all listed stocks feed the datalist suggestion; future
        // API sync keeps this table fresh without touching the view.
        $stocks = Stock::orderBy('code')->get(['code', 'name']);

        return view('stock.chart-litechart', compact('stocks'));
    }

    // TradingView embed (tv.js) variant.
    public function showChartTv(): View
    {
        $stocks = Stock::orderBy('code')->get(['code', 'name']);

        return view('stock.chart-tv', compact('stocks'));
    }

    // Load this user's saved drawings for a symbol.
    public function getDrawings(Request $request): JsonResponse
    {
        $symbol = strtoupper((string) $request->query('symbol', ''));
        if (! $symbol) {
            return response()->json(['drawings' => []]);
        }

        $row = ChartDrawing::where('user_id', $request->user()->id)
            ->where('symbol', $symbol)
            ->first();

        return response()->json(['drawings' => $row?->drawing_json ?? []]);
    }

    // Replace this user's drawings for a symbol (single-row upsert).
    public function saveDrawings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:20'],
            'drawings' => ['required', 'array'],
        ]);

        $symbol = strtoupper($data['symbol']);

        ChartDrawing::updateOrCreate(
            ['user_id' => $request->user()->id, 'symbol' => $symbol],
            ['drawing_json' => $data['drawings']],
        );

        return response()->json(['ok' => true]);
    }

    // Proxy IDX daily OHLC to the frontend. Currently Yahoo .JK (no key);
    // ponytail: swap the upstream for Invezgo when ready. Returns
    // lightweight-charts-shaped rows: {time:'YYYY-MM-DD', open, high, low, close}.
    public function getOhlc(Request $request): JsonResponse
    {
        $symbol = strtoupper((string) $request->query('symbol', 'BBCA'));
        // ponytail: map UI intervals to Yahoo's interval+range pairs.
        // Yahoo caps 1m/5m ranges; keep within limits to avoid 422.
        $map = [
            '1m' => ['i' => '1m', 'r' => '7d'],
            '5m' => ['i' => '5m', 'r' => '60d'],
            '15m' => ['i' => '15m', 'r' => '60d'],
            '1h' => ['i' => '60m', 'r' => '60d'],
            '2h' => ['i' => '90m', 'r' => '60d'],
            '4h' => ['i' => '4h', 'r' => '60d'],
            '1D' => ['i' => '1d', 'r' => '1y'],
            '1W' => ['i' => '1wk', 'r' => '2y'],
            '1M' => ['i' => '1mo', 'r' => '5y'],
        ];
        $key = $map[$request->query('interval', '1D')] ?? $map['1D'];
        $url = 'https://query2.finance.yahoo.com/v8/finance/chart/' . $symbol
            . '.JK?range=' . $key['r'] . '&interval=' . $key['i'];

        try {
            $json = json_decode(file_get_contents($url, false, stream_context_create([
                'http' => ['header' => "User-Agent: Mozilla/5.0\r\n"],
            ])), true);
        } catch (\Throwable $e) {
            return response()->json([], 502);
        }

        $result = $json['chart']['result'][0] ?? null;
        if (! $result) {
            return response()->json([], 502);
        }

        $ts = $result['timestamp'] ?? [];
        $quotes = $result['indicators']['quote'][0] ?? [];
        $out = [];
        foreach ($ts as $i => $t) {
            $o = $quotes['open'][$i] ?? null;
            $h = $quotes['high'][$i] ?? null;
            $l = $quotes['low'][$i] ?? null;
            $c = $quotes['close'][$i] ?? null;
            if ($o === null || $h === null || $l === null || $c === null) {
                continue;
            }
            $out[] = [
                'time' => gmdate('Y-m-d', $t),
                'open' => $o, 'high' => $h, 'low' => $l, 'close' => $c,
            ];
        }

        return response()->json($out);
    }
}

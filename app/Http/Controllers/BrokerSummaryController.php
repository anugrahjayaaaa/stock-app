<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\BrokerSummary\BrokerSummaryService;
use App\Services\TradingDayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Broker Summary (broksum) — single home for the broksum page + data API.
 * Source: Stockbit market-detector (NET per broker + bandar detector).
 *
 * - index(): HTML page (sidebar target), reuses <x-broker-summary> widget.
 * - data():  JSON endpoint the widget's "Ambil dari Stockbit" button hits.
 */
class BrokerSummaryController extends Controller
{
    public function __construct(
        private BrokerSummaryService $service,
        private TradingDayService $tradingDay,
    ) {}

    /** GET /stock/broker-summary  (?symbol=&from=&to=&txType=&board=&investor=) */
    public function index(Request $request): View
    {
        $stocks = Stock::orderBy('code')->get(['code', 'name']);
        $tradeDay = $this->tradingDay->defaultTradeDay();
        $symbol = strtoupper($request->query('symbol', $stocks->first()->code ?? 'BBCA'));
        // Y-m-d for input[type=date]; data() converts to Stockbit range as-is.
        $from = $request->query('from', $tradeDay);
        $to = $request->query('to', $tradeDay);
        $txType = $request->query('txType', 'TRANSACTION_TYPE_NET');
        $board = $request->query('board', 'MARKET_BOARD_REGULER');
        $investor = $request->query('investor', 'INVESTOR_TYPE_ALL');

        $bs = $this->service->getSummary($symbol, $from, $to, $txType, $board, $investor);

        return view('stock.broksum', [
            'stocks' => $stocks,
            'tradeDay' => $tradeDay,
            'symbol' => $symbol,
            'from' => $from,
            'to' => $to,
            'txType' => $txType,
            'board' => $board,
            'investor' => $investor,
            'buyers' => $bs['buyers'],
            'sellers' => $bs['sellers'],
            'bandar' => $bs['bandar'],
            'total' => $bs['total'],
            'error' => $bs['error'] ?? null,
        ]);
    }

    /** GET /stock/broker-summary/{code}  (JSON; ?from=&to=&txType=&board=&investor=) */
    public function data(Request $request, string $code): JsonResponse
    {
        $tradeDay = $this->tradingDay->defaultTradeDay();
        $from = $request->query('from', $tradeDay);
        $to = $request->query('to', $tradeDay);
        $txType = $request->query('txType', 'TRANSACTION_TYPE_NET');
        $board = $request->query('board', 'MARKET_BOARD_REGULER');
        $investor = $request->query('investor', 'INVESTOR_TYPE_ALL');

        return response()->json(
            $this->service->getSummary(strtoupper($code), $from, $to, $txType, $board, $investor)
        );
    }
}

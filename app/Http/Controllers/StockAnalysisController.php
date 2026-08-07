<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\TradingDayService;
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

        return view('stock.analyze', compact('stocks', 'tradeDay'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\View\View;

class StockController extends Controller
{
    public function showChart(): View
    {
        // ponytail: all listed stocks feed the datalist suggestion; future
        // API sync keeps this table fresh without touching the view.
        $stocks = Stock::orderBy('code')->get(['code', 'name']);

        return view('stock.chart', compact('stocks'));
    }
}

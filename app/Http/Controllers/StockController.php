<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StockController extends Controller
{
    public function showChart(): View
    {
        return view('stock.chart');
    }
}
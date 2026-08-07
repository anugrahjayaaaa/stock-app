<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use Illuminate\View\View;

class BrokerController extends Controller
{
    public function index(): View
    {
        $brokers = Broker::orderBy('category')
            ->orderBy('code')
            ->get(['code', 'name', 'category']);

        return view('stock.brokers', compact('brokers'));
    }
}

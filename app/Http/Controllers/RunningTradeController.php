<?php

namespace App\Http\Controllers;

use App\Services\RunningTapeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RunningTradeController extends Controller
{
    public function __construct(private RunningTapeService $tape) {}

    /** Running-trade tape (done/detail transactions) for a ticker/day. */
    public function tape(Request $request, string $code): JsonResponse
    {
        $date = $request->query('date', today()->toDateString());

        return response()->json($this->tape->for(strtoupper($code), $date));
    }

    /** Broker summary rolled up from the persisted tape. */
    public function brokerSummary(Request $request, string $code): JsonResponse
    {
        $date = $request->query('date', today()->toDateString());
        $this->tape->for(strtoupper($code), $date); // ensure persisted

        return response()->json($this->tape->brokerSummary(strtoupper($code), $date));
    }
}

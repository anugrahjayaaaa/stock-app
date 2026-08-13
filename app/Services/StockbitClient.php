<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Stockbit "market detector" API (exodus.stockbit.com).
 *
 * Auth: static Bearer token from STOCKBIT_BEARER_TOKEN (no refresh yet).
 * On 401/expiry the caller gets ['error'=>status] and should degrade gracefully.
 *
 * Valid params (probed):
 *  - transaction_type: TRANSACTION_TYPE_NET (default) | TRANSACTION_TYPE_GROSS
 *  - market_board: MARKET_BOARD_REGULER | MARKET_BOARD_TUNAI | MARKET_BOARD_ALL
 *  - investor_type: INVESTOR_TYPE_ALL | INVESTOR_TYPE_FOREIGN | INVESTOR_TYPE_DOMESTIC
 *  - from / to: YYYY-MM-DD
 *  - limit: only 25 (API rejects other values)
 */
class StockbitClient
{
    private string $base;

    private string $token;

    public function __construct()
    {
        $this->base = env('STOCKBIT_API_BASE', 'https://exodus.stockbit.com');
        $this->token = (string) env('STOCKBIT_BEARER_TOKEN', '');
    }

    /**
     * @return array{data:array}|array{error:int,body:string}
     */
    public function marketDetector(
        string $code,
        string $from,
        string $to,
        string $txType = 'TRANSACTION_TYPE_NET',
        string $board = 'MARKET_BOARD_REGULER',
        string $investor = 'INVESTOR_TYPE_ALL'
    ): array {
        $res = Http::withHeaders([
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$this->token,
            'user-agent' => 'Mozilla/5.0',
        ])->get($this->base.'/marketdetectors/'.$code, [
            'from' => $from,
            'to' => $to,
            'transaction_type' => $txType,
            'market_board' => $board,
            'investor_type' => $investor,
            'limit' => 25,
        ]);

        if (! $res->successful()) {
            // ponytail: static token, no refresh — surface upstream status, let caller degrade.
            return ['error' => $res->status(), 'body' => $res->body()];
        }

        $json = $res->json();

        return $json['data'] ?? [];
    }
}

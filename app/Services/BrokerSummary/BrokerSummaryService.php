<?php

namespace App\Services\BrokerSummary;

use App\Services\StockbitClient;
use App\Services\StockbitParser;

/**
 * Broker Summary service backed by Stockbit market-detector API.
 * Single home for broksum fetch/parse logic (was IPOT on the other branch).
 *
 * Endpoints feed this through BrokerSummaryController. No persistence yet
 * (fetch-live); cache save is commented out in getSummary().
 */
class BrokerSummaryService
{
    public function __construct(
        private StockbitClient $client,
        private StockbitParser $parser,
    ) {}

    /**
     * @return array{buyers:array,sellers:array,bandar:array,total:array,code:string,from:string,to:string,txType:string,board:string,investor:string,error?:int}
     */
    public function getSummary(
        string $code,
        string $from,
        string $to,
        string $txType = 'TRANSACTION_TYPE_NET',
        string $board = 'MARKET_BOARD_REGULER',
        string $investor = 'INVESTOR_TYPE_ALL'
    ): array {
        $data = $this->client->marketDetector($code, $from, $to, $txType, $board, $investor);

        if (isset($data['error'])) {
            return [
                'code' => strtoupper($code),
                'from' => $from,
                'to' => $to,
                'txType' => $txType,
                'board' => $board,
                'investor' => $investor,
                'error' => $data['error'],
                'buyers' => [],
                'sellers' => [],
                'bandar' => [],
                'total' => [],
            ];
        }

        $parsed = $this->parser->parse($data);

        return array_merge($parsed, [
            'code' => strtoupper($code),
            'from' => $from,
            'to' => $to,
            'txType' => $txType,
            'board' => $board,
            'investor' => $investor,
        ]);
    }
}

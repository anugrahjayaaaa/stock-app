<?php

namespace App\Console\Commands;

use App\Models\BroksumRow;
use App\Models\Stock;
use App\Services\Stockbit\StockbitClient;
use App\Services\Stockbit\StockbitParser;
use Illuminate\Console\Command;

/**
 * Crawl Stockbit broksum (GROSS) for every market×investor combo + the ALL
 * server-side views, and store rows. Stockbit "All" is a precompute, NOT a
 * sum of grains — so it must be fetched and stored as its own rows.
 *
 * ponytail: tn row count can be 0 for thinly-traded stocks — expected, not error.
 * ponytail: ng market param confirmed MARKET_BOARD_NEGO (not NEGOTIATION).
 * ponytail: GROSS field names assumed = NET (blot/bval/slot/sval); revisit if gross differs.
 */
class CrawlBroksumCommand extends Command
{
    protected $signature = 'broksum:crawl {--date=} {--symbol=}';

    protected $description = 'Crawl Stockbit GROSS broksum (market×investor + ALL views) into broksum_rows';

    // local key => Stockbit market_board param
    private const MARKETS = [
        'rg' => 'MARKET_BOARD_REGULER',
        'tn' => 'MARKET_BOARD_TUNAI',
        'ng' => 'MARKET_BOARD_NEGO',
    ];

    // local key => Stockbit investor_type param
    private const INVESTORS = [
        'f' => 'INVESTOR_TYPE_FOREIGN',
        'd' => 'INVESTOR_TYPE_DOMESTIC',
    ];

    public function handle(StockbitClient $client, StockbitParser $parser): int
    {
        $date = $this->option('date') ?: now()->toDateString();
        $symbols = $this->option('symbol')
            ? [strtoupper($this->option('symbol'))]
            : Stock::pluck('code')->all();

        $saved = 0;
        foreach ($symbols as $code) {
            // 1) granular grains (f/d × rg/tn/ng)
            foreach (self::MARKETS as $mKey => $mParam) {
                foreach (self::INVESTORS as $iKey => $iParam) {
                    $saved += $this->fetch($client, $parser, $code, $date, $mParam, $iParam, $mKey, $iKey);
                }
            }
            // 2) ALL server-side views (stored as market/investor = 'all')
            foreach (self::INVESTORS as $iKey => $iParam) {
                $saved += $this->fetch($client, $parser, $code, $date, 'MARKET_BOARD_ALL', $iParam, 'all', $iKey);
            }
            foreach (self::MARKETS as $mKey => $mParam) {
                $saved += $this->fetch($client, $parser, $code, $date, $mParam, 'INVESTOR_TYPE_ALL', $mKey, 'all');
            }
            $saved += $this->fetch($client, $parser, $code, $date, 'MARKET_BOARD_ALL', 'INVESTOR_TYPE_ALL', 'all', 'all');

            // 3) NET crawls (Stockbit NET is a separate source, not derivable from gross)
            foreach (self::MARKETS as $mKey => $mParam) {
                foreach (self::INVESTORS as $iKey => $iParam) {
                    $saved += $this->fetchNet($client, $parser, $code, $date, $mParam, $iParam, $mKey, $iKey);
                }
            }
            foreach (self::INVESTORS as $iKey => $iParam) {
                $saved += $this->fetchNet($client, $parser, $code, $date, 'MARKET_BOARD_ALL', $iParam, 'all', $iKey);
            }
            foreach (self::MARKETS as $mKey => $mParam) {
                $saved += $this->fetchNet($client, $parser, $code, $date, $mParam, 'INVESTOR_TYPE_ALL', $mKey, 'all');
            }
            $saved += $this->fetchNet($client, $parser, $code, $date, 'MARKET_BOARD_ALL', 'INVESTOR_TYPE_ALL', 'all', 'all');
        }

        $this->info("broksum:crawl done — {$saved} rows for {$date}");

        return self::SUCCESS;
    }

    private function fetchNet(StockbitClient $client, StockbitParser $parser, string $code, string $date, string $mParam, string $iParam, string $mKey, string $iKey): int
    {
        $data = $client->marketDetector($code, $date, $date, 'TRANSACTION_TYPE_NET', $mParam, $iParam);

        if (isset($data['error'])) {
            $this->warn("{$code}/{$mKey}/{$iKey} NET: HTTP {$data['error']}");

            return 0;
        }

        $parsed = $parser->parse($data);

        return $this->storeSide($code, $date, $mKey, $iKey, $parsed['buyers'], 'buy', 'net')
            + $this->storeSide($code, $date, $mKey, $iKey, $parsed['sellers'], 'sell', 'net');
    }

    private function fetch(StockbitClient $client, StockbitParser $parser, string $code, string $date, string $mParam, string $iParam, string $mKey, string $iKey): int
    {
        $data = $client->marketDetector($code, $date, $date, 'TRANSACTION_TYPE_GROSS', $mParam, $iParam);

        if (isset($data['error'])) {
            $this->warn("{$code}/{$mKey}/{$iKey}: HTTP {$data['error']}");

            return 0;
        }

        $parsed = $parser->parse($data);

        return $this->storeSide($code, $date, $mKey, $iKey, $parsed['buyers'], 'buy')
            + $this->storeSide($code, $date, $mKey, $iKey, $parsed['sellers'], 'sell');
    }

    private function storeSide(string $code, string $date, string $m, string $i, array $rows, string $side, string $txType = 'gross'): int
    {
        $n = 0;
        foreach ($rows as $r) {
            if (empty($r['code'])) {
                continue;
            }
            BroksumRow::updateOrCreate(
                [
                    'stock_code' => $code, 'date' => $date, 'tx_type' => $txType,
                    'market_type' => $m, 'investor_type' => $i,
                    'broker_code' => $r['code'], 'side' => $side,
                ],
                [
                    'lot' => $r['lot'] ?? 0,
                    'val' => $r['val'] ?? 0,
                    'avg' => $r['avg'] ?? null,
                    'freq' => $r['freq'] ?? 0,
                ]
            );
            $n++;
        }

        return $n;
    }
}

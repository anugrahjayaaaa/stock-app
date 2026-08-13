<?php

namespace App\Console\Commands;

use App\Models\BroksumRow;
use App\Models\Stock;
use App\Services\StockbitClient;
use App\Services\StockbitParser;
use Illuminate\Console\Command;

/**
 * Crawl Stockbit broksum (GROSS) for every market×investor combo and store rows.
 * Run via scheduler later; ALL axis is derived from stored rows, never fetched.
 *
 * ponytail: GROSS parser shape (field names) unverified vs NET — revisit storeSide
 *           if Stockbit returns different keys for gross buy/sell.
 // ponytail: tn row count can be 0 for thinly-traded stocks — expected, not an error.
 // ponytail: ng market param confirmed MARKET_BOARD_NEGO (not NEGOTIATION) vs API.
 */
class CrawlBroksumCommand extends Command
{
    protected $signature = 'broksum:crawl {--date=} {--symbol=}';

    protected $description = 'Crawl Stockbit GROSS broksum (market×investor) into broksum_rows';

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
            foreach (self::MARKETS as $mKey => $mParam) {
                foreach (self::INVESTORS as $iKey => $iParam) {
                    $data = $client->marketDetector(
                        $code, $date, $date,
                        'TRANSACTION_TYPE_GROSS', $mParam, $iParam
                    );

                    if (isset($data['error'])) {
                        $this->warn("{$code}/{$mKey}/{$iKey}: HTTP {$data['error']}");
                        continue;
                    }

                    $parsed = $parser->parse($data);
                    $saved += $this->storeSide($code, $date, $mKey, $iKey, $parsed['buyers'], 'buy');
                    $saved += $this->storeSide($code, $date, $mKey, $iKey, $parsed['sellers'], 'sell');
                }
            }
        }

        $this->info("broksum:crawl done — {$saved} rows for {$date}");

        return self::SUCCESS;
    }

    private function storeSide(string $code, string $date, string $m, string $i, array $rows, string $side): int
    {
        $n = 0;
        foreach ($rows as $r) {
            if (empty($r['code'])) {
                continue;
            }
            BroksumRow::updateOrCreate(
                [
                    'stock_code' => $code, 'date' => $date,
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

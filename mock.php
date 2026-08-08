#!/usr/bin/env php
<?php
/**
 * Standalone Invezgo-compatible mock for running-trade (done/detail transactions).
 *
 * Run:  php -S localhost:8787 mock.php
 * The app points services.invezgo.base_url at this in dev; swap to
 * https://api.invezgo.com in prod — same endpoint shape.
 *
 * GET /running-trade?code=BIPI&date=2026-08-10[&open=&high=&low=&close=]
 *   - explicit OHLC pins the day; otherwise derived deterministically from seed
 *   - tape: pre-open marker @08:58 (open), intraday trades 09:00-16:00
 *     (lunch break honoured; Friday 11:30-14:00), close marker @16:00
 *   - each trade carries a broker code + side (buy/sell) so a downstream
 *     broker-summary can be rolled up from the SAME tape (consistency).
 *
 * Deterministic per (code+date): same inputs -> identical tape (generate-once
 * persistence in the app stays stable across reloads).
 *
 * Usage:
 *   php mock.php                      # start mock server on :8787 (background: add &)
 *   php mock.php 9000                # custom port
 *   php mock.php help                # print this help
 *   php mock.php --selftest          # run logic self-checks (no server)
 *
 * When served, hit:
 *   http://localhost:8787/running-trade?code=BIPI&date=2026-08-10[&open=&high=&low=&close=]
 */

// ---- cli entrypoints --------------------------------------------------------
if (PHP_SAPI === 'cli') {
    $arg = $argv[1] ?? '8787';
    if ($arg === 'help' || $arg === '-h' || $arg === '--help') {
        $head = explode("\n", file_get_contents(__FILE__), 40);
        $in = false;
        foreach ($head as $line) {
            if (str_starts_with($line, ' * Usage:')) {
                $in = true;
            }
            if ($in) {
                if (str_starts_with($line, ' */')) {
                    break;
                }
                echo ltrim($line, ' *')."\n";
            }
        }
        exit;
    }
    if ($arg === '--selftest') {
        // handled at the bottom of the file; fall through.
    } elseif (is_numeric($arg)) {
        $port = (int) $arg;
        echo "Mock server running at http://localhost:{$port}/running-trade\n";
        echo "Hit: ?code=BIPI&date=2026-08-10[&open=&high=&low=&close=]\n";
        echo "Press Ctrl+C to stop.\n";
        // Delegate to PHP's built-in server with this file as router.
        $docroot = dirname(__FILE__);
        pcntl_exec(PHP_BINARY, ['-S', "localhost:{$port}", '-t', $docroot, __FILE__]);
        exit;
    } else {
        fwrite(STDERR, "Unknown command: {$arg}\nRun: php mock.php help\n");
        exit(1);
    }
}

// ---- zero-dependency router -------------------------------------------------
$routes = [];
function route(string $method, string $pattern, callable $handler): void
{
    global $routes;
    $routes[] = compact('method', 'pattern', 'handler');
}
function send(int $code, $body): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
}
function param(string $k, $def = null)
{
    return $_GET[$k] ?? $def;
}

// ---- deterministic PRNG (mulberry32) ---------------------------------------
function makeRng(string $seed): callable
{
    $s = (crc32($seed) ?: 1) & 0xffffffff;
    return function () use (&$s): float {
        $s = ($s + 0x6D2B79F5) & 0xffffffff;
        $t = $s;
        $t = ($t ^ ($t >> 15)) & 0xffffffff;
        $t = ($t * ($t | 5)) & 0xffffffff;
        $t = ($t ^ ($t >> 12)) & 0xffffffff;
        $t = ($t * ($t | 61)) & 0xffffffff;
        $t = ($t ^ ($t >> 15)) & 0xffffffff;
        return ($t & 0xffffffff) / 4294967296;
    };
}

const BROKER_POOL = ['AK','BK','CC','PD','YP','ZP','YU','KZ','NI','AZ','XL','RX','CS','BQ','TP'];

/**
 * Per-broker trading tendency is seeded per ticker (not hardcoded), so each
 * emiten gets its own buyer/seller mix. Returns -1..+1 via a seeded PRNG.
 */
function brokerTendency(string $code, string $bk): float
{
    $r = makeRng($code.'|tend|'.$bk)();
    return round($r * 2 - 1, 2); // -1.00 (net sell) .. +1.00 (net buy)
}

// IDX sessions in minutes-of-day. Regular: 09:00-12:00, 13:30-16:00.
// Friday: 09:00-11:30, 14:00-16:00 (longer lunch).
function tradingWindows(string $date): array
{
    $dow = (int) date('w', strtotime($date)); // 5 = Friday
    if ($dow === 5) {
        return [[9 * 60, 11 * 60 + 30], [14 * 60, 16 * 60]];
    }
    return [[9 * 60, 12 * 60], [13 * 60 + 30, 16 * 60]];
}
function minutesToTime(int $m): string
{
    return sprintf('%02d:%02d:00', intdiv($m, 60), $m % 60);
}

function buildTape(string $code, string $date, ?int $open, ?int $high, ?int $low, ?int $close): array
{
    $rng = makeRng($code.'|'.$date);

    // OHLC: explicit pins win; else derive a plausible IDX-range day from seed.
    if ($open === null) {
        $base  = 50 + (int) ($rng() * 14950);          // 50 - 15000
        $open  = $base;
        $close = max(50, (int) ($base * (0.95 + $rng() * 0.10)));
        $high  = max($open, $close) + (int) ($base * 0.02 * $rng());
        $low   = max(10, min($open, $close) - (int) ($base * 0.02 * $rng()));
    }
    $high = max($high, $open, $close);
    $low  = min($low, $open, $close);
    $high = max($high, $low + 1);

    // Density by seeded per-ticker liquidity (low-liquid -> fewer trades).
    $n = (int) (300 + $rng() * 4700);
    $n = max(50, min($n, 20000));

    $windows = tradingWindows($date);
    $totalMin = 0;
    foreach ($windows as [$a, $b]) {
        $totalMin += ($b - $a);
    }

    // Intraday trades only; markers (open/close) pinned separately after sort.
    $intra = [];
    $price = (float) $open;
    for ($i = 1; $i <= $n; $i++) {
        $t = $i / $n;
        // pick a minute within a session window, proportional to window length
        $pick = $rng() * $totalMin;
        $mm = null;
        foreach ($windows as [$a, $b]) {
            $len = $b - $a;
            if ($pick < $len) { $mm = $a + (int) $pick; break; }
            $pick -= $len;
        }
        // Brownian-bridge drift open->close, clamped to [low, high]; noise decays to 0.
        $base  = $open + ($close - $open) * $t;
        $noise = (($rng() * 2 - 1) * ($high - $low) * 0.04) * (1 - $t);
        $price = max($low, min($high, $price + ($base - $price) * 0.15 + $noise));

        $lot = max(1, (int) ($rng() * 50000));           // 1..50000 lot
        $bk  = BROKER_POOL[(int) ($rng() * count(BROKER_POOL))];
        $tend = brokerTendency($code, $bk);              // per-emiten seeded bias
        $side = ($rng() < (0.5 + $tend * 0.15)) ? 'buy' : 'sell';

        $intra[] = [
            'time'   => minutesToTime($mm),
            'price'  => (int) round($price),
            'lot'    => $lot,
            'broker' => $bk,
            'side'   => $side,
        ];
    }
    usort($intra, fn ($a, $b) => strcmp($a['time'], $b['time']));

    $openMarker  = ['time' => '08:58:00', 'price' => $open,  'lot' => 0, 'broker' => null, 'side' => null];
    $closeMarker = ['time' => '16:00:00', 'price' => $close, 'lot' => 0, 'broker' => null, 'side' => null];
    $tx = array_merge([$openMarker], $intra, [$closeMarker]);

    return [
        'code' => $code,
        'date' => $date,
        'open' => $open, 'high' => $high, 'low' => $low, 'close' => $close,
        'transactions' => $tx,
    ];
}

route('GET', '#^/running-trade$#', function () {
    $code = strtoupper(trim((string) param('code', '')));
    $date = param('date', date('Y-m-d'));
    if (!preg_match('/^[A-Z0-9]{4}$/', $code)) {
        send(400, ['error' => 'code must be a 4-char ticker']);
        return;
    }
    $ohlc = null;
    foreach (['open', 'high', 'low', 'close'] as $k) {
        $v = param($k);
        if ($v !== null) {
            $ohlc[$k] = (int) $v;
        }
    }
    if (isset($ohlc) && count($ohlc) !== 4) {
        send(400, ['error' => 'provide all of open/high/low/close or none']);
        return;
    }
    send(200, buildTape(
        $code, $date,
        $ohlc['open'] ?? null, $ohlc['high'] ?? null, $ohlc['low'] ?? null, $ohlc['close'] ?? null
    ));
});

// ---- dispatch ---------------------------------------------------------------
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$found = false;
foreach ($routes as $r) {
    if ($r['method'] === $method && preg_match($r['pattern'], (string) $path)) {
        $r['handler']();
        $found = true;
        break;
    }
}
if (!$found) {
    send(404, ['error' => 'not found']);
}

// ---- runnable self-check (no server needed): php mock.php --selftest -------
if (PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--selftest') {
    $t = buildTape('BIPI', '2026-08-10', 160, 180, 158, 170);
    assert($t['open'] === 160 && $t['close'] === 170);
    assert(min(array_column($t['transactions'], 'price')) >= 158);
    assert(max(array_column($t['transactions'], 'price')) <= 180);
    assert($t['transactions'][0] === ['time' => '08:58:00', 'price' => 160, 'lot' => 0, 'broker' => null, 'side' => null]);
    $last = end($t['transactions']);
    assert($last === ['time' => '16:00:00', 'price' => 170, 'lot' => 0, 'broker' => null, 'side' => null]);
    echo "selftest ok\n";
    exit;
}

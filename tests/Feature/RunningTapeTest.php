<?php

use App\Models\RunningTrade;
use App\Services\RunningTapeService;

/**
 * Boot the standalone mock server once per test and tear it down after.
 * Real HTTP round-trip: mock -> app service -> DB -> broker rollup.
 */
function startMockServer(): int
{
    $port = 8799;
    $mp = realpath(__DIR__.'/../../mock.php');
    $pidFile = sys_get_temp_dir()."/mock_{$port}.pid";

    exec(sprintf(
        'php -S localhost:%d %s >/dev/null 2>&1 & echo $! > %s',
        $port, escapeshellarg($mp), escapeshellarg($pidFile)
    ));

    for ($i = 0; $i < 30; $i++) {
        $c = @fsockopen('localhost', $port, $_, $_, 0.2);
        if ($c) { fclose($c); break; }
        usleep(100000);
    }

    register_shutdown_function(function () use ($pidFile) {
        if (file_exists($pidFile)) {
            exec('kill '.trim((string) @file_get_contents($pidFile)).' 2>/dev/null');
            @unlink($pidFile);
        }
    });

    return $port;
}

it('mock tape respects OHLC, lunch break, and is reproducible', function () {
    $port = startMockServer();
    config(['services.invezgo.base_url' => "http://localhost:$port"]);

    $url = "http://localhost:$port/running-trade?code=BIPI&date=2026-08-10&open=160&high=180&low=158&close=170";
    $a = json_decode((string) file_get_contents($url), true);
    $b = json_decode((string) file_get_contents($url), true);

    expect($a)->toBe($b); // deterministic
    expect($a['open'])->toBe(160)->and($a['close'])->toBe(170);
    expect($a['high'])->toBe(180)->and($a['low'])->toBe(158);

    expect($a['transactions'][0])->toBe([
        'time' => '08:58:00', 'price' => 160, 'lot' => 0, 'broker' => null, 'side' => null,
    ]);
    expect(end($a['transactions']))->toBe([
        'time' => '16:00:00', 'price' => 170, 'lot' => 0, 'broker' => null, 'side' => null,
    ]);

    $prices = array_column($a['transactions'], 'price');
    expect(min($prices))->toBeGreaterThanOrEqual(158);
    expect(max($prices))->toBeLessThanOrEqual(180);

    // Monday -> regular lunch 12:00-13:30 must contain no trades.
    $lunch = array_values(array_filter($a['transactions'], function ($t) {
        if ($t['broker'] === null) {
            return false;
        }
        $m = (int) substr($t['time'], 0, 2) * 60 + (int) substr($t['time'], 3, 2);
        return $m >= 12 * 60 && $m < 13 * 60 + 30;
    }));
    expect($lunch)->toBe([]);
});

it('broker summary is a consistent rollup and persists to DB', function () {
    $port = startMockServer();
    config(['services.invezgo.base_url' => "http://localhost:$port"]);

    $service = app(RunningTapeService::class);
    $code = 'BIPI';
    $date = '2026-08-10';

    $tape = $service->for($code, $date);
    $sum = $service->brokerSummary($code, $date);

    // Manual recompute straight from the tape.
    $mLot = 0; $mVal = 0; $per = [];
    foreach ($tape['transactions'] as $t) {
        if ($t['broker'] === null) {
            continue;
        }
        $val = $t['price'] * ($t['lot'] * 100);
        $mLot += $t['lot'];
        $mVal += $val;
        $per[$t['broker']] = ($per[$t['broker']] ?? 0) + $t['lot'];
    }
    expect($sum['totalLot'])->toBe($mLot);
    expect($sum['totalVal'])->toBe($mVal);
    expect($sum['avg'])->toBe((int) round($mVal / ($mLot * 100)));
    foreach ($sum['brokers'] as $br) {
        expect($br['lot'])->toBe($per[$br['code']]);
    }

    // Second call reads from DB -> identical tape.
    $tape2 = $service->for($code, $date);
    expect($tape2['transactions'])->toBe($tape['transactions']);

    // Row count matches the full tape (incl. open/close markers).
    expect(RunningTrade::forDay($code, $date)->count())->toBe(count($tape['transactions']));
});

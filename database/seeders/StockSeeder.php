<?php

namespace Database\Seeders;

use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\LazyCollection;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $csv = database_path('seeders/stocks.csv');
        if (! file_exists($csv)) {
            $this->command?->warn("Skipped: $csv not found.");

            return;
        }

        // ponytail: chunked lazy CSV read — no full-file array in memory.
        LazyCollection::make(function () use ($csv) {
            $handle = fopen($csv, 'r');
            fgetcsv($handle); // skip header
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row[0])) {
                    continue;
                }
                yield $row;
            }
            fclose($handle);
        })
            ->chunk(200)
            ->each(function (LazyCollection $chunk) {
                $rows = $chunk
                    ->map(fn ($r) => [
                        'code' => $r[0],
                        'name' => $r[1] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all();
                Stock::upsert($rows, ['code'], ['name', 'updated_at']);
            });
    }
}

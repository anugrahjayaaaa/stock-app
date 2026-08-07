<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HolidayService
{
    // ponytail: api.co.id (x-api-co-id key from config/services.php -> api_co_id.key / env API_CO_ID_KEY).
    // cached 1 year so no per-request hit. returns ISO date strings.
    public function getHolidays(int $year): array
    {
        return Cache::remember("idx_holidays_{$year}", now()->addYear(), function () use ($year) {
            $key = config('services.api_co_id.key');
            if (!$key) {
                return []; // no key -> no holidays (graceful, weekend-only skip)
            }
            $res = Http::withHeader('x-api-co-id', $key)
                ->get("https://api.co.id/api/indonesian-holidays/v1/{$year}");
            if (!$res->successful()) {
                return [];
            }
            // ponytail: shape unknown until key tested; accept ['date'=>...] or ISO string list.
            $body = $res->json();
            return collect($body)->map(function ($row) {
                if (is_string($row)) {
                    return $row;
                }
                return $row['date'] ?? $row['tanggal'] ?? null;
            })->filter()->values()->all();
        });
    }
}

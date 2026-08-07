<?php

namespace App\Services;

class TradingDayService
{
    public function __construct(private HolidayService $holidays)
    {
    }

    public function lastTradingDay(\DateTime $d): \DateTime
    {
        do {
            $w = (int) $d->format('w'); // 0 Sun, 6 Sat
            $iso = $d->format('Y-m-d');
            if ($w !== 0 && $w !== 6 && !in_array($iso, $this->holidays->getHolidays((int) $d->format('Y')), true)) {
                return $d;
            }
            $d = (clone $d)->modify('-1 day');
        } while (true);
    }

    // Default date for the analysis filter: today, Sat->Fri, Sun->Fri, holiday->prev trading day.
    public function defaultTradeDay(): string
    {
        $today = new \DateTime();
        $w = (int) $today->format('w');
        if ($w === 6) {
            $today->modify('-1 day');
        } elseif ($w === 0) {
            $today->modify('-2 days');
        }

        return $this->lastTradingDay(clone $today)->format('Y-m-d');
    }
}

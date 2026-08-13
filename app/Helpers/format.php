<?php

/**
 * Int -> short display "1.2 M" / "18.6 B" / "87,044" (IPOT/Stockbit-style magnitude suffix).
 * Used for broksum lot/value rendering so net values read like the source feed.
 */
function formatShort(int $v): string
{
    $neg = $v < 0 ? '-' : '';
    $v = abs($v);
    if ($v >= 1_000_000_000) {
        return $neg.number_format($v / 1_000_000_000, 1).' B';
    }
    if ($v >= 1_000_000) {
        return $neg.number_format($v / 1_000_000, 1).' M';
    }
    if ($v >= 1_000) {
        return $neg.number_format($v / 1_000, 1).' K';
    }

    return $neg.(string) $v;
}

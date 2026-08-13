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

/**
 * Map a Stockbit accdist label to a Bootstrap color utility (subtle bg + text).
 * Dist* → danger (red), *Acc* → success (green), else neutral.
 */
function accdistClass(string $label): string
{
    $l = strtolower($label);
    if (str_contains($l, 'dist')) {
        return 'bg-danger-subtle text-danger';
    }
    if (str_contains($l, 'acc')) {
        return 'bg-success-subtle text-success';
    }

    return 'bg-secondary-subtle text-secondary';
}

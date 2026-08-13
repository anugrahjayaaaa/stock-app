<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stored Stockbit NET bandar_detector per axis (market×investor, incl. 'all').
 * Net view reads this directly so it matches Stockbit exactly.
 */
class BroksumBandar extends Model
{
    protected $table = 'broksum_bandar';

    protected $fillable = ['stock_code', 'date', 'market_type', 'investor_type', 'bandar'];

    protected $casts = [
        'date' => 'date',
        'bandar' => 'array',
    ];
}

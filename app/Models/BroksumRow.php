<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One GROSS broksum row (a broker's buy or sell activity for a stock/date/axis).
 * Net + bandar detector are derived from these, not stored.
 */
class BroksumRow extends Model
{
    protected $table = 'broksum_rows';

    protected $fillable = [
        'stock_code', 'date', 'tx_type', 'market_type', 'investor_type',
        'broker_code', 'side', 'lot', 'val', 'avg', 'freq',
    ];

    protected $casts = [
        'date' => 'date',
        'lot' => 'integer',
        'val' => 'integer',
        'avg' => 'decimal:4',
        'freq' => 'integer',
    ];
}

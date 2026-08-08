<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunningTrade extends Model
{
    protected $fillable = ['code', 'trade_date', 'seq', 'time', 'price', 'lot', 'broker', 'side'];

    protected $casts = [
        'trade_date' => 'date',
        'lot' => 'integer',
        'price' => 'integer',
    ];

    /** All trades for a ticker/day in tape order. */
    public function scopeForDay($query, string $code, string $date)
    {
        return $query->where('code', $code)->where('trade_date', $date)->orderBy('seq');
    }
}

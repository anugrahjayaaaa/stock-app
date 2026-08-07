<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartDrawing extends Model
{
    protected $fillable = ['user_id', 'symbol', 'drawing_json'];

    protected $casts = [
        'drawing_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

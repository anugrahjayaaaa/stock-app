<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    protected $fillable = ['code', 'name', 'category'];

    protected $hidden = ['created_at', 'updated_at'];

    // Ownership -> badge class. Unknown code defaults to swasta (purple).
    public static function categoryClass(string $code): string
    {
        $cat = static::where('code', $code)->value('category');

        return match ($cat) {
            'asing' => 'bg-danger',
            'bumn'  => 'bg-success',
            default => 'bg-purple',
        };
    }
}

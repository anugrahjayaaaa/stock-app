<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    /**
     * Mass-assignable columns.
     *
     * @var array<int,string>
     */
    protected $fillable = ['code', 'name', 'category'];

    /**
     * Hidden attributes when serializing (timestamps are not needed downstream).
     *
     * @var array<int,string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Resolve the Bootstrap badge class for a broker code based on ownership.
     *
     * asing → bg-danger (red), bumn → bg-success (green), default → bg-purple (local private).
     * Unknown codes fall back to swasta (purple) so the badge always renders.
     *
     * @param  string  $code  IDX broker code, e.g. AK, CC, PD.
     * @return string Bootstrap background utility class.
     */
    public static function categoryClass(string $code): string
    {
        $cat = static::where('code', $code)->value('category');

        return match ($cat) {
            'asing' => 'bg-danger',
            'bumn' => 'bg-success',
            default => 'bg-purple',
        };
    }
}

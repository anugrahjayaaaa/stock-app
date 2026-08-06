<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['code', 'name', 'sector', 'logo'];

    protected $hidden = ['created_at', 'updated_at'];
}

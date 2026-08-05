<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends SpatiePermission
{
    use LogsActivity;

    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['name', 'guard_name'];
}
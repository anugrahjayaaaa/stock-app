<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends SpatieRole
{
    use LogsActivity;

    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['name', 'guard_name'];

    protected static $logName = 'role';
}
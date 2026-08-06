<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;
use Spatie\Activitylog\LogOptions;

class ActivityLog extends BaseActivity
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'event',
        'properties',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function getPropertiesAttribute($value)
    {
        return collect($value);
    }

    public function setPropertiesAttribute($value)
    {
        return $this->attributes['properties'] = is_array($value) ? collect($value) : $value;
    }
}
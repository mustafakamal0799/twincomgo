<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use HasFactory;

    protected $table = 'activity_log';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'logout_time' => 'datetime',
    ];
}

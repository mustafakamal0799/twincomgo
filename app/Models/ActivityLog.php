<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Facades\Activity;

class ActivityLog extends Activity
{
    use HasFactory;

    protected $table = 'activity_log';
}

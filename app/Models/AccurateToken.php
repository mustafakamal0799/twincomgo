<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccurateToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_db',
        'access_token',
        'refresh_token'
    ];
}

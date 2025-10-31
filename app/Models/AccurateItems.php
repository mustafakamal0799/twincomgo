<?php

namespace App\Models;

use App\Models\AccuratePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccurateItems extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prices()
    {
        return $this->hasMany(AccuratePrice::class, 'item_id');
    }
}

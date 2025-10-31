<?php

namespace App\Models;

use App\Models\AccurateItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccuratePrice extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'category_name', 'price', 'synced_at'];

    public function item()
    {
        return $this->belongsTo(AccurateItems::class, 'item_id');
    }
}

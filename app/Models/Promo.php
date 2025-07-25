<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;


    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'harga_asli',
        'harga_diskon',
    ];
}

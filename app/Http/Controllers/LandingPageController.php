<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index () {
        $promos = Promo::latest()->take(6)->get();
        return view('landing', compact('promos'));
    }
}

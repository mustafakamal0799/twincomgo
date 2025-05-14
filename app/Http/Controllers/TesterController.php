<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesterController extends Controller
{

    public function test () {

        return view('items.test-table');
    }
}

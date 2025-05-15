<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SyncCustomerFromAccurate;
use App\Jobs\SyncEmployeeFromAccurate;

class AccurateSyncController extends Controller
{
    public function syncEmployees()
    {
        SyncEmployeeFromAccurate::dispatch();
        return back()->with('success', 'Sinkronisasi karyawan sedang diproses.');
    }

    public function syncCustomers()
    {
        SyncCustomerFromAccurate::dispatch();
        return back()->with('success', 'Sinkronisasi customer sedang diproses.');
    }
}

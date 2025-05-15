<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index() {

        $totalUsers = User::count();
        // $totalItems = Item::count();
        $logToday = ActivityLog::whereDate('created_at', Carbon::today())->count();

        $recentLogs = ActivityLog::latest()->take(5)->get();
        // $recentItems = Item::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            // 'totalItems',
            'logToday',
            'recentLogs',
            // 'recentItems'
        ));
    }

    public function viewUser(Request $request) {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status); // pastikan ada field 'status' di tabel user
        }

        $users = $query->latest()->get();

        return view('admin.users-index', compact('users'));
    }

    public function logActivity(Request $request) {
        $query = ActivityLog::query();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('log_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Optional: filter berdasarkan tanggal (kalau kamu aktifkan input date di view)
    /*
    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }
    */

    $activities = $query->latest()->paginate(10);

    return view('admin.log-activity', compact('activities'));
    }

}

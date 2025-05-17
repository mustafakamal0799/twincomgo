<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function index() {

        $totalUsers = User::count();
        // $totalItems = Item::count();
        $logToday = Activity::whereDate('created_at', Carbon::today())->count();

        $recentLogs = Activity::latest()->take(5)->get();
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

    public function logActivity(Request $request)
    {
        $query = Activity::query();

        if ($request->filled('user')) {
            $query->where('log_name', $request->user);
        }

        if ($request->filled('status')) {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }


        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('log_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $activities = $query->latest()->paginate(10);
        return view('admin.log-activity', compact('activities'));
    }


    public function searchUser(Request $request)
    {
        $search = $request->input('q');

        $results = Activity::select('log_name')
            ->where('log_name', 'like', "%{$search}%")
            ->distinct()
            ->limit(10)
            ->get();

        return response()->json($results->pluck('log_name'));
    }


}

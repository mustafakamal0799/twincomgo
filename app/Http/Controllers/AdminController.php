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
        $logToday = Activity::whereDate('created_at', Carbon::today())->count();

        $recentLogs = Activity::latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'logToday',
            'recentLogs',
        ));
    }

    public function viewUser(Request $request) {

        $query = User::query()->orderBy('name', 'asc');

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

        $users = $query->get();
        $totalReseller = User::where('status', 'reseller')->count();
        $totalKaryawan = User::where('status', 'karyawan')->count();
        $totalAdmin    = User::where('status', 'admin')->count();
        $totalUsers = User::count();

        return view('admin.users-index', compact('users', 'totalReseller', 'totalKaryawan', 'totalAdmin', 'totalUsers'));
    }

    public function logActivity(Request $request)
    {
        $loginQuery = ActivityLog::query()
            ->where('description', 'like', '%sedang melakukan login%');

        if ($request->filled('user')) {
            $loginQuery->where('log_name', $request->user);
        }

        if ($request->filled('status')) {
            $loginQuery->whereHas('causer', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('start_date')) {
            $loginQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $loginQuery->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loginQuery->where(function ($q) use ($search) {
                $q->where('log_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $loginActivities = $loginQuery->latest()->paginate(50);

        return view('admin.log-activity', ['activities' => $loginActivities]);
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

    public function autoLogout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cari aktivitas login terakhir user yang belum memiliki logout_time
        $loginActivity = Activity::where('log_name', $user->name)
            ->where('description', 'like', '%sedang melakukan login%')
            ->whereNull('logout_time')
            ->latest('created_at')
            ->first();

        if ($loginActivity) {
            // Catat waktu logout sekarang
            $loginActivity->logout_time = now();
            $loginActivity->save();
        }

        return response()->json(['message' => 'Logout time recorded']);
    }
}

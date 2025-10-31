<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\AccurateAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function index() {

        // 🔹 Total seluruh user
        $totalUsers = User::count();

        // 🔹 Total reseller (status = RESELLER)
        $totalReseller = User::where('status', 'RESELLER')->count();

        // 🔹 Total Accurate Account (tabel accurate_accounts)
        $totalAccurate = AccurateAccount::count();

        // 🔹 Aktivitas hari ini
        $logToday = Activity::whereDate('created_at', Carbon::today())->count();

        // 🔹 Log terbaru (10 terakhir)
        $recentLogs = Activity::latest()->take(10)->get();

        // 🔹 Kirim ke view
        return view('admin.dashboard', compact(
            'totalUsers',
            'totalReseller',
            'totalAccurate',
            'logToday',
            'recentLogs'
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
            ->pluck('log_name');

        return response()->json($results);
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

    public function getCustomerUserList () {

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
        $page = request()->input('page', 1);
        $perPage = 100;
        
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $perPage,
            'fields' => 'id,name,email,suspended,customerBranchName',
            'filter.customerCategoryId' => 2701,
            'filter.suspended' => false,
        ];

        $response = Http::withHeaders($headers)->get('https://public.accurate.id/accurate/api/customer/list.do', $params);

        $custUser = [];
        $totalUsers = 0;

        if($response->successful()) {
            $custUser = $response->json()['d'] ?? [];
            $totalUsers = $response->json()['sp']['rowCount'] ?? 0;
        }

        return view ('admin.customer.list-user', [
            'custUser' => $custUser,
            'totalUsers' => $totalUsers,
            'currentPage' => $page,
            'perPage' => $perPage,
        ]);
    }

    public function create () 
    {
        $accounts = AccurateAccount::orderBy('label')->get(['id','label']);
        $user = new User();
        return view('admin.users.create', compact('user','accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6',
            'status' => 'required|string',
            'accurate_account_id' => ['nullable','exists:accurate_accounts,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'accurate_account_id' => $data['accurate_account_id'] ?? null,
        ]);

        return redirect()->route('users.create')->with('succes', 'Data berhasil ditambahkan');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.detail', compact('user'));
    }
}

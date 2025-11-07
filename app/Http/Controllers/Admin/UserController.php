<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AccurateAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = User::with('accurateAccount:id,label')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(12)->onEachSide(1)->withQueryString();

        return view('admin.users2.index', compact('users', 'search'));
    }

    public function create()
    {
        $accounts = AccurateAccount::orderBy('label')->get(['id', 'label']);
        $user = new User();
        return view('admin.users2.form', compact('user', 'accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'email'               => ['required', 'email', 'max:190', 'unique:users,email'],
            'password'            => ['required', 'string', 'min:6', 'confirmed'],
            'status'              => ['required', Rule::in(['admin', 'KARYAWAN', 'RESELLER'])],
            'accurate_account_id' => ['nullable', 'exists:accurate_accounts,id'],
        ]);

        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'password'            => Hash::make($data['password']),
            'status'              => $data['status'],
            'accurate_account_id' => $data['accurate_account_id'] ?? null,
        ]);

        return redirect()->route('users2.index')->with('ok', 'User berhasil dibuat.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $accounts = AccurateAccount::orderBy('label')->get(['id', 'label']);
        return view('admin.users2.form', compact('user', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'email'               => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password'            => ['nullable', 'string', 'min:6', 'confirmed'],
            'status'              => ['required', Rule::in(['admin', 'KARYAWAN', 'RESELLER'])],
            'accurate_account_id' => ['nullable', 'exists:accurate_accounts,id'],
        ]);

        $payload = [
            'name'                => $data['name'],
            'email'               => $data['email'],
            'status'              => $data['status'],
            'accurate_account_id' => $data['accurate_account_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect()->route('users2.index')->with('ok', 'User diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (auth()->id() === $user->id) {
            return back()->with('err', 'Tidak boleh menghapus user yang sedang login.');
        }
        $user->delete();

        return redirect()->route('users2.index')->with('ok', 'User dihapus.');
    }
}

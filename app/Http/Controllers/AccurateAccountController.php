<?php

namespace App\Http\Controllers;

use App\Models\AccurateAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AccurateAccountController extends Controller
{
    public function index()
    {
        $rows = AccurateAccount::orderBy('id','desc')->paginate(10);
        return view('admin.aa.index', compact('rows'));
    }

    public function create()
    {
        $row = new AccurateAccount();
        return view('admin.aa.form', compact('row'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'label'             => ['nullable','string','max:120'],
            'company_db_id'     => ['required','string','max:190'],
            'access_token'      => ['required','string'],
            'refresh_token'     => ['nullable','string'],
            'expires_at'        => ['nullable','date'],
            'session_id'        => ['nullable','string','max:190'],
            'scope'             => ['nullable','string'],
            'status'            => ['required','in:active,expired'],
        ]);

        $row = AccurateAccount::create([
            'label'             => $data['label'] ?? null,
            'provider'          => 'accurate',
            'company_db_id'     => $data['company_db_id'],
            'access_token_enc'  => Crypt::encryptString($data['access_token']),
            'refresh_token_enc' => !empty($data['refresh_token']) ? Crypt::encryptString($data['refresh_token']) : null,
            'expires_at'        => $data['expires_at'] ?? null,
            'session_id'        => $data['session_id'] ?? null,
            'scope'             => $data['scope'] ?? null,
            'status'            => $data['status'],
        ]);

        return redirect()->route('aa.index')->with('ok','Kepala berhasil dibuat.');
    }

    public function edit($id)
    {
        $row = AccurateAccount::findOrFail($id);
        // jangan decrypt & tampilkan token asli; tampilkan kosong (opsional hint: ***)
        return view('admin.aa.form', compact('row'));
    }

    public function update(Request $req, $id)
    {
        $row = AccurateAccount::findOrFail($id);

        $data = $req->validate([
            'label'             => ['nullable','string','max:120'],
            'company_db_id'     => ['required','string','max:190'],
            'access_token'      => ['nullable','string'], // kosongkan jika tidak ganti
            'refresh_token'     => ['nullable','string'],
            'expires_at'        => ['nullable','date'],
            'session_id'        => ['nullable','string','max:190'],
            'scope'             => ['nullable','string'],
            'status'            => ['required','in:active,expired'],
        ]);

        $payload = [
            'label'         => $data['label'] ?? null,
            'company_db_id' => $data['company_db_id'],
            'expires_at'    => $data['expires_at'] ?? null,
            'session_id'    => $data['session_id'] ?? null,
            'scope'         => $data['scope'] ?? null,
            'status'        => $data['status'],
        ];

        if (!empty($data['access_token'])) {
            $payload['access_token_enc'] = Crypt::encryptString($data['access_token']);
        }
        if (!empty($data['refresh_token'])) {
            $payload['refresh_token_enc'] = Crypt::encryptString($data['refresh_token']);
        }

        $row->update($payload);

        return redirect()->route('aa.index')->with('ok','Kepala berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $row = AccurateAccount::findOrFail($id);
        $row->delete();
        return redirect()->route('aa.index')->with('ok','Kepala dihapus.');
    }
}

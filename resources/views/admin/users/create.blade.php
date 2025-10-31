@extends('layout')

@section('content')

<style>
    .form-wrapper {
        max-width: 600px; /* Batasi lebar biar form tidak terlalu lebar */
        margin: 30px auto; /* Tengah secara horizontal */
    }

    .card {
        border-radius: 10px;
    }

    h3 {
        font-weight: 600;
        margin-bottom: 0;
    }

    .btn {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .btn + .btn {
        margin-left: 5px;
    }

    @media (max-width: 768px) {
        .form-wrapper {
            max-width: 100%;
            margin: 15px;
        }
    }
</style>

<div class="container-fluid form-wrapper">
    <div class="card p-3 shadow-sm">
        <h3>Tambah User</h3>
    </div>
    <div class="card p-4 mt-2 shadow-sm">
        <form action="{{ route('users.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nama Karyawan</label>
                <input type="text" class="form-control" name="name">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password">
            </div>
            <div class="mb-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">- Pilih status -</option>
                    <option value="RESELLER">Reseller</option>
                    <option value="admin">Admin</option>
                    <option value="KARYAWAN">Karyawan</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Kepala (Accurate Account)</label>
                <select name="accurate_account_id" class="form-select">
                    <option value="">- Tidak di-assign -</option>
                    @foreach($accounts as $a)
                    <option value="{{ $a->id }}" @selected(old('accurate_account_id',$user->accurate_account_id)==$a->id)>
                        {{ $a->label ?? ('AA#'.$a->id) }}
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih kepala supaya user ini memakai token & session kepala tersebut.</small>
            </div>
            <div class="text-end">
                <button class="btn btn-primary" type="submit">SIMPAN</button>
                <a href="{{ route('admin.user') }}" class="btn btn-danger">BATAL</a>
            </div>
        </form>
    </div>
</div>

@endsection

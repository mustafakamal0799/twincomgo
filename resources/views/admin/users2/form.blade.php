@extends('layouts.admin')

@section('content')
<style>
    /* ===== Header Section ===== */
    .page-header {
        background: linear-gradient(90deg, #1f2937, #374151);
        color: white;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .page-header h4 {
        font-weight: 700;
        margin: 0;
    }

    .page-header .btn {
        transition: all 0.2s ease;
    }

    .page-header .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.4);
    }

    /* ===== Form Card ===== */
    .form-card {
        background: white;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .form-card .card-body {
        padding: 30px;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
    }

    input.form-control, select.form-select {
        border-radius: 8px;
        border-color: #d1d5db;
        transition: all 0.2s;
    }

    input.form-control:focus,
    select.form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25);
    }

    .btn {
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .alert {
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .form-card .card-body {
            padding: 20px;
        }

        .page-header {
            flex-direction: column;
            text-align: center;
        }

        .page-header h4 {
            margin-bottom: 10px;
        }

        .page-header .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header mb-4 flex-wrap gap-3">
        <h4>
            <i class="bi bi-person-lines-fill me-2"></i> 
            {{ $user->exists ? 'Edit' : 'Tambah' }} User
        </h4>
        <a href="{{ route('users2.index') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </a>
    </div>

    {{-- Error Alert --}}
    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat beberapa kesalahan:
            <ul class="mt-2 mb-0 ps-3">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="card form-card">
        <div class="card-body">
            <form method="post" action="{{ $user->exists ? route('users2.update',$user->id) : route('users2.store') }}">
                @csrf
                @if($user->exists) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name',$user->name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password {{ $user->exists ? '(kosongkan jika tidak ganti)' : '' }}</label>
                        <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ $user->exists ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">— Pilih Status —</option>
                            <option value="admin" {{ old('status',$user->status)=='admin' ? 'selected' : '' }}>Admin</option>
                            <option value="KARYAWAN" {{ old('status',$user->status)=='KARYAWAN' ? 'selected' : '' }}>Karyawan</option>
                            <option value="RESELLER" {{ old('status',$user->status)=='RESELLER' ? 'selected' : '' }}>Reseller</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kepala (Accurate Account)</label>
                        <select name="accurate_account_id" class="form-select">
                            <option value="">— Tidak di-assign —</option>
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}" @selected(old('accurate_account_id',$user->accurate_account_id)==$a->id)>
                                    {{ $a->label ?? ('AA#'.$a->id) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">User akan memakai token & session dari kepala ini.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-dark px-4">
                        <i class="bi bi-save me-1"></i> {{ $user->exists ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('users2.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

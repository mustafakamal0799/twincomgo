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

    input.form-control, textarea.form-control, select.form-select {
        border-radius: 8px;
        border-color: #d1d5db;
        transition: all 0.2s;
    }

    input.form-control:focus,
    textarea.form-control:focus,
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
            <i class="bi bi-diagram-3 me-2"></i> 
            {{ $row->exists ? 'Edit' : 'Tambah' }} Accurate Account (Kepala)
        </h4>
        <a href="{{ route('aa.index') }}" class="btn btn-light text-dark fw-semibold">
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
            <form method="post" action="{{ $row->exists ? route('aa.update', $row->id) : route('aa.store') }}">
                @csrf
                @if($row->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Label (opsional)</label>
                    <input type="text" name="label" value="{{ old('label', $row->label) }}" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Company DB ID <span class="text-danger">*</span></label>
                    <input type="text" name="company_db_id" value="{{ old('company_db_id', $row->company_db_id) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Access Token {{ $row->exists ? '(kosongkan jika tidak ganti)' : '' }}</label>
                    <input type="text" name="access_token" value="" class="form-control" {{ $row->exists ? '' : 'required' }}>
                    @if($row->exists)
                        <small class="text-muted">Token disimpan terenkripsi. Isi hanya jika ingin mengganti.</small>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Refresh Token (opsional)</label>
                    <input type="text" name="refresh_token" value="" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expires At (opsional)</label>
                        <input type="datetime-local" name="expires_at"
                            value="{{ old('expires_at', $row->expires_at ? $row->expires_at->format('Y-m-d\TH:i') : '') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Session ID (opsional)</label>
                        <input type="text" name="session_id" value="{{ old('session_id', $row->session_id) }}" class="form-control">
                        <small class="text-muted">Bisa dikosongkan. Nanti akan diisi otomatis saat open-db.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Scope (opsional)</label>
                    <textarea name="scope" rows="2" class="form-control">{{ old('scope', $row->scope ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active"  @selected(old('status',$row->status)=='active')>Active</option>
                        <option value="expired" @selected(old('status',$row->status)=='expired')>Expired</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-dark px-4">
                        <i class="bi bi-save me-1"></i> {{ $row->exists ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('aa.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<style>
    /* ===== Header ===== */
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

    /* ===== Filter Card ===== */
    .filter-card {
        background: white;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .filter-card label {
        font-weight: 600;
        color: #374151;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border-color: #d1d5db;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25);
    }

    /* ===== Table ===== */
    .card.table-wrapper {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    thead th {
        background-color: #1f2937;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    tbody tr:hover {
        background-color: #f1f5f9;
        transition: 0.2s;
    }

    .table-scroll-container {
        max-height: calc(100vh - 330px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    .table-scroll-container::-webkit-scrollbar {
        width: 8px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .badge {
        font-size: 12px;
        padding: 6px 10px;
    }

    /* ===== Buttons ===== */
    .btn {
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
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

        .filter-card {
            padding: 10px;
        }

        table th, table td {
            font-size: 12px;
            white-space: nowrap;
        }

        .table-scroll-container {
            max-height: none;
        }
    }
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header mb-4 flex-wrap gap-3">
        <h4><i class="bi bi-people me-2"></i> Daftar User</h4>
        <a href="{{ route('users.create') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Tambah User
        </a>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card filter-card mb-4 p-3">
        <form method="GET" action="{{ route('admin.user') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="status" class="form-label">Filter Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="search" class="form-label">Cari</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Nama / Email" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i></button>
                        <a href="{{ route('admin.user') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-2 text-center">
                    @php
                        $total = 0;
                        if (request('status') === 'karyawan') $total = $totalKaryawan;
                        elseif (request('status') === 'reseller') $total = $totalReseller;
                        elseif (request('status') === 'admin') $total = $totalAdmin;
                        else $total = $totalUsers;
                    @endphp
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control text-center fw-bold" value="{{ $total }}" disabled>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="card table-wrapper shadow-sm">
        @if (count($users) > 0)
            <div class="table-scroll-container">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    @if ($user->status === 'KARYAWAN')
                                        <span class="badge bg-success">Karyawan</span>
                                    @elseif ($user->status === 'RESELLER')
                                        <span class="badge bg-danger">Reseller</span>
                                    @elseif($user->status === 'admin')
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $user->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning m-4 text-center">
                <i class="bi bi-info-circle me-1"></i> <strong>Data user tidak ditemukan.</strong>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection

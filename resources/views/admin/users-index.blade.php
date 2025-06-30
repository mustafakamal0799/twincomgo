@extends('layout')

@section('content')

<style>
    .table-scroll-container {
        max-height: 700px;
        overflow-y: auto;
    }

    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    @media only screen and (max-width: 768px) {
        table th, table td {
            font-size: 12px;
        }

        .card-header h5 {
            font-size: 16px;
        }

        .btn, .form-control {
            font-size: 12px;
            padding: 6px 8px;
        }
    }
</style>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h4 class="mb-4">Daftar User</h4>
            <form method="GET" action="{{ route('admin.user') }}">
                <div class="row g-3 align-items-end justify-content-end">
                    <div class="col-md-2">
                        <label for="status" class="form-label">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                            <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex flex-wrap align-items-end gap-2">
                            <div style="min-width: 100px; flex-grow: 1;">
                                <label for="search" class="form-label">Cari</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Nama / Email" value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.user') }}" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        @php
                            $total = 0;
                            if (request('status') === 'karyawan') $total = $totalKaryawan;
                            elseif (request('status') === 'reseller') $total = $totalReseller;
                            elseif (request('status') === 'admin') $total = $totalAdmin;
                            else $total = $totalUsers;
                        @endphp
                        <input type="text" class="form-control fw-bold text-center" value="{{ $total }}" disabled>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body px-0 pt-0 pb-2">
            @if (count($users) > 0)
                <div class="table-responsive table-scroll-container">
                    <table class="table table-striped align-middle">
                        <thead class="table-secondary">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
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
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning m-4 text-center">
                    <strong>Data user tidak ditemukan.</strong>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

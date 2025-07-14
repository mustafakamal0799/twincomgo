@extends('layout')

@section('content')

<style>
    .table-scroll-container {
        max-height: 500px;
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
                </div>
            </form>
        </div>

        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive table-scroll-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Cabang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($custUser as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['customerBranchName'] }}</td>
                            <td>{{ $user['suspended'] ? 'Nonaktif' : 'Aktif' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>                
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            {{-- Pagination --}}
            @php
                $totalPages = ceil($totalUsers / $perPage);
            @endphp

            @if ($totalPages > 1)
                <nav>
                    <ul class="pagination">
                        {{-- Tombol Sebelumnya --}}
                        <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $currentPage - 1 }}">Sebelumnya</a>
                        </li>

                        {{-- Selalu tampilkan halaman 1 --}}
                        <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                            <a class="page-link" href="?page=1">1</a>
                        </li>

                        {{-- Titik-titik jika currentPage > 4 --}}
                        @if ($currentPage > 4)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif

                        {{-- Halaman di sekitar currentPage --}}
                        @for ($i = max(2, $currentPage - 1); $i <= min($totalPages - 1, $currentPage + 1); $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                            </li>
                        @endfor

                        {{-- Titik-titik jika currentPage < totalPages - 3 --}}
                        @if ($currentPage < $totalPages - 3)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif

                        {{-- Selalu tampilkan halaman terakhir jika lebih dari 1 --}}
                        @if ($totalPages > 1)
                            <li class="page-item {{ $currentPage == $totalPages ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $totalPages }}">{{ $totalPages }}</a>
                            </li>
                        @endif

                        {{-- Tombol Selanjutnya --}}
                        <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $currentPage + 1 }}">Selanjutnya</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>

@endsection

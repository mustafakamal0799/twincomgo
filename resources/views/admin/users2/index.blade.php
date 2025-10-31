@extends('layouts.admin')

@section('content')
<style>
    /* ===== Page Header ===== */
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

    /* ===== Table Container ===== */
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-body {
        padding: 0;
    }
    .table-wrapper {
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    table {
        margin-bottom: 0;
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

    .badge {
        font-size: 12px;
        padding: 6px 10px;
    }

    /* ===== Scroll Area ===== */
    .table-scroll-container {
        max-height: calc(100vh - 300px);
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

    /* ===== Pagination ===== */
    .pagination {
        justify-content: center;
    }
    .pagination .page-item.active .page-link {
        background-color: #1f2937;
        border-color: #1f2937;
    }

    /* ===== Alerts ===== */
    .alert {
        border-radius: 10px;
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
        .table-scroll-container {
            max-height: none;
        }
    }
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header mb-4 flex-wrap gap-3">
        <h4><i class="bi bi-people me-2"></i>Kelola Pengguna</h4>
        <a href="{{ route('users2.create') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Tambah User
        </a>
    </div>

    {{-- Alert --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('err'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-x-circle me-1"></i> {{ session('err') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Form Pencarian --}}
    <div class="card border-0 shadow-sm mb-3 p-3 bg-white">
        <form method="GET" action="{{ route('users2.index') }}" class="d-flex flex-wrap align-items-end gap-3">
            <div class="flex-grow-1" style="max-width: 400px;">
                <label for="search" class="form-label fw-semibold">Cari Pengguna</label>
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search ?? '' }}">
                    <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i></button>
                    @if(!empty($search))
                        <a href="{{ route('users2.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="card border-0 shadow-sm table-wrapper">
        <div class="table-scroll-container">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="text-center">
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Akun Accurate</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td class="text-center">{{ optional($u->accurateAccount)->label ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('users2.edit', $u->id) }}" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('users2.destroy', $u->id) }}" 
                                      class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Belum ada pengguna
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection

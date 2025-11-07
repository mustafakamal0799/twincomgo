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

    .form-control,
    .form-select {
        border-radius: 8px;
        border-color: #d1d5db;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
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

    /* ===== Buttons ===== */
    .btn {
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
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
        <h4><i class="bi bi-clock-history me-2"></i> Log Aktivitas</h4>
        <a href="{{ route('admin.index') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-house me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="card filter-card mb-4 p-3">
        <form action="{{ route('admin.log') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="user-search" class="form-label">User</label>
                    <select id="user-search" name="user" class="form-select">
                        @if(request('user'))
                            <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="user" id="userId" value="{{ request('user') }}">
                </div>

                <div class="col-6 col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select" id="status">
                        <option value="">Semua Status</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label for="end_date" class="form-label">Tanggal</label>
                    <div class="dropdown w-100">
                        <button class="btn border dropdown-toggle w-100 bg-white text-start" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar3 me-1"></i> Filter Tanggal
                        </button>
                        <div class="dropdown-menu p-3 w-100">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Dari</label>
                                <input type="date" name="start_date" class="form-control" id="start_date" value="{{ request('start_date') }}" />
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Sampai</label>
                                <input type="date" name="end_date" class="form-control" id="end_date" value="{{ request('end_date') }}" />
                            </div>
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-filter me-1"></i> Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label for="search" class="form-label">Cari Log</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari log user..." value="{{ request('search') }}">
                        <button class="btn btn-dark" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('admin.log') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="card table-wrapper shadow-sm">
        <div class="table-scroll-container">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>User</th>
                        <th>Log</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Login</th>
                        <th>Logout</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $index => $activity)
                        <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $activities->firstItem() + $index }}</td>
                            <td>{{ $activity->log_name ?? '-' }}</td>
                            <td>{{ $activity->description }}</td>
                            <td class="text-center">{{ optional($activity->causer)->status ?? '-' }}</td>
                            <td class="text-center">{{ $activity->created_at->format('d-m-Y') }}</td>
                            <td class="text-center">{{ $activity->created_at->format('H:i:s') }}</td>
                            <td class="text-center">{{ $activity->logout_time ? $activity->logout_time->format('H:i:s') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Belum ada aktivitas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 🌟 Pagination Section --}}
        @if ($activities->hasPages())
            <div class="d-flex justify-content-between align-items-center flex-wrap px-4 py-3 border-top bg-light rounded-bottom">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan <strong>{{ $activities->firstItem() }}</strong>–<strong>{{ $activities->lastItem() }}</strong> dari <strong>{{ $activities->total() }}</strong> log aktivitas
                </div>
                <div class="pagination-container mb-0">
                    {{ $activities->onEachSide(0)->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    new TomSelect("#user-search", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        create: false,
        plugins: ['remove_button'],
        placeholder: 'Pilih / Cari User',
        maxOptions: 20,
        allowEmptyOption: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/log/user-search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const results = data.map(item => ({
                        id: item,
                        text: item
                    }));
                    callback(results);
                })
                .catch(() => callback());
        },
        onChange: function(value) {
            document.getElementById('userId').value = value;
            document.getElementById('filterForm').submit();
        }
    });

    const statusSelect = document.getElementById('status');
    statusSelect.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endpush

@endsection

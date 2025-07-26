@extends('layout')

@section('content')

<style>
    .table-scroll-container {
        height: 570px;
        overflow-y: auto;

        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    .table-scroll-container::-webkit-scrollbar {
        height: 8px;
        /* width: 5px; */
        background-color: #f1f1f1;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: #555;
    }

    @media (max-width: 768px) {
        .form-control,
        .form-select {
            width: 100% !important;
        }

        .form-label,
        .btn {
            font-size: 12px;
        }

        .d-flex.flex-wrap.gap-2.align-items-end.mb-2 {
            flex-direction: column;
            align-items: stretch;
        }

        table th,
        table td {
            font-size: 11px;
            white-space: nowrap;
        }

        .row.g-2.align-items-end {
            flex-direction: column;
        }

        .col-md-3, .col-md-2 {
            width: 100%;
        }

        .card-header h5 {
            font-size: 16px;
        }

        .btn i {
            margin-right: 4px;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }
</style>

<div class="container-fluid">
        <div class="card p-3 shadow-sm mb-1">
            <h5 class="mb-3">Log Aktivitas</h5>

            {{-- Form Pencarian Singkat + Filter Lanjutan --}}
            <form action="{{ route('admin.log') }}" method="GET">                
                <div class="row g-3">
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
                            <button class="btn border dropdown-toggle w-100" style="background-color: white; text-align: left;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Filter Tanggal
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
                                <button type="submit" class="btn btn-primary w-100 mt-2">Terapkan</button>
                            </div>
                        </div>
                    </div>                        
                    <div class="col-6 col-md-3">
                        <div class="d-flex flex-wrap align-items-end gap-2">
                            <div style="min-width: 250px; flex-grow: 1;">
                                <label for="search" class="form-label">Cari</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari log user..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.log') }}" class="btn btn-success d-flex align-items-center gap-1 btn-reset" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card shadow-sm p-0 position-relative">
            <div class="table-responsive p-0 table-scroll-container" id="table-container">
                <table class="table table-striped align-middle">
                    <thead class="table-secondary">
                        <tr class="text-center">
                            <th class="position-sticky top-0 z-10 text-uppercase">#</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">User</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">Log</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">Status</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">Tanggal</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">Login</th>
                            <th class="position-sticky top-0 z-10 text-uppercase">Logout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $index => $activity)
                            <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'table-warning' : '' }}">
                                <td class="text-center">{{ $activities->firstItem() + $index }}</td>
                                <td>{{ $activity->log_name ?? '-' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ optional($activity->causer)->status ?? '-' }}</td>
                                <td class="text-center">{{ $activity->created_at->format('d-m-Y') }}</td>
                                <td class="text-center">{{ $activity->created_at->format('H:i:s') }}</td>
                                <td class="text-center">{{ $activity->logout_time ? $activity->logout_time->format('H:i:s') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 p-3">
                {{-- Info Jumlah Data --}}
                <div class="text-muted small">
                    {{ $activities->firstItem() }} dari {{ $activities->lastItem() }} / {{ $activities->total() }} 
                </div>

                {{-- Pagination --}}
                <div>
                    {{ $activities->withQueryString()->links('vendor.pagination.simple-bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // TomSelect untuk kategori
        const selectedCategoryId = document.getElementById('user-search').value;

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
                    document.getElementById('filterForm').submit(); // 🔥 submit form GET
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

@extends('layout')

@section('content')

<style>
     .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    .form-control,
    .form-select {
        width: 15rem;
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

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-3">Log Aktivitas</h5>

            {{-- Form Pencarian Singkat + Filter Lanjutan --}}
            <form action="{{ route('admin.log') }}" method="GET">
                <div class="d-flex flex-wrap gap-2 align-items-end mb-2 w-100">
                    <input type="text" name="search" class="form-control" placeholder="Cari log user..." value="{{ request('search') }}" style="max-width: 250px;">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('admin.log') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-filter" aria-expanded="false" aria-controls="advanced-filter">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>

                {{-- Filter Lanjutan --}}
                <div class="collapse" id="advanced-filter">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="user-search" class="form-label">User</label>
                            <select id="user-search" name="user" class="form-select">
                                @if(request('user'))
                                    <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="status">
                                <option value="">Semua Status</option>
                                <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                                <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Dari</label>
                            <input type="date" name="start_date" class="form-control" id="start_date" value="{{ request('start_date') }}" />
                        </div>

                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Sampai</label>
                            <input type="date" name="end_date" class="form-control" id="end_date" value="{{ request('end_date') }}" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                <table class="table table-bordered table-striped align-middle text-center mb-0">
                    <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
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
                                <td>{{ $activities->firstItem() + $index }}</td>
                                <td>{{ $activity->log_name ?? '-' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ optional($activity->causer)->status ?? '-' }}</td>
                                <td>{{ $activity->created_at->format('d-m-Y') }}</td>
                                <td>{{ $activity->created_at->format('H:i:s') }}</td>
                                <td>{{ $activity->logout_time ? $activity->logout_time->format('H:i:s') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                {{-- Info Jumlah Data --}}
                <div class="text-muted small">
                    Menampilkan {{ $activities->firstItem() }} sampai {{ $activities->lastItem() }} dari total {{ $activities->total() }} data
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
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#user-search').select2({
            placeholder: 'Pilih user...',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.log.user-search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(name) {
                            return { id: name, text: name };
                        })
                    };
                },
                cache: true
            }
        });

        // Submit otomatis saat user dipilih
        $('#user-search').on('select2:select', function (e) {
            $(this).closest('form').submit();
        });

        // Auto logout saat user menutup tab atau browser
        window.addEventListener('beforeunload', function (e) {
            navigator.sendBeacon('{{ route("auto.logout") }}');
        });
    });
</script>
@endpush

@endsection

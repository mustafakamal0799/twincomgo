@extends('layout')

@section('content')

<style>
     .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    .table-scroll-container {
        max-height: 500px;
        overflow-y: auto;
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
                <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label for="user-search" class="form-label">User</label>
                            <select id="user-search" name="user" class="form-select">
                                @if(request('user'))
                                    <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                                    @endif
                            </select>
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
                                    <a href="{{ route('admin.log') }}" class="btn btn-success d-flex align-items-center gap-1 btn-reset">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive table-scroll-container" id="table-container">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-secondary text-center">
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

@extends('layout')

@section('content')

<style>
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }
    .form-control {
        width: 15rem;
    }
    .form-select {
        width: 15rem;
    }

    @media (max-width: 768px) {
        table th, table td {
            font-size: 12px;
        }

        .form-label, .form-control, .btn {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            
            <h5 class="mb-2 mb-md-0">Log Aktivitas</h5>

            {{-- Form Pencarian --}}
            <form action="{{ route('admin.log') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                
                {{-- Filter Nama User pakai Select2 --}}
                <select id="user-search" name="user" class="form-select" style="min-width: 200px;">
                    @if(request('user'))
                    <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                    @endif
                </select>
                

                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                    <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                
                {{-- Input Search Biasa --}}
                <input type="text" name="search" class="form-control" placeholder="Cari log user..."
                    value="{{ request('search') }}" />

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('admin.log') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </form>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Log</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $index => $activity)
                        <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'table-warning' : '' }}">
                            <td>{{ $activities->firstItem() + $index }}</td>
                            <td>{{ $activity->log_name ?? '-' }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ optional($activity->causer)->status ?? '-' }}</td>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                {{-- Info Jumlah Data --}}
                <div class="text-muted small">
                    Menampilkan {{ $activities->firstItem() }} sampai {{ $activities->lastItem() }} dari total {{ $activities->total() }} data
                </div>

                {{-- Pagination --}}
                <div>
                    {{ $activities->withQueryString()->links('pagination::bootstrap-5') }}
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
    });
</script>
@endpush

@endsection

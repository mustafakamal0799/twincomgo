@extends('layout')

@section('content')

<style>
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
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
            <form action="{{ route('admin.log') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari user / log"
                    value="{{ request('search') }}" />

                {{-- Filter tanggal (opsional, uncomment jika kamu pakai) --}}
                <!--
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" />
                -->

                <button type="submit" class="btn btn-success">🔍 Cari</button>
                <a href="{{ route('admin.log') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Log</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $index => $activity)
                        <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'table-warning' : '' }}">
                            <td>{{ $activities->firstItem() + $index }}</td>
                            <td>{{ $activity->log_name ?? '-' }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

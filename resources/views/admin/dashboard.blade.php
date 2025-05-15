@extends('layout')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <!-- Total Users -->
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total User</h5>
                    <h3>{{ $totalUsers }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Items -->
        {{-- <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Item</h5>
                    <h3>{{ $totalItems }}</h3>
                </div>
            </div>
        </div> --}}

        <!-- Log Hari Ini -->
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Log Hari Ini</h5>
                    <h3>{{ $logToday }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Aktivitas Terbaru -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Log Aktivitas Terbaru</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td>{{ $log->log_name ?? '-' }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada log terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Item Terbaru -->
    {{-- <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Item Terbaru</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->no }}</td>
                            <td>{{ $item->availableToSell }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada item terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div> --}}
</div>

@endsection

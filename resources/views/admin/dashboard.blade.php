@extends('layout')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <!-- Total Users -->
        <div class="col-md-4">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h4 class="card-title">Total User</h4>
                            <h1>{{ $totalUsers }}</h1>
                        </div>
                        <div class="col-3 text-center">
                            <i class="bi bi-people" style="font-size: 70px"></i>
                        </div>
                    </div>         
                </div>
            </div>
        </div>

        <!-- Log Hari Ini -->
        <div class="col-md-4">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h4 class="card-title">Log Hari Ini</h4>
                            <h1>{{ $logToday }}</h1>
                        </div>
                        <div class="col-3 text-center">
                            <i class="bi bi-clock" style="font-size: 70px"></i>
                        </div>                        
                    </div>
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
</div>

@endsection

@extends('layout')

@section('content')

<div class="container-fluid py-4">
    <div class="card p-4 bg-light text-white shadow-sm mb-4">
        <div class="row mb-3">
            <!-- Total Users -->
            <div class="col-md-6">
                <div class="card text-dark shadow-sm">
                    <div class="card-body p-4">                           
                        <div class="row">
                            <div class="col align-items-start d-flex flex-column justify-content-center">
                                <h4>User</h4>                                
                            </div>
                            <div class="text-end col-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-people" style="font-size: 40px"></i>
                            </div>                                                 
                        </div>
                        <div class="text-start">
                            <h1>{{ $totalUsers }}</h1>
                        </div> 
                    </div>
                </div>
            </div>
            <!-- Log Hari Ini -->
            <div class="col-md-6">
                <div class="card text-dark shadow-sm">
                    <div class="card-body p-4">                           
                        <div class="row">
                            <div class="col align-items-start d-flex flex-column justify-content-center">
                                <h4>Activity</h4>                                
                            </div>
                            <div class="text-end col-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock" style="font-size: 40px"></i>
                            </div>                                                 
                        </div>
                        <div class="text-start">
                            <h1>{{ $logToday }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Aktivitas Terbaru -->
        <div class="card mb-4 shadow-sm">
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
</div>

@endsection

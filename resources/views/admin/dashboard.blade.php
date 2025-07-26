@extends('layout')

@section('content')

<style>
    /* From Uiverse.io by gharsh11032000 */ 
    .card-hover {
    position: relative;
    display: flex;
    align-items: start;
    justify-content: center;
    width: 100%;
    height: 100%;
    /* padding: 36px; */
    border-radius: 24px;
    overflow: hidden;
    line-height: 1.6;
    border: 1px solid #999999;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 24px;
    color: #000000;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .content .heading {
    font-weight: 700;
    font-size: 36px;
    line-height: 1.3;
    z-index: 1;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .content .para {
    z-index: 1;
    opacity: 0.8;
    font-size: 25px;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .card-hover::before {
    content: "";
    position: absolute;
    right: -5.2rem;
    top: -5.2rem;
    width: 10.4rem;
    height: 10.4rem;
    background: #0a3cff;
    z-index: 0;
    opacity: 0;
    border-radius: 50%;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .card-hover::after {
    content: "";
    position: absolute;
    left: -5.2rem;
    bottom: -5.2rem;
    width: 10.4rem;
    height: 10.4rem;
    background: #0a3cff;
    z-index: 0;
    opacity: 0;
    border-radius: 50%;
    transition: all 0.48s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .card-hover:hover::before {
    scale: 7;
    opacity: 1;
    }

    .card-hover:hover::after {
    scale: 7;
    opacity: 1;
    }

    .card-hover:hover .content .heading {
    color: #ffffff;
    }

    .card-hover:hover .content .para {
    color: #ffffff;
    }

    .card-hover:hover {
    box-shadow: 0rem 6px 13px rgba(0, 0, 0, 0.1),
        0rem 24px 24px rgba(0, 0, 0, 0.09), 0rem 55px 33px rgba(0, 0, 0, 0.05),
        0rem 97px 39px rgba(0, 0, 0, 0.01), 0rem 152px 43px rgba(0, 0, 0, 0);
    border-color: #0a3cff;
    scale: 1.05;
    }

</style>

<div class="container-fluid p-0">
    <div class="p-4 text-white rounded-0">
        <div class="row mb-3">
            <!-- Total Users -->
            <div class="col-md-6">
                <div class="card text-dark shadow-sm card-hover" onclick="window.location.href='{{ route('admin.user') }}'" style="cursor: pointer;">
                    <div class="card-body p-4 content">                           
                        <div class="row">
                            <div class="col align-items-start d-flex flex-column justify-content-center">
                                <p class="heading">User</p>                                
                            </div>
                            <div class="text-end col-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-people heading" style="font-size: 40px"></i>
                            </div>                                                 
                        </div>
                        <div class="text-start content">
                            <p class="para">{{ $totalUsers }}</p>
                        </div> 
                    </div>
                </div>
            </div>
            <!-- Log Hari Ini -->
            <div class="col-md-6">
                <div class="card text-dark shadow-sm card-hover" onclick="window.location.href='{{ route('admin.log') }}'" style="cursor: pointer;">
                    <div class="card-body p-4 content">                           
                        <div class="row">
                            <div class="col align-items-start d-flex flex-column justify-content-center">
                                <p class="heading">Activity</p>                                
                            </div>
                            <div class="text-end col-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock heading" style="font-size: 40px"></i>
                            </div>                                                 
                        </div>
                        <div class="text-start content">
                            <p class="para">{{ $logToday }}</p>
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

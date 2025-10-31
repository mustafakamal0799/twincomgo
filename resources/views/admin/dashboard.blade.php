@extends('layouts.admin')

@section('content')
<style>
    /* ===== HEADER ===== */
    .page-header {
        background: linear-gradient(90deg, #1f2937, #374151);
        color: white;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-bottom: 1.8rem;
    }

    .page-header h4 {
        font-weight: 700;
        margin: 0;
    }

    /* ===== STAT CARDS ===== */
    .stat-card {
        position: relative;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        background: white;
        transition: all 0.4s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    .stat-card::before {
        content: "";
        position: absolute;
        top: -80px;
        right: -80px;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle at center, rgba(37,99,235,0.3), transparent 70%);
        transition: 0.4s;
    }

    .stat-card:hover::before {
        top: -100px;
        right: -100px;
        opacity: 1;
    }

    .stat-content {
        position: relative;
        z-index: 2;
        padding: 1.8rem;
        color: #1f2937;
    }

    .stat-icon {
        font-size: 38px;
        color: #2563eb;
        margin-bottom: 12px;
    }

    .stat-title {
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 4px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #111827;
    }

    /* ===== TABLE ===== */
    .log-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .log-card .card-header {
        background: #1f2937;
        color: white;
        border-radius: 14px 14px 0 0;
    }

    .log-card table {
        margin-bottom: 0;
    }

    thead th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody tr:hover {
        background: #f3f4f6;
        transition: 0.2s;
    }

    .empty-state {
        text-align: center;
        color: #6b7280;
        padding: 30px 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .stat-title { font-size: 1rem; }
        .stat-number { font-size: 1.5rem; }
        .stat-icon { font-size: 28px; }
        .page-header { text-align: center; }
    }
</style>

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-3">
        <h4><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</h4>
        <span class="text-white-50 small">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-4 mb-4">
        {{-- Total Users --}}
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user') }}'">
                <div class="stat-content text-center">
                    <i class="bi bi-people stat-icon"></i>
                    <div class="stat-title">Total User</div>
                    <div class="stat-number">{{ $totalUsers }}</div>
                </div>
            </div>
        </div>

        {{-- Total Activity Today --}}
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.log') }}'">
                <div class="stat-content text-center">
                    <i class="bi bi-clock stat-icon"></i>
                    <div class="stat-title">Aktivitas Hari Ini</div>
                    <div class="stat-number">{{ $logToday }}</div>
                </div>
            </div>
        </div>

        {{-- Accurate Accounts --}}
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('aa.index') }}'">
                <div class="stat-content text-center">
                    <i class="bi bi-diagram-3 stat-icon"></i>
                    <div class="stat-title">Accurate Accounts</div>
                    <div class="stat-number">{{ $totalAccurate ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Reseller --}}
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'">
                <div class="stat-content text-center">
                    <i class="bi bi-shop stat-icon"></i>
                    <div class="stat-title">Reseller</div>
                    <div class="stat-number">{{ $totalReseller ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOG TABLE --}}
    <div class="card log-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-activity me-2"></i> Log Aktivitas Terbaru</h5>
            <a href="{{ route('admin.log') }}" class="btn btn-light btn-sm">
                <i class="bi bi-list-ul me-1"></i> Lihat Semua
            </a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td>{{ $log->log_name ?? '-' }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ optional($log->causer)->status ?? '-' }}</td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="bi bi-info-circle me-1"></i> Tidak ada log aktivitas terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

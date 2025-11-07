@extends('layouts.admin')

@section('content')
<style>
/* ===== HEADER ===== */
.page-header {
    background: linear-gradient(90deg, #1f2937, #374151);
    color: #fff;
    border-radius: 14px;
    padding: 20px 25px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    margin-bottom: 2rem;
}
.page-header h4 {
    font-weight: 700;
    margin: 0;
}
.page-header span {
    color: #d1d5db;
    font-size: 0.9rem;
}

/* ===== STAT CARD ===== */
.stat-card {
    border: none;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: all 0.25s ease;
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}
.stat-card .stat-content {
    padding: 1.6rem 1.4rem;
    text-align: center;
    position: relative;
    z-index: 2;
}
.stat-icon {
    font-size: 38px;
    color: #1f2937;
    margin-bottom: 12px;
}
.stat-title {
    color: #4b5563;
    font-weight: 700;
    font-size: 1rem;
}
.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
}

/* ===== HOVER OVERLAY ===== */
.stat-card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(55,65,81,0.08), transparent 60%);
    opacity: 0;
    transition: 0.3s ease;
}
.stat-card:hover::after {
    opacity: 1;
}

/* ===== TABLE SECTION ===== */
.log-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    overflow: hidden;
}
.log-card .card-header {
    background: #1f2937;
    color: white;
    border: none;
    border-radius: 14px 14px 0 0;
    padding: 1rem 1.5rem;
}
.log-card .card-header h5 {
    font-weight: 700;
    margin: 0;
}
.log-card .table {
    margin-bottom: 0;
}
.log-card thead th {
    background-color: #f9fafb;
    color: #374151;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    border-bottom: 1px solid #e5e7eb;
}
.log-card tbody tr:hover {
    background-color: #f3f4f6;
    transition: 0.2s;
}
.empty-state {
    text-align: center;
    color: #6b7280;
    padding: 30px 0;
}

/* ===== BADGES ===== */
.badge-status {
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.4em 0.9em;
    border-radius: 50px;
}
.badge-status.karyawan { background: #16a34a; color: #fff; }
.badge-status.reseller { background: #dc2626; color: #fff; }
.badge-status.admin    { background: #2563eb; color: #fff; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .page-header { text-align: center; flex-direction: column; gap: .5rem; }
    .stat-number { font-size: 1.6rem; }
    .stat-icon { font-size: 28px; }
}
</style>

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-3">
        <h4><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</h4>
        <span>{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user') }}'">
                <div class="stat-content">
                    <i class="bi bi-people stat-icon"></i>
                    <div class="stat-title">Total User</div>
                    <div class="stat-number">{{ $totalUsers }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.log') }}'">
                <div class="stat-content">
                    <i class="bi bi-clock-history stat-icon"></i>
                    <div class="stat-title">Aktivitas Hari Ini</div>
                    <div class="stat-number">{{ $logToday }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('aa.index') }}'">
                <div class="stat-content">
                    <i class="bi bi-diagram-3 stat-icon"></i>
                    <div class="stat-title">Accurate Accounts</div>
                    <div class="stat-number">{{ $totalAccurate ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'">
                <div class="stat-content">
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
            <a href="{{ route('admin.log') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-list-ul me-1"></i> Lihat Semua
            </a>
        </div>
        <div class="table-responsive">
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
                            <td>
                                @php $status = strtolower(optional($log->causer)->status ?? '-'); @endphp
                                <span>{{ ucfirst($status) }}</span>
                            </td>
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

@extends('layouts.admin')

@section('page-title', 'Activity Logs')

@section('content')
<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --dark: #1e293b;
        --light: #f8fafc;
    }

    /* ===== HEADER SECTION ===== */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .page-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(90deg, #fff, #e0e7ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-description {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .back-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .stat-item:nth-child(2) { border-left-color: var(--success); }
    .stat-item:nth-child(3) { border-left-color: var(--info); }
    .stat-item:nth-child(4) { border-left-color: var(--warning); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== FILTER CARD ===== */
    .filter-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .filter-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 20px;
    }

    .filter-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        /* padding: 12px 15px; */
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        transform: translateY(-2px);
    }

    .date-filter-btn {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        /* padding: 12px 15px; */
        font-weight: 500;
        transition: all 0.3s ease;
        width: 100%;
        text-align: left;
    }

    .date-filter-btn:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .search-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
        border-radius: 12px;
        color: white;
        /* padding: 12px 12px; */
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    .reset-btn {
        background: #64748b;
        border: none;
        border-radius: 12px;
        color: white;
        /* padding: 12px; */
        transition: all 0.3s ease;
    }

    .reset-btn:hover {
        background: #475569;
        transform: translateY(-2px);
    }

    /* ===== TABLE SECTION ===== */
    .table-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .table-header {
        background: linear-gradient(135deg, #1e293b 0%, #374151 100%);
        color: white;
        padding: 25px;
        border: none;
    }

    .table-header h3 {
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-container {
        max-height: 600px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--primary) #f1f5f9;
    }

    .table-container::-webkit-scrollbar {
        width: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 10px;
    }

    .table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .table tbody tr.recent-activity {
        background: rgba(245, 158, 11, 0.05);
        border-left: 4px solid var(--warning);
    }

    .table tbody td {
        padding: 20px;
        vertical-align: middle;
        border: none;
        font-weight: 500;
    }

    /* ===== BADGES ===== */
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-karyawan {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-reseller {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .badge-admin {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .badge-other {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    /* ===== USER AVATAR ===== */
    .user-avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    /* ===== PAGINATION ===== */
    .pagination-section {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 20px 25px;
        border-radius: 0 0 20px 20px;
    }

    .pagination-info {
        color: #64748b;
        font-weight: 500;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        color: #475569;
        margin-bottom: 0.5rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        
        .table-header {
            padding: 20px;
        }
        
        .table-container {
            max-height: none;
        }
        
        .table thead th,
        .table tbody td {
            padding: 12px 8px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-content">
                <h1>
                    <i class="bi bi-clock-history me-3"></i>Activity Logs
                </h1>
                <p class="page-description">Pantau dan lacak semua aktivitas pengguna secara real-time</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn back-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item">
            <div class="stat-number">{{ $activities->total() }}</div>
            <div class="stat-label">Total Activities</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $activities->where('created_at', '>=', now()->startOfDay())->count() }}</div>
            <div class="stat-label">Today</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $activities->unique('log_name')->count() }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $activities->where('created_at', '>=', now()->subHours(1))->count() }}</div>
            <div class="stat-label">Last Hour</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="filter-card animate-card" style="animation-delay: 0.2s">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="bi bi-funnel"></i>
                Filter & Search
            </h3>
        </div>
        
        <form action="{{ route('admin.log') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                {{-- User Search --}}
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="user-search" class="form-label fw-semibold">User</label>
                    <select id="user-search" name="user" class="form-select">
                        @if(request('user'))
                            <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="user" id="userId" value="{{ request('user') }}">
                </div>

                {{-- Status Filter --}}
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select" id="status">
                        <option value="">All Status</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-xl-3 col-lg-5 col-md-6">
                    <label class="form-label fw-semibold">Date Range</label>
                    <div class="dropdown w-100">
                        <button class="btn date-filter-btn dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar3 me-2"></i>
                            @if(request('start_date') && request('end_date'))
                                {{ request('start_date') }} - {{ request('end_date') }}
                            @else
                                All Dates
                            @endif
                        </button>
                        <div class="dropdown-menu p-3" style="min-width: 300px;">
                            <div class="mb-3">
                                <label for="start_date" class="form-label fw-semibold">From</label>
                                <input type="date" name="start_date" class="form-control" id="start_date" value="{{ request('start_date') }}" />
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label fw-semibold">To</label>
                                <input type="date" name="end_date" class="form-control" id="end_date" value="{{ request('end_date') }}" />
                            </div>
                            <button type="submit" class="btn search-btn w-100">
                                <i class="bi bi-filter me-1"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <label for="search" class="form-label fw-semibold">Search Activities</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search activity logs..." value="{{ request('search') }}">
                        <button class="btn search-btn" type="submit">
                            <i class="bi bi-search me-1"></i>
                        </button>
                        <a href="{{ route('admin.log') }}" class="btn reset-btn" title="Reset Filters">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.3s">
        <div class="table-header">
            <h3>
                <i class="bi bi-activity"></i>
                Activity History
            </h3>
        </div>

        @if ($activities->count() > 0)
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th width="120">Status</th>
                            <th width="120">Date</th>
                            <th width="100">Login</th>
                            <th width="100">Logout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $index => $activity)
                            <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'recent-activity' : '' }} animate-card" 
                                style="animation-delay: {{ $loop->index * 0.05 }}s">
                                <td class="text-center fw-bold text-primary">
                                    {{ $activities->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar-sm">
                                            {{ strtoupper(substr($activity->log_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $activity->log_name ?? 'System' }}</div>
                                            @if(now()->diffInMinutes($activity->created_at) <= 60)
                                                <div class="badge bg-primary text-white small">Terbaru</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="activity-text">{{ $activity->description }}</div>
                                </td>
                                <td class="text-center">
                                    @php $status = strtolower(optional($activity->causer)->status ?? 'other'); @endphp
                                    @if ($status === 'karyawan')
                                        <span class="status-badge badge-karyawan">Karyawan</span>
                                    @elseif ($status === 'reseller')
                                        <span class="status-badge badge-reseller">Reseller</span>
                                    @elseif($status === 'admin')
                                        <span class="status-badge badge-admin">Admin</span>
                                    @else
                                        <span class="status-badge badge-other">{{ $status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold">{{ $activity->created_at->format('d M Y') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="text-success fw-bold">{{ $activity->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="text-center">
                                    @if ($activity->logout_time)
                                        <div class="text-danger fw-bold">{{ $activity->logout_time->format('H:i:s') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($activities->hasPages())
                <div class="pagination-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="pagination-info">
                            Showing <strong>{{ $activities->firstItem() }}</strong> to <strong>{{ $activities->lastItem() }}</strong> 
                            of <strong>{{ $activities->total() }}</strong> activities
                        </div>
                        <div class="pagination-container">
                            {{ $activities->appends(request()->except('page'))->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-activity empty-icon"></i>
                <h4>No Activities Found</h4>
                <p>No activity logs match your current filters</p>
                <a href="{{ route('admin.log') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                </a>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // TomSelect for user search
    new TomSelect("#user-search", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        create: false,
        plugins: ['remove_button'],
        placeholder: 'Select / Search User',
        maxOptions: 20,
        allowEmptyOption: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/log/user-search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const results = data.map(item => ({
                        id: item,
                        text: item
                    }));
                    callback(results);
                })
                .catch(() => callback());
        },
        onChange: function(value) {
            document.getElementById('userId').value = value;
            document.getElementById('filterForm').submit();
        }
    });

    // Auto-submit status filter
    const statusSelect = document.getElementById('status');
    statusSelect.addEventListener('change', function() {
        this.form.submit();
    });

    // Add loading state to form
    const form = document.getElementById('filterForm');
    const searchBtn = form.querySelector('.search-btn');
    
    form.addEventListener('submit', function() {
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Searching...';
            searchBtn.disabled = true;
        }
    });
});
</script>
@endpush

@endsection
@extends('layouts.admin')

@section('page-title', 'Dashboard Overview')

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

<div class="container-fluid py-4">

    {{-- DASHBOARD HEADER --}}
    <div class="dashboard-header animate-card">
        <div class="header-content">
            <h1>Welcome back, Administrator! 👋</h1>
            <p class="mb-3">Here's what's happening with your system today</p>
            <div class="date-display">
                <i class="bi bi-calendar3"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>

    {{-- STATISTICS GRID --}}
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card animate-card" onclick="window.location='{{ route('admin.user') }}'" style="animation-delay: 0.1s">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Total Users</div>
                            <div class="stat-number">{{ $totalUsers }}</div>
                            <div class="stat-trend trend-up">
                                <i class="bi bi-arrow-up-short"></i>
                                12% increase
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card animate-card" onclick="window.location='{{ route('admin.log') }}'" style="animation-delay: 0.2s">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Today's Activity</div>
                            <div class="stat-number">{{ $logToday }}</div>
                            <div class="stat-trend trend-up">
                                <i class="bi bi-arrow-up-short"></i>
                                8% increase
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card animate-card" onclick="window.location='{{ route('aa.index') }}'" style="animation-delay: 0.3s">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Accurate Accounts</div>
                            <div class="stat-number">{{ $totalAccurate ?? 0 }}</div>
                            <div class="stat-trend trend-down">
                                <i class="bi bi-arrow-down-short"></i>
                                3% decrease
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card animate-card" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'" style="animation-delay: 0.4s">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-shop-window"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Reseller Accounts</div>
                            <div class="stat-number">{{ $totalReseller ?? 0 }}</div>
                            <div class="stat-trend trend-up">
                                <i class="bi bi-arrow-up-short"></i>
                                15% increase
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT ACTIVITY & QUICK ACTIONS --}}
    <div class="row g-4">
        {{-- RECENT ACTIVITY --}}
        <div class="col-xl-8">
            <div class="activity-card animate-card" style="animation-delay: 0.5s">
                <div class="card-header-gradient d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h3>
                        <i class="bi bi-clock-history"></i>
                        Recent Activity
                    </h3>
                    <div class="header-actions">
                        <a href="{{ route('admin.log') }}" class="btn view-all-btn">
                            <i class="bi bi-list-ul me-2"></i>
                            View All
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table activity-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLogs as $log)
                                <tr class="animate-card" style="animation-delay: {{ $loop->index * 0.1 }}s">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="user-avatar-sm">
                                                {{ strtoupper(substr($log->log_name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="activity-details">
                                                <span class="activity-user">{{ $log->log_name ?? 'Unknown User' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-action">{{ $log->description }}</span>
                                    </td>
                                    <td>
                                        @php 
                                            $status = strtolower(optional($log->causer)->status ?? 'completed');
                                            $statusClass = $status === 'success' ? 'status-success' : 
                                                          ($status === 'warning' ? 'status-warning' : 'status-danger');
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="time-ago">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox empty-icon"></i>
                                            <h4>No recent activity</h4>
                                            <p>All activities will appear here</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="col-xl-4">
            <div class="activity-card animate-card" style="animation-delay: 0.6s">
                <div class="card-header-gradient">
                    <h3>
                        <i class="bi bi-lightning-fill"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="quick-actions">
                        <div class="actions-grid">
                            <a href="{{ route('admin.user') }}" class="action-btn">
                                <div class="action-icon">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <span class="action-label">Manage Users</span>
                            </a>
                            <a href="{{ route('admin.log') }}" class="action-btn">
                                <div class="action-icon">
                                    <i class="bi bi-activity"></i>
                                </div>
                                <span class="action-label">View Logs</span>
                            </a>
                            <a href="{{ route('aa.index') }}" class="action-btn">
                                <div class="action-icon">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <span class="action-label">Accurate Accounts</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Add hover effects and animations
document.addEventListener('DOMContentLoaded', function() {
    // Add click animation to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Add real-time clock update
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        document.querySelector('.date-display').innerHTML = 
            `<i class="bi bi-calendar3"></i> ${now.toLocaleDateString('id-ID', options)}`;
    }

    // Update clock every second
    setInterval(updateClock, 1000);
});
</script>
@endsection
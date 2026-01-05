@extends('layouts.admin')

@section('page-title', 'User Management')

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

    /* ===== SEARCH CARD ===== */
    .search-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .search-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .search-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .search-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
    }

    .search-form {
        display: flex;
        gap: 15px;
        align-items: end;
    }

    .search-input {
        flex: 1;
        max-width: 400px;
    }

    .form-control {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        transform: translateY(-2px);
    }

    .search-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px 25px;
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
        padding: 12px 15px;
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

    .table tbody td {
        padding: 20px;
        vertical-align: middle;
        border: none;
        font-weight: 500;
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

    /* ===== ACTION BUTTONS ===== */
    .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        margin: 0 2px;
    }

    .btn-edit {
        background: rgba(6, 182, 212, 0.1);
        color: var(--info);
    }

    .btn-edit:hover {
        background: var(--info);
        color: white;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .btn-delete:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    /* ===== ACCOUNT BADGE ===== */
    .account-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-accurate {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-no-account {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.2);
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

    /* ===== ALERTS ===== */
    .alert {
        border-radius: 16px;
        border: none;
        padding: 20px;
        margin-bottom: 2rem;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
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
        
        .search-form {
            flex-direction: column;
        }
        
        .search-input {
            max-width: none;
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
                <h1>👥 User Management</h1>
                <p class="page-description">Manage and monitor all user accounts in your system</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn back-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item">
            <div class="stat-number">{{ $users->total() }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $users->where('accurateAccount', '!=', null)->count() }}</div>
            <div class="stat-label">With Accurate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $users->count() }}</div>
            <div class="stat-label">Showing</div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show animate-card" style="animation-delay: 0.2s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('ok') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('err'))
        <div class="alert alert-danger alert-dismissible fade show animate-card" style="animation-delay: 0.2s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('err') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- SEARCH CARD --}}
    <div class="search-card animate-card" style="animation-delay: 0.3s">
        <div class="search-header">
            <div class="search-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="search-title">Search Users</h3>
        </div>
        
        <form method="GET" action="{{ route('users2.index') }}" class="search-form">
            <div class="search-input">
                <label for="search" class="form-label fw-semibold">Search by name or email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Enter name or email..." value="{{ $search ?? '' }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn search-btn">
                    <i class="bi bi-search me-1"></i>Search
                </button>
                @if(!empty($search))
                    <a href="{{ route('users2.index') }}" class="btn reset-btn" title="Reset Search">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.4s">
        <div class="table-header">
            <h3>
                <i class="bi bi-people-fill"></i>
                Users List
            </h3>
        </div>

        @if ($users->count() > 0)
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>User Information</th>
                            <th width="200">Accurate Account</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $u)
                            <tr class="animate-card" style="animation-delay: {{ $loop->index * 0.05 }}s">
                                <td class="text-center fw-bold text-primary">
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar-sm">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($u->accurateAccount)
                                        <span class="account-badge badge-accurate">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ $u->accurateAccount->label }}
                                        </span>
                                    @else
                                        <span class="account-badge badge-no-account">
                                            <i class="bi bi-dash-circle me-1"></i>
                                            No Account
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('users2.edit', $u->id) }}" 
                                           class="btn action-btn btn-edit" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('users2.destroy', $u->id) }}" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="btn action-btn btn-delete" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($users->hasPages())
                <div class="pagination-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="pagination-info">
                            Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> 
                            of <strong>{{ $users->total() }}</strong> users
                        </div>
                        <div class="pagination-container">
                            {{ $users->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-people empty-icon"></i>
                <h4>No Users Found</h4>
                <p>No users match your current search criteria</p>
                <a href="{{ route('users2.index') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Search
                </a>
            </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

    // Add loading state to search
    const form = document.querySelector('form');
    const searchBtn = form?.querySelector('.search-btn');
    
    if (form && searchBtn) {
        form.addEventListener('submit', function() {
            searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Searching...';
            searchBtn.disabled = true;
        });
    }

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
@endsection
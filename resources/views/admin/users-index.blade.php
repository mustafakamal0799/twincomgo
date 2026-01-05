@extends('layouts.admin')

@section('page-title', 'Kelola Pengguna')

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/user.css') }}">
@endpush

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-content">
                <h1>👥 Kelola Pengguna</h1>
                <p class="page-description">Kelola dan pantau semua akun pengguna di sistem Anda</p>
            </div>
            <a href="{{ route('users2.create') }}" class="btn add-user-btn">
                <i class="bi bi-person-plus"></i>Add New User
            </a>
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

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item" onclick="window.location='{{ route('admin.user') }}'">
            <div class="stat-number">{{ $totalUsers }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'karyawan']) }}'">
            <div class="stat-number">{{ $totalKaryawan ?? 0 }}</div>
            <div class="stat-label">Karyawan</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'">
            <div class="stat-number">{{ $totalReseller ?? 0 }}</div>
            <div class="stat-label">Reseller</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'admin']) }}'">
            <div class="stat-number">{{ $totalAdmin ?? 0 }}</div>
            <div class="stat-label">Admin</div>
        </div>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-card" style="animation-delay: 0.2s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="filter-card animate-card" style="animation-delay: 0.3s">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="bi bi-funnel"></i>
                Filter & Search
            </h3>
        </div>
        
        <form method="GET" action="{{ route('admin.user') }}">
            <div class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="status" class="form-label fw-semibold">User Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Users</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="col-xl-5 col-lg-6 col-md-8">
                    <label for="search" class="form-label fw-semibold">Search Users</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control border-start-0" 
                               placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <label class="form-label fw-semibold">Results</label>
                    <div class="form-control text-center fw-bold text-primary bg-light">
                        @php
                            $total = 0;
                            if (request('status') === 'karyawan') $total = $totalKaryawan;
                            elseif (request('status') === 'reseller') $total = $totalReseller;
                            elseif (request('status') === 'admin') $total = $totalAdmin;
                            else $total = $totalUsers;
                        @endphp
                        {{ $total }}
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn search-btn flex-fill">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                        <a href="{{ route('admin.user') }}" class="btn reset-btn" title="Reset Filters">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
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

        @if (count($users) > 0)
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="80">#</th>
                            <th>User Information</th>
                            <th width="150">Status</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="animate-card" style="animation-delay: {{ $loop->index * 0.05 }}s">
                                <td class="text-center fw-bold text-primary">
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($user->status === 'KARYAWAN')
                                        <span class="status-badge badge-karyawan">Karyawan</span>
                                    @elseif ($user->status === 'RESELLER')
                                        <span class="status-badge badge-reseller">Reseller</span>
                                    @elseif($user->status === 'admin')
                                        <span class="status-badge badge-admin">Admin</span>
                                    @else
                                        <span class="status-badge badge-other">{{ $user->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('users.show', $user->id) }}" 
                                           class="btn action-btn btn-view" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('users2.edit', $user->id) }}" 
                                           class="btn action-btn btn-edit" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('users2.destroy', $user->id) }}" 
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
        @else
            <div class="empty-state">
                <i class="bi bi-people empty-icon"></i>
                <h4>No Users Found</h4>
                <p>No users match your current filters</p>
                <a href="{{ route('admin.user') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                </a>
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit status filter
        const statusSelect = document.getElementById('status');
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

        // Add loading state to search
        const form = document.querySelector('form');
        const searchBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Searching...';
            searchBtn.disabled = true;
        });
    });
</script>

<style>
    .user-avatar-sm {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
</style>
@endsection
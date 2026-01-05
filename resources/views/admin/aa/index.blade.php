@extends('layouts.admin')

@section('page-title', 'Accurate Token')

@section('content')
<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --dark: #1e293b;
        --light: #f8fafc;
    }

    /* ===== HEADER SECTION ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(15, 118, 110, 0.2);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(90deg, #fff, #e0f2fe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-description {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .back-btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.15);
        color: white;
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #f1f5f9;
    }

    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }

    .stat-item:nth-child(2) { border-left-color: var(--success); }
    .stat-item:nth-child(3) { border-left-color: var(--info); }
    .stat-item:nth-child(4) { border-left-color: var(--warning); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-description {
        color: #94a3b8;
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }

    /* ===== FILTER CARD ===== */
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        border: 1px solid #f1f5f9;
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .filter-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.25rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.1);
        transform: translateY(-2px);
    }

    .search-btn {
        background: var(--primary);
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15, 118, 110, 0.3);
    }

    .reset-btn {
        background: #64748b;
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .reset-btn:hover {
        background: #475569;
        transform: translateY(-2px);
    }

    /* ===== ACCOUNT CARDS GRID ===== */
    .accounts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .account-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .account-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }

    .account-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .account-card.expired::before {
        background: linear-gradient(90deg, var(--danger), #dc2626);
    }

    .account-card.inactive::before {
        background: linear-gradient(90deg, #64748b, #475569);
    }

    .account-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .account-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .account-card.expired .account-icon {
        background: linear-gradient(135deg, var(--danger), #dc2626);
    }

    .account-card.inactive .account-icon {
        background: linear-gradient(135deg, #64748b, #475569);
    }

    .account-info {
        flex: 1;
        margin-left: 1rem;
    }

    .account-label {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    .account-company {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .account-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid transparent;
    }

    .status-active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .status-inactive {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border-color: rgba(100, 116, 139, 0.2);
    }

    .status-expired {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* ===== ACCOUNT DETAILS ===== */
    .account-details {
        margin: 1rem 0;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #64748b;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .detail-value {
        color: var(--dark);
        font-weight: 600;
        font-size: 0.85rem;
    }

    .session-id {
        font-family: 'Courier New', monospace;
        background: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        color: #475569;
        border: 1px solid #e2e8f0;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ===== ACTION BUTTONS ===== */
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .action-btn {
        flex: 1;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-edit {
        background: var(--primary);
        color: white;
    }

    .btn-edit:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        color: white;
    }

    .btn-delete {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-delete:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1.25rem;
        color: #64748b;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
    }

    .empty-icon {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--primary);
    }

    .empty-state h4 {
        color: #475569;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    /* ===== PAGINATION ===== */
    .pagination-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        margin-top: 2rem;
        border: 1px solid #e2e8f0;
    }

    .pagination-info {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .pagination {
        margin: 0;
    }

    .page-link {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #64748b;
        font-weight: 600;
        margin: 0 4px;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* ===== ADD BUTTON ===== */
    .add-btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .add-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.15);
        color: white;
    }

    /* ===== ALERT ===== */
    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .page-header .d-flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .accounts-grid {
            grid-template-columns: 1fr;
        }
        
        .account-header {
            flex-direction: column;
            gap: 1rem;
        }
        
        .account-info {
            margin-left: 0;
            text-align: center;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .pagination-section {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
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
</style>

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="header-content">
                <h1>
                    <i class="bi bi-diagram-3"></i>
                    Accurate Token
                </h1>
                <p class="page-description">Manage and monitor all Accurate token in your system</p>
            </div>
            <a href="{{ route('aa.create') }}" class="btn add-btn">
                <i class="bi bi-plus-circle me-1"></i>Add Token
            </a>
        </div>
    </div>

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item">
            <div class="stat-number">{{ $rows->total() }}</div>
            <div class="stat-label">Total Accounts</div>
            <div class="stat-description">All Accurate accounts</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $rows->where('status', 'active')->count() }}</div>
            <div class="stat-label">Active</div>
            <div class="stat-description">Currently active accounts</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $rows->where('status', 'inactive')->count() }}</div>
            <div class="stat-label">Inactive</div>
            <div class="stat-description">Disabled accounts</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $rows->where('expires_at', '<', now())->count() }}</div>
            <div class="stat-label">Expired</div>
            <div class="stat-description">Expired sessions</div>
        </div>
    </div>

    {{-- ALERT SUCCESS --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show animate-card mb-4" style="animation-delay: 0.1s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div class="flex-grow-1">{{ session('ok') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- ACCOUNTS GRID --}}
    @if($rows->count() > 0)
        <div class="accounts-grid">
            @foreach($rows as $r)
                @php
                    $statusClass = 'status-inactive';
                    $cardClass = 'inactive';
                    if ($r->status === 'active') {
                        $statusClass = 'status-active';
                        $cardClass = '';
                    } elseif ($r->expires_at && $r->expires_at->isPast()) {
                        $statusClass = 'status-expired';
                        $cardClass = 'expired';
                    }
                @endphp
                
                <div class="account-card {{ $cardClass }} animate-card" style="animation-delay: {{ $loop->index * 0.05 }}s">
                    <div class="account-header">
                        <div class="account-icon">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div class="account-info">
                            <div class="account-label">{{ $r->label ?? 'Unlabeled Account' }}</div>
                            <div class="account-company">{{ $r->company_db_id ?? 'No Company DB' }}</div>
                        </div>
                        <span class="account-status {{ $statusClass }}">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i>
                            {{ $r->status === 'active' ? 'Active' : ($r->expires_at && $r->expires_at->isPast() ? 'Expired' : 'Inactive') }}
                        </span>
                    </div>

                    <div class="account-details">
                        <div class="detail-item">
                            <span class="detail-label">Session ID:</span>
                            <span class="session-id" data-bs-toggle="tooltip" title="{{ $r->session_id }}">
                                {{ $r->session_id ? \Illuminate\Support\Str::limit($r->session_id, 12) : '—' }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Expires:</span>
                            <span class="detail-value">
                                @if($r->expires_at)
                                    {{ $r->expires_at->format('M d, Y H:i') }}
                                @else
                                    Never
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Created:</span>
                            <span class="detail-value">{{ $r->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('aa.edit', $r->id) }}" 
                           class="action-btn btn-edit" 
                           data-bs-toggle="tooltip" 
                           title="Edit Account">
                            <i class="bi bi-pencil"></i>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('aa.destroy', $r->id) }}" 
                              class="d-inline w-100"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus account ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn btn-delete w-100" data-bs-toggle="tooltip" title="Delete Account">
                                <i class="bi bi-trash"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state animate-card" style="animation-delay: 0.3s">
            <i class="bi bi-diagram-3 empty-icon"></i>
            <h4>Belum ada data kepala</h4>
            <p>Mulai dengan menambahkan Accurate Account pertama Anda</p>
            <a href="{{ route('aa.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Kepala
            </a>
        </div>
    @endif

    {{-- PAGINATION --}}
    @if($rows->hasPages())
        <div class="pagination-section animate-card" style="animation-delay: 0.4s">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="pagination-info">
                    Menampilkan <strong>{{ $rows->firstItem() }}</strong> –
                    <strong>{{ $rows->lastItem() }}</strong> dari
                    <strong>{{ $rows->total() }}</strong> hasil
                </div>
                <div>
                    {{ $rows->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

        // Auto-submit status filter
        const statusSelect = document.getElementById('status');
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });

        // Add loading state to search
        const form = document.getElementById('filterForm');
        const searchBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            if (searchBtn) {
                const originalHTML = searchBtn.innerHTML;
                searchBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Searching...';
                searchBtn.disabled = true;
                
                // Revert after 3 seconds (in case of error)
                setTimeout(() => {
                    searchBtn.innerHTML = originalHTML;
                    searchBtn.disabled = false;
                }, 3000);
            }
        });

        // Add loading state to delete buttons
        const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('button[type="submit"]');
                const originalHTML = button.innerHTML;
                
                button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
                button.disabled = true;
                
                // Revert after 3 seconds (in case of error)
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 3000);
            });
        });

        // Auto-hide success alert after 5 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            }, 5000);
        }
    });
</script>
@endsection
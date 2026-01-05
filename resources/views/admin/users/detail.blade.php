@extends('layouts.admin')

@section('page-title', 'User Details')

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

    /* ===== PROFILE GRID ===== */
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ===== PROFILE CARD ===== */
    .profile-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .profile-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        font-size: 3rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        border: 4px solid white;
        position: relative;
    }

    .profile-avatar::after {
        content: "";
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        z-index: -1;
        opacity: 0.6;
        filter: blur(10px);
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .profile-email {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .profile-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-karyawan {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-reseller {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .status-admin {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .status-other {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    .profile-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== DETAILS CARD ===== */
    .details-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }

    .details-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--success), #059669);
    }

    .details-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 2rem;
    }

    .details-header h3 {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .details-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--success), #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .details-grid {
        display: grid;
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .detail-item:hover {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem 1rem;
        transform: translateX(5px);
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #64748b;
    }

    .detail-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        background: rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 0.9rem;
    }

    .detail-value {
        font-weight: 700;
        color: var(--dark);
        text-align: right;
    }

    .detail-meta {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    /* ===== ACTIVITY CARD ===== */
    .activity-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-top: 2rem;
    }

    .activity-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--info), #0891b2);
    }

    .activity-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.5rem;
    }

    .activity-header h3 {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .activity-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--info), #0891b2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .activity-list {
        display: grid;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #f1f5f9;
        transform: translateX(5px);
    }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        font-weight: 500;
        color: var(--dark);
        margin-bottom: 4px;
    }

    .activity-time {
        font-size: 0.85rem;
        color: #94a3b8;
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
        
        .profile-grid {
            gap: 1.5rem;
        }
        
        .profile-card,
        .details-card,
        .activity-card {
            padding: 20px;
        }
        
        .profile-stats {
            grid-template-columns: 1fr;
        }
        
        .detail-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        
        .detail-value {
            text-align: left;
        }
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-content">
                <h1>👤 User Details</h1>
                <p class="page-description">Complete information and activity history for {{ $user->name }}</p>
            </div>
            <a href="{{ route('admin.user') }}" class="btn back-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    {{-- PROFILE GRID --}}
    <div class="profile-grid">
        {{-- PROFILE CARD --}}
        <div class="profile-card animate-card" style="animation-delay: 0.1s">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            
            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-email">{{ $user->email }}</div>
            
            @if ($user->status === 'KARYAWAN')
                <div class="profile-status status-karyawan">
                    <i class="bi bi-person-check"></i>Karyawan
                </div>
            @elseif ($user->status === 'RESELLER')
                <div class="profile-status status-reseller">
                    <i class="bi bi-shop"></i>Reseller
                </div>
            @elseif ($user->status === 'admin')
                <div class="profile-status status-admin">
                    <i class="bi bi-shield-check"></i>Admin
                </div>
            @else
                <div class="profile-status status-other">
                    <i class="bi bi-person"></i>{{ ucfirst($user->status) }}
                </div>
            @endif

            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $user->created_at->diffInDays(now()) }}</div>
                    <div class="stat-label">Days Active</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $user->id }}</div>
                    <div class="stat-label">User ID</div>
                </div>
            </div>
        </div>

        {{-- DETAILS CARD --}}
        <div class="details-card animate-card" style="animation-delay: 0.2s">
            <div class="details-header">
                <div class="details-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <h3>User Information</h3>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-person"></i>
                        </div>
                        Full Name
                    </div>
                    <div class="detail-value">{{ $user->name }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        Email Address
                    </div>
                    <div class="detail-value">{{ $user->email }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        Province
                    </div>
                    <div class="detail-value">
                        {{ $user->province ?? 'Not specified' }}
                        @if(!$user->province)
                            <div class="detail-meta">Location not provided</div>
                        @endif
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        Member Since
                    </div>
                    <div class="detail-value">
                        {{ $user->created_at->format('d M Y') }}
                        <div class="detail-meta">{{ $user->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        Last Updated
                    </div>
                    <div class="detail-value">
                        {{ $user->updated_at->format('d M Y, H:i') }}
                        <div class="detail-meta">{{ $user->updated_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <div class="detail-icon">
                            <i class="bi bi-shield"></i>
                        </div>
                        Account Status
                    </div>
                    <div class="detail-value">
                        @if ($user->status === 'KARYAWAN')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVITY CARD --}}
    <div class="activity-card animate-card" style="animation-delay: 0.3s">
        <div class="activity-header">
            <div class="activity-icon">
                <i class="bi bi-activity"></i>
            </div>
            <h3>Recent Activity</h3>
        </div>

        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                    <div class="activity-text">Account created</div>
                    <div class="activity-time">{{ $user->created_at->diffForHumans() }}</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                    <div class="activity-text">Profile last updated</div>
                    <div class="activity-time">{{ $user->updated_at->diffForHumans() }}</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                    <div class="activity-text">Last login</div>
                    <div class="activity-time">2 hours ago</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                    <div class="activity-text">Password changed</div>
                    <div class="activity-time">1 week ago</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.profile-card, .details-card, .activity-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endsection
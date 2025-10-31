@extends('layouts.admin')

@section('content')
<style>
    /* ===== PAGE HEADER ===== */
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

    /* ===== PROFILE CARD ===== */
    .profile-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-radius: 18px;
        border: none;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        padding: 2.5rem;
        max-width: 850px;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
    }

    .profile-card::before {
        content: "";
        position: absolute;
        top: -80px;
        right: -80px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle at center, #60a5fa33, transparent 70%);
    }

    .avatar-wrapper {
        text-align: center;
        margin-bottom: 25px;
    }

    .avatar-wrapper .avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: linear-gradient(135deg, #60a5fa, #2563eb);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        margin: 0 auto 15px;
        position: relative;
    }

    .avatar::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: linear-gradient(135deg, #93c5fd, #3b82f6);
        z-index: -1;
        filter: blur(12px);
        opacity: 0.4;
    }

    .user-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .user-email {
        color: #6b7280;
        font-size: 0.95rem;
    }

    /* ===== INFO GRID ===== */
    .info-section {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.2rem 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        background: white;
        padding: 1rem 1.2rem;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        border-left: 4px solid transparent;
        transition: 0.25s;
    }

    .info-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #2563eb;
    }

    .info-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    /* ===== STATUS BADGES ===== */
    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.9em;
        border-radius: 50px;
    }

    /* ===== BUTTON ===== */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s ease;
        background-color: #1f2937;
        color: white;
        border: none;
    }

    .btn-back:hover {
        transform: translateY(-2px);
        background-color: #111827;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .page-header {
            text-align: center;
        }

        .btn-back {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-3">
        <h4><i class="bi bi-person-badge me-2"></i> Detail Karyawan</h4>
        <a href="{{ route('admin.user') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </a>
    </div>

    {{-- Profile Card --}}
    <div class="profile-card">
        <div class="avatar-wrapper">
            <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h4 class="user-name">{{ $user->name }}</h4>
            <p class="user-email mb-1">{{ $user->email }}</p>
        </div>

        {{-- Info Section --}}
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if ($user->status === 'KARYAWAN')
                            <span class="badge bg-success">Karyawan</span>
                        @elseif ($user->status === 'RESELLER')
                            <span class="badge bg-danger">Reseller</span>
                        @elseif($user->status === 'admin')
                            <span class="badge bg-primary">Admin</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Provinsi</div>
                    <div class="info-value">{{ $user->province ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Dibuat Pada</div>
                    <div class="info-value">{{ $user->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Terakhir Diperbarui</div>
                    <div class="info-value">{{ $user->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('admin.user') }}" class="btn btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection

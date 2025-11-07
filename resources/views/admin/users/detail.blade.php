@extends('layouts.admin')

@section('content')
<style>
/* ===== GENERAL ===== */
body {
    background-color: #f3f4f6;
}

.page-header {
    background: linear-gradient(90deg, #1f2937, #374151);
    color: #fff;
    border-radius: 14px;
    padding: 1.2rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* ===== PROFILE SECTION ===== */
.profile-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    align-items: stretch;
}

/* LEFT SIDE (Profile Overview) */
.profile-side {
    flex: 1 1 260px;
    background: white;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 2rem 1.5rem;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    text-align: center;
    transition: all 0.25s ease;
}
.profile-side:hover {
    transform: translateY(-3px);
}

.profile-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(145deg, #2563eb, #1e3a8a);
    color: #fff;
    font-size: 40px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 5px 15px rgba(37,99,235,0.3);
}

.profile-side h4 {
    font-weight: 700;
    margin-bottom: .25rem;
}
.profile-side p {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* RIGHT SIDE (Detail Table) */
.detail-side {
    flex: 2 1 500px;
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 2rem 2.5rem;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
}

.detail-side h5 {
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 1.4rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: .75rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.detail-item:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 600;
    color: #4b5563;
}
.detail-value {
    font-weight: 600;
    color: #111827;
    text-align: right;
}

/* BADGE STYLE */
.badge {
    font-size: 0.85rem;
    border-radius: 30px;
    padding: 0.35em 0.9em;
}
.badge.bg-success { background-color: #10b981 !important; }
.badge.bg-danger  { background-color: #ef4444 !important; }
.badge.bg-primary { background-color: #3b82f6 !important; }

/* RESPONSIVE */
@media (max-width: 992px) {
    .profile-wrapper { flex-direction: column; }
    .detail-side { padding: 1.5rem; }
}
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i> Detail Karyawan</h4>
        <a href="{{ route('admin.user') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </a>
    </div>

    {{-- PROFILE CONTENT --}}
    <div class="profile-wrapper">

        {{-- Left Side (Avatar + Basic Info) --}}
        <div class="profile-side">
            <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h4>{{ $user->name }}</h4>
            <p>{{ $user->email }}</p>
            <hr>
            @if ($user->status === 'KARYAWAN')
                <span class="badge bg-success">Karyawan</span>
            @elseif ($user->status === 'RESELLER')
                <span class="badge bg-danger">Reseller</span>
            @elseif ($user->status === 'admin')
                <span class="badge bg-primary">Admin</span>
            @else
                <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
            @endif
        </div>

        {{-- Right Side (Detailed Info) --}}
        <div class="detail-side">
            <h5>Informasi Lengkap</h5>

            <div class="detail-item">
                <span class="detail-label">Nama Lengkap</span>
                <span class="detail-value">{{ $user->name }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $user->email }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Provinsi</span>
                <span class="detail-value">{{ $user->province ?? '-' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Dibuat Pada</span>
                <span class="detail-value">{{ $user->created_at->format('d M Y, H:i') }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Terakhir Diperbarui</span>
                <span class="detail-value">{{ $user->updated_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

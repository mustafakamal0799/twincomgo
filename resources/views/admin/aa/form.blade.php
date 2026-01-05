@extends('layouts.admin')

@section('page-title', $row->exists ? 'Edit Accurate Account' : 'Tambah Accurate Account')

@section('content')
<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary: #0d9488;
        --primary-dark: #0f766e;
        --primary-darker: #115e59;
        --secondary: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
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

    /* ===== FORM CARD ===== */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #f1f5f9;
        margin-bottom: 2rem;
    }

    .form-header {
        background: white;
        color: var(--dark);
        padding: 1.5rem 1.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-header h3 {
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.25rem;
    }

    .form-body {
        padding: 2rem;
    }

    /* ===== FORM STYLES ===== */
    .form-label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-label .required {
        color: var(--danger);
    }

    .form-control, .form-select, .form-textarea {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: white;
        color: var(--dark);
    }

    .form-control:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.1);
        transform: translateY(-2px);
        outline: none;
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-hint {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .form-hint i {
        font-size: 0.7rem;
    }

    /* ===== ALERT STYLES ===== */
    .alert {
        border-radius: 12px;
        border: none;
        padding: 1.25rem;
        margin-bottom: 2rem;
        border: 1px solid transparent;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.2);
    }

    .alert-danger ul {
        margin: 0.5rem 0 0 0;
        padding-left: 1.5rem;
    }

    .alert-danger li {
        margin: 0.25rem 0;
    }

    /* ===== BUTTON STYLES ===== */
    .btn {
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15, 118, 110, 0.3);
        color: white;
    }

    .btn-secondary {
        background: #64748b;
        color: white;
    }

    .btn-secondary:hover {
        background: #475569;
        transform: translateY(-2px);
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .btn-outline:hover {
        background: #f8fafc;
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* ===== FORM SECTIONS ===== */
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: var(--primary);
    }

    /* ===== GRID LAYOUT ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .form-full-width {
        grid-column: 1 / -1;
    }

    /* ===== STATUS BADGE PREVIEW ===== */
    .status-preview {
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
        margin-left: 10px;
    }

    .status-active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .status-expired {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.2);
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
        
        .form-body {
            padding: 1.5rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .btn-group {
            flex-direction: column;
            width: 100%;
        }
        
        .btn {
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .form-body {
            padding: 1rem;
        }
        
        .form-header {
            padding: 1.25rem;
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
                    {{ $row->exists ? 'Edit Accurate Account' : 'Tambah Accurate Account' }}
                </h1>
                <p class="page-description">
                    {{ $row->exists ? 'Update informasi Accurate Account' : 'Buat Accurate Account baru' }}
                </p>
            </div>
            <a href="{{ route('aa.index') }}" class="btn back-btn">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if($errors->any())
        <div class="alert alert-danger animate-card" style="animation-delay: 0.1s">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="flex-grow-1">
                    <strong>Terdapat beberapa kesalahan:</strong>
                    <ul class="mt-2 mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM CARD --}}
    <div class="form-card animate-card" style="animation-delay: 0.2s">
        <div class="form-header">
            <h3>
                <i class="bi bi-pencil-square text-primary"></i>
                Form Accurate Account
            </h3>
        </div>

        <div class="form-body">
            <form method="post" action="{{ $row->exists ? route('aa.update', $row->id) : route('aa.store') }}" id="accountForm">
                @csrf
                @if($row->exists) 
                    @method('PUT') 
                @endif

                {{-- BASIC INFORMATION SECTION --}}
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-info-circle"></i>
                        Informasi Dasar
                    </h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Label <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="label" 
                                   value="{{ old('label', $row->label) }}" 
                                   class="form-control" 
                                   placeholder="Masukkan label account">
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Nama atau label untuk mengidentifikasi account
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Company DB ID <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="company_db_id" 
                                   value="{{ old('company_db_id', $row->company_db_id) }}" 
                                   class="form-control" 
                                   required
                                   placeholder="Masukkan Company DB ID">
                            <div class="form-hint">
                                <i class="bi bi-database"></i>
                                ID database company dari Accurate
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOKEN SECTION --}}
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Token & Keamanan
                    </h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Access Token <span class="required">{{ $row->exists ? '' : '*' }}</span>
                            </label>
                            <input type="text" 
                                   name="access_token" 
                                   value="" 
                                   class="form-control" 
                                   {{ $row->exists ? '' : 'required' }}
                                   placeholder="Masukkan access token">
                            <div class="form-hint">
                                <i class="bi bi-key"></i>
                                @if($row->exists)
                                    Token disimpan terenkripsi. Isi hanya jika ingin mengganti token.
                                @else
                                    Access token dari Accurate API
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Refresh Token <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="refresh_token" 
                                   value="" 
                                   class="form-control" 
                                   placeholder="Masukkan refresh token">
                            <div class="form-hint">
                                <i class="bi bi-arrow-repeat"></i>
                                Refresh token untuk memperbarui akses
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SESSION & EXPIRY SECTION --}}
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-clock"></i>
                        Session & Masa Berlaku
                    </h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Expires At <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="datetime-local" 
                                   name="expires_at"
                                   value="{{ old('expires_at', $row->expires_at ? $row->expires_at->format('Y-m-d\TH:i') : '') }}"
                                   class="form-control">
                            <div class="form-hint">
                                <i class="bi bi-calendar-x"></i>
                                Tanggal dan waktu kadaluarsa token
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Session ID <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="session_id" 
                                   value="{{ old('session_id', $row->session_id) }}" 
                                   class="form-control" 
                                   placeholder="Masukkan session ID">
                            <div class="form-hint">
                                <i class="bi bi-person-badge"></i>
                                Bisa dikosongkan, akan diisi otomatis saat open-db
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ADDITIONAL INFORMATION SECTION --}}
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-gear"></i>
                        Informasi Tambahan
                    </h4>
                    
                    <div class="form-grid">
                        <div class="form-group form-full-width">
                            <label class="form-label">
                                Scope <span class="text-muted">(Opsional)</span>
                            </label>
                            <textarea name="scope" 
                                      rows="3" 
                                      class="form-control form-textarea"
                                      placeholder="Masukkan scope permissions">{{ old('scope', $row->scope ?? '') }}</textarea>
                            <div class="form-hint">
                                <i class="bi bi-list-check"></i>
                                Scope permissions untuk API access
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Status
                            </label>
                            <select name="status" class="form-select" id="statusSelect">
                                <option value="active" @selected(old('status', $row->status) == 'active')>Active</option>
                                <option value="expired" @selected(old('status', $row->status) == 'expired')>Expired</option>
                            </select>
                            <div class="form-hint">
                                <i class="bi bi-circle-fill" id="statusIcon"></i>
                                <span id="statusText">Status account</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="form-section">
                    <div class="d-flex gap-3 flex-wrap btn-group">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save me-2"></i>
                            {{ $row->exists ? 'Update Account' : 'Simpan Account' }}
                        </button>
                        <a href="{{ route('aa.index') }}" class="btn btn-outline flex-fill">
                            <i class="bi bi-x-circle me-2"></i>
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status preview update
        const statusSelect = document.getElementById('statusSelect');
        const statusIcon = document.getElementById('statusIcon');
        const statusText = document.getElementById('statusText');

        function updateStatusPreview() {
            const status = statusSelect.value;
            if (status === 'active') {
                statusIcon.style.color = 'var(--success)';
                statusText.textContent = 'Account aktif dan dapat digunakan';
            } else {
                statusIcon.style.color = 'var(--danger)';
                statusText.textContent = 'Account telah kadaluarsa';
            }
        }

        statusSelect.addEventListener('change', updateStatusPreview);
        updateStatusPreview(); // Initial call

        // Form submission loading state
        const form = document.getElementById('accountForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Revert after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Add hover effects to form controls
        const formControls = document.querySelectorAll('.form-control, .form-select, .form-textarea');
        formControls.forEach(control => {
            control.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-1px)';
            });
            control.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Auto-format datetime input
        const expiresAtInput = document.querySelector('input[name="expires_at"]');
        if (!expiresAtInput.value) {
            // Set default to 30 days from now
            const defaultDate = new Date();
            defaultDate.setDate(defaultDate.getDate() + 30);
            expiresAtInput.value = defaultDate.toISOString().slice(0, 16);
        }
    });
</script>
@endsection
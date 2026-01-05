@extends('layouts.admin')

@section('page-title', $user->exists ? 'Edit User' : 'Create User')

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
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
    }

    /* ===== FORM CARD ===== */
    .form-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .form-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 2.5rem;
    }

    .form-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    .form-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        font-size: 1.8rem;
    }

    .form-subtitle {
        color: #64748b;
        margin: 0;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-section {
        margin-bottom: 2.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.5rem;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .section-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        font-size: 1.3rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .required::after {
        content: "*";
        color: var(--danger);
        margin-left: 4px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 14px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.3rem rgba(99, 102, 241, 0.15);
        transform: translateY(-2px);
        background: white;
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        z-index: 2;
    }

    .input-with-icon .form-control {
        padding-left: 45px;
    }

    .form-text {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ===== STATUS BADGES ===== */
    .status-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 8px;
    }

    .status-option {
        position: relative;
    }

    .status-option input {
        position: absolute;
        opacity: 0;
    }

    .status-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 15px 10px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .status-label:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .status-option input:checked + .status-label {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
    }

    .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .status-admin .status-icon {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
    }

    .status-karyawan .status-icon {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-reseller .status-icon {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-name {
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
    }

    /* ===== BUTTONS ===== */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f1f5f9;
    }

    .btn {
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    }

    .btn-outline {
        background: white;
        border: 2px solid #e2e8f0;
        color: #64748b;
    }

    .btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* ===== ALERTS ===== */
    .alert {
        border-radius: 16px;
        border: none;
        padding: 20px 25px;
        margin-bottom: 2rem;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .alert-icon {
        font-size: 1.2rem;
        margin-right: 12px;
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
        
        .form-card {
            padding: 25px;
        }
        
        .form-header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .status-options {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
        }
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-content">
                <h1>{{ $user->exists ? '✏️ Edit User' : '👤 Create New User' }}</h1>
                <p class="page-description">
                    {{ $user->exists ? 'Update user information and permissions' : 'Add a new user to the system' }}
                </p>
            </div>
            <a href="{{ route('admin.user') }}" class="btn back-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show animate-card" style="animation-delay: 0.1s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                <div class="flex-grow-1">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-2 mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- FORM CARD --}}
    <div class="form-card animate-card" style="animation-delay: 0.2s">
        <div class="form-header">
            <div class="form-icon">
                <i class="bi bi-person-gear"></i>
            </div>
            <div>
                <h2 class="form-title">{{ $user->exists ? 'Edit User' : 'Create New User' }}</h2>
                <p class="form-subtitle">Fill in the user details below</p>
            </div>
        </div>

        <form method="post" action="{{ $user->exists ? route('users2.update', $user->id) : route('users2.store') }}" id="userForm">
            @csrf
            @if($user->exists) @method('PUT') @endif

            {{-- BASIC INFORMATION SECTION --}}
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <h3 class="section-title">Basic Information</h3>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required">
                                <i class="bi bi-person"></i>
                                Full Name
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                       class="form-control" placeholder="Enter full name" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required">
                                <i class="bi bi-envelope"></i>
                                Email Address
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                       class="form-control" placeholder="Enter email address" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PASSWORD SECTION --}}
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="section-title">Security</h3>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label {{ !$user->exists ? 'required' : '' }}">
                                <i class="bi bi-key"></i>
                                Password
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-key input-icon"></i>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="{{ $user->exists ? 'Leave blank to keep current' : 'Enter password' }}"
                                       {{ !$user->exists ? 'required' : '' }}>
                            </div>
                            @if($user->exists)
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Leave blank to keep current password
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label {{ !$user->exists ? 'required' : '' }}">
                                <i class="bi bi-key-fill"></i>
                                Confirm Password
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-key-fill input-icon"></i>
                                <input type="password" name="password_confirmation" class="form-control" 
                                       placeholder="Confirm password"
                                       {{ !$user->exists ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROLE & PERMISSIONS SECTION --}}
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3 class="section-title">Role & Permissions</h3>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required">
                                <i class="bi bi-shield-check"></i>
                                User Role
                            </label>
                            <div class="status-options">
                                <div class="status-option status-admin">
                                    <input type="radio" name="status" value="admin" id="status_admin" 
                                           {{ old('status', $user->status) == 'admin' ? 'checked' : '' }} required>
                                    <label class="status-label" for="status_admin">
                                        <div class="status-icon">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <span class="status-name">Administrator</span>
                                    </label>
                                </div>

                                <div class="status-option status-karyawan">
                                    <input type="radio" name="status" value="KARYAWAN" id="status_karyawan" 
                                           {{ old('status', $user->status) == 'KARYAWAN' ? 'checked' : '' }} required>
                                    <label class="status-label" for="status_karyawan">
                                        <div class="status-icon">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                        <span class="status-name">Karyawan</span>
                                    </label>
                                </div>

                                <div class="status-option status-reseller">
                                    <input type="radio" name="status" value="RESELLER" id="status_reseller" 
                                           {{ old('status', $user->status) == 'RESELLER' ? 'checked' : '' }} required>
                                    <label class="status-label" for="status_reseller">
                                        <div class="status-icon">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <span class="status-name">Reseller</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-{{ $user->exists ? 'check-lg' : 'plus-lg' }} me-1"></i>
                    {{ $user->exists ? 'Update User' : 'Create User' }}
                </button>
                <a href="{{ route('admin.user') }}" class="btn btn-outline">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Add loading state to form submission
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
        submitBtn.disabled = true;
    });

    // Add real-time validation feedback
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    });

    // Password strength indicator (basic)
    const passwordInput = form.querySelector('input[name="password"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = document.getElementById('password-strength') || createPasswordStrength();
            
            if (password.length === 0) {
                strength.textContent = '';
                return;
            }
            
            let strengthText = '';
            let strengthClass = '';
            
            if (password.length < 6) {
                strengthText = 'Weak';
                strengthClass = 'text-danger';
            } else if (password.length < 10) {
                strengthText = 'Medium';
                strengthClass = 'text-warning';
            } else {
                strengthText = 'Strong';
                strengthClass = 'text-success';
            }
            
            strength.textContent = `Strength: ${strengthText}`;
            strength.className = `form-text ${strengthClass}`;
        });
    }

    function createPasswordStrength() {
        const strength = document.createElement('div');
        strength.id = 'password-strength';
        strength.className = 'form-text';
        passwordInput.parentNode.appendChild(strength);
        return strength;
    }
});
</script>
@endsection
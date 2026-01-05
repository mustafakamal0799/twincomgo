<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Account Security</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --error-color: #ef4444;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%23f1f5f9"/><path d="M0 0L100 100" stroke="%23e2e8f0" stroke-width="0.5"/><path d="M100 0L0 100" stroke="%23e2e8f0" stroke-width="0.5"/></svg>');
        }
        
        .reset-container {
            width: 100%;
            max-width: 460px;
        }
        
        .reset-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .reset-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
        }
        
        .header-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 1.75rem;
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.2);
        }
        
        .title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .subtitle {
            color: var(--secondary-color);
            text-align: center;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        
        .user-info {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--success-color);
        }
        
        .user-info i {
            color: var(--success-color);
            margin-right: 8px;
        }
        
        .user-info strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .password-strength {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 4px;
            display: none;
        }
        
        .password-strength.weak {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }
        
        .password-strength.medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .password-strength.strong {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group .form-control {
            padding-right: 2.75rem;
            padding-left: 2.75rem;
        }
        
        .input-icon-left {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 5;
        }
        
        .input-icon-right {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 5;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .input-icon-right:hover {
            color: var(--primary-color);
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .password-requirements {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: var(--secondary-color);
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .requirement i {
            margin-right: 0.5rem;
            font-size: 0.75rem;
            width: 16px;
            text-align: center;
        }
        
        .requirement.met {
            color: var(--success-color);
        }
        
        .requirement.unmet {
            color: var(--secondary-color);
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 10px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            margin-top: 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--secondary-color);
            font-size: 0.85rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        @media (max-width: 576px) {
            .reset-card {
                padding: 2rem 1.5rem;
            }
            
            .title {
                font-size: 1.5rem;
            }
            
            .password-requirements {
                padding: 0.875rem;
            }
        }
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .reset-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes successCheck {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .success-check {
            animation: successCheck 0.5s ease-out;
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="header-icon">
                <i class="fas fa-key"></i>
            </div>
            
            <h1 class="title">Set New Password</h1>
            <p class="subtitle">Create a strong, secure password for your account</p>
            
            <div class="user-info">
                <i class="fas fa-user-check"></i>
                <strong>{{ $email }}</strong>
            </div>

            <form action="/reset-password" method="POST" id="resetForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="mb-4">
                    <div class="form-label">
                        <span>New Password</span>
                        <span class="password-strength" id="passwordStrength"></span>
                    </div>
                    <div class="input-group">
                        <span class="input-icon-left">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control" 
                            placeholder="Enter new password"
                            required
                            autofocus
                        >
                        <span class="input-icon-right" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="password-requirements">
                        <div class="requirement" id="reqLength">
                            <i class="fas fa-circle"></i>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="requirement" id="reqUppercase">
                            <i class="fas fa-circle"></i>
                            <span>At least one uppercase letter</span>
                        </div>
                        <div class="requirement" id="reqLowercase">
                            <i class="fas fa-circle"></i>
                            <span>At least one lowercase letter</span>
                        </div>
                        <div class="requirement" id="reqNumber">
                            <i class="fas fa-circle"></i>
                            <span>At least one number</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-label">
                        <span>Confirm Password</span>
                        <span id="passwordMatch" style="display: none; font-size: 0.75rem;">
                            <i class="fas fa-check-circle text-success"></i> Passwords match
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-icon-left">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="confirmPassword" 
                            class="form-control" 
                            placeholder="Confirm new password"
                            required
                        >
                        <span class="input-icon-right" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="passwordError" class="text-danger small mt-1" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i> Passwords do not match
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                    <i class="fas fa-save me-2"></i> Update Password
                </button>
            </form>

            <div class="footer">
                <p class="mb-0">© 2025 Account Security System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // DOM Elements
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
        const passwordStrengthEl = document.getElementById('passwordStrength');
        const passwordMatchEl = document.getElementById('passwordMatch');
        const passwordErrorEl = document.getElementById('passwordError');
        const submitBtn = document.getElementById('submitBtn');
        const resetForm = document.getElementById('resetForm');
        
        // Password visibility toggle
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        toggleConfirmPasswordBtn.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false
            };
            
            // Length requirement
            if (password.length >= 8) {
                strength++;
                requirements.length = true;
            }
            
            // Uppercase requirement
            if (/[A-Z]/.test(password)) {
                strength++;
                requirements.uppercase = true;
            }
            
            // Lowercase requirement
            if (/[a-z]/.test(password)) {
                strength++;
                requirements.lowercase = true;
            }
            
            // Number requirement
            if (/[0-9]/.test(password)) {
                strength++;
                requirements.number = true;
            }
            
            // Update requirement indicators
            updateRequirementIndicators(requirements);
            
            // Update strength indicator
            let strengthText = '';
            let strengthClass = '';
            
            if (password.length === 0) {
                passwordStrengthEl.style.display = 'none';
                return;
            }
            
            passwordStrengthEl.style.display = 'inline-block';
            
            if (strength <= 1) {
                strengthText = 'Weak';
                strengthClass = 'weak';
            } else if (strength <= 3) {
                strengthText = 'Medium';
                strengthClass = 'medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strong';
            }
            
            passwordStrengthEl.textContent = strengthText;
            passwordStrengthEl.className = 'password-strength ' + strengthClass;
        }
        
        // Update requirement indicators
        function updateRequirementIndicators(requirements) {
            const reqElements = {
                length: document.getElementById('reqLength'),
                uppercase: document.getElementById('reqUppercase'),
                lowercase: document.getElementById('reqLowercase'),
                number: document.getElementById('reqNumber')
            };
            
            for (const [key, element] of Object.entries(reqElements)) {
                if (requirements[key]) {
                    element.classList.add('met');
                    element.classList.remove('unmet');
                    element.querySelector('i').className = 'fas fa-check-circle text-success';
                } else {
                    element.classList.add('unmet');
                    element.classList.remove('met');
                    element.querySelector('i').className = 'fas fa-circle';
                }
            }
        }
        
        // Check if passwords match
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatchEl.style.display = 'none';
                passwordErrorEl.style.display = 'none';
                return;
            }
            
            if (password === confirmPassword) {
                passwordMatchEl.style.display = 'inline-block';
                passwordErrorEl.style.display = 'none';
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                passwordMatchEl.style.display = 'none';
                passwordErrorEl.style.display = 'block';
                confirmPasswordInput.classList.add('is-invalid');
                confirmPasswordInput.classList.remove('is-valid');
            }
        }
        
        // Form validation
        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                passwordErrorEl.style.display = 'block';
                confirmPasswordInput.focus();
                return false;
            }
            
            // Check password strength
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                passwordInput.focus();
                return false;
            }
            
            return true;
        }
        
        // Event listeners
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // Form submission
        resetForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Updating...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Update Password';
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Initialize with empty password check
        checkPasswordStrength('');
        
        // Focus on password input on page load
        passwordInput.focus();
    </script>
</body>
</html>
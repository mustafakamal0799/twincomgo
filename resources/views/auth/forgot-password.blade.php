<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Account Security</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
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
        
        .password-container {
            width: 100%;
            max-width: 440px;
        }
        
        .password-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .password-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
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
        
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
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
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            padding-left: 2.75rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 5;
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
        
        .alert {
            border-radius: 10px;
            padding: 0.875rem 1rem;
            font-size: 0.875rem;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--secondary-color);
            font-size: 0.85rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            transition: var(--transition);
        }
        
        .back-link:hover {
            color: var(--primary-dark);
            gap: 0.7rem;
        }
        
        @media (max-width: 576px) {
            .password-card {
                padding: 2rem 1.5rem;
            }
            
            .title {
                font-size: 1.5rem;
            }
        }
        
        /* Animation for form entry */
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
        
        .password-card {
            animation: fadeInUp 0.5s ease-out;
        }
    </style>
</head>

<body>
    <div class="password-container">
        <div class="password-card">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="title">Reset Password</h1>
                <p class="subtitle">Enter your email address below and we'll send you an OTP to reset your password</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger small d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="/forgot-password" method="POST" id="resetForm">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="name@company.com"
                            required
                            autofocus
                        >
                    </div>
                    <div class="form-text mt-2">
                        <i class="fas fa-info-circle me-1"></i> We'll send a 6-digit OTP to this email
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i> Send OTP Code
                </button>
                
                <div class="text-center mt-3">
                    <a href="/" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to login
                    </a>
                </div>
            </form>

            <div class="footer">
                <p class="mb-0">© 2025 Account Security System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission feedback
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...';
            submitBtn.disabled = true;
            
            // Re-enable after 3 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
        
        // Add focus effect to input
        const emailInput = document.querySelector('input[name="email"]');
        emailInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        emailInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification | Account Security</title>
    
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
        
        .verification-container {
            width: 100%;
            max-width: 460px;
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .verification-card::before {
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
            margin-bottom: 0.5rem;
        }
        
        .email-display {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .email-display strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        .email-display i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2.5rem;
        }
        
        .otp-input {
            width: 52px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background-color: white;
            transition: var(--transition);
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .otp-input.filled {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
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
        
        .timer-container {
            text-align: center;
            margin: 1.5rem 0;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 10px;
        }
        
        .timer {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .timer-expired {
            color: var(--error-color);
        }
        
        .btn-secondary {
            background-color: #f1f5f9;
            border: 1px solid var(--border-color);
            color: #334155;
            border-radius: 10px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background-color: #e2e8f0;
            transform: translateY(-2px);
        }
        
        .btn-secondary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--secondary-color);
            font-size: 0.85rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .instructions {
            font-size: 0.85rem;
            color: var(--secondary-color);
            text-align: center;
            margin-top: 1rem;
            line-height: 1.5;
        }
        
        @media (max-width: 576px) {
            .verification-card {
                padding: 2rem 1.5rem;
            }
            
            .title {
                font-size: 1.5rem;
            }
            
            .otp-input-container {
                gap: 8px;
            }
            
            .otp-input {
                width: 46px;
                height: 54px;
                font-size: 1.25rem;
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
        
        .verification-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s;
        }
    </style>
</head>

<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="header-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <h1 class="title">Verify OTP</h1>
            <p class="subtitle">Enter the 6-digit verification code sent to your email</p>
            
            <div class="email-display">
                <i class="fas fa-envelope"></i>
                <strong>{{ $email }}</strong>
            </div>

            @if(session('error'))
                <div class="alert alert-danger small d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="/verify-otp" method="POST" id="verifyForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                
                <!-- Hidden input for OTP (will be populated by JavaScript) -->
                <input type="hidden" name="otp" id="otpValue" required>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-3 text-center d-block">Enter OTP Code</label>
                    <div class="otp-input-container">
                        <input type="text" maxlength="1" class="otp-input" data-index="1" autofocus>
                        <input type="text" maxlength="1" class="otp-input" data-index="2">
                        <input type="text" maxlength="1" class="otp-input" data-index="3">
                        <input type="text" maxlength="1" class="otp-input" data-index="4">
                        <input type="text" maxlength="1" class="otp-input" data-index="5">
                        <input type="text" maxlength="1" class="otp-input" data-index="6">
                    </div>
                    <div class="instructions">
                        <i class="fas fa-info-circle me-1"></i> Enter the 6-digit code from your email
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-check-circle me-2"></i> Verify Code
                </button>
            </form>
            
            <div class="timer-container">
                <div class="timer" id="timer">Resend OTP in <span id="countdown">60</span> seconds</div>
            </div>
            
            <form action="/resend-otp" method="POST" id="resendForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit" class="btn-secondary" id="resendBtn" disabled>
                    <i class="fas fa-redo-alt me-2"></i> <span id="resendText">Resend OTP</span>
                </button>
            </form>

            <div class="footer">
                <p class="mb-0">© 2025 Account Security System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValueInput = document.getElementById('otpValue');
        const verifyForm = document.getElementById('verifyForm');
        const resendBtn = document.getElementById('resendBtn');
        const resendText = document.getElementById('resendText');
        const countdownEl = document.getElementById('countdown');
        const timerContainer = document.querySelector('.timer-container');
        
        let countdown = 60;
        let countdownInterval;
        
        // Function to start the countdown timer
        function startCountdown() {
            countdown = 60;
            countdownEl.textContent = countdown;
            resendBtn.disabled = true;
            resendText.textContent = 'Resend OTP';
            timerContainer.style.display = 'block';
            
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    resendBtn.disabled = false;
                    resendText.textContent = 'Resend OTP';
                    timerContainer.querySelector('.timer').innerHTML = 'OTP expired. Click button to resend.';
                    timerContainer.classList.add('timer-expired');
                }
            }, 1000);
        }
        
        // Initialize countdown
        startCountdown();
        
        // Handle OTP input navigation
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Update OTP value
                updateOTPValue();
                
                // Add filled class
                if (e.target.value) {
                    e.target.classList.add('filled');
                } else {
                    e.target.classList.remove('filled');
                }
                
                // Auto-focus next input
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                // Handle arrow keys
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                
                // Only allow numbers
                if (e.key.length === 1 && !/\d/.test(e.key)) {
                    e.preventDefault();
                }
            });
            
            // Paste OTP handling
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').trim();
                
                if (/^\d{6}$/.test(pastedData)) {
                    // Fill all inputs with pasted OTP
                    for (let i = 0; i < Math.min(6, pastedData.length); i++) {
                        otpInputs[i].value = pastedData[i];
                        otpInputs[i].classList.add('filled');
                    }
                    
                    // Focus last input
                    if (pastedData.length >= 6) {
                        otpInputs[5].focus();
                    } else {
                        otpInputs[pastedData.length].focus();
                    }
                    
                    updateOTPValue();
                }
            });
        });
        
        // Update hidden OTP value
        function updateOTPValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpValueInput.value = otp;
        }
        
        // Form submission
        verifyForm.addEventListener('submit', function(e) {
            const otp = otpValueInput.value;
            
            if (otp.length !== 6) {
                e.preventDefault();
                
                // Shake animation for error
                otpInputs.forEach(input => {
                    if (!input.value) {
                        input.classList.add('shake');
                        setTimeout(() => input.classList.remove('shake'), 500);
                    }
                });
                
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Resend OTP form handling
        document.getElementById('resendForm').addEventListener('submit', function(e) {
            // Prevent default and use fetch for better UX
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
            submitBtn.disabled = true;
            
            // Send request
            fetch('/resend-otp', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: "{{ $email }}" })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset countdown
                    startCountdown();
                    timerContainer.classList.remove('timer-expired');
                    
                    // Clear OTP inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    updateOTPValue();
                    otpInputs[0].focus();
                    
                    // Show success message temporarily
                    const originalTimerHTML = timerContainer.querySelector('.timer').innerHTML;
                    timerContainer.querySelector('.timer').innerHTML = 
                        '<i class="fas fa-check-circle text-success me-1"></i> New OTP sent successfully!';
                    
                    setTimeout(() => {
                        timerContainer.querySelector('.timer').innerHTML = originalTimerHTML;
                    }, 3000);
                }
                
                // Restore button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
        
        // Auto-focus first OTP input on page load
        otpInputs[0].focus();
    </script>
</body>
</html>
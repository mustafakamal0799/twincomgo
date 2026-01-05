<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mempersiapkan Sistem - Harap Tunggu</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            line-height: 1.5;
        }
        
        .container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }
        
        /* Card utama */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 48px 40px;
            text-align: center;
            border: 1px solid var(--border);
            transition: var(--transition);
        }
        
        /* Header dengan logo */
        .header {
            margin-bottom: 32px;
        }
        
        .logo-container {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            color: white;
            font-size: 24px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }
        
        .company-name {
            font-size: 20px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.01em;
        }
        
        .company-tagline {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 4px;
            font-weight: 400;
        }
        
        /* Konten status */
        .status-content {
            margin-bottom: 32px;
        }
        
        .status-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 16px;
        }
        
        .status-message {
            font-size: 15px;
            color: var(--text-light);
            line-height: 1.6;
        }
        
        /* Loader yang lebih clean */
        .loader-container {
            margin: 32px 0 40px;
            position: relative;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loader {
            width: 48px;
            height: 48px;
            position: relative;
        }
        
        .loader-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid transparent;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1.5s cubic-bezier(0.76, 0.35, 0.2, 0.75) infinite;
        }
        
        .loader-inner-circle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
            border: 3px solid transparent;
            border-top: 3px solid var(--primary-light);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.76, 0.35, 0.2, 0.75) infinite reverse;
        }
        
        .loader-dot {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            box-shadow: 0 0 12px rgba(37, 99, 235, 0.3);
        }
        
        /* Progress section */
        .progress-section {
            margin-top: 32px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .progress-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .progress-percent {
            font-size: 14px;
            color: var(--primary);
            font-weight: 600;
        }
        
        .progress-bar-container {
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 3px;
            transition: width 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent);
            animation: shimmer 2s infinite;
        }
        
        /* Status info */
        .status-info {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(37, 99, 235, 0.05);
            border: 1px solid rgba(37, 99, 235, 0.1);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-light);
            margin-top: 24px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            position: relative;
        }
        
        .status-dot::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        
        /* Footer */
        .footer {
            margin-top: 32px;
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
        }
        
        /* Animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .card {
                padding: 40px 24px;
            }
            
            .logo-container {
                width: 56px;
                height: 56px;
                font-size: 20px;
            }
            
            .status-title {
                font-size: 22px;
            }
            
            .status-message {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <!-- Header dengan logo dan nama perusahaan -->
            <div class="header">
                <div class="logo-container">T</div>
                <div class="company-name">TWINCOMGO</div>
                <div class="company-tagline">Enterprise Systems</div>
            </div>
            
            <!-- Konten status -->
            <div class="status-content">
                <h1 class="status-title">Mempersiapkan Sistem</h1>
                <p class="status-message">
                    Sistem sedang diinisialisasi untuk pengalaman optimal.
                    Harap tunggu sebentar, proses ini akan selesai dalam waktu singkat.
                </p>
            </div>
            
            <!-- Loader animasi -->
            <div class="loader-container">
                <div class="loader">
                    <div class="loader-circle"></div>
                    <div class="loader-inner-circle"></div>
                    <div class="loader-dot"></div>
                </div>
            </div>
            
            <!-- Progress bar -->
            <div class="progress-section">
                <div class="progress-header">
                    <span class="progress-label">Inisialisasi Sistem</span>
                    <span class="progress-percent" id="progress-percent">0%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="main-progress"></div>
                </div>
            </div>
            
            <!-- Status info -->
            <div class="status-info">
                <div class="status-dot"></div>
                <span>Sistem aktif • Mengalokasikan sumber daya</span>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                &copy; 2024 Twincomgo Enterprise. All rights reserved.
            </div>
        </div>
    </div>

    <form id="continueForm" method="GET" action="{{ route('wait.continue') }}">
        @csrf
    </form>

    <script>
        let queueNumber = {{ session('queue_number') }};
        let baseDelay = 3000;
        let perUserDelay = 20;
        let finalDelay = baseDelay + (queueNumber * perUserDelay);

        // Update progress bar
        const progressBar = document.getElementById('main-progress');
        const progressPercent = document.getElementById('progress-percent');
        
        // Animate progress percentage
        let progress = 0;
        const totalSteps = 100;
        const stepTime = finalDelay / totalSteps;
        
        const updateProgress = () => {
            progress += 1;
            progressBar.style.width = progress + '%';
            progressPercent.textContent = progress + '%';
            
            if (progress < 100) {
                setTimeout(updateProgress, stepTime);
            }
        };
        
        // Start progress animation
        setTimeout(updateProgress, 100);

        console.log("Total processing time:", finalDelay, "ms");

        // Redirect setelah selesai
        setTimeout(() => {
            document.getElementById('continueForm').submit();
        }, finalDelay);
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo - Admin Panel</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">

    {{-- ðŸ”¹ Library CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    {{-- ðŸ”¹ Custom CSS --}}
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-darker: #115e59;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
            overflow: hidden;
        }

        body {
            display: flex;
            position: relative;
        }

        /* ===== Glass Sidebar ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 30px 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0d9488, #115e59);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .logo:hover .logo-icon {
            transform: rotate(15deg);
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            transition: opacity 0.3s ease;
            background: linear-gradient(to right, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
        }

        /* ===== Navigation ===== */
        .sidebar-nav {
            flex: 1;
            padding: 25px 0;
            overflow-y: auto;
        }

        .nav-item {
            margin: 8px 15px;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 12px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            box-shadow: 0 4px 15px rgba(13, 148, 136, 0.4);
        }

        .nav-icon {
            width: 24px;
            text-align: center;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .nav-link:hover .nav-icon {
            transform: scale(1.1);
        }

        .nav-text {
            font-weight: 500;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ===== Main Wrapper ===== */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            position: relative;
        }

        .sidebar.collapsed ~ .main-wrapper {
            margin-left: var(--sidebar-collapsed);
        }

        /* ===== Glass Header ===== */
        .navbar-admin {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toggle-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(13, 148, 136, 0.3);
        }

        .toggle-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(13, 148, 136, 0.4);
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* ===== User Menu ===== */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-bell {
            position: relative;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--dark);
        }

        .notification-bell:hover {
            background: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(13, 148, 136, 0.3);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
        }

        .user-role {
            font-size: 12px;
            color: var(--secondary);
        }

        /* ===== Main Content ===== */
        .navbar-admin {
            position: relative;
            z-index: 9999 !important;
        }

        main {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: transparent;
        }

        .content-wrapper {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .content-wrapper:hover {
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
        }

        /* ===== Footer ===== */
        footer {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--secondary);
            text-align: center;
            padding: 20px;
            font-size: 14px;
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* ===== Animations ===== */
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

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.5s ease-out;
        }

        /* ===== Floating Elements ===== */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0 !important;
            }
            
            .user-info {
                display: none;
            }
            
            .page-title h1 {
                font-size: 22px;
            }
        }

        /* ===== Loader ===== */
        #loader-display {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ðŸ”¹ Floating Background Elements --}}
<div class="floating-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

{{-- ðŸ”¹ Glass Sidebar --}}
<aside class="sidebar animate__animated animate__fadeInLeft" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="logo">
            <div class="logo-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>
            <div class="logo-text">Twincomgo</div>
        </a>
    </div>

    <nav class="sidebar-nav">
        @php
            $navItems = [
                ['route' => 'admin.index', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'badge' => null],
                ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Kelola Pengguna', 'badge' => null],
                ['route' => 'admin.log', 'icon' => 'bi-archive', 'label' => 'Log Aktivitas', 'badge' => null],
                // ['route' => 'items.index', 'icon' => 'bi-box-seam', 'label' => 'Stok Item', 'badge' => null],
                // ['route' => 'users2.index', 'icon' => 'bi-person-bounding-box', 'label' => 'Accurate Users', 'badge' => null],
                ['route' => 'aa.index', 'icon' => 'bi-diagram-3', 'label' => 'Accurate Token', 'badge' => null],
            ];
        @endphp

        @foreach ($navItems as $item)
            <div class="nav-item animate-slide-in-left" style="animation-delay: {{ $loop->index * 0.1 }}s">
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                    <i class="nav-icon bi {{ $item['icon'] }}"></i>
                    <span class="nav-text">{{ $item['label'] }}</span>
                    @if($item['badge'])
                        <span class="nav-badge">{{ $item['badge'] }}</span>
                    @endif
                </a>
            </div>
        @endforeach
    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer p-3 border-top border-white-10">
        <div class="text-center text-white-60 small">
            <i class="bi bi-shield-check me-1"></i>
            Secure Admin Panel
        </div>
    </div>
</aside>

{{-- ðŸ”¹ Main Content Area --}}
<div class="main-wrapper animate__animated animate__fadeIn">
    {{-- Glass Header --}}
    <nav class="navbar-admin">
        <div class="header-left">
            <button class="toggle-btn" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="page-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
        </div>

        <div class="user-menu">
            <div class="notification-bell">
                <i class="bi bi-bell"></i>
                <span class="notification-badge">3</span>
            </div>
            
            <div class="dropdown">
                <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
                        <div class="user-role">Super Admin</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        <div class="content-wrapper animate-fade-in-up">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="animate__animated animate__fadeInUp">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start text-center">
                    <small>Â© {{ date('Y') }} Cv Twin Group â€” All rights reserved</small>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Secure Admin Panel v2.0
                    </small>
                </div>
            </div>
        </div>
    </footer>
</div>

{{-- ðŸ”¹ Loader --}}
<div id="loader-display">
    <div class="loader-spinner"></div>
</div>

{{-- ðŸ”¹ Script --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
    // Sidebar Toggle - Fixed version
    $('#toggleSidebar').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('#sidebar').toggleClass('collapsed');
        
        // Remove any existing animation classes
        $('#sidebar').removeClass('animate__fadeInLeft animate__fadeOutLeft');
        
        // Add appropriate animation
        if ($('#sidebar').hasClass('collapsed')) {
            $('#sidebar').addClass('animate__fadeOutLeft');
            setTimeout(() => {
                $('#sidebar').removeClass('animate__fadeOutLeft');
            }, 500);
        } else {
            $('#sidebar').addClass('animate__fadeInLeft');
            setTimeout(() => {
                $('#sidebar').removeClass('animate__fadeInLeft');
            }, 500);
        }
    });

    // Mobile sidebar handling
    function handleMobileSidebar() {
        if (window.innerWidth <= 768) {
            $('#sidebar').removeClass('collapsed').addClass('mobile-open');
        } else {
            $('#sidebar').removeClass('mobile-open');
        }
    }

    // Initialize
    $(document).ready(function() {
        handleMobileSidebar();
        $(window).resize(handleMobileSidebar);
        
        // Add loading state to nav links
        $('.nav-link').on('click', function(e) {
            // Only show loader for external links, not for sidebar toggle
            if (!$(this).attr('href') || $(this).attr('href') === '#') {
                return;
            }
            $('#loader-display').fadeIn();
        });
        
        // Remove loading when page is ready
        $(window).on('load', function() {
            $('#loader-display').fadeOut();
        });
        
        // Add hover effect to content wrapper
        $('.content-wrapper').hover(
            function() {
                $(this).css('transform', 'translateY(-5px)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );
    });

    // Notification bell animation
    $('.notification-bell').on('click', function() {
        $(this).addClass('animate__animated animate__tada');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__tada');
        }, 1000);
    });
    
    document.addEventListener("DOMContentLoaded", function () {
    // DOM selesai → matikan loader
        $('#loader-display').fadeOut();
    });
    
    // Fallback anti macet
    setTimeout(() => {
        $('#loader-display').fadeOut();
    }, 2500);
</script>

@stack('scripts')
</body>
</html>
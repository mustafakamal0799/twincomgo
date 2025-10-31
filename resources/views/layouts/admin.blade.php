<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">

    {{-- 🔹 Library CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- 🔹 Custom CSS --}}
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Nunito", sans-serif;
            background-color: #f6f8fb;
            overflow: hidden; /* tidak scroll */
        }

        body {
            display: flex;
            background-image: url('{{ asset('images/bg5.jpg') }}');
        }

        /* ===== Sidebar ===== */
        .sidebar {
            width: 240px;
            background-color: #1f2937;
            color: white;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar h5 {
            text-align: center;
            margin: 20px 0;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed h5 {
            opacity: 0;
        }

        .sidebar .nav-link {
            color: #d1d5db;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            transition: background-color 0.2s;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            min-width: 24px;
            text-align: center;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background-color: #374151;
            color: #fff;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* ===== Wrapper utama ===== */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* ===== Navbar ===== */
        .navbar-admin {
            background-color: #fff;
            border-bottom: 1px solid #e5e7eb;
            height: 70px;
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        /* ===== Konten ===== */
        main {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
        }

        /* ===== Footer ===== */
        footer {
            background: #fff;
            border-top: 1px solid #e5e7eb;
            color: #696969;
            text-align: center;
            padding: 10px;
            flex-shrink: 0;
        }

        /* Loader */
        #loader-display {
            display:none;
            position:fixed;
            top:0; left:0;
            width:100vw; height:100vh;
            background:rgba(0, 0, 0, 0.4);
            z-index:2000;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
        }

        /* Hilangkan scroll bar default tapi tetap bisa scroll konten */
        main::-webkit-scrollbar {
            width: 8px;
        }
        main::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- 🔹 Sidebar Navigasi --}}
<aside class="sidebar" id="sidebar">
    <h5 class="fw-bold">Twincomgo</h5>
    <ul class="nav flex-column">
        @php
            $navItems = [
                ['route' => 'admin.index', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
                ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Kelola Pengguna'],
                ['route' => 'admin.log', 'icon' => 'bi-archive', 'label' => 'Log Aktivitas'],
                ['route' => 'items.index', 'icon' => 'bi-box-seam', 'label' => 'Stok Item'],
                ['route' => 'users2.index', 'icon' => 'bi-person-bounding-box', 'label' => 'Accurate Users'],
                ['route' => 'aa.index', 'icon' => 'bi-diagram-3', 'label' => 'Accurate Accounts'],
            ];
        @endphp

        @foreach ($navItems as $item)
            <li>
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</aside>

{{-- 🔹 Wrapper Konten --}}
<div class="main-wrapper">
    {{-- Navbar --}}
    <nav class="navbar-admin">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 fw-bold">Panel Admin</h6>
        </div>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2 fs-5"></i> {{ Auth::user()->name ?? 'Admin' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="px-3">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Konten --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer tetap terlihat --}}
    <footer>
        <small>© {{ date('Y') }} Cv Twin Group — Admin Panel</small>
    </footer>
</div>

{{-- 🔹 Script --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar toggle
    $('#toggleSidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });
</script>

@stack('scripts')
</body>
</html>

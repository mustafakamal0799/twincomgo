<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo</title>
    {{-- <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/png"> --}}
    

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-weight: <weight>;
            font-style: normal;
            /* ✅ Ini bagian yang menambahkan background image */
            background-image: url("{{ asset('images/bg1.jpg') }}");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh; 
        }

        .full-height {
            height: 100vh;
        }

        .btn-secondary.active,
        .btn-secondary:active {
            background-color: #6c757d !important; /* warna asli btn-secondary */
            border-color: #6c757d !important;
            color: #fff !important;
        }

        .logout-btn {
            font-size: 14px;
            padding: 6px 10px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn i {
            font-size: 16px;
            margin-right: 5px;
        }
        .person-icon {
            color: white;
            font-size: 35px;
        }

        .toggle-btn {
            position: fixed;
            top: -5px;
            left: 245px;
            /* z-index: 100; */
            transition: left 0.1s ease;
            z-index: 1000; /* above sidebar */
        }

        .toggle-btn.toggled {
            left: -5px !important;
        }

        #sidebar {
            transition: all 0.3s ease;
            z-index: 1040;
            background-color: #212529; /* Bootstrap dark bg */
            min-height: 100vh;
        }

        #toggleSidebar {
            transition: left 0.3s ease;
        }

        #toggleSidebar.toggled {
            left: 10px; /* Move right when toggled */
        }

        .navbar {
            background-color: #343a40;
            color: #fff;
        }
        .navbar-brand {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .tooltip .tooltip-inner {
            background-color: #ffe695;
            color: #000;
        }
        .bs-tooltip-top .tooltip-arrow::before,
        .bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before {
            border-top-color: #ffe695 !important;
        }
        .bs-tooltip-bottom .tooltip-arrow::before,
        .bs-tooltip-auto[data-popper-placement^="bottom"] .tooltip-arrow::before {
            border-bottom-color: #ffe695 !important;
        }

        @media only screen and (max-width: 768px) {
            body {
                font-size: 12px;
                overflow: visible !important;
                overflow-x: hidden !important;
            }

            .navbar-brand {
                font-size: 10px;
                font-weight: bold;
                color: white;
            }

            .dropdown-menu {
                min-width: 100px;
            }

            .logout-btn {
                font-size: 12px;
                padding: 4px 8px;
            }

            .logout-btn i {
                font-size: 12px;
                margin-right: 3px;
            }
            .person-icon {
                font-size: 15px;
            }

            .dropdown a strong {
                font-size: 10px;
            }

            /* Sidebar hidden by default on mobile */
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 250px;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            /* Toggle button position on mobile */
            .toggle-btn {
                top: 10px;
                left: 10px;
            }

            /* Overlay to cover content when sidebar is open */
            #sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
                display: none;
            }

            #sidebar-overlay.show {
                display: block;
            }
            .navbar-toggler {
                font-size: 10px;
            }
        }

    </style>

</head>

<body>
    <div id="loader-display" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0, 0, 0, 0.377); z-index:1050; display:flex; justify-content:center; align-items:center; flex-direction: column">
        <div class="d-flex justify-content-center align-items-center mb-4">
            <dotlottie-wc
            src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
            style="width: 100%; max-width: 300px; height: auto; display: block; margin: auto;"
            speed="1"
            autoplay
            loop
            ></dotlottie-wc>
        </div>
        <p style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.8); font-weight: 500; margin-top: -50px">
            Mohon tunggu...
        </p>
    </div>
<div class="d-flex flex-column full-height">
    @if (Auth::user()->status === 'admin')
        <button class="btn btn-dark toggle-btn" id="toggleSidebar">
            <i class="bi bi-caret-right"></i>
        </button>
        <div class="d-flex">
            <div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px; min-height: 100vh;">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2 person-icon"></i>
                        <strong style="margin-right: 10px; margin-left: 10px;">{{ Auth::user()->name }}</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="px-3">
                                @csrf
                                <button class="btn btn-danger w-100 mt-2">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{route('admin.index')}}"  class="nav-link text-white mb-2 text-start btn btn-secondary
                            {{ Request::routeIs('admin.index') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.user')}}" class="nav-link text-white mb-2 text-start btn btn-secondary
                            {{ Request::routeIs('admin.user') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="{{route('admin.log')}}" class="nav-link text-white mb-2 text-start btn btn-secondary
                            {{ Request::routeIs('admin.log') ? 'active' : '' }}">
                            <i class="bi bi-archive me-2"></i> Log Aktivitas
                        </a>
                    </li>
                    {{-- <li>
                        <a href="{{route('promo.index')}}" class="nav-link text-white mb-2 text-start btn btn-secondary
                            {{ Request::routeIs('promo.index') ? 'active' : '' }}">
                            <i class="bi bi-megaphone me-2"></i> Promo
                        </a>
                    </li> --}}
                    <li>
                        <a href="{{route('items.index')}}" class="nav-link text-white mb-2 text-start btn btn-secondary
                            {{ Request::routeIs('items.index') ? 'active' : '' }}">
                            <i class="bi bi-box-seam me-2"></i> Stok Item
                        </a>
                    </li>
                    <!-- Tambah menu lainnya sesuai kebutuhan -->
                </ul>
            </div>

            <div class="flex-grow-1 p-4">
                @yield('content')
            </div>
        </div>
    @else
        <nav class="navbar navbar-expand-lg navbar-dark p-3">
            <div class="container-fluid">
                <!-- Judul Navbar -->
                <a class="navbar-brand py-0" href="{{ route('items.index') }}">
                    SISTEM INFORMASI STOK BARANG
                </a>

                <!-- Tombol hamburger (hanya muncul di layar kecil) -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserMenu" aria-controls="navbarUserMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Isi Navbar: nama user & dropdown -->
                <div class="collapse navbar-collapse justify-content-end" id="navbarUserMenu">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none" data-bs-toggle="dropdown">
                            <strong class="me-2">
                                {{ Auth::user()->name }}
                            </strong>
                            <i class="bi bi-person-circle person-icon me-2" style="font-size: 1.2rem;"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="px-3 py-2">
                                    @csrf
                                    <button class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>


        <!-- Konten -->
        <div class="flex-grow-1 d-flex">
            <div class="flex-grow-1">
                @yield('content')
            </div>
        </div>
        
        @endif
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
    

    @stack('scripts')
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Show loader on page unload (navigation or reload)
        window.addEventListener('beforeunload', function () {
            const loader = document.getElementById('loader-display');
            if (loader) {
                loader.style.display = 'flex';
            }
        });

        // Hide loader on page load
        window.addEventListener('load', function () {
            const loader = document.getElementById('loader-display');
            if (loader) {
                loader.style.display = 'none';
            }
        });

        // Hide loader on pageshow (including when coming back from bfcache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const loader = document.getElementById('loader-display');
                if (loader) {
                    loader.style.display = 'none';
                }
            }
        });
    </script>
    @stack('scripts')
    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // Fungsi untuk menyimpan status sidebar ke localStorage
        function saveSidebarState(isClosed) {
            localStorage.setItem('sidebarClosed', isClosed ? 'true' : 'false');
        }

        // Fungsi untuk membaca status sidebar dari localStorage dan mengatur tampilan
        function loadSidebarState() {
            const isClosed = localStorage.getItem('sidebarClosed') === 'true';
            if (window.innerWidth <= 768) {
                // On mobile, sidebar is hidden by default
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                toggleBtn.classList.remove('toggled');
            } else {
                if (isClosed) {
                    sidebar.classList.add('d-none');
                    toggleBtn.classList.add('toggled');
                } else {
                    sidebar.classList.remove('d-none');
                    toggleBtn.classList.remove('toggled');
                }
            }
        }

        // Muat status sidebar saat halaman dimuat
        loadSidebarState();

        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                // On mobile, toggle sidebar overlay
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            } else {
                // Toggle kelas 'd-none' untuk menyembunyikan/memperlihatkan sidebar
                sidebar.classList.toggle('d-none');
                // Toggle class untuk pindahkan posisi tombol
                toggleBtn.classList.toggle('toggled');

                // Simpan status sidebar setelah toggle
                const isClosed = sidebar.classList.contains('d-none');
                saveSidebarState(isClosed);
            }
        });

        // Click on overlay to close sidebar on mobile
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Handle window resize to reset sidebar state
        window.addEventListener('resize', function() {
            loadSidebarState();
        });

    </script>

</body>
</html>

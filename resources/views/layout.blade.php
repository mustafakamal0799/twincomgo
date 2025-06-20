<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo</title>
    {{-- <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/png"> --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">



    <style>
        body {
            margin: 0;
            padding: 0;
            /* ✅ Ini bagian yang menambahkan background image */
            background-image: url('{{ asset('images/bg1.jpg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh; /* Full screen */
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
            z-index: 1050; /* above sidebar */
        }

        .toggle-btn.toggled {
            left: -5px !important;
        }

        #sidebar {
            transition: all 0.3s ease;
            z-index: 1040;
            background-color: #212529; /* Bootstrap dark bg */
            height: 100vh;
        }

        #toggleSidebar {
            transition: left 0.3s ease;
        }

        #toggleSidebar.toggled {
            left: 10px; /* Move right when toggled */
        }

        @media only screen and (max-width: 768px) {
            .navbar-brand {
                font-size: 10px;            
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
                box-shadow: 2px 0 5px rgba(0,0,0,0.5);
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
                background: rgba(0,0,0,0.5);
                z-index: 1030;
                display: none;
            }

            #sidebar-overlay.show {
                display: block;
            }
        }
        
        
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    @if (Auth::user()->status === 'admin')
        <button class="btn btn-dark toggle-btn" id="toggleSidebar">
            <i class="bi bi-caret-right"></i>
        </button>
        <div class="d-flex">
            <div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px; min-height: 100vh;">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
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
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container">
                <a class="navbar-brand" href="/">SISTEM INFORMASI STOK BARANG</a>

                <div class="btn-group">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong style="margin-right: 10px">{{ Auth::user()->name }}</strong>
                            <i class="bi bi-person-circle person-icon me-2"></i>
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
                </div>
            </div>
        </nav>

        <!-- Konten -->
        <div class="flex-fill container">
            @yield('content')
        </div>

    @endif
         <!-- Footer -->
        {{-- <footer class="bg-light text-center py-3 mt-auto">
                <small>© {{ date('Y') }} Sistem Informasi Stok Barang - <i>Powered by</i> Twincom</small>
        </footer> --}}




    <div id="sidebar-overlay"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
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

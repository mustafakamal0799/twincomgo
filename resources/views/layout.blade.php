<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">
    

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{asset('css/layout.css')}}">

    @stack('styles')

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
        <!-- WRAPPER -->
        <div class="d-flex">
            <!-- SIDEBAR -->
            <nav id="sidebar" class="bg-dark p-3 flex-shrink-0" style="width: 250px; min-height: 100vh;">
                <!-- LOGO -->
                <div class="text-center mb-4">
                    <a href="{{ route('admin.index') }}">
                        <img src="{{ asset('images/logo-putih.png') }}" alt="Logo" class="img-fluid" style="max-height: 80px;">
                    </a>
                </div>
                <ul class="nav nav-pills flex-column mb-auto mt-2 border-top">
                    @php
                        $navItems = [
                            ['route' => 'admin.index', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
                            ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Kelola Pengguna'],
                            ['route' => 'admin.log', 'icon' => 'bi-archive', 'label' => 'Log Aktivitas'],
                            ['route' => 'items.index', 'icon' => 'bi-box-seam', 'label' => 'Stok Item'],
                            ['route' => 'users2.index', 'icon' => 'bi-person-bounding-box', 'label' => 'Accurate Users'],
                            ['route' => 'aa.index', 'icon' => 'bi bi-diagram-3', 'label' => 'Accurate Accounts'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        <li class="nav-item mt-2">
                            <a href="{{ route($item['route']) }}" class="nav-link text-start {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <i class="bi {{ $item['icon'] }} me-2"></i> {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                    @csrf
                    <button class="btn btn-danger w-100 mt-3">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </nav>

            <!-- KANAN: KONTEN + NAVBAR -->
            <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
                <!-- NAVBAR ATAS -->
                <nav class="navbar navbar-dark bg-dark px-4 d-flex justify-content-between align-items-center" style="height: 60px;">
                    <button class="btn btn-outline-light" id="toggleSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="d-flex align-items-center">
                        <strong class="text-white">{{ Auth::user()->name }}</strong>
                        <i class="bi bi-person-circle ms-2 text-white"></i>
                    </div>
                </nav>

                <!-- MAIN CONTENT -->
                <div class="flex-grow-1 d-flex">
                    <div class="flex-grow-1 p-4">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    @else
        <nav class="navbar navbar-expand-lg navbar-dark px-3 py-2">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand text-truncate" href="{{ route('items.index') }}" style="max-width: 60%;">
                SISTEM INFORMASI STOK BARANG
                </a>

                <!-- Toggler -->
                <button class="navbar-toggler border-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarUserMenu"
                        aria-controls="navbarUserMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menu kanan -->
                <div class="collapse navbar-collapse justify-content-end mt-2 mt-lg-0" id="navbarUserMenu">
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center gap-2 py-2 px-2 px-lg-0"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle person-icon"></i>
                            <strong class="text-white d-sm-inline">{{ Auth::user()->name }}</strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
                            <li><hr class="dropdown-divider d-sm-none"></li>
                            <li class="px-3 py-2">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                </div>
            </div>
        </nav>


        <!-- Konten -->
        <div class="flex-grow-1 d-flex flex-column min-vh-100">
            <div class="flex-grow-1 d-flex flex-column">
                @yield('content')
            </div>
        </div>
        
        @endif
</div>
    
    <!-- Library JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>

<!-- Script utama proyek -->
<script src="{{ asset('js/layout.js') }}"></script>

@stack('scripts')
</body>

</body>
</html>

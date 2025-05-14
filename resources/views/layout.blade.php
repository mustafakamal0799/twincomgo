<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincom GO</title>

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

        @media only screen and (max-width: 768px) {
            .navbar-brand {
                font-size: 12px;
                font-style: italic;                
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
                font-size: 50px;
            }
        }

        
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">SISTEM INFORMASI STOK BARANG</a>

            <div class="btn-group">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                    <i class="bi bi-person-circle person-icon"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start custom-dropdown">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="px-3">
                            @csrf
                            <button type="submit" class="btn btn-danger logout-btn">
                                <i class="bi bi-box-arrow-in-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten -->
    <div class="flex-fill container">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-auto">
        <small>© {{ date('Y') }} Sistem Informasi Stok Barang - <i>Powered by</i> Twincom</small>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
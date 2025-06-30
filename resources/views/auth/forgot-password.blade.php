<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SISB</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
         body {
            margin: 0;
            padding: 0;
            background-image: url('{{ asset('images/bg1.jpg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh; /* Full screen */
            font-family: 'Poppins', sans-serif;
        }
        .login-card {
            max-width: 900px;
            width: 100%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-image {
            background-image: url('{{ asset('images/g.jpg') }}');
            background-size: cover;
            background-position: center;
            min-height: 100%; /* Tambahkan tinggi penuh */
        }
        .card-body-wrapper {
            height: 100%; /* Agar baris mengikuti tinggi penuh */
        }

        .card {
            box-shadow: 10px 10px 10px 0px rgba(0,0,0,0.4)
        }
    </style>
    
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="card p-3 login-card">
            <div class="row g-0 card-body-wrapper" style="min-height: 450px;">
                <!-- KIRI: Gambar -->
                <div class="col d-none d-md-block login-image">
                    <!-- Background image sudah diatur -->
                </div>

                <!-- KANAN: Form -->
                <div class="col p-5 d-flex flex-column justify-content-center">
                    <h2 class="text-center mb-4" style="font-style: italic">SISB</h2>
                    <h5 class="text-center mb-4">Masukkan Email Reset</h5>
                
                    {{-- Alert login gagal --}}
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                
                    <form method="POST" action="{{route('password.email')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>                
                        <div class="text-end mb-3">
                            <small>Sudah punya akun? <a href="{{ route('auth.login') }}" style="text-decoration: none">Login sekarang</a></small>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

</body>
</html>

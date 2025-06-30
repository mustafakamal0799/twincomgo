<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SISB</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('{{ asset('images/bg1.jpg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .card.login-card {
            max-width: 1000px;
            width: 100%;
            box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
            border: none;
        }

        .login-image {
            background-image: url('{{ asset('images/g.jpg') }}');
            background-size: cover;
            background-position: center;
            min-height: 100%;
        }

        .form-side {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">        
        <div class="card login-card p-2">
            <div class="row g-0" style="min-height: 450px;">
                @if (session('loginGagal'))
                    <div class="alert alert-warning">
                        {{ session('loginGagal') }}
                    </div>
                @endif
                <!-- Kolom kiri: Gambar -->
                <div class="col-md-6 d-none d-md-block login-image"></div>

                <!-- Kolom kanan: Form -->
                <div class="col-md-6 form-side">
                    <p class="text-center">
                        <img src="{{asset('images/logo-hijau-tua.png')}}" alt="Logo SISB" style="width: 200px">
                    </p>
                    <h4 class="text-center mb-6"><strong>SISTEM INFORMASI
                        <br> STOK BARANG</strong></h4>

                    {{-- Alert login gagal --}}
                    @if (session('loginError'))
                        <div class="alert alert-warning" role="alert">
                            {{ session('loginError') }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{route('auth.login-post')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                                <label class="form-check-label" for="showPassword">Tampilkan Password</label>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <small><a href="{{ route('password.request') }}" style="text-decoration: none">Lupa password?</a></small>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>                       
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
          function togglePassword() {
                const passwordField = document.getElementById("password");
                passwordField.type = passwordField.type === "password" ? "text" : "password";
            }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">
    <title>Login SISB</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

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
            max-width: 900px;
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card p-3 login-card">
            <div class="row g-0" style="min-height: 450px;">
                
                <!-- Kolom kiri: Gambar -->
                <div class="col-md-6 d-none d-md-block login-image"></div>

                <!-- Kolom kanan: Form -->
                <div class="col-md-6 form-side">
                    <h2 class="text-center mb-2" style="font-style: italic">SISB</h2>
                    <h5 class="text-center mb-3">Reset Password</h5>
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{route('password.update')}}">
                        @csrf
                        <input type="hidden" name="token" value="{{$token}}">
                        <input type="hidden" name="email" value="{{$email}}">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                                <label class="form-check-label" for="showPassword">Tampilkan Password</label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const pwConfirm = document.getElementById('password_confirmation');
            const type = pw.type === 'password' ? 'text' : 'password';
            pw.type = type;
            pwConfirm.type = type;
            }
    </script>

    @if (session('status'))
        <script>
            Toastify({
                text: "{{ session('status') }}",
                duration: 3000,
                gravity: "top", // top or bottom
                position: "right", // left, center or right
                backgroundColor: "#28a745", // warna hijau sukses
                stopOnFocus: true, 
            }).showToast();
        </script>
    @endif
</body>
</html>

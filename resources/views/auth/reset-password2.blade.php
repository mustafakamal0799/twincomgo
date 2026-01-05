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
        .input {
        line-height: 28px;
        border: 2px solid transparent;
        border-bottom-color: #777;
        padding: .2rem 0;
        outline: none;
        background-color: transparent;
        color: #0d0c22;
        transition: .3s cubic-bezier(0.645, 0.045, 0.355, 1);
        }

        .input:focus, .input:hover {
        outline: none;
        padding: .2rem 1rem;
        border-radius: 1rem;
        border-color: #7a9cc6;
        }

        .input::placeholder {
        color: #777;
        }

        .input:focus::placeholder {
        opacity: 0;
        transition: opacity .3s;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">
        <div class="card p-3 login-card">
            <div class="row g-0" style="min-height: 450px;">
                
                <!-- Kolom kiri: Gambar -->
                <div class="col-md-6 d-none d-md-block d-flex align-items-center justify-content-center" style="background-color: #ffffff;">
                    <dotlottie-wc
                    src="https://lottie.host/a12f57e7-c27a-4008-8c69-46a49d02f443/zxs5Y20F0O.lottie"
                    style="max-width: 1000px; width: 100%; height: 100%;"
                    speed="1"
                    autoplay
                    loop
                    ></dotlottie-wc>
                </div>

                <!-- Kolom kanan: Form -->
                <div class="col-md-6 form-side">
                    <h2 class="text-center mb-2">TWINCOMGO</h2>
                    <h5 class="text-center mb-3">Reset Password</h5>

                    <form method="POST" action="{{route('password.update')}}">
                        @csrf
                        <input type="hidden" name="token" value="{{$token}}">
                        <input type="hidden" name="email" value="{{$email}}">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control input" id="password" name="password" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control input" id="password_confirmation" name="password_confirmation" required>

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

    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
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

    {{-- ERROR VALIDASI -> munculkan sebagai toast merah --}}
    @if ($errors->any())
        <script>
            (function(){
                const errs = @json($errors->all());
                errs.forEach(function(msg){
                    Toastify({
                        text: msg,
                        duration: 5000,
                        gravity: "top",
                        position: "center",
                        backgroundColor: "#ff3b30", // merah
                        stopOnFocus: true,
                        close: true,
                    }).showToast();
                });
            })();
        </script>
    @endif

    {{-- SUCCESS (mis. dari redirect lain yang balikin 'status') --}}
    @if (session('status'))
        <script>
            Toastify({
                text: "{{ session('status') }}",
                duration: 4000,
                gravity: "top",
                position: "center",
                backgroundColor: "#28a745", // hijau
                stopOnFocus: true,
                close: true,
            }).showToast();
        </script>
    @endif

    {{-- OPTIONAL: sukses generik (kalau pakai key 'success' dari controller) --}}
    @if (session('success'))
        <script>
            Toastify({
                text: "{{ session('success') }}",
                duration: 4000,
                gravity: "top",
                position: "center",
                backgroundColor: "#28a745",
                stopOnFocus: true,
                close: true,
            }).showToast();
        </script>
    @endif
</body>
</html>

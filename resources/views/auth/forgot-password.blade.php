<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">
    <title>Login SISB</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="card p-3 login-card">
            <div class="row g-0 card-body-wrapper" style="min-height: 450px;">
                <!-- KIRI: Gambar -->
                <div class="col-md-6 d-none d-md-block login-image d-flex align-items-center justify-content-center" style="background-color: #ffffff;">
                    <dotlottie-wc
                    src="https://lottie.host/0f54c466-1a48-4f1b-bad4-2ef341ea8656/DPy7qxb43Z.json"
                    style="max-width: 1000px; width: 100%; height: 100%;"
                    speed="1"
                    autoplay
                    loop
                    ></dotlottie-wc>
                </div>

                <!-- KANAN: Form -->
                <div class="col p-5 d-flex flex-column justify-content-center">
                    <h2 class="text-center mb-4" style="font-style: italic">SISB</h2>
                    <h5 class="text-center mb-4">Masukkan Email Reset</h5>
                    <form method="POST" action="{{route('password.email')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control input" id="email" name="email" required placeholder="Masukkan Email">
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

    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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

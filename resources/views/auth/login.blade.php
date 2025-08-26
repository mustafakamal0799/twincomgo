<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">
    <title>Twincomgo | Login</title>
    

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
            max-width: 1000px;
            width: 100%;
            box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
            border: none;
        }

        .login-image {
            background-size: cover;
            background-position: center;
            min-height: 100%;
            background: #fff;
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
        /* From Uiverse.io by cssbuttons-io */ 
        .c-button {
        color: #000;
        font-weight: 700;
        font-size: 16px;
        text-decoration: none;
        padding: 0.4em 1.1em;
        cursor: pointer;
        display: inline-block;
        vertical-align: middle;
        position: relative;
        z-index: 1;
        }

        .c-button--gooey {
        color: #0649d9;
        text-transform: uppercase;
        letter-spacing: 2px;
        border: 2px solid #0649d9;
        border-radius: 10px;
        position: relative;
        transition: all 700ms ease;
        }

        .c-button--gooey .c-button__blobs {
        height: 100%;
        filter: url(#goo);
        overflow: hidden;
        position: absolute;
        top: 0;
        left: 0;
        bottom: -3px;
        right: -1px;
        z-index: -1;
        }

        .c-button--gooey .c-button__blobs div {
        background-color: #0649d9;
        width: 34%;
        height: 100%;
        border-radius: 100%;
        position: absolute;
        transform: scale(1.4) translateY(125%) translateZ(0);
        transition: all 700ms ease;
        }

        .c-button--gooey .c-button__blobs div:nth-child(1) {
        left: -5%;
        }

        .c-button--gooey .c-button__blobs div:nth-child(2) {
        left: 30%;
        transition-delay: 60ms;
        }

        .c-button--gooey .c-button__blobs div:nth-child(3) {
        left: 66%;
        transition-delay: 25ms;
        }

        .c-button--gooey:hover {
        color: #fff;
        }

        .c-button--gooey:hover .c-button__blobs div {
        transform: scale(1.4) translateY(0) translateZ(0);
}
    </style>
</head>
<body>
    <div id="loader-display" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0, 0, 0, 0.377); z-index:1050; display:flex; justify-content:center; align-items:center;">
        <dotlottie-wc
        src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
        style="width: 100%; max-width: 300px; height: auto; display: block; margin: auto;"
        speed="1"
        autoplay
        loop
        ></dotlottie-wc>
    </div>
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">        
        <div class="card login-card p-2">
            <div class="row g-0" style="min-height: 450px;">
                @if (session('loginGagal'))
                    <div class="alert alert-warning">
                        {{ session('loginGagal') }}
                    </div>
                @endif
                <!-- Kolom kiri: Gambar -->
                <div class="col-md-6 d-none d-md-block login-image d-flex align-items-center justify-content-center" style="background-color: #ffffff;">
                    <dotlottie-wc
                    src="https://lottie.host/84e03c2a-1407-41ba-b04e-cc6e3a140915/MSHr2gOVGR.json"
                    style="max-width: 1000px; width: 100%; height: 100%;"
                    speed="1"
                    autoplay
                    loop
                    ></dotlottie-wc>
                </div>

                <!-- Kolom kanan: Form -->
                <div class="col-md-6 form-side">
                    <p class="text-center">
                        <img src="{{asset('images/logo-hijau-tua.png')}}" alt="Logo SISB" style="width: 200px">
                    </p>
                    <h4 class="text-center mb-6"><strong>SISTEM INFORMASI
                        <br> STOK BARANG</strong></h4>
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{route('auth.login.post')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control input" id="email" name="email" required placeholder="Masukkan Email">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control input" id="password" name="password" required placeholder="Masukkan Password">

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                                <label class="form-check-label" for="showPassword">Tampilkan Password</label>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <small><a href="{{ route('password.request') }}" style="text-decoration: none">Lupa password?</a></small>
                        </div>
                        <div class="d-grid mt-4">
                            {{-- <button type="submit" class="btn btn-primary">Login</button> --}}
                            <button class="c-button c-button--gooey" type="submit"> Login
                            <div class="c-button__blobs">
                            <div></div>
                            <div></div>
                            <div></div>
                            </div>
                            </button>
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="display: block; height: 0; width: 0;">
                            <defs>
                                <filter id="goo">
                                <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur>
                                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo"></feColorMatrix>
                                <feBlend in="SourceGraphic" in2="goo"></feBlend>
                                </filter>
                            </defs>
                            </svg>
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

    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    @if (session('logoutSuccess'))
        <script>
            Toastify({
                text: "{{ session('logoutSuccess') }}",
                duration: 5000,
                gravity: "top", // top or bottom
                position: "center", // left, center or right
                backgroundColor: "#ff0000", // warna hijau sukses
                stopOnFocus: true, 
            }).showToast();
        </script>
    @endif

    @if (session('loginError'))
        <script>
            Toastify({
                text: "{{ session('loginError') }}",
                duration: 5000,
                gravity: "top", // top or bottom
                position: "center", // left, center or right
                backgroundColor: "#ffa500", // warna hijau sukses
                stopOnFocus: true, 
            }).showToast();
        </script>
    @endif
</body>
</html>

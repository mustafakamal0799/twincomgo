<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twincomgo</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    <div class="bg-overlay"></div>

    {{-- Navbar --}}
    <nav class="navbar navbar-dark fixed-top px-4 d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold" href="#">Twincomgo</a>

        @auth
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-2"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="px-3">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth
    </nav>

    {{-- Konten utama --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer>
        <small>© {{ date('Y') }} CV. TWIN GROUP — All Rights Reserved.</small>
    </footer>

    
<!-- Library JS (CDN) -->
<script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>

<!-- Script utama proyek -->
<script src="{{ asset('js/layout.js') }}"></script>

@stack('scripts')
</body>
</html>

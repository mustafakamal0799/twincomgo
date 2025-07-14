<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Aplikasi')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }

    .full-height {
      height: 100vh;
    }

    .card-body-scroll {
      overflow-y: auto;
    }
  </style>

  @stack('styles')
</head>
<body>
  <div class="d-flex flex-column full-height">
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark px-3">
      <span class="navbar-brand mb-0 h1">Navbar</span>
    </nav>

    <!-- Content -->
    <div class="flex-grow-1 d-flex">
      <div class="flex-grow-1">
        @yield('content')
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>

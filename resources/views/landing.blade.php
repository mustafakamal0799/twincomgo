@extends('layouts.app')

@section('content')


  @include('components.hero')

  <section id="promo" class="py-5">
      <div class="container">
          @include('components.promo', ['promos' => $promos])
      </div>
  </section>

  <section id="produk" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Produk Terbaru & Terpopuler</h2>
      {{-- @include('components.new') --}}
    </div>
  </section>

  <section id="testimoni" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Testimoni Pelanggan</h2>
      {{-- @include('components.testimonial') --}}
    </div>
  </section>

  <section class="py-5 text-center">
    <h3>Ayo, Pesan Sekarang Juga!</h3>
    <a href="https://wa.me/6282154923388" class="btn btn-success btn-lg mt-3">Chat WhatsApp</a>
  </section>

  <section id="kontak" class="py-5 bg-dark text-white">
    <div class="container">
      <h2 class="text-center mb-4">Hubungi Kami</h2>
      {{-- @include('components.contact') --}}
    </div>
  </section>


  @push('styles')
  <style>
    /* ... CSS tadi di sini ... */
  </style>
  @endpush

  @push('scripts')
  <script>
    // ... JS scrollPromo di sini ...
  </script>
  @endpush

@endsection

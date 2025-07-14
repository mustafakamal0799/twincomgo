<style>
.swiper {
  width: 100%;
  height: auto;
  position: relative;
  padding: 5px;
}

.swiper-slide {
  display: flex;
  justify-content: center;
  align-items: stretch;
  border-radius: 10px;
}

.card-img {
  width: 418px;
  display: flex;
  flex-direction: column; 
  border-radius: 10px;
  overflow: hidden;
  /* box-shadow: 0px 2px 5px 1px rgba(0, 0, 0, 0.24); */
  z-index: 10;
}

.card-desc {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-start;
}
.card-desc .card-body {
  height: 180px;
  border: none;
}
/* Tombol navigasi custom kanan atas */
.swiper-custom-nav {
  position: absolute;
  top: -40px; /* Atur sesuai preferensi */
  right: 0;
  z-index: 10;
  display: flex;
  gap: 10px;
}

.swiper-button-prev,
.swiper-button-next {
  all: unset;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border: 2px solid #ccc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s;
  background: white;
}

.swiper-button-prev:hover,
.swiper-button-next:hover {
  background-color: #dc3545;
  color: #fff;
  border-color: #dc3545;
}

.swiper-button-prev i,
.swiper-button-next i {
  font-size: 16px;
  color: inherit;
}

/* Untuk menghilangkan icon panah default swiper */
.swiper-button-prev::after,
.swiper-button-next::after {
  display: none;
}

/* Rapatkan antar slide */
.mySwiper {
  padding-bottom: 40px;
}

.swiper-slide {
  padding: 0 8px;
}
</style>


<section id="promo" class="py-5 overflow-hidden">
  <div class="container text-center" style="margin-bottom: 100px;">
    <h2 class="fw-bold">🔥 Promo Spesial Minggu Ini 🔥</h2>
    <p class="text-muted">Dapatkan penawaran terbaik sebelum kehabisan!</p>
  </div>
  <div class="swiper mySwiper">    
    <div class="swiper-wrapper">
      @foreach ($promos as $promo)
      <div class="swiper-slide p-0">
        <div class="container-fluid d-flex flex-column p-0">
          <div class="card card-img border-0">
            <div class="card-body p-0">
              <img src="{{ Storage::url($promo->gambar) }}" class="card-img-top" alt="{{ $promo->judul }}" style="height: 200px; object-fit: contain;">
            </div>
          </div>
          <div class="card border-0 card-desc">            
            <div class="card-body">
              <h5 class="card-title text-start">{{ $promo->judul }}</h5>
              <p class="text-muted small">{{ $promo->deskripsi }}</p>
              <div class="mb-0">
                <span class="text-danger fw-bold">Rp {{ number_format($promo->harga_diskon, 0, ',', '.') }}</span>
                <span class="text-muted text-decoration-line-through small">Rp {{ number_format($promo->harga_asli, 0, ',', '.') }}</span>
              </div>
              <span class="badge bg-success">Hemat Rp {{ number_format($promo->harga_asli - $promo->harga_diskon, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>        
      </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-custom-nav">
      <div class="swiper-button-prev"><i class="fas fa-chevron-left"></i></div>
      <div class="swiper-button-next"><i class="fas fa-chevron-right"></i></div>
    </div>
  </div>
</section>



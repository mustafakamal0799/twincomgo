@extends('layouts.app')

@section('content')
<style>
/* 🌈 HEADER */
.detail-header {
    background: linear-gradient(90deg, #1f2937, #374151);
    color: white;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}
.detail-header h3 { 
    font-weight: 700; 
    letter-spacing: 0.3px; 
    font-size: 1.35rem;
}

/* 💰 HARGA BOX */
.price-box {
    border-radius: 12px;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    background: #fff;
    padding: 1.25rem;
    transition: 0.3s;
}
.price-box:hover { transform: translateY(-3px); }
.price-box .title {
    font-weight: 700;
    margin-bottom: .5rem;
}
.text-user {
    color: #6c757d;
    text-decoration: line-through;
    font-size: 1rem;
    margin-right: .6rem;
}
.text-reseller {
    color: #000;
    font-weight: 700;
    font-size: 1.5rem;
}

/* 🏬 GUDANG */
.warehouse-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 1rem;
}
.warehouse-card .card-header {
    font-weight: 600;
    background: #f8f9fa;
}
.warehouse-card .table {
    font-size: .9rem;
}

/* 🖼️ CAROUSEL IMAGE */
.carousel-item img {
    border-radius: 12px;
}

/* 💎 RESPONSIVE TUNING */
@media (max-width: 1199.98px) {
    .detail-header h3 { font-size: 1.2rem; }
}

@media (max-width: 991.98px) {
    .price-box { padding: 1rem; }
    .text-reseller { font-size: 1.3rem; }
    .text-user { font-size: 0.95rem; }
    .detail-header {
        padding: 15px 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}

@media (max-width: 767.98px) {
    .container-fluid {
        padding: 0 12px;
    }
    .detail-header {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }
    .detail-header h3 {
        font-size: 1.1rem;
        width: 100%;
        margin-bottom: 10px;
    }
    .detail-header a.btn {
        width: 100%;
        border-radius: 8px;
    }

    .card.p-4 {
        padding: 1rem !important;
    }
    .text-reseller { 
        font-size: 1.1rem; 
        display: block;
    }
    .text-user { 
        font-size: .85rem; 
        display: block;
        margin-bottom: 4px;
    }

    .fw-bold.text-primary {
        font-size: 1rem;
        text-align: center;
    }

    /* Gambar ke atas, harga ke bawah */
    .row.g-4.align-items-center {
        flex-direction: column;
    }

    /* Tabel gudang agar tidak melebar */
    .warehouse-card .table {
        font-size: .8rem;
    }
    .warehouse-card .card-header {
        font-size: .9rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .price-box { padding: 0.75rem; }
    .price-box .title { font-size: .9rem; }
    .text-reseller { font-size: 1rem; }
    .text-user { font-size: .8rem; }
    .fw-semibold { font-size: .85rem; }
    .card-footer strong { font-size: .9rem; }
    .badge.bg-secondary {
        font-size: 0.85rem !important;
        padding: 4px 8px;
    }
}
</style>

<div class="container-fluid">

    {{-- 🌈 HEADER --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3><i class="bi bi-box-seam me-2"></i> Detail Produk</h3>
        <a href="#" id="btn-back" class="btn btn-light fw-semibold">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </a>
    </div>

    {{-- 💰 HARGA & GAMBAR --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-4 align-items-center">
            {{-- 🖼️ Gambar --}}
            <div class="col-md-4 text-center">
                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @forelse ($images as $index => $file)
                            @php
                                $imageUrl = $file ? route('proxy.image', ['fileName' => $file, 'session' => $session]) : asset('/images/noimage.jpg');
                            @endphp
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="Gambar {{ $index + 1 }}"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                    onerror="this.onerror=null; this.src='{{ asset('/images/noimage.jpg') }}';"
                                >
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <img
                                    src="{{ asset('/images/noimage.jpg') }}"
                                    alt="Gambar default"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                >
                            </div>
                        @endforelse

                        <button class="carousel-control-prev" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Sebelumnya</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Selanjutnya</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 💰 Harga --}}
            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-md-10">
                        <h4 class="fw-bold text-primary mb-2 mb-md-0 me-3">{{ $item['name'] }}</h4>
                    </div>
                    <div class="col-md-2 text-md-end text-center">
                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3 fs-6">
                            <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="price-box">
                    <div class="title"><i class="bi bi-cash-stack me-1"></i> Harga</div>
                    <div class="d-flex flex-wrap align-items-baseline">
                        @if(isset($prices['user']) && $prices['user'] > ($prices['reseller'] ?? 0))
                            <span class="text-user d-inline-block me-2">
                                Rp {{ number_format($prices['user'], 0, ',', '.') }}
                            </span>
                        @endif
                        <span class="text-reseller d-inline-block">
                            Rp {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="small text-muted">Garansi</div>
                            <div class="fw-semibold">{{ $item['charField7'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🏬 GUDANG --}}
    @foreach (['Store', 'Tsc', 'Reseller', 'Konsinyasi'] as $type)
        @php
            $var = 'warehouses' . $type;
            $total = 'total' . $type;
        @endphp
        @if(!empty($$var) && count($$var) > 0)
            <div class="card warehouse-card" id="warehouse{{ $type }}">
                <div class="card-header text-center text-md-start">{{ $type }}</div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Lokasi</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @include("partials.table{$type}", [$var => $$var])
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <strong>Total</strong>
                    <strong>{{ $$total }}</strong>
                </div>
            </div>
        @endif
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const backBtn = document.getElementById('btn-back');

    backBtn.addEventListener('click', function (e) {
        e.preventDefault();

        // ✅ Jika ada riwayat halaman sebelumnya di browser
        if (window.history.length > 1) {
            window.history.back();
        } else {
            // fallback kalau user datang langsung ke halaman detail
            window.location.href = "{{ route('reseller.index') }}";
        }
    });
});
</script>
@endsection

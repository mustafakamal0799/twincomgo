@extends('layout')

@section('content')
<style>
/* ========================= */
/* 💻 DESKTOP DEFAULT */
/* ========================= */
.table th, .table td {
    vertical-align: middle;
}
.text-reseller {
    color: #000000;
}
.text-user {
    color: #6c757d;
    text-decoration: line-through;
}

/* ========================= */
/* 📱 MOBILE ONLY STYLES */
/* ========================= */
@media (max-width: 767.98px) {
    body {
        font-size: 12px;
    }

    .desktop-view { display: none !important; }

    .mobile-card {
        background: #fff;
        border-radius: .6rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .item-image {
        width: 100%;
        border-radius: .6rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        object-fit: contain;
        max-height: 250px;
        margin-bottom: .75rem;
    }

    .item-title {
        font-size: 14px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: .5rem;
        line-height: 1.3;
    }

    .price-section {
        background: #f8f9fa;
        border-radius: .5rem;
        padding: .75rem;
        margin-top: .5rem;
        font-size: 12px;
    }

    .price-label {
        font-weight: 600;
        color: #555;
    }

    .price-combo {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
    }

    .price-reseller {
        color: #198754;
        font-size: 14px;
        font-weight: 700;
    }

    .price-user {
        color: #6c757d;
        text-decoration: line-through;
        font-size: 11px;
    }

    .warranty-section {
        margin-top: .8rem;
        display: flex;
        flex-direction: column;
        align-items: flex-center;
    }

    .warranty-title {
        font-weight: 600;
        color: #555;
        margin-bottom: .25rem;
    }

    .warranty-text {
        font-size: 12px;
        color: #000;
        line-height: 1.3;
        word-wrap: break-word;
    }

    .warehouse-section {
        margin-top: 1rem;
        background: #fff;
        border-radius: .6rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: .75rem;
    }

    .warehouse-title {
        font-size: 13px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .warehouse-table {
        width: 100%;
        font-size: 11px;
    }

    .warehouse-table th {
        font-weight: 600;
        background: #f1f3f5;
        padding: .4rem;
    }

    .warehouse-table td {
        padding: .35rem .4rem;
        border-top: 1px solid #dee2e6;
    }

    .warehouse-footer {
        margin-top: .4rem;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        padding-top: .4rem;
        border-top: 1px solid #dee2e6;
    }

    .btn-back {
        font-size: 12px;
        padding: 6px 10px;
    }
}

/* ========================= */
/* 💻 SEMBUNYIKAN MOBILE DI DESKTOP */
/* ========================= */
@media (min-width: 768px) {
    .mobile-view { display: none !important; }
}
</style>

<div class="container py-4">

    {{-- 💻 DESKTOP VIEW --}}
    <div class="desktop-view">
        <h3 class="fw-bold mb-3">
            <i class="bi bi-box-seam me-2"></i> Detail Item
        </h3>

        <div class="card p-4 mb-4 shadow-sm">
            <div class="row align-items-center">

                {{-- 🖼️ Gambar --}}
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    @php
                        $imageUrl = isset($fileName[0])
                            ? route('proxy.image', ['fileName' => $fileName[0], 'session' => $session])
                            : asset('/images/noimage.jpg');
                    @endphp
                    <img src="{{ $imageUrl }}" alt="Gambar"
                         class="img-fluid rounded shadow-sm" style="max-height:300px;object-fit:contain;">
                </div>

                {{-- 📋 Detail --}}
                <div class="col-md-8">
                    <h4 class="fw-bold text-primary mb-3">{{ $item['name'] }}</h4>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td>Kode</td><td>:</td><td>{{ $item['no'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Harga</td><td>:</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(isset($prices['user']) && $prices['user'] > ($prices['reseller'] ?? 0))
                                        <span class="text-user">
                                            Rp. {{ number_format($prices['user'], 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="text-reseller">
                                        Rp. {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Garansi</td><td>:</td>
                            <td>{{ $item['charField7'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>

        {{-- 🏬 Gudang --}}
        @foreach (['Store', 'Tsc', 'Reseller', 'Konsinyasi'] as $type)
            @php
                $var = 'warehouses' . $type;
                $total = 'total' . $type;
            @endphp
            @if(!empty($$var) && count($$var) > 0)
                <div class="card mt-2 shadow-sm">
                    <div class="card-header">
                        <span class="fs-3 fw-bold">
                            {{ $type }}
                        </span>
                    </div>
                    <div class="card-body py-2">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr>
                                    <th>Lokasi</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Satuan</th>
                                </tr></thead>
                                <tbody>
                                    @include("partials.table{$type}", [$var => $$var])
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer py-2 d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold">{{ $$total }}</span>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="mt-4">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- 📱 MOBILE VIEW --}}
    <div class="mobile-view">

        <div class="mobile-card">
            @php
                $imageUrl = isset($fileName[0])
                    ? route('proxy.image', ['fileName' => $fileName[0], 'session' => $session])
                    : asset('/images/noimage.jpg');
            @endphp
            <img src="{{ $imageUrl }}" alt="Item Image" class="item-image"
                 onerror="this.onerror=null;this.src='{{ asset('/images/noimage.jpg') }}';">

            <div class="item-title">{{ $item['name'] }}</div>

            <div class="price-section">
                <div class="d-flex justify-content-between">
                    <span class="price-label">Kode</span>
                    <span>{{ $item['no'] ?? '-' }}</span>
                </div>

                {{-- 💰 Harga Gabung --}}
                <div class="d-flex justify-content-between mt-1">
                    <span class="price-label">Harga</span>
                    <div class="price-combo">
                        <span class="price-reseller">Rp {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}</span>
                        @if(isset($prices['user']) && $prices['user'] > ($prices['reseller'] ?? 0))
                            <span class="price-user">Rp {{ number_format($prices['user'], 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                {{-- 🧾 Garansi: Title di atas, isi di bawah --}}
                <div class="warranty-section">
                    <div class="warranty-title text-center">Garansi</div>
                    <div class="warranty-text text-center">
                        {{ $item['charField7'] ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 🏬 Gudang --}}
        @foreach (['Store', 'Tsc', 'Reseller', 'Konsinyasi'] as $type)
            @php
                $var = 'warehouses' . $type;
                $total = 'total' . $type;
            @endphp
            @if(!empty($$var) && count($$var) > 0)
                <div class="warehouse-section">
                    <div class="warehouse-title">
                        <i class="bi bi-shop"></i> {{ $type }}
                    </div>
                    <table class="warehouse-table">
                        <thead><tr>
                            <th>Lokasi</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satuan</th>
                        </tr></thead>
                        <tbody>
                            @include("partials.table{$type}", [$var => $$var])
                        </tbody>
                    </table>
                    <div class="warehouse-footer">
                        <span>Total</span>
                        <span>{{ $$total }}</span>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="text-center mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-back">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection

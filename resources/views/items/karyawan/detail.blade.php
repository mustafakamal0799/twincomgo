@extends('layouts.app')

@section('content')

{{-- ============================
    STYLE
============================ --}}
@include('items.karyawan.css')

<div class="container-fluid">

    {{-- ============================
        HEADER
    ============================ --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="fw-bold"><i class="bi bi-box"></i> Detail Produk</h3>

        <div>
            <button class="btn btn-light fw-semibold me-2" onclick="history.back()">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </button>

            <a href="#" id="btn-export-pdf" class="btn btn-danger fw-semibold">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    {{-- ============================
        FILTERS
    ============================ --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-3">

            {{-- Cabang --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Harga Cabang</label>
                <select id="branchSelect" class="form-select">
                    <option value="">Semua Cabang</option>
                </select>

                <div id="priceSpinner" class="spinner-border spinner-border-sm text-success d-none mt-1"></div>
            </div>

            {{-- Jenis Harga --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Tampilkan Harga</label>
                <select id="priceType" class="form-select">
                    <option value="all" selected>Semua Harga</option>
                    <option value="user">User</option>
                    <option value="reseller">Reseller</option>
                </select>
            </div>

            {{-- Lokasi Gudang --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Filter Gudang</label>
                <select id="warehouseFilter" class="form-select" multiple>
                    <option value="store">Store</option>
                    <option value="tsc">TSC</option>
                    <option value="reseller">Reseller</option>
                    <option value="konsinyasi">Konsinyasi</option>
                    <option value="panda">Panda</option>
                    <option value="transit">Transit</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ============================
        IMAGE + PRICE
    ============================ --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-4 align-items-center">

            {{-- IMAGE --}}
            <div class="col-md-4 text-center">
                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel" >
                    <div class="carousel-inner">
                        @forelse ($images as $index => $file)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img 
                                    src="{{ route('proxy.image', ['file' => $file, 'session' => $session]) }}"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                    onerror="this.onerror=null; this.src='{{ asset('images/noimage.jpg') }}';"
                                >
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <img 
                                    src="{{ asset('images/noimage.jpg') }}" 
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                >
                            </div>
                        @endforelse
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>

            {{-- DETAIL & PRICE --}}
            <div class="col-md-8">

                <div class="row g-2 align-items-center mb-3">
                    <!-- Untuk mobile: full width, untuk desktop: 10 kolom -->
                    <div class="col-12 col-md-10">
                        <h5 class="fw-bold text-primary mb-0">{{ $item['name'] }}</h5>
                    </div>
                    
                    <!-- Untuk mobile: full width dengan text alignment, untuk desktop: 2 kolom -->
                    <div class="col-12 col-md-2 text-md-end mt-2 mt-md-0 text-end">
                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3 fs-6 d-inline-block">
                            <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6" id="userPriceBox">
                        <div class="price-box p-3 h-100">
                            <div class="title text-success mb-2">
                                <i class="bi bi-person-circle me-1"></i> User
                            </div>
                            <p class="text-muted mb-1">Harga</p>
                            @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                                @foreach($unitPrices as $unitName => $p)
                                    @if(isset($p['user']) && $p['user'] > 0)
                                        <h3 id="userPrice" class="price-user" data-unit="{{$unitName}}">
                                            Rp {{ number_format($p['user'], 0, ',', '.') }} / {{ strtoupper($unitName) }}
                                        </h3>
                                    @endif
                                @endforeach
                            @else
                                <h3 id="userPriceMain" class="price-user">
                                    Rp {{ number_format($prices['user'],0,',','.') }}
                                </h3>
                            @endif                            
                            <hr>
                            <p class="text-muted mb-1">Garansi</p>
                            <p class="fw-semibold">{{ $item['charField6'] ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6" id="resellerPriceBox">
                        <div class="price-box p-3 h-100">
                            <div class="title text-primary mb-2">
                                <i class="bi bi-people-fill me-1"></i> Reseller
                            </div>
                            <p class="text-muted mb-1">Harga</p>
                            @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                                @foreach($unitPrices as $unitName => $p)
                                    @if(isset($p['reseller']) && $p['reseller'] > 0)
                                        <h3 id="resellerPrice" class="price-reseller" data-unit="{{$unitName}}">
                                            Rp {{ number_format($p['reseller'],0,',','.') }} / {{ strtoupper($unitName) }}
                                        </h3>
                                    @endif
                                @endforeach
                            @else
                                <h3 id="resellerPriceMain" class="price-reseller">
                                    Rp {{ number_format($prices['reseller'],0,',','.') }}
                                </h3>
                            @endif  
                            <hr>
                            <p class="text-muted mb-1">Garansi</p>
                            <p class="fw-semibold">{{ $item['charField7'] ?? '-' }}</p>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        @if(trim($note) !== '')
        <div class="card p-4 mb-2 mt-3 shadow-sm" id="catatanCard">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-journal-text me-1"></i> Selling Point
            </h5>

            <div class="text-secondary" style="font-size: 16px;">
                {!! nl2br(e($note)) !!}
            </div>
        </div>
        @endif
    </div>

    {{-- ============================
    WAREHOUSE GROUP TABLES
    ============================ --}}
    @foreach([
        'store' => 'Store',
        'tsc' => 'TSC',
        'reseller' => 'Reseller',
        'konsinyasi' => 'Konsinyasi',
        'panda' => 'Panda',
    ] as $key => $label)

        @php
            $var = 'warehouses' . ucfirst($key);
        @endphp

        @if(isset($$var) && count($$var) > 0)

            <div class="warehouse-card card warehouse-{{ $key }}" id="warehouse_{{ $key }}">
                <div class="card-header fw-bold">{{ $label }}</div>

                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satuan</th>
                        </tr>
                        </thead>

                        <tbody>
                            @include("partials.table" . ucfirst($key), [ $var => $$var ])
                        </tbody>
                    </table>
                </div>

                {{-- ===== TOTAL DI BAWAH CARD ===== --}}
                <div class="px-3 py-2 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>

                    <span class="total-badge" id="total_{{ $key }}">
                        {{ number_format($$var->sum('balance'), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endif
    @endforeach

    {{-- ============================
    TRANSIT (SPECIAL)
    ============================ --}}
    @if(!empty($warehousesTransit) && count($warehousesTransit) > 0)

    <div class="warehouse-card card  warehouse-transit" id="warehouse_transit">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center">

        {{-- KIRI --}}
        <div class="d-flex align-items-center gap-2 text-start">
            <span>Transit (AOL System)</span>
            <span class="badge bg-secondary">transit</span>
        </div>

        {{-- KANAN --}}
        <span class="total-badge" id="total_transit">
            {{ number_format($warehousesTransit->sum('balance'), 0, ',', '.') }}
        </span>

    </div>
</div>

    @endif
</div>


{{-- ============================
    SCRIPT SECTION
============================ --}}
@include('items.karyawan.partial-js')

@endsection

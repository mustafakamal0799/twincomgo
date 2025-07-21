@extends('layout')

@section('content')

@php
    use Illuminate\Support\Facades\Auth;

    $sellingPrices = collect($item['detailSellingPrice']);

    $resellerPrice = $sellingPrices->first(fn($p) => strtolower($p['priceCategory']['name']) === 'reseller')['price'] ?? 0;

    $userPrice = $sellingPrices->first(fn($p) => strtolower($p['priceCategory']['name']) === 'user')['price'] ?? 0;

    $status = Auth::user()->status;

    $totalKonsinyasiStok = collect($konsinyasiWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalNonKonsinyasiStok = collect($nonKonsinyasiWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalTscStok = collect($tscWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalResellerStok = collect($resellerWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalTransitStok = collect($transitWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalPandaStok = collect($pandaWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);

    $filteredNonKonsinyasi = collect($nonKonsinyasiWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? 0) > 0)->values();

    // Tetapkan harga default dan harga final (hasil penyesuaian atau default)
    $finalUserPrice = $userPrice;
    $finalResellerPrice = $resellerPrice;
    $isUserPriceAdjusted = false;
    $isResellerPriceAdjusted = false;

    if ($adjustedPrice && $priceCategory) {
        if (strtolower($priceCategory) === 'user') {
            $finalUserPrice = $adjustedPrice;
            $isUserPriceAdjusted = true;
        } elseif (strtolower($priceCategory) === 'reseller') {
            $finalResellerPrice = $adjustedPrice;
            $isResellerPriceAdjusted = true;
        }
    }
@endphp

<style>
    body {
        overflow-x: hidden;
    }
    /* From Uiverse.io by fabiodevbr */ 
    .button-copy {
    background-color: #f2f7fa;
    width: 100px;
    height: 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    overflow: hidden;
    transition-duration: 700ms;
    }

    .button-copy span:first-child {
    color: #0e418f;
    position: absolute;
    transform: translate(-50%, -50%);
    }

    .button-copy span:last-child {
    position: absolute;
    color: #b5ccf3;
    opacity: 0;
    transform: translateY(100%) translateX(-50%);
    height: 14px;
    line-height: 13px;
    }

    .button-copy:focus {
    background-color: #0e418f;
    width: 100px;
    height: 30px;
    transition-delay: 100ms;
    transition-duration: 500ms;
    }

    .button-copy:focus span:first-child {
    color: #b5ccf3;
    transform: translateX(-50%) translateY(-150%);
    opacity: 0;
    transition-duration: 500ms;
    }

    .button-copy:focus span:last-child {
    transform: translateX(-50%) translateY(-50%);
    opacity: 1;
    transition-delay: 300ms;
    transition-duration: 500ms;
    }

    .button-copy:focus:end {
    background-color: #ffffff;
    width: 100px;
    height: 30px;
    transition-duration: 900ms;
    }

    .centralize {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    }

    .description {
    margin-top: 10px;
    color: #b5ccf3;
    }

    .container-fluid {
        padding: 0;
    }

    i {
        font-size: 20px;
    }

    .label-col {
        width: 120px;
        font-weight: bold;
        margin-right: 8px;
        position: relative;
    }

    .label-col::after {
        content: ":";
        position: absolute;
        right: 0;
    }

    .value-col {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }
    .table th {  
        font-size: 16px; 
    }

    .card-total {
        width: 60px;
        height: 32px;
        background-color: #6c757d; /* abu-abu gelap elegan */
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        margin-left: auto; /* tengah secara horizontal kalau perlu */
        box-shadow: 0 1px 3px rgba(0,0,0,0.2); /* sedikit bayangan */
        color: #fff;
    }
    
    @media only screen and (max-width: 768px) {
        .title { 
            font-size: 15px; 
        }

        .title-table {
            font-size: 15px;
        }

        .title-total {
            font-size: 15px;
        }

        .totalNonKonsinyasiStok {
            font-size: 12px;
        }

        .table .title-kode {
            width: 50px !important;
        }

        .table th { 
            width: 100px !important; 
            font-size: 12px; 
        }

        .table th, .table td { 
            font-size: 12px; 
        }

        li { 
            font-size: 12px; 
        }

        .btn { 
            font-size: 8px; 
            padding: 4px 8px; 
        }

        .btn i {
            font-size: 10px;
        }

        .card { 
            height: auto !important; 
        }

        .label-col {
            width: 90px;
        }

        .title-item {
            font-size : 16px;
        }
        .title-hargaReseller {
            width: 50px !important;
        }
        .title-hargaUser {
            width: 50px !important;
        }
        .title-garansiUser {
            width: 50px !important;
        }
        .title-garansiReseller {
            width: 50px !important;
        }
        .form-check {
            margin-right: 5px !important;
        }
        .form-label {
            font-size: 10px;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
            margin-top: 10px;
        }
        .form-control, .form-select {
            font-size: 10px;
            padding: 6px;
        }
        .form-check-label {
            font-size: 10px;
        }
        .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .card-total span {
            font-size: 12px !important;
        }

        .card-total {
            width: 50px;
            height: 28px;
            font-size: 12px;
        }

    }

</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 border-0">
                <div class="card-header py-2 d-flex flex-column bg-secondary text-white rounded-0">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h4 class="title m-0">DETAIL BARANG</h4>
                        <div class="d-flex align-items-center gap-2">
                            @if ($status === 'admin' || $status === 'KARYAWAN')
                                <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-1" id="btnExportPdf" data-bs-toggle="tooltip" data-bs-placement="top" title="Export PDF">
                                    <i class="bi bi-filetype-pdf"></i>
                                </button>
                            @endif
                            <button id="btnRefresh" onclick="saveReferrerAndReload()" class="btn btn-success btn-sm d-flex align-items-center gap-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button id="btnBack" onclick="goBack()" class="btn btn-warning btn-sm d-flex align-items-center gap-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Kembali">
                                <i class="bi bi-box-arrow-left"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Admin dan Karyawan -->
                    @if ($status === 'KARYAWAN' || $status === 'admin')
                        <div class="row align-items-end mt-2">
                            <div class="col-md-4">
                                <label for="branch_id" class="form-label fw-semibold">Pilih Harga Cabang</label>
                                <div class="d-flex align-items-center">
                                    <select name="branch_id" id="branch_id" class="form-select me-2">
                                        <option value="">Semua Cabang</option>
                                        @foreach($allBranches as $branch)
                                            <option value="{{ $branch['name'] }}" {{ $selectedBranchId == $branch['name'] ? 'selected' : '' }}>
                                                {{ $branch['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="priceSpinner" class="spinner-border spinner-border-sm text-info ms-2" role="status" style="display: none;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="filterHargaGaransi" class="form-label fw-semibold">Tampilkan Harga</label>
                                <select id="filterHargaGaransi" class="form-select">
                                    <option value="semua">Semua Harga</option>
                                    <option value="reseller">Reseller</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="dropdownGudang" class="form-label fw-semibold">Pilih Lokasi</label>
                                <div class="dropdown w-100" style="z-index: 101;">
                                    <button class="form-select text-start" type="button" id="dropdownGudang" data-bs-toggle="dropdown" aria-expanded="false">
                                        Semua Lokasi
                                    </button>
                                    <ul class="dropdown-menu p-3 w-100" aria-labelledby="dropdownGudang">
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="store" id="gudangStore"><label class="form-check-label" for="gudangStore">Store</label></div></li>
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="tsc" id="gudangTSC"><label class="form-check-label" for="gudangTSC">TSC</label></div></li>
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="konsinyasi" id="gudangKonsinyasi"><label class="form-check-label" for="gudangKonsinyasi">Konsinyasi</label></div></li>
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="resel" id="gudangReseller"><label class="form-check-label" for="gudangReseller">Reseller</label></div></li>
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="trans" id="gudangTransit"><label class="form-check-label" for="gudangTransit">Transit</label></div></li>
                                        <li><div class="form-check"><input class="form-check-input gudang-check" type="checkbox" value="panda" id="gudangPanda"><label class="form-check-label" for="gudangPanda">Panda</label></div></li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>

                <div class="card-body p-4 bg-light">                    
                    <div class="row mb-3">
                        <div class="col-md-4 text-center">
                            <div class="card mb-3 shadow-sm p-3">
                                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel" style="max-height: 320px;">
                                    {{-- Tombol dropdown tetap --}}
                                    <div class="dropdown position-absolute top-0 end-0 me-2 mt-2" style="z-index: 100;">
                                        <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item" id="downloadActiveImage">Download</button>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" id="viewActiveImage" target="_blank">Lihat Gambar</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="carousel-inner">
                                        @forelse ($fileName as $index => $file)
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
                                    </div>

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
                        <div class="col-md-8">
                            @if ($status === 'KARYAWAN' || $status === 'admin')                                
                                    <div class="card p-3 shadow-sm mb-3">
                                        <p class="mb-3 fw-semibold" style="font-size: 16px">{{ $item['name'] }}</p>
                                        <table class="table table-borderless table-sm mb-4">
                                            <tbody>
                                                <tr>
                                                    <th class="title-kode" style="width: 50px;">Kode</th>
                                                    <td style="width: 5px">:</td>
                                                    <td style="width: 50px">
                                                        <span id="kodeProduk">{{ $item['no'] }}</span>                                                        
                                                    </td>
                                                    <td>
                                                        <button class="button-copy" onclick="copyKodeProduk()">
                                                            <span
                                                                ><svg
                                                                width="12"
                                                                height="12"
                                                                fill="#0E418F"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                shape-rendering="geometricPrecision"
                                                                text-rendering="geometricPrecision"
                                                                image-rendering="optimizeQuality"
                                                                fill-rule="evenodd"
                                                                clip-rule="evenodd"
                                                                viewBox="0 0 467 512.22"
                                                                >
                                                                <path
                                                                    fill-rule="nonzero"
                                                                    d="M131.07 372.11c.37 1 .57 2.08.57 3.2 0 1.13-.2 2.21-.57 3.21v75.91c0 10.74 4.41 20.53 11.5 27.62s16.87 11.49 27.62 11.49h239.02c10.75 0 20.53-4.4 27.62-11.49s11.49-16.88 11.49-27.62V152.42c0-10.55-4.21-20.15-11.02-27.18l-.47-.43c-7.09-7.09-16.87-11.5-27.62-11.5H170.19c-10.75 0-20.53 4.41-27.62 11.5s-11.5 16.87-11.5 27.61v219.69zm-18.67 12.54H57.23c-15.82 0-30.1-6.58-40.45-17.11C6.41 356.97 0 342.4 0 326.52V57.79c0-15.86 6.5-30.3 16.97-40.78l.04-.04C27.51 6.49 41.94 0 57.79 0h243.63c15.87 0 30.3 6.51 40.77 16.98l.03.03c10.48 10.48 16.99 24.93 16.99 40.78v36.85h50c15.9 0 30.36 6.5 40.82 16.96l.54.58c10.15 10.44 16.43 24.66 16.43 40.24v302.01c0 15.9-6.5 30.36-16.96 40.82-10.47 10.47-24.93 16.97-40.83 16.97H170.19c-15.9 0-30.35-6.5-40.82-16.97-10.47-10.46-16.97-24.92-16.97-40.82v-69.78zM340.54 94.64V57.79c0-10.74-4.41-20.53-11.5-27.63-7.09-7.08-16.86-11.48-27.62-11.48H57.79c-10.78 0-20.56 4.38-27.62 11.45l-.04.04c-7.06 7.06-11.45 16.84-11.45 27.62v268.73c0 10.86 4.34 20.79 11.38 27.97 6.95 7.07 16.54 11.49 27.17 11.49h55.17V152.42c0-15.9 6.5-30.35 16.97-40.82 10.47-10.47 24.92-16.96 40.82-16.96h170.35z"
                                                                ></path>
                                                                </svg>
                                                                Copy</span>
                                                                <span>Copied</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @if ($status === 'admin')
                                                    <tr>
                                                        <th>Stok Dapat Dijual</th>
                                                        <td>:</td>
                                                        <td>{{ $item['availableToSell'] }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        {{-- RESELLER --}}
                                        <div class="col-md-6 mb-3" id="resellerSection">
                                            <div class="card p-3 shadow-sm" style="height: 180px">
                                                <h6 class="text-primary border-bottom pb-2 mb-3">RESELLER</h6>
                                                @php
                                                    $resellerUnitPrices = [];

                                                    foreach ($sellingPrices as $price) {
                                                        $unitName = strtoupper($price['unit']['name'] ?? '');
                                                        $categoryName = strtolower($price['priceCategory']['name'] ?? '');
                                                        $priceValue = $price['price'] ?? 0;

                                                        if ($categoryName === 'reseller' && $priceValue > 0) {
                                                            $resellerUnitPrices[$unitName] = $priceValue;
                                                        }
                                                    }

                                                    $unitOrder = ['PACK', 'PCS'];
                                                    $sortedPrices = [];

                                                    foreach ($unitOrder as $unit) {
                                                        if (isset($resellerUnitPrices[$unit])) {
                                                            $sortedPrices[$unit] = $resellerUnitPrices[$unit];
                                                        }
                                                    }

                                                    $resellerUnitPrices = $sortedPrices;
                                                @endphp

                                                @if (count($resellerUnitPrices) === 1)
                                                    @php
                                                        $onlyPrice = reset($resellerUnitPrices); // ambil harga pertama
                                                    @endphp
                                                    <div class="d-flex">
                                                        <strong class="me-2 title-hargaReseller" style="width: 60px;">Harga</strong>
                                                        <span>:</span>
                                                        <span class="ms-2">
                                                            <p id="hargaResellerValue">Rp {{ number_format($onlyPrice, 0, ',', '.') }}</p>
                                                        </span>
                                                    </div>

                                                {{-- Jika lebih dari 1 unit, tampilkan nama unit --}}
                                                @elseif(count($resellerUnitPrices) > 1)
                                                    <div class="d-flex">
                                                        <strong class="me-2 title-hargaReseller" style="width: 60px;">Harga</strong>
                                                        <span>:</span>
                                                        <div class="ms-2">
                                                            @foreach($resellerUnitPrices as $unitName => $priceValue)
                                                            <p>
                                                                Rp <span class="hargaResellerValue" data-unit="{{ $unitName }}" id="hargaResellerValue_{{ $unitName }}">
                                                                    {{ number_format($priceValue, 0, ',', '.') }}
                                                                </span> / {{ $unitName }}
                                                            </p>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="mb-2 d-flex">
                                                    <strong class="me-2 title-garansiReseller" style="width: 60px;">Garansi</strong>
                                                    <span>:</span>
                                                    <span class="ms-2">{{ $garansiReseller ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- USER --}}
                                        <div class="col-md-6 mb-3" id="userSection">
                                            <div class="card p-3 shadow-sm h-100" style="height: 180px">                                                
                                                <h6 class="text-success border-bottom pb-2 mb-3">USER</h6>
                                                @php
                                                    $userUnitPrices = [];

                                                    foreach ($sellingPrices as $price) {
                                                        $unitName = strtoupper($price['unit']['name'] ?? '');
                                                        $categoryName = strtolower($price['priceCategory']['name'] ?? '');
                                                        $priceValue = $price['price'] ?? 0;

                                                        if ($categoryName === 'user' && $priceValue > 0) {
                                                            $userUnitPrices[$unitName] = $priceValue;
                                                        }
                                                    }

                                                    $unitOrder = ['PACK', 'PCS'];
                                                    $sortedPrices = [];

                                                    foreach ($unitOrder as $unit) {
                                                        if (isset($userUnitPrices[$unit])) {
                                                            $sortedPrices[$unit] = $userUnitPrices[$unit];
                                                        }
                                                    }

                                                    $userUnitPrices = $sortedPrices;
                                                @endphp

                                                @if (count($userUnitPrices) === 1)
                                                    @php
                                                        $onlyPrice = reset($userUnitPrices);
                                                    @endphp
                                                    <div class="d-flex">
                                                        <strong class="me-2 title-hargaUser" style="width: 60px;">Harga</strong>
                                                        <span>:</span>
                                                        <span class="ms-2">
                                                            <p id="hargaUserValue">Rp {{ number_format($onlyPrice, 0, ',', '.') }}</p>
                                                        </span>
                                                    </div>

                                                @elseif(count($userUnitPrices) > 1)
                                                <div class="d-flex">
                                                    <strong class="me-2 title-hargaUser" style="width: 60px;">Harga</strong>
                                                    <span>:</span>
                                                    <div id="hargaUserValue" class="ms-2">
                                                        {{-- Harga awal tampil di sini --}}
                                                        @foreach($userUnitPrices as $unitName => $priceValue)
                                                            <p>Rp {{ number_format($priceValue, 0, ',', '.') }} / {{ $unitName }}</p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="mb-2 d-flex">
                                                    <strong class="me-2 title-garansiUser" style="width: 60px;">Garansi</strong>
                                                    <span>:</span>
                                                    <span class="ms-2">{{ $garansiUser ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>    
                                @else
                                <div class="card mb-3 shadow-sm p-3" style="height: 337px;">
                                    <h5 class="mb-2 title-item">{{ $item['name'] }}</h5>
                                    <ul class="list-unstyled mt-4">
                                        <li class="d-flex flex-wrap mb-2 align-items-center">
                                            <div class="label-col">Kode</div>
                                            <div class="value-col">
                                                <span class="text-monospace" id="kode-{{ $item['id'] }}">{{ $item['no'] }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                                    onclick="copyToClipboard('{{ $item['id'] }}')">Copy</button>
                                            </div>
                                        </li>
                                        @php
                                            $userPrices = [];
                                            $resellerPrices = [];
                                            $unitNames = [];

                                            foreach ($sellingPrices as $price) {
                                                $unit = strtolower($price['unit']['name'] ?? '');
                                                $unitFormatted = strtoupper($unit);
                                                $category = strtolower($price['priceCategory']['name'] ?? '');
                                                $amount = $price['price'] ?? 0;

                                                if (!in_array($unitFormatted, $unitNames)) {
                                                    $unitNames[] = $unitFormatted;
                                                }

                                                if ($category === 'user') {
                                                    $userPrices[$unitFormatted] = $amount;
                                                } elseif ($category === 'reseller') {
                                                    $resellerPrices[$unitFormatted] = $amount;
                                                }
                                            }

                                            $activePrices = $status === 'RESELLER' ? $resellerPrices : $userPrices;

                                            // Hanya ambil yang nilainya lebih dari 0
                                            $visiblePrices = array_filter($activePrices, fn($price) => $price > 0);
                                        @endphp

                                        <li class="d-flex flex-wrap mb-2 align-items-start">
                                            <div class="label-col">Harga</div>
                                            <div class="value-col" id="hargaReseller" data-user-prices='@json($userPrices)'>
                                                @if (count($visiblePrices) === 1)
                                                    @php
                                                        $unit = array_key_first($visiblePrices);
                                                        $harga = $visiblePrices[$unit];
                                                        $hargaUser = $userPrices[$unit] ?? null;
                                                    @endphp

                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                        @if ($status === 'RESELLER' && $hargaUser)
                                                            <span class="text-decoration-line-through text-muted">
                                                                Rp {{ number_format($hargaUser, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                        <span class="text-dark">
                                                            Rp {{ number_format($harga, 0, ',', '.') }}
                                                        </span>


                                                        @if ($status === 'RESELLER' && isset($discItem) && $discItem > 0)
                                                            <span class="text-danger ms-2">Diskon: {{ $discItem }}%</span>
                                                        @endif
                                                    </div>
                                                @elseif (count($visiblePrices) > 1)
                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach($visiblePrices as $unit => $harga)
                                                            @php $hargaUser = $userPrices[$unit] ?? null; @endphp
                                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                                <span class="text-dark">
                                                                    Rp {{ number_format($harga, 0, ',', '.') }} / {{ $unit }}
                                                                </span>
                                                                @if ($status === 'RESELLER' && $hargaUser)
                                                                    <span class="text-decoration-line-through text-muted">
                                                                        Rp {{ number_format($hargaUser, 0, ',', '.') }} / {{ $unit }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </li>

                                        <li class="d-flex flex-wrap mb-2 align-items-center">
                                            <div class="label-col">Garansi</div>
                                            <div class="value-col">{{ $garansiReseller ?? '-' }}</div>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        @php
                            $points = array_filter(explode('-', $item['notes']), fn($p) => trim($p) !== '');
                        @endphp

                        @if (count($points) > 0)
                        <div class="col-md-12">
                            <div class="card shadow-sm p-4">
                                <h6 class="mb-2">Selling Point</h6>
                                <ul class="ps-3 mb-0">
                                    @foreach ($points as $point)
                                        <li>{{ trim($point) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- START TAMPILAN TABEL KARYAWAN, ADMIN DAN RESELLER --}}
                    
                    @if ($status === 'KARYAWAN' || $status === 'admin' || $status === 'RESELLER')                        
                        {{-- GUDANG NON-KONSINYASI --}}
                        @if ($filteredNonKonsinyasi->isNotEmpty())
                        <div class="mb-4" id="nonKonsinyasiTable">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header" style="background: #10B981">
                                    <h5 class="mb-0 title-table text-white">Store</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table p-2">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Stok</th>
                                                    <th class="text-center">Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($filteredNonKonsinyasi as $data)
                                                    <tr>
                                                        <td data-label="Lokasi" style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>      
                                                        <td data-label="Stok" class="text-center" data-warehouse-id="{{ $data['id'] }}">
                                                            {{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}
                                                        </td>
                                                        @php
                                                            $balanceUnit = trim(str_replace(['[', ']'], '', $data['balanceUnit']));
                                                            $stock = $stokNew[$data['id']]['balance'] ?? $data['balance'];
                                                            $ratio2 = $ratio ?? null;

                                                            preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
                                                            
                                                            preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
                                                            $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

                                                            $showBalanceUnit = false;

                                                            if (count($matches[0]) > 1) {
                                                                $showBalanceUnit = true;
                                                            } elseif ($ratio2 && $firstNumber !== $stock) {
                                                                $showBalanceUnit = true;
                                                            }

                                                            $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
                                                        @endphp 
                                                        <td data-label="Satuan" class="text-center">
                                                            {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
                                                        </td>
                                                    </tr>                                                
                                                @endforeach
                                            </tbody>
                                        </table>                                        
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <h5 class="title-total">Total</h5>
                                        </div>
                                        <div class="card-total text-white">
                                            <span id="totalNonKonsinyasiStok">
                                                {{ number_format($totalNonKonsinyasiStok) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                            
                        {{-- GUDANG PANDA --}}
                        @php
                            $filteredPanda = collect($pandaWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredPanda->count())
                        <div class="mb-4" id="pandawarehouseTable">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header bg-danger">
                                    <h5 class="mb-0 text-white title-table">Panda Store</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Stok</th>
                                                    <th class="text-center">Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($filteredPanda as $data)
                                                    <tr>
                                                        <td style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>                                                            
                                                        <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                        @php
                                                            $balanceUnit = trim(str_replace(['[', ']'], '', $data['balanceUnit']));
                                                            $stock = $stokNew[$data['id']]['balance'] ?? $data['balance'];
                                                            $ratio2 = $ratio ?? null;

                                                            preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
                                                            
                                                            preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
                                                            $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

                                                            $showBalanceUnit = false;

                                                            if (count($matches[0]) > 1) {
                                                                $showBalanceUnit = true;
                                                            } elseif ($ratio2 && $firstNumber !== $stock) {
                                                                $showBalanceUnit = true;
                                                            }

                                                            $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
                                                        @endphp 
                                                        <td class="text-center">
                                                            {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <h5 class="title-total">Total</h5>
                                        </div>
                                        <div class="card-total text-white">
                                            <span id="totalPandaStok">
                                                {{ number_format($totalPandaStok) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- GUDANG TSC --}}
                        @php
                            $filteredTsc = collect($tscWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredTsc->count())
                        <div class="mb-4" id="tscwarehouseTable">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header bg-success">
                                    <h5 class="mb-0 text-white title-table">TSC</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Stok</th>
                                                    <th class="text-center">Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($filteredTsc as $data)
                                                    <tr>
                                                        <td style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>                                                            
                                                        <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                        @php
                                                            $balanceUnit = trim(str_replace(['[', ']'], '', $data['balanceUnit']));
                                                            $stock = $stokNew[$data['id']]['balance'] ?? $data['balance'];
                                                            $ratio2 = $ratio ?? null;

                                                            preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
                                                            
                                                            preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
                                                            $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

                                                            $showBalanceUnit = false;

                                                            if (count($matches[0]) > 1) {
                                                                $showBalanceUnit = true;
                                                            } elseif ($ratio2 && $firstNumber !== $stock) {
                                                                $showBalanceUnit = true;
                                                            }

                                                            $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
                                                        @endphp 
                                                        <td class="text-center">
                                                            {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <h5 class="title-total">Total</h5>
                                        </div>
                                        <div class="card-total text-white">
                                            <span id="totalTscStok">
                                                {{ number_format($totalTscStok) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- GUDANG KONSINYASI --}}
                        @php
                            $filteredKonsinyasi = collect($konsinyasiWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredKonsinyasi->count())
                        <div class="mb-4" id="konsinyasiTable">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header bg-warning">
                                    <h5 class="mb-0 title-table">Konsinyasi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Stok</th>
                                                    <th class="text-center">Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($filteredKonsinyasi as $data)
                                                <tr>
                                                    <td style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                    @php
                                                        $balanceUnit = trim(str_replace(['[', ']'], '', $data['balanceUnit']));
                                                        $stock = $stokNew[$data['id']]['balance'] ?? $data['balance'];
                                                        $ratio2 = $ratio ?? null;

                                                        preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
                                                        
                                                        preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
                                                        $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

                                                        $showBalanceUnit = false;

                                                        if (count($matches[0]) > 1) {
                                                            $showBalanceUnit = true;
                                                        } elseif ($ratio2 && $firstNumber !== $stock) {
                                                            $showBalanceUnit = true;
                                                        }

                                                        $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
                                                    @endphp 
                                                    <td class="text-center">
                                                        {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <h5 class="title-total">Total</h5>
                                        </div>
                                        <div class="card-total text-white">
                                            <span id="totalKonsinyasiStok">
                                                {{ number_format($totalKonsinyasiStok) }}
                                            </span>
                                        </div>                            
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- GUDANG RESELLER --}}
                        @php
                            $filteredReseller = collect($resellerWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredReseller->count())
                        <div class="mb-4" id="resellerwarehouseTable">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0 title-table">Reseller</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Stok</th>
                                                    <th class="text-center">Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($filteredReseller as $data)
                                                <tr>
                                                    <td style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                    @php
                                                        $balanceUnit = trim(str_replace(['[', ']'], '', $data['balanceUnit']));
                                                        $stock = $stokNew[$data['id']]['balance'] ?? $data['balance'];
                                                        $ratio2 = $ratio ?? null;

                                                        preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
                                                        
                                                        preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
                                                        $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

                                                        $showBalanceUnit = false;

                                                        if (count($matches[0]) > 1) {
                                                            $showBalanceUnit = true;
                                                        } elseif ($ratio2 && $firstNumber !== $stock) {
                                                            $showBalanceUnit = true;
                                                        }

                                                        $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <h5 class="title-total">Total</h5>
                                        </div>
                                        <div class="card-total text-white">
                                            <span id="totalResellerStok">
                                                {{ number_format($totalResellerStok) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if ($status === 'KARYAWAN' || $status === 'admin')
                        {{-- GUDANG TRANSIT --}}
                        @php
                            $filteredTransit = collect($transitWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredTransit->count())
                        <div class="mb-4" id="transitwarehouseTable">
                            <div class="card mb-2 shadow-sm ">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0 title-table">Transit</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <th class="text-center">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($filteredTransit as $data)
                                                    <tr>
                                                        <td style="width: {{ $status === 'KARYAWAN' ? '800px' : '1200px'}}">{{ $data['name'] }}</td>
                                                        <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>                                    
                            </div>
                        </div>
                        @endif        
                    @endif        

                    {{-- END TAMPILAN KARYAWAN DAN ADMIN --}}

                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const downloadBtn = document.getElementById('downloadActiveImage');
            const viewBtn = document.getElementById('viewActiveImage');

            function getActiveImageUrl() {
                const activeItem = document.querySelector('.carousel-item.active img');
                return activeItem ? activeItem.getAttribute('src') : '';
            }

            downloadBtn.addEventListener('click', function () {
                const imageUrl = getActiveImageUrl();
                const fileName = imageUrl.split('/').pop().split('?')[0] || 'gambar.jpg';
                const link = document.createElement('a');
                link.href = imageUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            viewBtn.addEventListener('click', function () {
                const imageUrl = getActiveImageUrl();
                this.href = imageUrl;
            });
        });
        
        function saveReferrerAndReload() {
            location.reload();
        }

        function goBack() {
            const lastPage = sessionStorage.getItem('lastPage') || '/';
            const loader = document.getElementById("loader");
            if (loader) {
                loader.style.display = "flex"; // Tampilkan loader saat balik
            }
            sessionStorage.removeItem('lastPage');
            window.history.back();
        }

        function copyKodeProduk() {
            const kode = document.getElementById('kodeProduk').innerText;
            navigator.clipboard.writeText(kode);
        }

        function copyToClipboard(id) {
            const text = document.getElementById('kode-' + id).textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                alert('Kode berhasil disalin: ' + text);
            }).catch(err => {
                console.error('Gagal salin kode:', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.gudang-check');
            const toggleButton = document.getElementById('dropdownGudang');

            const tableMap = {
                store: document.getElementById('nonKonsinyasiTable'),
                tsc: document.getElementById('tscwarehouseTable'),
                konsinyasi: document.getElementById('konsinyasiTable'),
                resel: document.getElementById('resellerwarehouseTable'),
                trans: document.getElementById('transitwarehouseTable'),
                panda: document.getElementById('pandawarehouseTable')
            };

            function updateTableDisplay() {
                const selected = Array.from(checkboxes)
                .filter(c => c.checked)
                .map(c => c.value);

                const showAll = selected.length === 0;

                for (const [key, table] of Object.entries(tableMap)) {
                if (table) {
                    table.style.display = (showAll || selected.includes(key)) ? 'block' : 'none';
                }
                }

                // Update label tombol
                toggleButton.textContent = selected.length > 0
                ? `Dipilih: ${selected.join(', ')}`
                : 'Semua Lokasi';
            }

            // Pas load halaman langsung tampilkan semua
            updateTableDisplay();

            checkboxes.forEach(cb => cb.addEventListener('change', updateTableDisplay));
        });

        const filterHargaGaransi = document.getElementById('filterHargaGaransi');
        if (filterHargaGaransi) {
            filterHargaGaransi.addEventListener('change', function () {
                const value = this.value;

                const resellerSection = document.getElementById('resellerSection');
                const userSection = document.getElementById('userSection');

                if (value === 'semua') {
                    if (resellerSection) resellerSection.style.display = 'block';
                    if (userSection) userSection.style.display = 'block';
                } else if (value === 'reseller') {
                    if (resellerSection) resellerSection.style.display = 'block';
                    if (userSection) userSection.style.display = 'none';
                } else if (value === 'user') {
                    if (resellerSection) resellerSection.style.display = 'none';
                    if (userSection) userSection.style.display = 'block';
                }
            });
        }

        const itemId = {{ $item['id'] }};
        const stokNewElements = {};
        let currentStokNew = {};
        document.addEventListener('DOMContentLoaded', function () {
            
            if (!sessionStorage.getItem('lastPage')) {
                sessionStorage.setItem('lastPage', document.referrer);
            }

            // Event listener untuk select branch_id
            const branchSelect = document.getElementById('branch_id');
            const hargaUserValue = document.getElementById("hargaUserValue");
            const hargaResellerValue = document.getElementById("hargaResellerValue");

            const initialHargaUserHTML = hargaUserValue ? hargaUserValue.innerHTML : '';
            const initialHargaResellerHTML = hargaResellerValue ? hargaResellerValue.innerHTML : '';
            if (branchSelect) {
                branchSelect.addEventListener('change', function () {
                    const branchId = this.value;
                    const itemId = {{ $item['id'] }};
                    const hargaResellerValue = document.getElementById('hargaResellerValue');
                    const hargaUserValue = document.getElementById('hargaUserValue');
                    const spinner = document.getElementById('priceSpinner');

                    if (!branchId) {
                        if (hargaUserValue) {
                            hargaUserValue.innerHTML = initialHargaUserHTML;
                        }

                        if (hargaResellerValue) {
                            hargaResellerValue.innerHTML = initialHargaResellerHTML;
                        }

                        return;
                    }

                    spinner.style.display = 'inline-block';
                    branchSelect.disabled = true;

                    fetch(`{{ url('/items/adjusted-price-ajax') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            branch_name: branchId,
                            item_id: itemId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.adjustedPrices && hargaUserValue) {
                                hargaUserValue.innerHTML = '';

                                const entries = Object.entries(data.adjustedPrices);

                                if (entries.length === 1) {
                                    // Kalau cuma 1 harga (misal hanya "pcs"), tampilkan tanpa satuan
                                    const price = Number(entries[0][1]).toLocaleString('id-ID');
                                    hargaUserValue.innerHTML = `<p>Rp ${price}</p>`;
                                } else {
                                    // Kalau lebih dari 1 (misal pcs, dus, pak), tampilkan semua dengan satuan
                                    entries.forEach(([unit, price]) => {
                                        hargaUserValue.innerHTML += `<p>Rp ${Number(price).toLocaleString('id-ID')} / ${unit}</p>`;
                                    });
                                }

                            } else if (hargaUserValue) {
                                hargaUserValue.textContent = `${Number(price).toLocaleString('id-ID')} / ${unit}`;
                            }
                        } else {
                            if (hargaUserValue) {
                                hargaUserValue.textContent = `${Number(price).toLocaleString('id-ID')} / ${unit}`;
                            }
                        }
                    })

                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengambil harga penyesuaian.');
                    })
                    .finally(() => {
                        spinner.style.display = 'none';
                        branchSelect.disabled = false;
                    });
                });
            }

             // --- Price Adjustment Reseller (khusus RESELLER) ---
            @if ($status === 'RESELLER')
                const no = "{{ $item['no'] }}";
                const priceCategoryName = "RESELLER";
                const discountCategoryName = "RESELLER";
                const branchName = "{{ $selectedBranchName ?? '' }}"; // misalnya dari backend

                function fetchAdjustedPrice() {
                    fetch("{{ route('items.adjusted-price-reseller-ajax') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            no: no,
                            priceCategoryName: priceCategoryName,
                            discountCategoryName: discountCategoryName,
                            branchName: branchName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const hargaReseller = document.getElementById('hargaReseller');
                        const userPrices = JSON.parse(hargaReseller.dataset.userPrices || '{}');
                        hargaReseller.innerHTML = '';
                        if (data.success && data.adjustedPrices) {
                            hargaReseller.innerHTML = '';

                            const entries = Object.entries(data.adjustedPrices);
                            let html = '';

                            if (entries.length === 1) {
                                    const [unit, resellerPrice] = entries[0];
                                    const userPrice = userPrices[unit];
                                    let html = `<div class="mb-1 d-flex align-items-center flex-wrap gap-2">`;

                                    if (userPrice && resellerPrice != userPrice) {
                                        html += `<span class="text-decoration-line-through text-muted">Rp ${Number(userPrice).toLocaleString('id-ID')}</span>`;
                                    }

                                    html += `<span class="text-dark">Rp ${Number(resellerPrice).toLocaleString('id-ID')}</span>`;
                                    html += `</div>`;

                                    hargaReseller.innerHTML = html;
                                } else {
                                let html = `<div class="d-flex flex-column gap-1">`;

                                entries.forEach(([unit, resellerPrice]) => {
                                    const userPrice = userPrices[unit];
                                    html += `<div class="d-flex align-items-center flex-wrap gap-2">`;

                                    if (userPrice && resellerPrice != userPrice) {
                                        html += `<span class="text-decoration-line-through text-muted">Rp ${Number(userPrice).toLocaleString('id-ID')} / ${unit}</span>`;
                                    }

                                    html += `<span class="text-dark">Rp ${Number(resellerPrice).toLocaleString('id-ID')} / ${unit}</span>`;
                                    html += `</div>`;

                                });

                                html += `</div>`;
                                hargaReseller.innerHTML += html;
                            }
                        } else {
                            hargaReseller.textContent = 'Harga tidak tersedia';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengambil harga.');
                    });
                }
                fetchAdjustedPrice();
            @endif

            // --- Export PDF filter sync ---
            const exportBranchInput = document.getElementById('export_branch_id');
            const exportFilterGudangInput = document.getElementById('export_filterGudang');
            const exportFilterHargaGaransiInput = document.getElementById('export_filterHargaGaransi');

            const filterGudangSelect = document.getElementById('filterGudang');
            const filterHargaGaransiSelect = document.getElementById('filterHargaGaransi');

            if (branchSelect && exportBranchInput) {
                branchSelect.addEventListener('change', function () {
                    exportBranchInput.value = this.value;
                });
            }
            if (filterGudangSelect && exportFilterGudangInput) {
                filterGudangSelect.addEventListener('change', function () {
                    exportFilterGudangInput.value = this.value;
                });
            }
            if (filterHargaGaransiSelect && exportFilterHargaGaransiInput) {
                filterHargaGaransiSelect.addEventListener('change', function () {
                    exportFilterHargaGaransiInput.value = this.value;
                });
            }

            // --- Stok Table AJAX & Update (untuk semua role) ---
            
            
            @foreach ($konsinyasiWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#konsinyasiTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach

            @foreach ($nonKonsinyasiWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#nonKonsinyasiTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach

            @foreach ($tscWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#tscwarehouseTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach

            @foreach ($resellerWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#resellerwarehouseTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach

            @foreach ($transitWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#transitwarehouseTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach
            
            @foreach ($pandaWarehouses as $w)
                stokNewElements[{{ $w['id'] }}] = document.querySelector(`#pandawarehouseTable td[data-warehouse-id="{{ $w['id'] }}"]`);
            @endforeach

            function updateStokTable(stokNew) {
                currentStokNew = stokNew;

                for (const [warehouseId, data] of Object.entries(stokNew)) {
                    const td = stokNewElements[warehouseId];
                    if (td) {
                        td.textContent = data.balance !== null && data.balance !== undefined ? new Intl.NumberFormat('id-ID').format(data.balance) : '0';
                        const tr = td.closest('tr');
                        if (tr) {
                            if (data.balance > 0) {
                                tr.style.display = '';
                            } else {
                                tr.style.display = 'none';
                            }
                        }
                    }
                }
                updateTotalStok();
            }

            function updateTotalStok() {
                let totalNonKonsinyasi = 0;
                let totalTsc = 0;
                let totalKonsinyasi = 0;
                let totalReseller = 0;
                let totalTransit = 0;
                let totalPanda = 0;

                function getBalance(warehouseId) {
                    const data = currentStokNew[warehouseId];
                    const balance = data&&data.balance ? parseFloat(data.balance) : 0;
                    return isNaN(balance) ? 0 : balance;
                }

                @foreach ($nonKonsinyasiWarehouses as $w)
                {
                    const el = stokNewElements[{{ $w['id'] }}];
                    if (el) {
                        totalNonKonsinyasi += getBalance({{ $w['id'] }});
                    } else {
                        totalNonKonsinyasi += 0;
                    }
                }
                @endforeach

                @foreach ($tscWarehouses as $w)
                {
                    totalTsc += getBalance({{ $w['id'] }});
                }
                @endforeach

                @foreach ($konsinyasiWarehouses as $w)
                {
                    totalKonsinyasi += getBalance({{ $w['id'] }});
                }
                @endforeach

                @foreach ($resellerWarehouses as $w)
                {
                    totalReseller += getBalance({{ $w['id'] }});
                }
                @endforeach

                @foreach ($transitWarehouses as $w)
                {
                    totalTransit += getBalance({{ $w['id'] }});
                }
                @endforeach

                @foreach ($pandaWarehouses as $w)
                {
                    totalPanda += getBalance({{ $w['id'] }});
                }
                @endforeach

                const formatter = new Intl.NumberFormat('id-ID');

                const totalNonKonsinyasiEl = document.getElementById('totalNonKonsinyasiStok');
                if (totalNonKonsinyasiEl) {
                    totalNonKonsinyasiEl.textContent = formatter.format(totalNonKonsinyasi);
                }

                const totalTscEl = document.getElementById('totalTscStok');
                if (totalTscEl) {
                    totalTscEl.textContent = formatter.format(totalTsc);
                }

                const totalKonsinyasiEl = document.getElementById('totalKonsinyasiStok');
                if (totalKonsinyasiEl) {
                    totalKonsinyasiEl.textContent = formatter.format(totalKonsinyasi);
                }

                const totalResellerEl = document.getElementById('totalResellerStok');
                if (totalResellerEl) {
                    totalResellerEl.textContent = formatter.format(totalReseller);
                }

                const totalPandaEl = document.getElementById('totalPandaStok');
                if (totalPandaEl) {
                    totalPandaEl.textContent = formatter.format(totalPanda);
                }

                const totalSemua = totalNonKonsinyasi + totalTsc + totalKonsinyasi + totalReseller + totalTransit;
                const totalKeseluruhanEl = document.getElementById('totalKeseluruhanStok');
                if (totalKeseluruhanEl) {
                    totalKeseluruhanEl.textContent = formatter.format(totalSemua);
                }

                const nonKonsinyasiWrapper = document.getElementById('nonKonsinyasiTable');
                if (nonKonsinyasiWrapper) {
                    if (totalNonKonsinyasi === 0) {
                        nonKonsinyasiWrapper.style.display = 'none';
                    } else {
                        nonKonsinyasiWrapper.style.display = '';
                    }
                }

                const tscWrapper = document.getElementById('tscwarehouseTable');
                if (tscWrapper) {
                    if (totalTsc === 0) {
                        tscWrapper.closest('.mb-4').style.display = 'none'; 
                    } else {
                        tscWrapper.closest('.mb-4').style.display = '';
                    }
                }

                const konsinyasiWrapper = document.getElementById('konsinyasiTable');
                if (konsinyasiWrapper) {
                    if (totalKonsinyasi === 0) {
                        konsinyasiWrapper.closest('.mb-4').style.display = 'none';
                    } else {
                        konsinyasiWrapper.closest('.mb-4').style.display = '';
                    }
                }

                const resellerWrapper = document.getElementById('resellerwarehouseTable');
                if (resellerWrapper) {
                    if (totalReseller === 0) {
                        resellerWrapper.closest('.mb-4').style.display = 'none';
                    } else {
                        resellerWrapper.closest('.mb-4').style.display = '';
                    }
                }

                const pandaWrapper = document.getElementById('pandawarehouseTable');
                if (pandaWrapper) {
                    if (totalPanda === 0) {
                        pandaWrapper.closest('.mb-4').style.display = 'none';
                    } else {
                        pandaWrapper.closest('.mb-4').style.display = '';
                    }
                }

            }

            // ---- Perhitungan stok sales order dilanjutkan dengan sales invoice (Faktur dimukan) ----

            // document.getElementById('btnExportPdf')?.setAttribute('disabled', true);
            // document.getElementById('btnRefresh')?.setAttribute('disabled', true);


            // fetch(`{{ url('/items/salesorder-stock-ajax') }}`, {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //     },
            //     body: JSON.stringify({ item_id: itemId, includeInvoice: false })
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         currentStokNew = data.stokNew;

            //         return fetch(`{{ url('/items/matching-invoices-ajax') }}`, {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //             },
            //             body: JSON.stringify({
            //                 item_id: itemId,
            //                 stok_awal: currentStokNew
            //             })
            //         });
            //     } else {
            //         throw new Error('Gagal mengambil data stok sales order: ' + data.message);
            //     }
            // })
            // .then(async response => {
            //     if (!response.ok) {
            //         const text = await response.text();
            //         console.error('Server error:', text);
            //         throw new Error('Server returned an error for matching-invoices-ajax');
            //     }
            //     return response.json();
            // })
            // .then(data => {
            //     if (data.success) {
            //         currentStokNew = data.stokNew;
            //         updateStokTable(currentStokNew);

            //         Toastify({
            //             text: "Data stok sudah terupdate!",
            //             duration: 10000,
            //             gravity: "top", // atau "bottom"
            //             position: "right", // atau "left"
            //             style: {
            //                 background: "linear-gradient(to right, #00b09b, #96c93d)",
            //             },
            //             stopOnFocus: true,
            //         }).showToast();
            //     } else {
            //         console.error('Gagal mengambil data matching invoices:', data.message);

            //         Toastify({
            //             text: "Gagal mengambil data invoice",
            //             duration: 10000,
            //             gravity: "top",
            //             position: "right",
            //             backgroundColor: "#dc3545", // merah error
            //         }).showToast();
            //     }
            // })
            // .catch(error => {
            //     console.error('Terjadi kesalahan saat mengambil data stok:', error);
            // })
            // .finally(() => {
            //     // Aktifkan tombol kembali
            //     document.getElementById('btnExportPdf')?.removeAttribute('disabled');
            //     document.getElementById('btnRefresh')?.removeAttribute('disabled');
            // });
        });

        document.getElementById('btnExportPdf').addEventListener('click', function () {
            const branchId = document.getElementById('branch_id') ? document.getElementById('branch_id').value : '';
            const filterHargaGaransi = document.getElementById('filterHargaGaransi') ? document.getElementById('filterHargaGaransi').value : 'semua';

            if (!currentStokNew) {
                alert('Data stok belum tersedia. Silakan tunggu hingga data selesai dimuat.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('branch_id', branchId);
            formData.append('filterHargaGaransi', filterHargaGaransi);
            formData.append('stokNew', JSON.stringify(currentStokNew));

            // ✅ Perbaikan bagian checkbox filterGudang
            const gudangCheckboxes = document.querySelectorAll('.gudang-check:checked');
            if (gudangCheckboxes.length === 0) {
                formData.append('filterGudang[]', 'semua'); // kalau tidak ada yang dicentang
            } else {
                gudangCheckboxes.forEach(cb => {
                    formData.append('filterGudang[]', cb.value);
                });
            }

            // Kirim form ke server
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('items.export-pdf.post', ['encrypted' => Hashids::encode($item['id'])]) }}";
            form.target = '_blank';

            for (const [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });


    </script>

@endsection

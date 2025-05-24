@extends('layout')

@section('content')

@php
    use Illuminate\Support\Facades\Auth;

    $sellingPrices = collect($item['detailSellingPrice']);

    $resellerPrice = $sellingPrices
        ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'reseller')['price'] ?? 0;

    $userPrice = $sellingPrices
        ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'user')['price'] ?? 0;

    

    $status = Auth::user()->status;

    $totalKonsinyasiStok = collect($konsinyasiWarehouses)->sum(function($w) use ($stokNew) {
        return $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0;
    });

    $totalNonKonsinyasiStok = collect($nonKonsinyasiWarehouses)->sum(function($w) use ($stokNew) {
        return $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0;
    });

    $totalTscStok = collect($tscWarehouses)->sum(function($w) use ($stokNew) {
        return $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0;
    });

    $filteredNonKonsinyasi = collect($nonKonsinyasiWarehouses)->filter(function ($data) use ($stokNew) {
        $stok = $stokNew[$data['id']]['balance'] ?? $data['balance'];
        return $stok > 0;
    })->values();

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
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    @media only screen and (max-width: 768px) {
        .title {
            font-size: 15px;
        }
        .table th {
            width: 100px !important; /* Atau lebih kecil kalau perlu */
            font-size: 12px;
        }
        .table th,
        .table td {
            font-size: 12px;
        }
        .btn {
            font-size: 12px;
            padding: 4px 8px;
        }
        .card {
            height: auto !important;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h4 class="title">DETAIL BARANG</h4>
                    <div>
                        <button onclick="saveReferrerAndReload()" class="btn btn-primary">Refresh</button>
                        <button onclick="goBack()" class="btn btn-warning">Kembali</button>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($status === 'karyawan' || $status === 'admin')
                    <div class="row align-items-end mb-4">
                            <div class="col-md-4">                                
                                <form method="GET">
                                    <label for="branch_id" class="form-label fw-semibold">Pilih Harga Cabang</label>
                                    <select name="branch_id" id="branch_id" onchange="this.form.submit()" class="form-select">
                                        <option value="">Semua Cabang</option>
                                        @foreach($allBranches as $branch)
                                            <option value="{{ $branch['id'] }}" {{ $selectedBranchId == $branch['id'] ? 'selected' : '' }}>
                                                {{ $branch['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
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
                                <label for="filterGudang" class="form-label fw-semibold">Lokasi Stok</label>
                                <select id="filterGudang" class="form-select">
                                    <option value="semua">Semua Lokasi</option>
                                    <option value="non">Store</option>
                                    <option value="tsc">TSC</option>
                                    <option value="konsinyasi">Konsinyasi</option>
                                </select>
                            </div>
                        </div>
                    @endif                    
                    <div class="row mb-3">
                        <div class="col-md-4 text-center">
                            <div class="card mb-3 shadow-sm p-3" style="height: 337px;">
                                <div id="itemImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @forelse ($fileName as $index => $file)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                <img 
                                                    src="{{ $file ? route('proxy.image', ['fileName' => $file, 'session' => $session]) : asset('/images/noimage.jpg') }}" 
                                                    alt="Gambar {{ $index + 1 }}" 
                                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                                    style="max-height: 300px; object-fit: contain;"
                                                    onerror="this.onerror=null; this.src='{{ asset('/images/noimage.jpg') }}';"
                                                >
                                            </div>
                                        @empty
                                            {{-- Jika $fileName kosong total, tampilkan 1 gambar default --}}
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
                            {{-- GARANSI & HARGA--}}
                            @if ($status === 'karyawan' || $status === 'admin')
                            <div class="card mb-3 shadow-sm p-3" style="height: 337px;">
                                <h5>{{ $item['name'] }}</h5>
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-start" style="width: 150px;">Kode</th>
                                            <td style="width: 5px">:</td>
                                            <td>
                                                <span id="kodeProduk">{{ $item['no'] }}</span>
                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyKodeProduk()">Copy</button>
                                            </td>
                                        </tr>
                                        <tr id="hargaResellerWrapper">
                                            <th class="text-start" style="width: 150px;">Harga Reseller</th>
                                            <td style="width: 5px">:</td>
                                             @if ($discItem)
                                                    <p>Diskon: {{ $discItem }}%</p>
                                                @endif
                                            <td>
                                                @if ($isResellerPriceAdjusted)
                                                    <span>
                                                        Rp {{ number_format($finalResellerPrice, 0, ',', '.') }} 
                                                    </span>
                                                @else
                                                    Rp {{ number_format($resellerPrice, 0, ',', '.') }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr id="garansiResellerWrapper">
                                            <th class="text-start" style="width: 150px;">Garansi Reseller</th>
                                            <td style="width: 5px">:</td>
                                            <td>{{ $garansiReseller }}</td>
                                        </tr>
                                        <tr id="hargaUserWrapper">
                                            <th class="text-start" style="width: 150px;">Harga User</th>
                                            <td style="width: 5px">:</td>
                                            <td>
                                                @if ($isUserPriceAdjusted)
                                                    <span>
                                                        Rp {{ number_format($finalUserPrice, 0, ',', '.') }}  {{$discItem}}
                                                    </span>
                                                @else
                                                    Rp {{ number_format($userPrice, 0, ',', '.') }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr id="garansiUserWrapper">
                                            <th class="text-start" style="width: 150px;">Garansi User</th>
                                            <td style="width: 5px">:</td>
                                            <td>{{ $garansiUser }}</td>
                                        </tr>
                                        @if ($status === 'admin')
                                            <tr>
                                                <th class="text-start" style="width: 150px;">Stok Dapat Dijual</th>
                                                <td style="width: 5px">:</td>
                                                <td>{{ $item['availableToSell'] }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            @else
                                <div class="card mb-3 shadow-sm p-3" style="height: 337px;">
                                    <h5 class="mb-2">{{ $item['name'] }}</h5>

                                    <ul class="list-unstyled mt-4">
                                        <li class="d-flex align-items-center mb-2">
                                            <strong class="me-3" style="width: 120px;">Kode</strong>
                                            <span class="me-2">: <span class="text-monospace" id="kode-{{ $item['id'] }}">{{ $item['no'] }}</span></span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="copyToClipboard('{{ $item['id'] }}')">Copy</button>
                                        </li>
                                        <li class="d-flex align-items-center mb-2" id="hargaResellerWrapper">
                                            <strong class="me-3" style="width: 120px;">Harga</strong>
                                            <span>:</span>
                                            <span class="ms-1 text-decoration-line-through text-muted">Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</span>
                                            <span class="ms-3 text-dark">Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}</span>
                                        </li>
                                        <li class="d-flex mb-2" id="garansiResellerWrapper">
                                            <strong class="me-3" style="width: 120px;">Garansi</strong>
                                            <span>: {{ $garansiReseller ?? '-' }}</span>
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

@if ($status === 'karyawan' || $status === 'admin')
    {{-- TABEL GUDANG NON-KONSINYASI --}}
    <div class="mb-4" id="nonKonsinyasiTable">
        <div class="table-responsive">
            @if ($filteredNonKonsinyasi->isNotEmpty())
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Lokasi Store</th>
                            <th>Stok</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredNonKonsinyasi as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">
                                    {{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}
                                </td>
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ count($nonKonsinyasiWarehouses) }}">
                                        {{ number_format($totalNonKonsinyasiStok) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center p-3 border rounded bg-light text-muted">
                    Stok tidak ada!
                </div>
            @endif
        </div>
    </div>
    
    {{-- TABEL GUDANG TSC --}}
    @php
        $filteredTsc = collect($tscWarehouses)->filter(function ($data) use ($stokNew) {
            $stok = $stokNew[$data['id']]['balance'] ?? $data['balance'];
            return $stok > 0;
        })->values();
    @endphp
    @if ($filteredTsc->count())
        <div class="mb-4" id="tscwarehouseTable">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>TSC</th>
                            <th>Stok</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredTsc as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ $filteredTsc->count() }}">
                                        {{ number_format($totalTscStok) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TABEL GUDANG KONSINYASI (HANYA JIKA TOTAL STOK > 0) --}}
    @php
        $filteredKonsinyasi = collect($konsinyasiWarehouses)->filter(function ($data) use ($stokNew) {
            $stok = $stokNew[$data['id']]['balance'] ?? $data['balance'];
            return $stok > 0;
        })->values();
    @endphp

    @if ($filteredKonsinyasi->count())
        <div class="mb-4" id="konsinyasiTable">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Konsinyasi</th>
                            <th>Stok</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredKonsinyasi as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ $filteredKonsinyasi->count() }}">
                                        {{ number_format($totalKonsinyasiStok) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
@elseif ($status === 'reseller')
    {{-- HANYA NON-KONSINYASI UNTUK RESELLER --}}
    <div class="mb-4" id="nonKonsinyasiTable">
        <div class="table-responsive">
            @if ($filteredNonKonsinyasi->isNotEmpty())
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Lokasi Store</th>
                            <th>Stok</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredNonKonsinyasi as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">
                                    {{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}
                                </td>
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ count($nonKonsinyasiWarehouses) }}">
                                        {{ number_format($totalNonKonsinyasiStok) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center p-3 border rounded bg-light text-muted">
                    Tidak ada data stok
                </div>
            @endif
        </div>
    </div>
    @php
        $filteredKonsinyasi = collect($konsinyasiWarehouses)->filter(function ($data) use ($stokNew) {
            $stok = $stokNew[$data['id']]['balance'] ?? $data['balance'];
            return $stok > 0;
        })->values();
    @endphp

    @if ($filteredKonsinyasi->count())
        <div class="mb-4" id="konsinyasiTable">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Konsinyasi</th>
                            <th>Stok</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredKonsinyasi as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ $filteredKonsinyasi->count() }}">
                                        {{ number_format($totalKonsinyasiStok) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif




<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!sessionStorage.getItem('lastPage')) {
            sessionStorage.setItem('lastPage', document.referrer);
        }
    });

    function saveReferrerAndReload() {
        location.reload();
    }

    function goBack() {
        const lastPage = sessionStorage.getItem('lastPage') || '/';
        sessionStorage.removeItem('lastPage');
        window.location.href = lastPage;
    }

    function copyKodeProduk() {
        const kode = document.getElementById('kodeProduk').innerText;
        navigator.clipboard.writeText(kode);
        alert('Kode produk disalin!');
    }

    function copyToClipboard(id) {
        const text = document.getElementById('kode-' + id).textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            alert('Kode berhasil disalin: ' + text);
        }).catch(err => {
            console.error('Gagal salin kode:', err);
        });
    }

    document.getElementById('filterGudang').addEventListener('change', function () {
        const value = this.value;

        const nonKonsinyasiTable = document.getElementById('nonKonsinyasiTable');
        const konsinyasiTable = document.getElementById('konsinyasiTable');

        if (value === 'semua') {
            if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'block';
            if (konsinyasiTable) konsinyasiTable.style.display = 'block';
            if (tscwarehouseTable) tscwarehouseTable.style.display = 'block';
        } else if (value === 'non') {
            if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'block';
            if (konsinyasiTable) konsinyasiTable.style.display = 'none';
            if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
        } else if (value === 'konsinyasi') {
            if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
            if (konsinyasiTable) konsinyasiTable.style.display = 'block';
            if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
        } else if (value === 'tsc') {
            if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
            if (konsinyasiTable) konsinyasiTable.style.display = 'none';
            if (tscwarehouseTable) tscwarehouseTable.style.display = 'block';
        }
    });

    //FILTER HARGA DAN GARANSI
    document.getElementById('filterHargaGaransi').addEventListener('change', function () {
        const value = this.value;

        const hargaReseller = document.getElementById('hargaResellerWrapper');
        const hargaUser = document.getElementById('hargaUserWrapper');
        const garansiReseller = document.getElementById('garansiResellerWrapper');
        const garansiUser = document.getElementById('garansiUserWrapper');

        if (value === 'semua') {
            if (hargaReseller) hargaReseller.style.display = 'table-row';
            if (hargaUser) hargaUser.style.display = 'table-row';
            if (garansiReseller) garansiReseller.style.display = 'table-row';
            if (garansiUser) garansiUser.style.display = 'table-row';
        } else if (value === 'reseller') {
            if (hargaReseller) hargaReseller.style.display = 'table-row';
            if (hargaUser) hargaUser.style.display = 'none';
            if (garansiReseller) garansiReseller.style.display = 'table-row';
            if (garansiUser) garansiUser.style.display = 'none';
        } else if (value === 'user') {
            if (hargaReseller) hargaReseller.style.display = 'none';
            if (hargaUser) hargaUser.style.display = 'table-row';
            if (garansiReseller) garansiReseller.style.display = 'none';
            if (garansiUser) garansiUser.style.display = 'table-row';
        }
    });

</script>

@endsection

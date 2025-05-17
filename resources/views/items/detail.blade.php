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
    
@endphp

<style>
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    @media only screen and (max-width: 768px) {
        .title {
            font-size: 15px;
        }
        .table th,
        .table td {
            font-size: 12px;
        }
        .btn {
            font-size: 12px;
            padding: 4px 8px;
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
                                <label for="filterHargaGaransi" class="form-label fw-semibold">Harga</label>
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
                            <div id="itemImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach ($fileName as $index => $file)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img 
                                                src="{{ route('proxy.image', ['fileName' => $file, 'session' => $session]) }}" 
                                                alt="Gambar {{ $index + 1 }}" 
                                                class="d-block w-100 img-fluid rounded shadow-sm"
                                                style="max-height: 300px; object-fit: contain;"
                                                onerror="this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\'text-danger\'>Gambar Kosong</div>')"
                                            >
                                        </div>
                                    @endforeach
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
                        <div class="col-md-8">
                            <h5>{{ $item['name'] }}</h5>

                            {{-- GARANSI & HARGA--}}
                            @if ($status === 'karyawan' || $status === 'admin')
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-start">Kode</th>
                                            <td class="">
                                                : {{ $item['no'] }}
                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyKodeProduk()">Copy</button>
                                            </td>
                                        </tr>
                                        <tr id="hargaResellerWrapper">
                                            <th class="text-start">Harga Reseller</th>
                                            <td class="">: Rp {{ number_format($resellerPrice, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr id="garansiResellerWrapper">
                                            <th class="text-start">Garansi Reseller</th>
                                            <td class="">: {{ $garansiReseller }}</td>
                                        </tr>
                                        <tr id="hargaUserWrapper">
                                            <th class="text-start">Harga User</th>
                                            <td class="">: Rp {{ number_format($userPrice, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr id="garansiUserWrapper">
                                            <th class="text-start">Garansi User</th>
                                            <td class="">: {{ $garansiUser }}</td>
                                        </tr>
                                        @if ($status === 'admin')
                                        <tr>
                                            <th>Stok Real</th>
                                            <td>: {{ $item['availableToSell'] }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @else
                                <div class="row mb-2" id="garansiResellerWrapper">
                                    <div class="col-sm-4 fw-bold">Harga Reseller</div>
                                    <div class="col-sm-8">: Rp {{ number_format($resellerPrice, 0, ',', '.') }}</div>
                                </div>
                                <div class="row mb-2" id="hargaUserWrapper">
                                    <div class="col-sm-4 fw-bold">Harga User</div>
                                    <div class="col-sm-8 text-muted text-decoration-line-through">
                                        : Rp {{ number_format($userPrice, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="row mb-2" id="garansiResellerWrapper">
                                    <div class="col-sm-4 fw-bold">Garansi Reseller:</div>
                                    <div class="col-sm-8">: {{ $garansiReseller }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

@if ($status === 'karyawan' || $status === 'admin')
    {{-- TABEL GUDANG NON-KONSINYASI --}}
    <div class="mb-4" id="nonKonsinyasiTable">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Lokasi Store</th>
                        <th>Stok</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nonKonsinyasiWarehouses as $index => $data)
                        <tr>
                            <td>{{ $data['name'] }}</td>
                            <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                            
                            @if ($loop->first)
                                <td class="text-center" rowspan="{{ count($nonKonsinyasiWarehouses) }}">
                                    {{ number_format($totalNonKonsinyasiStok) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- TABEL GUDANG TSC --}}
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
                    @foreach ($tscWarehouses as $data)
                        <tr>
                            <td>{{ $data['name'] }}</td>
                            <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                            @if ($loop->first)
                                <td class="text-center" rowspan="{{ count($tscWarehouses) }}">
                                    {{ number_format($totalTscStok) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- TABEL GUDANG KONSINYASI (HANYA JIKA TOTAL STOK > 0) --}}
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
                    @foreach ($konsinyasiWarehouses as $data)
                        <tr>
                            <td>{{ $data['name'] }}</td>
                            <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                            @if ($loop->first)
                                <td class="text-center" rowspan="{{ count($konsinyasiWarehouses) }}">
                                    {{ number_format($totalKonsinyasiStok) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@elseif ($status === 'reseller')
    {{-- HANYA NON-KONSINYASI UNTUK RESELLER --}}
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark text-center">
                <tr>
                    <th>Lokasi Store</th>
                    <th>Stok</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nonKonsinyasiWarehouses as $data)
                    <tr>
                        <td>{{ $data['name'] }}</td>
                        <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>

                        @if ($loop->first)
                            <td class="text-center" rowspan="{{ count($nonKonsinyasiWarehouses) }}">
                                {{ number_format($totalNonKonsinyasiStok) }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($totalKonsinyasiStok > 0)
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
                        @foreach ($konsinyasiWarehouses as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                 @if ($loop->first)
                                <td class="text-center" rowspan="{{ count($konsinyasiWarehouses) }}">
                                    {{ number_format($totalKonsinyasiStok) }}
                                </td>
                            @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="mb-4" id="konsinyasiTable">
            <h6 class="text-warning">Gudang Konsinyasi</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Konsinyasi</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>                       
                        <tr>
                            <td class="text-center" colspan="2">Kosong</td>                                
                        </tr>
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

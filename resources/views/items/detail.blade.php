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
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h4 class="title">DETAIL BARANG</h4>
                    <div>
                        <button onclick="goBack()" class="btn btn-warning">Kembali</button>
                        <button onclick="saveReferrerAndReload()" class="btn btn-primary">Refresh</button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-4 text-center">
                            @if(isset($item['detailItemImage'][0]['fileName']))
                                <img src="https://public.accurate.id{{ $item['detailItemImage'][0]['fileName'] }}" alt="Gambar Item" class="img-fluid rounded">
                            @else
                                <p class="text-muted">Tidak ada gambar</p>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5>{{ $item['name'] }}</h5>
                            <div class="d-flex align-items-center mb-2">
                                <strong>Kode: </strong>
                                <span id="kodeProduk" class="ms-2">{{ $item['no'] }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyKodeProduk()">Copy</button>
                            </div>

                            <p><strong>Harga Reseller:</strong> Rp {{ number_format($resellerPrice, 0, ',', '.') }}</p>
                            <p><strong>Harga User:</strong> 
                                <span class="text-muted text-decoration-line-through">
                                    Rp {{ number_format($userPrice, 0, ',', '.') }}
                                </span>
                            </p>

                            <p><span class="badge bg-success">Garansi: {{ $garansiReseller }}</span></p>
                            @if ($status === 'karyawan' || $status === 'admin')
                                <p><span class="badge bg-success">Stok Custom: {{ $totalNonKonsinyasiStok }}</span></p>
                                <p><span class="badge bg-info">Stok Real: {{ $item['availableToSell'] }}</span></p>                                
                            @else
                                <p><span class="badge bg-success">Stok Dijual: {{ $totalNonKonsinyasiStok }}</span></p>
                            @endif
                        </div>
                    </div>

                    <h6>Stok per Gudang:</h6>

@php
    $status = Auth::user()->status;
@endphp

@if ($status === 'karyawan' || $status === 'admin')
    {{-- TABEL GUDANG NON-KONSINYASI --}}
    <div class="mb-4">
        <h6 class="text-primary">Gudang Non-Konsinyasi</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Gudang</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nonKonsinyasiWarehouses as $data)
                        <tr>
                            <td>{{ $data['name'] }}</td>
                            <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL GUDANG KONSINYASI (HANYA JIKA TOTAL STOK > 0) --}}
    @if ($totalKonsinyasiStok > 0)
        <div class="mb-4">
            <h6 class="text-warning">Gudang Konsinyasi</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Gudang</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($konsinyasiWarehouses as $data)
                            <tr>
                                <td>{{ $data['name'] }}</td>
                                <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="mb-4">
            <h6 class="text-warning">Gudang Konsinyasi</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Gudang</th>
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

@elseif ($status === 'reseller')
    {{-- HANYA NON-KONSINYASI UNTUK RESELLER --}}
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark text-center">
                <tr>
                    <th>Gudang</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nonKonsinyasiWarehouses as $data)
                    <tr>
                        <td>{{ $data['name'] }}</td>
                        <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
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
</script>

@endsection

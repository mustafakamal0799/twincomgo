@extends('layout')

@section('content')

@php
    $sellingPrices = collect($item['detailSellingPrice']);

    $resellerPrice = $sellingPrices
        ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'reseller')['price'] ?? 0;

    $userPrice = $sellingPrices
        ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'user')['price'] ?? 0;

    // Ambil data stok gudang terbaru (sudah dikurangi jika ada sales order 'menunggu diproses')
    $warehouses = collect($stokNew)->keys()->toArray();
    $warehouseBalances = collect($stokNew)->mapWithKeys(fn($w, $id) => [$w['name'] => $w['balance']])->toArray();
@endphp

<style>
    .vertical-text {
        max-width: 150px;
        word-break: break-word;
        white-space: normal !important;
    }

    .table-responsive {
        overflow-x: auto;
    }
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
    }

    @media only screen and (max-width: 768px) {
        .title {
            font-size: 15px;
        }
        .table th,
        .table td {
            font-size: 10px;
        }
        .form-control {
            font-size: 12px;
            padding: 4px 8px;
        }

        .form-check-label,
        .form-check-input {
            font-size: 12px;
        }

        .btn {
            font-size: 12px;
            padding: 4px 8px;
        }

        .btn i {
            font-size: 14px !important;
        }

        .form-check {
            margin-right: 5px !important;
        }
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h4 class="title">DETAIL BARANG</h4>
                    <div class="mb-2">
                        <div class="mb-2">
                            <button onclick="goBack()" class="btn btn-warning">Kembali</button>
                            <button onclick="saveReferrerAndReload()" class="btn btn-primary">Refresh</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="text-center table-dark">
                                <tr>
                                    <th rowspan="4" colspan="2">
                                        @if(isset($item['detailItemImage'][0]['fileName']))
                                            <img src="https://public.accurate.id{{ $item['detailItemImage'][0]['fileName'] }}" alt="Gambar Item" width="100">
                                        @else
                                            <p>Tidak ada gambar</p>
                                        @endif
                                    </th>
                                    <td colspan="4">{{ $item['name'] }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-center">{{ $item['no'] }}</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th colspan="2" colspan="2" class="text-center">Harga</th>
                                    <th rowspan="2" class="text-center">Garansi</th>
                                    <th rowspan="2" class="text-center">Stok</th>
                                    <th colspan="2" colspan="2" class="text-center">Posisi</th>
                                </tr>
                                <tr>
                                    <th class="text-center">Reseller</th>
                                    <th class="text-center">User</th>
                                    <th class="text-center">Gudang</th>
                                    <th class="text-center">Stok</th>
                                </tr>
                                <tr>
                                    <td rowspan="{{ count($warehouses) + 1 }}">Rp {{ number_format($resellerPrice, 0, ',', '.') }}</td>
                                    <td rowspan="{{ count($warehouses) + 1 }}">
                                        <span class="text-muted text-decoration-line-through">
                                            Rp {{ number_format($userPrice, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td rowspan="{{ count($warehouses) + 1 }}">{{$garansiReseller}}</td>
                                    <td rowspan="{{ count($warehouses) + 1 }}" class="text-center">{{ $item['availableToSell'] }}</td>
                                </tr>
                                @foreach ($stokNew as $data)
                                    <tr>
                                        <td>{{ $data['name'] }}</td>
                                        <td class="text-center">{{ number_format($data['balance']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<script>
    // Simpan URL sebelum reload saat tombol Refresh ditekan
     document.addEventListener('DOMContentLoaded', function () {
        if (!sessionStorage.getItem('lastPage')) {
            sessionStorage.setItem('lastPage', document.referrer);
        }
    });

    // Fungsi untuk refresh halaman dan tetap menyimpan asalnya
    function saveReferrerAndReload() {
        location.reload();
    }

    // Fungsi kembali ke halaman sebelumnya yang sudah disimpan
    function goBack() {
        const lastPage = sessionStorage.getItem('lastPage') || '/';
        sessionStorage.removeItem('lastPage'); // opsional: bersihkan setelah digunakan
        window.location.href = lastPage;
    }
</script>

@endsection

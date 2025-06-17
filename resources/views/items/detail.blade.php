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

    $totalKonsinyasiStok = collect($konsinyasiWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalNonKonsinyasiStok = collect($nonKonsinyasiWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalTscStok = collect($tscWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalResellerStok = collect($resellerWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);
    $totalTransitStok = collect($transitWarehouses)->sum(fn($w) => $stokNew[$w['id']]['balance'] ?? $w['balance'] ?? 0);

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
    .card {
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
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
    @media only screen and (max-width: 768px) {
        .title { font-size: 15px; }
        .table th { width: 100px !important; font-size: 12px; }
        .table th, .table td { font-size: 12px; }
        li { font-size: 12px; }
        .btn { font-size: 12px; padding: 4px 8px; }
        .card { height: auto !important; }

        .label-col {
            width: 90px;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h4 class="title">DETAIL BARANG</h4>
                    <div class="d-flex align-items-center gap-2">
                        <button onclick="saveReferrerAndReload()" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i></button>
                            {{-- Remove the form and replace with button that triggers JS function --}}
                        @if ($status === 'admin' || $status === 'KARYAWAN')
                            <button type="button" class="btn btn-danger" id="btnExportPdf"><i class="bi bi-filetype-pdf"></i></button>
                        @endif
                        <button onclick="goBack()" class="btn btn-warning"><i class="bi bi-box-arrow-left"></i></button>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($status === 'KARYAWAN' || $status === 'admin')
                        <div class="row align-items-end mb-4">
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
                                    <div id="priceSpinner" class="spinner-border spinner-border-sm text-primary ms-2" role="status" style="display: none;">
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
                                <label for="filterGudang" class="form-label fw-semibold">Lokasi Stok</label>
                                <select id="filterGudang" class="form-select">
                                    <option value="semua">Semua Lokasi</option>
                                    <option value="non">Store</option>
                                    <option value="tsc">TSC</option>
                                    <option value="konsinyasi">Konsinyasi</option>
                                    <option value="resel">Reseller</option>
                                    <option value="trans">Transit</option>
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
                                                <td>
                                                    <span id="hargaResellerValue">
                                                        Rp {{ number_format($resellerPrice, 0, ',', '.') }}
                                                    </span>
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
                                                    <span id="hargaUserValue">
                                                        Rp {{ number_format($userPrice, 0, ',', '.') }}
                                                    </span>
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
                                        <li class="d-flex flex-wrap mb-2 align-items-center">
                                            <div class="label-col">Kode</div>
                                            <div class="value-col">
                                                <span class="text-monospace" id="kode-{{ $item['id'] }}">{{ $item['no'] }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                                    onclick="copyToClipboard('{{ $item['id'] }}')">Copy</button>
                                            </div>
                                        </li>
                                        <li class="d-flex flex-wrap mb-2 align-items-center">
                                            <div class="label-col">Harga</div>
                                            <div class="value-col">
                                                <span class="text-decoration-line-through text-muted">Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</span>
                                                <span class="ms-3 text-dark">Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}</span>
                                                @if ($status === 'RESELLER' && isset($discItem) && $discItem > 0)
                                                    <span class="text-danger ms-2">Diskon: {{ $discItem }}%</span>
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

                    @if ($status === 'KARYAWAN' || $status === 'admin')
                        {{-- GUDANG NON-KONSINYASI --}}
                        <div class="mb-4" id="nonKonsinyasiTable">
                            <div class="table-responsive">
                                @if ($filteredNonKonsinyasi->isNotEmpty())
                                    <table class="table">
                                        <thead class="table-secondary text-center">
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
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">
                                                        {{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}
                                                    </td>
                                                     @if ($loop->first)
                                                       <td class="text-center" rowspan="{{ count($filteredNonKonsinyasi) }}" id="totalNonKonsinyasiStok">
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

                        {{-- GUDANG TSC --}}
                        @php
                            $filteredTsc = collect($tscWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredTsc->count())
                        <div class="mb-4" id="tscwarehouseTable">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-secondary text-center">
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
                                                <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                @if ($loop->first)
                                                    <td class="text-center" rowspan="{{ $filteredTsc->count() }}" id="totalTscStok">
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

                        {{-- GUDANG KONSINYASI --}}
                        @php
                            $filteredKonsinyasi = collect($konsinyasiWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredKonsinyasi->count())
                        <div class="mb-4" id="konsinyasiTable">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-secondary text-center">
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
                                            <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                            @if ($loop->first)
                                                <td class="text-center" rowspan="{{ $filteredKonsinyasi->count() }}" id="totalKonsinyasiStok">
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

                        {{-- GUDANG RESELLER --}}
                        @php
                            $filteredReseller = collect($resellerWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredReseller->count())
                            <div class="mb-4" id="resellerwarehouseTable">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th>Reseller</th>
                                                <th>Stok</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($filteredReseller as $data)
                                            <tr>
                                                <td>{{ $data['name'] }}</td>
                                                <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                @if ($loop->first)
                                                    <td class="text-center" rowspan="{{ $filteredReseller->count() }}" id="totalResellerStok">
                                                        {{ number_format($totalResellerStok) }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- GUDANG TRANSIT --}}
                        @php
                            $filteredTransit = collect($transitWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredTransit->count())
                            <div class="mb-4" id="transitwarehouseTable">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-info text-center">
                                            <tr>
                                                <th>Transit</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($filteredTransit as $data)
                                                <tr>
                                                    <td>{{ $data['name'] }}</td>
                                                    <td class="text-center">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @elseif ($status === 'RESELLER')
                        {{-- GUDANG NON KONSINYASI --}}
                        <div class="mb-4" id="nonKonsinyasiTable">
                            <div class="table-responsive">
                                @if ($filteredNonKonsinyasi->isNotEmpty())
                                    <table class="table">
                                        <thead class="table-secondary text-center">
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
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">
                                                        {{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}
                                                    </td>
                                                    @if ($loop->first)
                                                        <td class="text-center" rowspan="{{ count($nonKonsinyasiWarehouses) }}" id="totalNonKonsinyasiStok">
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

                        {{-- GUDANG TSC --}}
                        @php
                            $filteredTsc = collect($tscWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredTsc->count())
                            <div class="mb-4" id="tscwarehouseTable">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-secondary text-center">
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
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                    @if ($loop->first)
                                                        <td class="text-center" rowspan="{{ $filteredTsc->count() }}" id="totalTscStok">
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

                        {{-- GUDANG RESELLER --}}
                        @php
                            $filteredReseller = collect($resellerWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredReseller->count())
                            <div class="mb-4" id="resellerwarehouseTable">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th>Reseller</th>
                                                <th>Stok</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($filteredReseller as $data)
                                            <tr>
                                                <td>{{ $data['name'] }}</td>
                                                <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                @if ($loop->first)
                                                    <td class="text-center" rowspan="{{ $filteredReseller->count() }}" id="totalResellerStok">
                                                        {{ number_format($totalResellerStok) }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- GUDANG KONSINYASI --}}
                        @php
                            $filteredKonsinyasi = collect($konsinyasiWarehouses)->filter(fn($data) => ($stokNew[$data['id']]['balance'] ?? $data['balance']) > 0)->values();
                        @endphp
                        @if ($filteredKonsinyasi->count())
                            <div class="mb-4" id="konsinyasiTable">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-secondary text-center">
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
                                                    <td class="text-center" data-warehouse-id="{{ $data['id'] }}">{{ number_format($stokNew[$data['id']]['balance'] ?? $data['balance']) }}</td>
                                                    @if ($loop->first)
                                                        <td class="text-center" rowspan="{{ $filteredKonsinyasi->count() }}" id="totalKonsinyasiStok">
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
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        function saveReferrerAndReload() {
            location.reload();
        }

        function goBack() {
            const lastPage = sessionStorage.getItem('lastPage') || '/';
            sessionStorage.removeItem('lastPage');
            window.history.back();
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
        const itemId = {{ $item['id'] }};
        const stokNewElements = {};
        let currentStokNew = {};
        document.addEventListener('DOMContentLoaded', function () {
            if (!sessionStorage.getItem('lastPage')) {
                sessionStorage.setItem('lastPage', document.referrer);
            }

            // Event listener untuk select branch_id
            const branchSelect = document.getElementById('branch_id');
            if (branchSelect) {
                branchSelect.addEventListener('change', function () {
                    const branchId = this.value;
                    const itemId = {{ $item['id'] }};
                    const hargaResellerValue = document.getElementById('hargaResellerValue');
                    const hargaUserValue = document.getElementById('hargaUserValue');
                    const spinner = document.getElementById('priceSpinner');

                    if (!branchId) {
                        if (hargaResellerValue) hargaResellerValue.textContent = `Rp ${Number({{ $resellerPrice }}).toLocaleString('id-ID')}`;
                        if (hargaUserValue) hargaUserValue.innerHTML = `Rp ${Number({{ $userPrice }}).toLocaleString('id-ID')}`;
                        @if ($discItem !== null && $discItem > 0)
                            if (hargaUserValue) hargaUserValue.innerHTML += `<p style="margin:0; color: red;">Diskon: {{ $discItem }}%</p>`;
                        @endif
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
                            if (data.adjustedPrice !== null && data.adjustedPrice > 0) {
                                if (hargaUserValue) hargaUserValue.textContent = `Rp ${Number(data.adjustedPrice).toLocaleString('id-ID')}`;
                                if (data.discItem !== null && data.discItem > 0) {
                                    if (hargaUserValue && !hargaUserValue.querySelector('p')) {
                                        const discP = document.createElement('p');
                                        discP.style.margin = '0';
                                        discP.style.color = 'red';
                                        discP.textContent = `Diskon: ${data.discItem}%`;
                                        hargaUserValue.appendChild(discP);
                                    } else if (hargaUserValue) {
                                        hargaUserValue.querySelector('p').textContent = `Diskon: ${data.discItem}%`;
                                    }
                                } else if (hargaUserValue) {
                                    const discP = hargaUserValue.querySelector('p');
                                    if (discP) {
                                        discP.remove();
                                    }
                                }
                            } else {
                                if (hargaUserValue) hargaUserValue.textContent = `Rp ${Number({{ $userPrice }}).toLocaleString('id-ID')}`;
                            }
                        } else {
                            if (hargaUserValue) hargaUserValue.textContent = `Rp ${Number({{ $userPrice }}).toLocaleString('id-ID')}`;
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

            const filterGudang = document.getElementById('filterGudang');
            if (filterGudang) {
                filterGudang.addEventListener('change', function () {
                    const value = this.value;

                    const nonKonsinyasiTable = document.getElementById('nonKonsinyasiTable');
                    const konsinyasiTable = document.getElementById('konsinyasiTable');
                    const tscwarehouseTable = document.getElementById('tscwarehouseTable');
                    const resellerwarehouseTable = document.getElementById('resellerwarehouseTable');
                    const transitwarehouseTable = document.getElementById('transitwarehouseTable');

                    if (value === 'semua') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'block';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'block';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'block';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'block';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'block';
                    } else if (value === 'non') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'block';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'none';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'none';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'none';
                    } else if (value === 'konsinyasi') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'block';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'none';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'none';
                    } else if (value === 'tsc') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'none';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'block';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'none';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'none';
                    } else if (value === 'resel') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'none';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'block';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'none';
                    } else if (value === 'trans') {
                        if (nonKonsinyasiTable) nonKonsinyasiTable.style.display = 'none';
                        if (konsinyasiTable) konsinyasiTable.style.display = 'none';
                        if (tscwarehouseTable) tscwarehouseTable.style.display = 'none';
                        if (resellerwarehouseTable) resellerwarehouseTable.style.display = 'none';
                        if (transitwarehouseTable) transitwarehouseTable.style.display = 'block';
                    }
                });
            }

            const filterHargaGaransi = document.getElementById('filterHargaGaransi');
            if (filterHargaGaransi) {
                filterHargaGaransi.addEventListener('change', function () {
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
            }

            // --- Price Adjustment Reseller (khusus RESELLER) ---
            @if ($status === 'RESELLER')
            const no = "{{ $item['no'] }}";
            const priceCategoryName = "RESELLER";
            const discountCategoryName = "RESELLER";
            function updatePriceAdjustment() {
                const branchName = branchSelect ? branchSelect.value : '';
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
                    const hargaUserStrikethrough = document.getElementById('hargaUserStrikethrough');
                    const discElement = document.getElementById('discItemReseller');
                    if (data.success) {
                        if (hargaReseller && hargaUserStrikethrough && discElement) {
                            if (data.adjustedPrice && data.adjustedPrice > 0) {
                                hargaReseller.textContent = `Rp ${Number(data.adjustedPrice).toLocaleString('id-ID')}`;
                                discElement.textContent = data.discItem ? `Diskon: ${data.discItem}%` : '';
                            } else {
                                hargaReseller.textContent = `Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}`;
                                discElement.textContent = '';
                            }
                        }
                    } else {
                        if (hargaReseller && hargaUserStrikethrough && discElement) {
                            hargaReseller.textContent = `Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}`;
                            discElement.textContent = '';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            updatePriceAdjustment();
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

                @foreach ($nonKonsinyasiWarehouses as $w)
                {
                    let nonKonsinyasiTd = stokNewElements[{{ $w['id'] }}];
                    if (nonKonsinyasiTd) {
                        let val = nonKonsinyasiTd.textContent.replace(/\./g, '');
                        let parsed = parseInt(val);
                        if (!isNaN(parsed)) {
                            totalNonKonsinyasi += parsed;
                        }
                    }
                }
                @endforeach

                @foreach ($tscWarehouses as $w)
                {
                    let tscTd = stokNewElements[{{ $w['id'] }}];
                    if (tscTd) {
                        let val = tscTd.textContent.replace(/\./g, '');
                        let parsed = parseInt(val);
                        if (!isNaN(parsed)) {
                            totalTsc += parsed;
                        }
                    }
                }
                @endforeach

                @foreach ($konsinyasiWarehouses as $w)
                {
                    let konsinyasiTd = stokNewElements[{{ $w['id'] }}];
                    if (konsinyasiTd) {
                        let val = konsinyasiTd.textContent.replace(/\./g, '');
                        let parsed = parseInt(val);
                        if (!isNaN(parsed)) {
                            totalKonsinyasi += parsed;
                        }
                    }
                }
                @endforeach

                @foreach ($resellerWarehouses as $w)
                {
                    let resellerTd = stokNewElements[{{ $w['id'] }}];
                    if (resellerTd) {
                        let val = resellerTd.textContent.replace(/\./g, '');
                        let parsed = parseInt(val);
                        if (!isNaN(parsed)) {
                            totalReseller += parsed;
                        }
                    }
                }
                @endforeach

                @foreach ($transitWarehouses as $w)
                {
                    let transitTd = stokNewElements[{{ $w['id'] }}];
                    if (transitTd) {
                        let val = transitTd.textContent.replace(/\./g, '');
                        let parsed = parseInt(val);
                        if (!isNaN(parsed)) {
                            totalTransit += parsed;
                        }
                    }
                }
                @endforeach

                const totalNonKonsinyasiEl = document.getElementById('totalNonKonsinyasiStok');
                if (totalNonKonsinyasiEl) {
                    totalNonKonsinyasiEl.textContent = new Intl.NumberFormat('id-ID').format(totalNonKonsinyasi);
                }

                const totalTscEl = document.getElementById('totalTscStok');
                if (totalTscEl) {
                    totalTscEl.textContent = new Intl.NumberFormat('id-ID').format(totalTsc);
                }

                const totalKonsinyasiEl = document.getElementById('totalKonsinyasiStok');
                if (totalKonsinyasiEl) {
                    totalKonsinyasiEl.textContent = new Intl.NumberFormat('id-ID').format(totalKonsinyasi);
                }

                const totalResellerEl = document.getElementById('totalResellerStok');
                if (totalResellerEl) {
                    totalResellerEl.textContent = new Intl.NumberFormat('id-ID').format(totalReseller);
                }

                const totalTransitEl = document.getElementById('totalTransitStok');
                if (totalTransitEl) {
                    totalTransitEl.textContent = new Intl.NumberFormat('id-ID').format(totalTransit);
                }
            }

            // Step 1: Fetch Sales Order stock (kurangi dulu stok berdasarkan Sales Order)
            fetch(`{{ url('/items/salesorder-stock-ajax') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ item_id: itemId, includeInvoice: false })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentStokNew = data.stokNew;

                    // Step 2: Fetch Matching Invoices (lanjut kurangi stok dari hasil sebelumnya)
                    return fetch(`{{ url('/items/matching-invoices-ajax') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            item_id: itemId,
                            stok_awal: currentStokNew // ⬅️ kirim stok yang sudah dikurangi sales order
                        })
                    });
                } else {
                    throw new Error('Gagal mengambil data stok sales order: ' + data.message);
                }
            })
            .then(async response => {
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Server error:', text);
                    throw new Error('Server returned an error for matching-invoices-ajax');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    currentStokNew = data.stokNew;
                    updateStokTable(currentStokNew); // ✅ tampilkan stok terbaru
                } else {
                    console.error('Gagal mengambil data matching invoices:', data.message);
                }
            })
            .catch(error => {
                console.error('Terjadi kesalahan saat mengambil data stok:', error);
            });
        });

        document.getElementById('btnExportPdf').addEventListener('click', function () {
            const branchId = document.getElementById('branch_id') ? document.getElementById('branch_id').value : '';
            const filterGudang = document.getElementById('filterGudang') ? document.getElementById('filterGudang').value : 'semua';
            const filterHargaGaransi = document.getElementById('filterHargaGaransi') ? document.getElementById('filterHargaGaransi').value : 'semua';

            if (!currentStokNew) {
                alert('Data stok belum tersedia. Silakan tunggu hingga data selesai dimuat.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('branch_id', branchId);
            formData.append('filterGudang', filterGudang);
            formData.append('filterHargaGaransi', filterHargaGaransi);
            formData.append('stokNew', JSON.stringify(currentStokNew));

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('items.export-pdf.post', ['id' => $item['id']]) }}";
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

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Produk - Twincomgo</title>
<style>
@page {
    margin: 10px 25px 10px 25px;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 11.5px;
    color: #333;
    margin: 0;
    padding: 0;
    line-height: 1.4;
}

/* === HEADER === */
header {
    padding-bottom: 5px;
    margin-bottom: 15px;
}
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
}
.logo-section {
    display: flex;
    align-items: center;
}
.logo-section img {
    width: 160px;
    height: auto;
    margin-right: 15px;
}
.company-info {
    border-left: 3px solid #2c5aa0;
    padding-left: 15px;
}
.company-name {
    font-weight: bold;
    font-size: 18px;
    color: #1a3c6e;
    margin-bottom: 4px;
}
.company-tagline {
    font-size: 14px;
    color: #2c5aa0;
    font-weight: 500;
}
.header-details {
    text-align: right;
    font-size: 10px;
    color: #555;
}
.header-details div {
    margin-bottom: 3px;
}
.document-title {
    font-size: 16px;
    font-weight: bold;
    color: #1a3c6e;
    text-align: center;
    margin-top: 8px;
    padding: 5px 0;
    border-top: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
}

/* === FOOTER === */
footer {
    position: fixed;
    bottom: 20px;
    left: 0;
    right: 0;
    height: 40px;
    font-size: 9px;
    color: #666;
    padding: 8px 25px;
    border-top: 1px solid #2c5aa0;
    background-color: #fff;
    z-index: 1000;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    height: 100%;
    align-items: center;
}

.footer-left {
    text-align: left;
    flex: 1;
}
.footer-right {
    text-align: right;
    flex: 0 0 auto;
}
.page-number::after {
    content: counter(page);
}
.company-address {
    font-size: 8px;
    margin-top: 2px;
    color: #888;
}

/* === CONTENT === */
main {
    margin-top: 15px;
}
.product-section {
    margin-bottom: 20px;
    page-break-inside: avoid;
}
.product-images {
    text-align: center;
    /* margin: 15px 0; */
    page-break-inside: avoid;
}
.product-images img {
    max-width: 150px;
    max-height: 150px;
    margin: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.product-header {
    background-color: #f8f9fa;
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 12px;
    border-left: 4px solid #2c5aa0;
}
.product-name {
    font-size: 12px;
    font-weight: bold;
    color: #1a3c6e;
    margin-bottom: 5px;
}
.product-code {
    font-size: 12px;
    color: #666;
}
.price-section {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}
.price-box {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px 12px;
    background-color: #f8f9fa;
}
.price-box.user {
    border-top: 3px solid #198754;
}
.price-box.reseller {
    border-top: 3px solid #0d6efd;
}
.price-label {
    font-size: 10px;
    color: #666;
    margin-bottom: 5px;
}
.price-value {
    font-size: 14px;
    font-weight: bold; 
}
.price-user {
    color: #198754;
}
.price-reseller {
    color: #0d6efd;
}
.warranty-info {
    font-size: 10px;
    color: #666;
    font-style: italic;
}

.section-title {
    font-size: 14px;
    font-weight: bold;
    color: #1a3c6e;
    /* margin: 15px 0 8px 0; */
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 11px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    page-break-inside: avoid;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
}

th {
    background: #136b35;
    color: white;
    text-align: center;
    font-weight: 600;
}
tr:nth-child(even) {
    background-color: #f8f9fa;
}
tfoot tr {
    background-color: #e9ecef !important;
    font-weight: bold;
}
.text-center { text-align: center; }
.text-right { text-align: right; }

.section-title {
    font-size: 14px;
    font-weight: bold;
    color: #1a3c6e;
    padding: 8px 0;
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
    page-break-inside: avoid;
    page-break-after: avoid;
}

/* Smart page break untuk warehouse group */
.warehouse-group {
    margin-bottom: 15px;
    page-break-inside: avoid;
}

.warehouse-group:not(:last-child) {
    page-break-after: auto;
}

.page-break {
    page-break-after: always;
}

/* Watermark effect */
.watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 80px;
    color: rgba(44, 90, 160, 0.05);
    z-index: -1;
    font-weight: bold;
    pointer-events: none;
}
</style>
</head>
<body>

<div class="watermark">TWINCOMGO</div>

@if($loopFirstHeader ?? true)
<header>
    <div class="header-container">
        <div class="logo-section">
            <img src="{{ base_path('images/logo-hijau.png') }}" alt="Twincomgo Logo" style="margin-bottom:10px;">
            <div class="company-info">
                <div class="company-name">CV. TWIN GROUP</div>
                <div class="company-tagline">Sistem Informasi Stok Barang</div>
            </div>
        </div>
    </div>
    <div class="document-title">INFORMASI PRODUK DAN STOK</div>
</header>
@endif

<footer>
    <div class="footer-content">
        <div class="footer-left">
            <div>Dicetak oleh: Sistem Twincomgo | {{ now()->format('d M Y, H:i') }}</div>
            <div class="company-address">CV TWIN GROUP - Jl. Kampung Baru RT.03 RW.02 Jl.Seroja No.11 Landasan Ulin, Banjarbaru</div>
        </div>
        <div class="footer-right">
            Hal. <span class="page-number"></span>
        </div>
    </div>
</footer>

<main>
    <div class="product-section">
        <div class="product-images">
            @forelse($images as $img)
                <img src="{{ $img }}" alt="Gambar Produk">
            @empty
                <img src="{{ public_path('images/noimage.jpg') }}" alt="Tidak ada gambar">
            @endforelse
        </div>

        <div class="product-header">
            <div class="product-name">{{ $item['name'] }}</div>
            <div class="product-code">Kode Produk: {{ $item['no'] ?? '-' }}</div>
        </div>

        @if($priceType === 'all')
            <div class="price-section">
                <div class="price-box user">
                    <div class="price-label">Harga User</div>
                    <div class="price-value price-user">
                        @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                            @foreach($unitPrices as $unitName => $p)
                                @if(isset($p['user']) && $p['user'] > 0)
                                    <h5 id="userPrice" class="price-user" data-unit="{{$unitName}}">
                                        Rp {{ number_format($p['user'], 0, ',', '.') }} / {{ strtoupper($unitName) }}
                                    </h5>
                                @endif
                            @endforeach
                        @else
                            <h3 id="userPriceMain" class="price-user">
                                Rp {{ number_format($prices['user'],0,',','.') }}
                            </h3>
                        @endif
                    </div>
                    <div class="warranty-info">Garansi: {{ $item['charField6'] ?? '-' }}</div>
                </div>

                <div class="price-box reseller">
                    <div class="price-label">Harga Reseller</div>
                    <div class="price-value price-reseller">
                        @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                            @foreach($unitPrices as $unitName => $p)
                                @if(isset($p['reseller']) && $p['reseller'] > 0)
                                    <h5 id="resellerPrice" class="price-reseller" data-unit="{{$unitName}}">
                                        Rp {{ number_format($p['reseller'],0,',','.') }} / {{ strtoupper($unitName) }}
                                    </h5>
                                @endif
                            @endforeach
                        @else
                            <h3 id="resellerPriceMain" class="price-reseller">
                                Rp {{ number_format($prices['reseller'],0,',','.') }}
                            </h3>
                        @endif
                    </div>
                    <div class="warranty-info">Garansi: {{ $item['charField7'] ?? '-' }}</div>
                </div>
            </div>
        @elseif($priceType === 'user')
            <div class="price-section">
                <div class="price-box user">
                    <div class="price-label">Harga</div>
                    <div class="price-value price-user">
                        @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                            @foreach($unitPrices as $unitName => $p)
                                @if(isset($p['user']) && $p['user'] > 0)
                                    <h5 id="userPrice" class="price-user" data-unit="{{$unitName}}">
                                        Rp {{ number_format($p['user'], 0, ',', '.') }} / {{ strtoupper($unitName) }}
                                    </h5>
                                @endif
                            @endforeach
                        @else
                            <h3 id="userPriceMain" class="price-user">
                                Rp {{ number_format($prices['user'],0,',','.') }}
                            </h3>
                        @endif
                    </div>
                    <div class="warranty-info">Garansi: {{ $item['charField6'] ?? '-' }}</div>
                </div>
            </div>
        @elseif($priceType === 'reseller')
            <div class="price-section">
                <div class="price-box reseller">
                    <div class="price-label">Harga</div>
                    <div class="price-value price-reseller">
                        @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                            @foreach($unitPrices as $unitName => $p)
                                @if(isset($p['reseller']) && $p['reseller'] > 0)
                                    <h5 id="resellerPrice" class="price-reseller" data-unit="{{$unitName}}">
                                        Rp {{ number_format($p['reseller'],0,',','.') }} / {{ strtoupper($unitName) }}
                                    </h5>
                                @endif
                            @endforeach
                        @else
                            <h3 id="resellerPriceMain" class="price-reseller">
                                Rp {{ number_format($prices['reseller'],0,',','.') }}
                            </h3>
                        @endif
                    </div>
                    <div class="warranty-info">Garansi: {{ $item['charField7'] ?? '-' }}</div>
                </div>
            </div>
        @endif
    </div>

    @foreach($warehouses as $type => $list)
        @if(count($list) > 0)
            <div class="warehouse-group">
                <div class="section-title">STOK {{ strtoupper($type) }}</div>
                <table>
                    <thead>
                    <tr>
                        <th>Lokasi</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Satuan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $w)
                        <tr>
                            <td>{{ $w['name'] }}</td>
                            <td class="text-center">{{ $w['balance'] ?? 0 }}</td>
                            <td class="text-center">{{ $w['unit_display'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-center">{{ $list->sum('balance') }}</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 🔸 Tambahkan page break setelah tabel Store --}}
            @php
                // Cari apakah masih ada gudang lain setelah "store" yang punya isi
                $hasNextGroup = false;
                $foundStore = false;
                foreach ($warehouses as $key => $group) {
                    if (strtolower($key) === 'store') {
                        $foundStore = true;
                        continue;
                    }
                    if ($foundStore && count($group) > 0) {
                        $hasNextGroup = true;
                        break;
                    }
                }
            @endphp

            @if(strtolower($type) === 'store' && $hasNextGroup)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach
</main>

</body>
</html>
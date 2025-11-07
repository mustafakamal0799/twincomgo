<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Produk Twincomgo</title>
<style>
@page {
    margin: 90px 25px 60px 25px;
}
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11.5px;
    color: #333;
    margin: 0;
    padding: 0;
}

/* === HEADER === */
header {
    position: relative;
    left: 25px;
    right: 25px;
    height: 80px;
    padding-bottom: 5px;
    margin-bottom: 10px;
}
header table {
    width: 100%;
    border-collapse: collapse;
}
header td {
    border: none;
    vertical-align: middle;
}
header img {
    width: 160px;
    height: auto;
    display: block;
    margin-top: 5px;
}
header .right {
    padding-left: 15px;
    border-left: 3px solid #8dbf9b;
    font-family: Arial, sans-serif;
    color: #000000;
    text-transform: uppercase;
}
header .right .line1 {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 6px;
}
header .right .line2 {
    font-weight: bold;
    font-size: 22px;
    letter-spacing: 0.5px;
}
header .divider {
    border-bottom: 2px solid #8dbf9b;
    margin-top: 8px;
}

/* === FOOTER === */
footer {
    position: fixed;
    bottom: 10px;
    left: 25px;
    right: 25px;
    font-size: 10px;
    color: #555;
    padding-top: 5px;
    border-top: 1px solid #8dbf9b;
}

.footer-table {
    width: 100%;
    border: none !important;
    border-collapse: separate !important;
}
.footer-table tr,
.footer-table td,
.footer-table th {
    border: none !important;
    background: transparent !important;
}
.footer-table td {
    padding: 0;
    vertical-align: top;
}
.footer-table td.left {
    text-align: left;
}
.footer-table td.right {
    text-align: right;
}
.page-number::after {
    content: counter(page);
}

/* === CONTENT === */
main {
    margin-top: 15px; /* supaya tidak nabrak header */
}
.img-container {
    text-align: center;
    margin: 15px 0;
    page-break-inside: avoid;
}
.img-container img {
    max-width: 150px;
    max-height: 150px;
    margin: 5px;
    border: 1px solid #ddd;
    border-radius: 6px;
    vertical-align: middle;
}

h4 {
    margin-bottom: 4px;
    margin-top: 10px;
}
.price-box {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 8px 10px;
    margin-bottom: 10px;
}
.price-user {
    color: #198754;
    font-weight: bold;
}
.price-reseller {
    color: #0d6efd;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 11px;
}
th, td {
    border: 1px solid #ccc;
    padding: 6px;
}
th {
    background: #f2f2f2;
    text-align: center;
}
.text-center { text-align: center; }
.text-right { text-align: right; }

.page-break {
    page-break-after: always;
}
</style>
</head>
<body>

@if($loopFirstHeader ?? true)
<header>
    <table>
        <tr>
            <td style="width:220px;">
                <img src="{{ public_path('images/logo-hijau.png') }}" alt="Twincomgo Logo">
            </td>
            <td class="right">
                <div class="line1">SISTEM INFORMASI STOK BARANG</div>
                <div class="line2">TWINCOMGO</div>
            </td>
        </tr>
    </table>
    <div class="divider"></div>
</header>
@endif

<footer>
    <table class="footer-table">
        <tr>
            <td class="left">
                {{ now()->format('d M Y, H:i') }} | Twincomgo
            </td>
            <td class="right">
                Hal. <span class="page-number"></span>
            </td>
        </tr>
    </table>
</footer>

<main>
    <div class="img-container">
        @forelse($images as $img)
            <img src="{{ $img }}" alt="Gambar Produk">
        @empty
            <img src="{{ public_path('images/noimage.jpg') }}" alt="Tidak ada gambar">
        @endforelse
    </div>

    <h4>{{ $item['name'] }}</h4>
    <p><strong>Kode Produk:</strong> {{ $item['no'] ?? '-' }}</p>

    @if($priceType === 'all')
        <div class="price-box">
            <div class="price-user">Harga User: Rp {{ number_format($prices['user'] ?? 0, 0, ',', '.') }}</div>
            <div style="font-size: 10px; color:#555;">Garansi: {{ $item['charField6'] ?? '-' }}</div>
        </div>

        <div class="price-box">
            <div class="price-reseller">Harga Reseller: Rp {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}</div>
            <div style="font-size: 10px; color:#555;">Garansi: {{ $item['charField7'] ?? '-' }}</div>
        </div>
    @elseif($priceType === 'user')
        <div class="price-box">
            <div class="price-user">Harga: Rp {{ number_format($prices['user'] ?? 0, 0, ',', '.') }}</div>
            <div style="font-size: 10px; color:#555;">Garansi: {{ $item['charField6'] ?? '-' }}</div>
        </div>
    @elseif($priceType === 'reseller')
        <div class="price-box">
            <div class="price-reseller">Harga: Rp {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}</div>
            <div style="font-size: 10px; color:#555;">Garansi: {{ $item['charField7'] ?? '-' }}</div>
        </div>
    @endif

    @foreach($warehouses as $type => $list)
        @if(count($list) > 0)
            <h4 style="margin-top:15px;">{{ ucfirst($type) }}</h4>
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
                        <td class="text-center">{{ $w['unit'] ?? '-' }}</td>
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

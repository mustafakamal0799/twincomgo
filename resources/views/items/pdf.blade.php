<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Produk - Twincomgo</title>
<style>

    /* === VARIABEL WARNA PROFESIONAL === */
    :root {
        --primary: #1a5632;
        --primary-dark: #0f3d22;
        --primary-light: #2e8b57;
        --gray-dark: #2d3748;
        --gray-medium: #4a5568;
        --gray-light: #718096;
        --gray-ultralight: #f7fafc;
        --border: #e2e8f0;
    }

    /* === BASE === */
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11.5px;
        color: #333;
        margin: 0 0 0 0;
        margin-bottom: 60px;
    }

    /* === HEADER === */
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #136b35;
        padding-bottom: 8px;
        margin-bottom: 10px;
    }

    .header img {
        height: 45px;
    }

    .header .title {
        text-align: right;
        font-weight: 700;
        font-size: 16px;
        color: #136b35;
    }

    /* === FOOTER === */
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 45px;
        font-size: 9px;
        color: #666;
        padding: 5px 25px 0 25px;
        border-top: 1px solid #2c5aa0;
        background-color: #fff;
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

    /* === SUBTITLE / FILTER INFO === */
    .subtitle {
        text-align: center;
        font-size: 11px;
        color: #444;
        margin-bottom: 12px;
    }

    /* === TABLE === */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 6px 8px;
    }
    th {
        background: #136b35;
        color: white;
        font-weight: 600;
    }
    tr:nth-child(even) { background: #f8f9fa; }
    tr:nth-child(odd)  { background: #ffffff; }

    .text-center { text-align: center; }
    .text-end { text-align: right; }
    
    /* === LOGO PLACEHOLDER SAFE === */
    .logo-placeholder {
        font-size: 12px;
        color: #999;
        border: 1px dashed #999;
        padding: 10px;
        display: inline-block;
        border-radius: 4px;
    }

    /* === METADATA SECTION === */
    .metadata {
        display: flex;
        justify-content: space-between;
        /* border-bottom: 1px solid var(--border); */
        font-size: 10px;
        color: var(--gray-medium);
    }

    .metadata-left, .metadata-right {
        font-size: 10px;
        text-align: right;
        color: var(--gray-medium);
    }

    .metadata-right {
        text-align: right;
    }

</style>
</head>
<body>

    {{-- 🔹 HEADER --}}
    <div class="header">
        {{-- Ganti URL di bawah dengan asset logo kamu --}}
        @if(file_exists(base_path('images/logo-hijau-tua.png')))
            <img src="{{ base_path('images/logo-hijau-tua.png') }}" alt="Twincomgo Logo">
        @else
            <div class="logo-placeholder">[ LOGO TWINCOMGO ]</div>
        @endif

        <div class="title">
            <div>DAFTAR PRODUK</div>
            <div style="font-size:12px;font-weight:400;color:#555;">Twincomgo</div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div>Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | {{ now()->format('d M Y | H:i') }}</div>
                <div class="company-address">CV TWIN GROUP - Jl. Kampung Baru RT.03 RW.02 Jl.Seroja No.11 Landasan Ulin, Banjarbaru</div>
            </div>
            <div class="footer-right">
                Hal. <span class="page-number"></span>
            </div>
        </div>
    </footer>

    {{-- 🔹 SUBTITLE --}}
    <div class="metadata">
        <div class="metadata-left">
            {{ now()->translatedFormat('l, d F Y') }}            
        </div>
        <div class="metadata-right">
            PROD-{{ now()->format('Ymd-His') }}
        </div>
    </div>

    {{-- 🔹 TABLE --}}
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:10%">Kode</th>
                <th>Nama Produk</th>
                <th style="width:18%" class="text-end">Harga (Rp)</th>
                <th style="width:8%" class="text-center">Stok</th>
                <th style="width:12%" class="text-center">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item['no'] ?? '-' }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="text-end">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item['availableToSell'] ?? 0 }}</td>
                <td class="text-center">
                    {{ preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-'))) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data ditampilkan.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

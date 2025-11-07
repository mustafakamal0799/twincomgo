<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Produk - Twincomgo</title>
<style>
    /* === BASE === */
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11.5px;
        color: #333;
        margin: 25px 30px 50px 30px;
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

    /* === FOOTER === */
    footer {
        position: fixed;
        bottom: 15px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #777;
        border-top: 1px solid #ccc;
        padding-top: 5px;
    }

    /* === LOGO PLACEHOLDER SAFE === */
    .logo-placeholder {
        font-size: 12px;
        color: #999;
        border: 1px dashed #999;
        padding: 10px;
        display: inline-block;
        border-radius: 4px;
    }

</style>
</head>
<body>

    {{-- 🔹 HEADER --}}
    <div class="header">
        {{-- Ganti URL di bawah dengan asset logo kamu --}}
        @if(file_exists(public_path('images/logo-hijau-tua.png')))
            <img src="{{ public_path('images/logo-hijau-tua.png') }}" alt="Twincomgo Logo">
        @else
            <div class="logo-placeholder">[ LOGO TWINCOMGO ]</div>
        @endif

        <div class="title">
            <div>DAFTAR PRODUK</div>
            <div style="font-size:12px;font-weight:400;color:#555;">Twincomgo</div>
        </div>
    </div>

    {{-- 🔹 SUBTITLE --}}
    <div class="subtitle">
        Harga: 
        <strong>
            {{ strtoupper($filters['priceMode'] ?? 'USER') === 'DEFAULT' ? 'USER' : strtoupper($filters['priceMode'] ?? 'USER') }}
        </strong> |
        Stok: <strong>{{ ($filters['stokAda'] ?? '1') == '1' ? 'Ready' : 'Semua' }}</strong>
        @if(!empty($filters['search']))
            | Pencarian: "<strong>{{ $filters['search'] }}</strong>"
        @endif
        @if(!empty($filters['minPrice']) || !empty($filters['maxPrice']))
            | Harga: 
            <strong>
                {{ $filters['minPrice'] ? 'Rp ' . number_format($filters['minPrice'], 0, ',', '.') : '0' }}
                –
                {{ $filters['maxPrice'] ? 'Rp ' . number_format($filters['maxPrice'], 0, ',', '.') : '∞' }}
            </strong>
        @endif
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

    {{-- 🔹 FOOTER --}}
    <footer>
        Dicetak pada {{ now()->format('d M Y, H:i') }} | Twincomgo
    </footer>

</body>
</html>

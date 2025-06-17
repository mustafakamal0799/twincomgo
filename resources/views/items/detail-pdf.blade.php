<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LIST STOK - {{ $item['name'] }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .kop {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }
        .harga {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .harga p {
            margin: 4px 0;
        }
        .harga-table {
            width: auto;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .harga-table th, .harga-table td {
            border: none;
            padding: 4px 8px;
            text-align: left;
            vertical-align: top;
            background: #ffffff;
        }
        .harga-table th {
            min-width: 120px;
            font-weight: normal;
            padding-right: 20px;
        }
        .label-value {
            display: inline-block;
            min-width: 150px;
            padding-left: 10px;
            text-align: left;
        }
        .kop .sub-title {
            margin-top: -25px;
            font-size: 12px;
            margin: 0;
        }
        .kop .title {
            font-size: 30px;
            margin: 0;
        }
        .kop .logo-text-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
        }

        .kop .logo {
            width: 60px;
            height: auto;
            display: block;
        }

        /* Remove border from kop table cells */
        .kop table td {
            border: none;
        }
    </style>
</head>
<body>
    @php
        $status = Auth::user()->status;
    @endphp
    <div class="kop" style="margin-bottom: 20px; border-bottom: 1px solid #8dbf9b; padding-bottom: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle;">
                    <img src="{{ public_path('images/logo-hijau.png') }}" alt="Logo" style="width: 200px; height: auto; display: block;">
                </td>
                <td style="vertical-align: middle; padding-left: 15px; border-left: 3px solid #8dbf9b; font-family: Arial, sans-serif; font-size: 14px; color: #000000; text-align: left; text-transform: uppercase;">
                    <div style="font-weight: bold; font-size: 20px; margin-bottom: 10px;">SISTEM INFORMASI STOK BARANG</div>
                    <div style="font-weight: bold; font-size: 24px;">TWINCOMGO</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="harga" style="margin-top: 10px; margin-bottom: 20px; border-bottom: 1px solid #8dbf9b; padding-bottom: 10px;">
        <table class="harga-table" style="width: 100%;">
            <tbody>
                @if ($status === "admin" || $status === "KARYAWAN")
                    <tr>
                        <th>Nama Barang</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $item['name'] }}</td>
                    </tr>
                    <tr>
                        <th>Kode Barang</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $item['no'] }}</td>
                    </tr>
                    @if ($filterHargaGaransi === 'user' || $filterHargaGaransi === 'semua')
                    <tr>
                        <th>Harga User</th>
                        <td style="width: 2px">:</td>
                        <td>Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Garansi User</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $garansiUser ?? '-' }}</td>
                    </tr>
                    @endif
                    @if ($filterHargaGaransi === 'reseller' || $filterHargaGaransi === 'semua')
                    <tr>
                        <th>Harga Reseller</th>
                        <td style="width: 2px">:</td>
                        <td>Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Garansi Reseller</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $garansiReseller ?? '-' }}</td>
                    </tr>
                    @endif
                @elseif ($status === "RESELLER")
                    <tr>
                        <th>Nama Barang</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $item['name'] }}</td>
                    </tr>
                    <tr>
                        <th>Kode Barang</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $item['no'] }}</td>
                    </tr>
                    @if ($filterHargaGaransi === 'user' || $filterHargaGaransi === 'semua')
                    <tr>
                        <th>Harga</th>
                        <td style="width: 2px">:</td>
                        <td>Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Garansi</th>
                        <td style="width: 2px">:</td>
                        <td>{{ $garansiUser ?? '-' }}</td>
                    </tr>
                    @endif                    
                @endif
            </tbody>
        </table>
    </div>

    @if(count($tscStock) > 0)
    <div class="section-title">Stok TSC</div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lokasi</th>
                <th style="text-align: center;">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tscStock as $stok)
            <tr>
                <td>{{ $stok['name'] }}</td>
                <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($nonKonsinyasiStock) > 0)
    <div class="section-title">Stok Store</div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lokasi</th>
                <th style="text-align: center;">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($nonKonsinyasiStock as $stok)
            <tr>
                <td>{{ $stok['name'] }}</td>
                <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($resellerStock) > 0)
    <div class="section-title">Stok Reseller</div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lokasi</th>
                <th style="text-align: center;">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resellerStock as $stok)
            <tr>
                <td>{{ $stok['name'] }}</td>
                <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($konsinyasiStock) > 0)
    <div class="section-title">Stok Konsinyasi</div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lokasi</th>
                <th style="text-align: center;">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($konsinyasiStock as $stok)
            <tr>
                <td>{{ $stok['name'] }}</td>
                <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($transitStock) > 0)
    <div class="section-title">Stok Transit</div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lokasi</th>
                <th style="text-align: center;">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transitStock as $stok)
            <tr>
                <td>{{ $stok['name'] }}</td>
                <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="position: fixed; bottom: 20px; right: 20px; font-size: 10px; color: #666;">
        Dicetak pada tanggal: {{ \Carbon\Carbon::now('Asia/Makassar')->format('d-m-Y H:i:s') }}
    </div>

</body>
</html>

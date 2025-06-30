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
            margin: 10;
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
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            font-size: 12px;
            width: 100%;
        }

        .footer .left {
            float: left;
            text-align: left;
        }

        .footer .right {
            float: right;
            text-align: right;
        }

        .footer .center {
            text-align: center;
            clear: both;
        }
    </style>
    
</head>
<body>
    
    @php
        $status = Auth::user()->status;

        $sectionCount = 0;
        if (count($tscStock) > 0) $sectionCount++;
        if (count($nonKonsinyasiStock) > 0) $sectionCount++;
        if (count($resellerStock) > 0) $sectionCount++;
        if (count($konsinyasiStock) > 0) $sectionCount++;
        if (count($transitStock) > 0) $sectionCount++;
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
        <table style="width: 100%;">
            <tr>
                <td style="width: 200px; vertical-align: top; border:none;">
                    @if($imageBase64)
                        <img src="data:image/png;base64,{{ $imageBase64 }}" alt="Gambar Item" width="200">
                    @else
                        <img src="{{ public_path('/images/noimage.jpg') }}" alt="Gambar default" width="200" style="margin-top: -20px">
                    @endif
                </td>
                <td style="padding-left: 20px; vertical-align: top; border:none;">
                    <table class="harga-table" style="width: 100%;">
                        <tbody>
                            @if ($status === "admin" || $status === "KARYAWAN")
                                <tr>
                                    <td colspan="3" style="font-weight: bold; height: 50px;">{{ $item['name'] }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 100px; text-align: left;">Kode Barang</th>
                                    <td style="width: 10px; text-align: left;">:</td>
                                    <td>{{ $item['no'] }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 100px; text-align: left;">Satuan Barang</th>
                                    <td style="width: 10px; text-align: left;">:</td>
                                    <td>{{ preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $satuanItem ))) ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $satuanItem ))) : trim(str_replace(['[', ']'], '', $satuanItem ))}}</td>
                                </tr>
                                @if ($filterHargaGaransi === 'user' || $filterHargaGaransi === 'semua')
                                <tr>
                                    <th>
                                        @if ($filterHargaGaransi === 'semua')
                                            Harga User
                                        @else 
                                            Harga
                                        @endif
                                    </th>
                                    <td>:</td>
                                    <td>Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        @if ($filterHargaGaransi === 'semua')
                                            Harga Pack User
                                        @else 
                                            Harga Pack
                                        @endif
                                    </th>
                                    <td>:</td>
                                    <td>Rp {{ number_format($pckUserPrice, 0, ',', '.') ?? '0' }}</td>
                                </tr>
                                <tr>
                                    <th>Garansi</th>
                                    <td>:</td>
                                    <td>{{ $garansiUser ?? '-' }}</td>
                                </tr>
                                @endif
                                @if ($filterHargaGaransi === 'reseller' || $filterHargaGaransi === 'semua')
                                <tr>
                                    <th>
                                        @if ($filterHargaGaransi === 'semua')
                                            Harga Reseller
                                        @else 
                                            Harga
                                        @endif
                                    </th>
                                    <td>:</td>
                                    <td>Rp {{ number_format($finalResellerPrice, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        @if ($filterHargaGaransi === 'semua')
                                            Harga Pack Reseller
                                        @else 
                                            Harga Pack
                                        @endif
                                    </th>
                                    <td>:</td>
                                    <td>Rp {{ number_format($pckResellerPrice, 0, ',', '.') ?? '0' }}</td>
                                </tr>
                                <tr>
                                    <th>Garansi</th>
                                    <td>:</td>
                                    <td>{{ $garansiReseller ?? '-' }}</td>
                                </tr>
                                @endif
                            @elseif ($status === "RESELLER")
                                 <tr>
                                    <td colspan="3" style="font-weight: bold; height: 50px;">{{ $item['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Kode Barang</th>
                                    <td>:</td>
                                    <td>{{ $item['no'] }}</td>
                                </tr>
                                @if ($filterHargaGaransi === 'user' || $filterHargaGaransi === 'semua')
                                <tr>
                                    <th>Harga</th>
                                    <td>:</td>
                                    <td>Rp {{ number_format($finalUserPrice, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Garansi</th>
                                    <td>:</td>
                                    <td>{{ $garansiUser ?? '-' }}</td>
                                </tr>
                                @endif                    
                            @endif
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    
    @if(count($nonKonsinyasiStock) > 0 && ($filterGudang === 'semua' || $filterGudang === 'non'))
    <div class="section-title ">Store</div>
    <table>
        <thead>
                <tr>
                    <th style="text-align: center;">Lokasi</th>
                    <th style="text-align: center;">Stok</th>
                    <th style="text-align: center;">Satuan</th>
                    <th style="text-align: center;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nonKonsinyasiStock as $stok)
                <tr>
                    <td>{{ $stok['name'] }}</td>
                    <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
                    <td style="text-align: center;">
                        {{ 
                            preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                            ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                            : trim(str_replace(['[', ']'], '', $stok['balanceUnit'] ))
                        }}
                    </td>
                    @if ($loop->first)
                    <td rowspan="{{ count($nonKonsinyasiStock) }}" style="text-align: center; vertical-align: middle;">
                        {{ number_format($totalNonKonsinyasi, 0, ',', '.') }}
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <div class="footer">
            <div class="left">SISB TWINCOMGO</div>
            <div class="right">{{ \Carbon\Carbon::now('Asia/Makassar')->translatedFormat('d F Y H:i') }}</div>
        </div>
        <div class="page-break"></div>
        @if(count($tscStock) > 0 && ($filterGudang === 'semua' || $filterGudang === 'tsc'))
            <div class="section-title">TSC</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">Lokasi</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Satuan</th>
                        <th style="text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tscStock as $stok)
                    <tr>
                        <td>{{ $stok['name'] }}</td>
                        <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
                        <td style="text-align: center;">
                            {{ 
                                preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                : trim(str_replace(['[', ']'], '', $stok['balanceUnit'] ))
                            }}
                        </td>
                        @if ($loop->first)
                        <td rowspan="{{ count($tscStock) }}" style="text-align: center; vertical-align: middle;">
                            {{ number_format($totalTsc, 0, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
            
        @if(count($resellerStock) > 0 && ($filterGudang === 'semua' || $filterGudang === 'resel'))
            <div class="section-title">Marketing Reseller</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">Lokasi</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Satuan</th>
                        <th style="text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resellerStock as $stok)
                    <tr>
                        <td>{{ $stok['name'] }}</td>
                        <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
                        <td style="text-align: center;">
                            {{ 
                                preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                : trim(str_replace(['[', ']'], '', $stok['balanceUnit'] ))
                            }}
                        </td>
                        @if ($loop->first)
                        <td rowspan="{{ count($resellerStock) }}" style="text-align: center; vertical-align: middle;">
                            {{ number_format($totalReseller, 0, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
        @endif

        @if(count($konsinyasiStock) > 0 && ($filterGudang === 'semua' || $filterGudang === 'konsinyasi'))
            <div class="section-title">Konsinyasi</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">Lokasi</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Satuan</th>
                        <th style="text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($konsinyasiStock as $stok)
                    <tr>
                        <td>{{ $stok['name'] }}</td>
                        <td style="text-align: center;">{{ number_format($stok['balance']) }}</td>
                        <td style="text-align: center;">
                            {{ 
                                preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $stok['balanceUnit'] )))
                                : trim(str_replace(['[', ']'], '', $stok['balanceUnit'] ))
                            }}
                        </td>
                        @if ($loop->first)
                        <td rowspan="{{ count($konsinyasiStok) }}" style="text-align: center; vertical-align: middle;">
                            {{ number_format($totalKonsinyasi, 0, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(count($transitStock) > 0 && ($filterGudang === 'semua' || $filterGudang === 'trans'))
            <div class="section-title">Transit</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">Lokasi</th>
                        <th style="text-align: center;">Total</th>
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
</body>
</html>

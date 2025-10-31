<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; color: #555; margin-bottom: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 6px 8px;
        }
        th {
            background-color: #e9ecef;
            text-align: center;
        }
        td:nth-child(1),
        td:nth-child(3),
        td:nth-child(4),
        td:nth-child(5) {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="meta">Tanggal Cetak: {{ $date }}</div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $it)
                <tr>
                    <td>{{ $it['no'] ?? '-' }}</td>
                    <td>{{ $it['name'] ?? '-' }}</td>
                    <td>{{ $it['itemCategory']['name'] ?? '-' }}</td>
                    <td>Rp {{ number_format($it['unitPrice'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $it['availableToSell'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

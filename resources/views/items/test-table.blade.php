@extends('layout')

@section('content')
<table class="table table-bordered">
    <thead class="text-center">
        <tr>
            <th rowspan="4">Gambar</th>
            <th colspan="2">Kode Barang</th>
            <th colspan="2">Nama Barang</th>
        </tr>
        <tr>
            <td colspan="2">111111</td>
            <td colspan="2">21241</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">Harga</th>
            <th colspan="2">Posisi</th>
        </tr>
        <tr>
            <th>User</th>
            <th>Reseller</th>
            <th>Gudang</th>
            <th>Stok</th>
        </tr>
        <tr>
            <td>1000</td>
            <td>20000</td>
            <td>Ayani</td>
            <td>111</td>
        </tr>
    </tbody>
    {{-- <tbody>
        <tr>
            <td rowspan="{{ count($warehouses) + 1 }}" class="text-center">{{ $item['no'] }}</td>
            <td rowspan="{{ count($warehouses) + 1 }}">{{ $item['name'] }}</td>
            <td rowspan="{{ count($warehouses) + 1 }}">Rp {{ number_format($resellerPrice, 0, ',', '.') }}</td>
            <td rowspan="{{ count($warehouses) + 1 }}">
                <span class="text-muted text-decoration-line-through">
                    Rp {{ number_format($userPrice, 0, ',', '.') }}
                </span>
            </td>
            <td rowspan="{{ count($warehouses) + 1 }}" class="text-center">{{ $item['availableToSell'] }}</td>
        </tr>
        @foreach ($stokBaru as $data)
            <tr>
                <td>{{ $data['name'] }}</td>
                <td class="text-center">{{ number_format($data['balance']) }}</td>
            </tr>
        @endforeach
    </tbody> --}}
</table>
@endsection
@foreach ($items as $item)
    <tr onclick="window.location='{{ route('items.detail', ['encrypted' => Hashids::encode($item['id'])]) }}'" style="cursor: pointer;">
        <!-- Nomor -->
        <td class="text-center td-kode" style="width: 100px;">
            {{ $item['no'] ?? 'N/A' }}
        </td>

        <!-- Nama Barang -->
        <td class="text-start td-name" style="max-width: {{ $status === 'admin' ? '400px' : '150px' }};" title="{{ $item['name'] }}">
            <p class="text-xs font-weight-bold mb-0">
                {{ $item['name'] ?? '-' }}
            </p>
        </td>

        <!-- Harga (Rp + Angka) -->
        <td class="td-harga" style="width: 150px;">
            <div class="harga-grid">
                <span class="harga-rp">Rp</span>
                <span class="harga-nominal">
                {{ is_numeric($item['branchPrice'] ?? null) ? number_format($item['branchPrice'], 0, ',', '.') : '-' }}
                </span>
            </div>
        </td>

        <!-- Stok -->
        <td class="text-center td-stok" style="width: 80px;">
            <p class="text-xs font-weight-bold mb-0">
                {{ $item['availableToSell'] ?? '-' }}
            </p>
        </td>

        <td class="text-center td-satuan" style="width: 100px;">
            <p class="text-xs font-weight-bold mb-0">
                {{
                    preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $item['availableToSellInAllUnit'], $matches)
                    && count($matches[0]) > 1
                        ? trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit']))
                        : preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'])))
                }}
            </p>
        </td>

        <!-- Tombol Detail -->
        {{-- <td class="text-center" style="width: 40px;">
            <a href="{{ route('items.detail', ['encrypted' => Hashids::encode($item['id'])]) }}" class="btn btn-warning btn-sm">
                Detail
            </a>
        </td> --}}
    </tr>
@endforeach

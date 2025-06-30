@foreach ($items as $item)
    <tr onclick="window.location='{{ route('items.detail', ['encrypted' => Hashids::encode($item['id'])]) }}'" style="cursor: pointer;">
        <!-- Nomor -->
        <td class="text-center" style="width: 100px;">
            {{ $item['no'] ?? 'N/A' }}
        </td>

        <!-- Nama Barang -->
        <td class="text-truncate" style="max-width: {{ $status === 'admin' ? '400px' : '150px' }};" title="{{ $item['name'] }}">
            <p class="text-xs font-weight-bold mb-0">
                {{ Str::limit($item['name'], $status === 'admin' ? 100 : 100, '...') ?? '-' }}
            </p>
        </td>

        <!-- Harga (Rp + Angka) -->
        <td class="text-end" style="width: 130px;">
            <p class="text-xs font-weight-bold mb-0">
                {{ is_numeric($item['branchPrice'] ?? null) ? number_format($item['branchPrice'], 0, ',', '.') : '-' }}
            </p>
        </td>

        <!-- Stok -->
        <td class="text-center" style="width: 80px;">
            <p class="text-xs font-weight-bold mb-0">
                {{ $item['availableToSell'] ?? '-' }}
            </p>
        </td>

        <td class="text-center" style="width: 80px;">
            <p class="text-xs font-weight-bold mb-0">
                {{-- @php
                    $balance = $item['availableToSell'] ?? 0;
                    $unit1 = $item['unit1Name'] ?? 'PCS';
                    $unit2 = $item['unit2Name'] ?? null;
                    $ratio2 = $item['ratio2'] ?? null;

                    $display = '';

                    if ($unit2 && $ratio2 && is_numeric($ratio2) && $ratio2 > 0) {
                        $pack = floor($balance / $ratio2);
                        $pcs = $balance % $ratio2;

                        if ($pack > 0) {
                            $display .= "{$pack} {$unit2}";
                        }

                        if ($pcs > 0) {
                            $display .= ($pack > 0 ? ' ' : '') . "{$pcs} {$unit1}";
                        }

                        if ($balance == 0) {
                            $display = "0 {$unit1}";
                        }
                    } else {
                        // Logika khusus: kalau angka = yang tertulis di satuan, cukup tampilkan nama satuannya saja
                        if (preg_match('/^(\d+)\s*' . preg_quote($unit1, '/') . '$/i', "{$balance} {$unit1}", $match)) {
                            if ((int)$match[1] === (int)$balance) {
                                $display = $unit1; // tampil hanya satuannya saja
                            } else {
                                $display = "{$balance} {$unit1}";
                            }
                        } else {
                            // fallback
                            $display = "{$balance} {$unit1}";
                        }
                    }

                    echo $display;
                @endphp --}}
                    {{ 
                        preg_match('/^0\s+PACK$/i', trim($item['availableToSellInAllUnit'])) 
                        ? 'PACK'
                        : (
                            preg_match('/^[\d.,]+\s+(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG)$/i', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] )))
                            ? preg_replace('/^[\d.,]+\s+/', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] )))
                            : trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ))
                        )
                    }}
            </p>
        </td>

        <!-- Tombol Detail -->
        <td class="text-center" style="width: 40px;">
            <a href="{{ route('items.detail', ['encrypted' => Hashids::encode($item['id'])]) }}" class="btn btn-warning btn-sm">
                Detail
            </a>
        </td>
    </tr>
@endforeach
